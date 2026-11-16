<?php
declare(strict_types=1);

final class OrderService
{
    public static function createOrderFromCart(
        int $userId,
        int $merchantId,
        array $addressData,
        string $paymentMethod,
        ?string $voucherCode,
        string $customerNotes
    ): array {
        $pdo = Database::connection();

        $cartStmt = $pdo->prepare('SELECT * FROM carts WHERE user_id = ? AND merchant_id = ? LIMIT 1');
        $cartStmt->execute([$userId, $merchantId]);
        $cart = $cartStmt->fetch();
        if (!$cart) {
            return ['ok' => false, 'message' => 'Keranjang belanja untuk toko ini kosong.'];
        }

        $itemsStmt = $pdo->prepare(
            'SELECT ci.*, p.name AS product_name, p.price, p.promo_price, p.stock, p.is_available,
                    pv.variant_name, COALESCE(pv.additional_price, 0) AS additional_price
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id
             LEFT JOIN product_variants pv ON pv.id = ci.variant_id
             WHERE ci.cart_id = ?'
        );
        $itemsStmt->execute([$cart['id']]);
        $items = $itemsStmt->fetchAll();

        if (empty($items)) {
            return ['ok' => false, 'message' => 'Tidak ada produk di keranjang.'];
        }

        $mStmt = $pdo->prepare('SELECT * FROM merchants WHERE id = ? AND deleted_at IS NULL LIMIT 1');
        $mStmt->execute([$merchantId]);
        $merchant = $mStmt->fetch();
        if (!$merchant || !$merchant['is_open']) {
            return ['ok' => false, 'message' => 'Merchant sedang tutup atau tidak tersedia.'];
        }

        $district = null;
        if (!empty($addressData['district_id'])) {
            $dStmt = $pdo->prepare('SELECT * FROM districts WHERE id = ? AND is_active = 1 LIMIT 1');
            $dStmt->execute([$addressData['district_id']]);
            $district = $dStmt->fetch() ?: null;
        }

        $voucher = null;
        if ($voucherCode !== null && trim($voucherCode) !== '') {
            $vStmt = $pdo->prepare(
                'SELECT * FROM vouchers
                 WHERE code = ? AND is_active = 1 AND deleted_at IS NULL
                   AND NOW() BETWEEN start_date AND end_date
                   AND used_count < quota
                 LIMIT 1'
            );
            $vStmt->execute([strtoupper(trim($voucherCode))]);
            $voucher = $vStmt->fetch() ?: null;
            if (!$voucher) {
                return ['ok' => false, 'message' => 'Kode voucher tidak valid atau sudah habis kuota.'];
            }
        }

        $distanceKm = 2.0;
        if (!empty($merchant['latitude']) && !empty($merchant['longitude']) && !empty($addressData['latitude']) && !empty($addressData['longitude'])) {
            $distanceKm = haversine_km(
                (float)$merchant['latitude'],
                (float)$merchant['longitude'],
                (float)$addressData['latitude'],
                (float)$addressData['longitude']
            );
        }

        $calc = PricingService::calculate($items, $merchant, $district, $distanceKm, $voucher);
        $minTrx = (float)get_setting('min_transaction_amount', '15000');
        if ($calc['subtotal'] < $minTrx) {
            return ['ok' => false, 'message' => 'Minimum subtotal transaksi adalah ' . rupiah($minTrx) . '.'];
        }

        $userRow = current_user();
        $isDemo = ((int)($userRow['is_demo'] ?? 0) === 1 || (int)$merchant['is_demo'] === 1) ? 1 : 0;
        $initialStatus = $paymentMethod === 'cod' ? 'menunggu_konfirmasi_merchant' : 'menunggu_pembayaran';
        $orderCode = 'DK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $pdo->beginTransaction();
        try {
            // Cek stok
            foreach ($items as $it) {
                if (!$it['is_available'] || (int)$it['stock'] < (int)$it['quantity']) {
                    throw new RuntimeException('Stok produk "' . $it['product_name'] . '" tidak mencukupi.');
                }
            }

            $ordStmt = $pdo->prepare(
                'INSERT INTO orders (
                    order_code, user_id, merchant_id, district_id, voucher_id,
                    recipient_name, recipient_phone, delivery_address, delivery_lat, delivery_lng, distance_km,
                    subtotal, merchant_discount, voucher_discount, delivery_fee, service_fee,
                    platform_commission, merchant_net_income, driver_net_income, total_amount,
                    payment_method, status, customer_notes, is_demo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $ordStmt->execute([
                $orderCode,
                $userId,
                $merchantId,
                $addressData['district_id'] ?? $merchant['district_id'],
                $voucher['id'] ?? null,
                $addressData['recipient_name'],
                $addressData['recipient_phone'],
                $addressData['full_address'],
                $addressData['latitude'] ?? null,
                $addressData['longitude'] ?? null,
                $distanceKm,
                $calc['subtotal'],
                $calc['merchant_discount'],
                $calc['voucher_discount'],
                $calc['delivery_fee'],
                $calc['service_fee'],
                $calc['platform_commission'],
                $calc['merchant_net_income'],
                $calc['driver_net_income'],
                $calc['total_amount'],
                $paymentMethod,
                $initialStatus,
                $customerNotes,
                $isDemo,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $oiStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, original_price, unit_price, quantity, subtotal, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ?, sold_count = sold_count + ? WHERE id = ?');

            foreach ($items as $it) {
                $orig = (float)$it['price'];
                $promo = ($it['promo_price'] !== null && (float)$it['promo_price'] > 0) ? (float)$it['promo_price'] : $orig;
                $unit = $promo + (float)$it['additional_price'];
                $lineSub = $unit * (int)$it['quantity'];
                $oiStmt->execute([
                    $orderId,
                    $it['product_id'],
                    $it['variant_id'],
                    $it['product_name'],
                    $it['variant_name'],
                    $orig,
                    $unit,
                    $it['quantity'],
                    $lineSub,
                    $it['notes'],
                ]);
                $stockStmt->execute([$it['quantity'], $it['quantity'], $it['product_id']]);
            }

            // Riwayat status awal
            $pdo->prepare(
                'INSERT INTO order_status_histories (order_id, from_status, to_status, changed_by, actor_role, notes) VALUES (?, NULL, ?, ?, "user", ?)'
            )->execute([$orderId, $initialStatus, $userId, 'Pesanan dibuat oleh pelanggan']);

            // Pembayaran
            $payCode = 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $pdo->prepare(
                'INSERT INTO payments (order_id, payment_code, method, amount, status) VALUES (?, ?, ?, ?, "pending")'
            )->execute([$orderId, $payCode, $paymentMethod, $calc['total_amount']]);

            // Pengiriman
            $pin = (string)random_int(1000, 9999);
            $pdo->prepare(
                'INSERT INTO deliveries (order_id, pickup_address, pickup_lat, pickup_lng, dropoff_address, dropoff_lat, dropoff_lng, delivery_fee, driver_earning, verification_pin, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "waiting_driver")'
            )->execute([
                $orderId,
                $merchant['address'],
                $merchant['latitude'],
                $merchant['longitude'],
                $addressData['full_address'],
                $addressData['latitude'] ?? null,
                $addressData['longitude'] ?? null,
                $calc['delivery_fee'],
                $calc['driver_net_income'],
                $pin,
            ]);

            // Komisi
            $pdo->prepare(
                'INSERT INTO commissions (order_id, merchant_id, gross_subtotal, commission_rate, platform_commission_amount, service_fee_amount, merchant_net_amount, driver_fee_rate, driver_net_amount, platform_delivery_cut, is_demo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $orderId,
                $merchantId,
                $calc['subtotal'],
                $calc['commission_rate'],
                $calc['platform_commission'],
                $calc['service_fee'],
                $calc['merchant_net_income'],
                $calc['driver_share_rate'],
                $calc['driver_net_income'],
                $calc['platform_delivery_cut'],
                $isDemo,
            ]);

            if ($voucher !== null && $calc['voucher_discount'] > 0) {
                $pdo->prepare('INSERT INTO voucher_usages (voucher_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)')
                    ->execute([$voucher['id'], $userId, $orderId, $calc['voucher_discount']]);
                $pdo->prepare('UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?')
                    ->execute([$voucher['id']]);
            }

            // Kosongkan keranjang
            $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cart['id']]);
            $pdo->prepare('DELETE FROM carts WHERE id = ?')->execute([$cart['id']]);

            $pdo->commit();

            notify_user((int)$merchant['user_id'], 'Pesanan Baru #' . $orderCode, 'Ada pesanan baru masuk sebesar ' . rupiah($calc['total_amount']), route_url('merchant/orders'));
            log_activity('CREATE_ORDER', 'orders', "Pesanan {$orderCode} berhasil dibuat.", $userId, 'user');

            return ['ok' => true, 'order_id' => $orderId, 'order_code' => $orderCode];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public static function transitionStatus(
        int $orderId,
        string $toStatus,
        int $actorId,
        string $actorRole,
        string $notes = '',
        ?int $driverId = null
    ): array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
        }

        $fromStatus = $order['status'];
        $allowedNext = ORDER_STATUS_TRANSITIONS[$fromStatus] ?? [];
        if (!in_array($toStatus, $allowedNext, true)) {
            return ['ok' => false, 'message' => "Transisi status tidak diizinkan dari '{$fromStatus}' ke '{$toStatus}'."];
        }

        $pdo->beginTransaction();
        try {
            $extraSql = '';
            $params = [$toStatus];

            if ($driverId !== null) {
                $extraSql .= ', driver_id = ?';
                $params[] = $driverId;
            }
            if ($toStatus === 'dibatalkan' && $notes !== '') {
                $extraSql .= ', cancel_reason = ?';
                $params[] = $notes;
            }
            if ($toStatus === 'selesai') {
                $extraSql .= ', completed_at = NOW()';
                if ($actorRole === 'user') {
                    $extraSql .= ', customer_confirmed_at = NOW()';
                }
            }

            $params[] = $orderId;
            $pdo->prepare("UPDATE orders SET status = ?{$extraSql} WHERE id = ?")->execute($params);

            $pdo->prepare(
                'INSERT INTO order_status_histories (order_id, from_status, to_status, changed_by, actor_role, notes) VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$orderId, $fromStatus, $toStatus, $actorId, $actorRole, $notes]);

            // Sinkronisasi ke tabel deliveries & commissions & transactions
            $deliveryMap = [
                'driver_menuju_merchant' => 'heading_to_merchant',
                'pesanan_diambil' => 'picked_up',
                'dalam_pengiriman' => 'on_the_way',
                'selesai' => 'delivered',
                'dibatalkan' => 'cancelled',
            ];

            if (isset($deliveryMap[$toStatus])) {
                $delivStatus = $deliveryMap[$toStatus];
                $dExtra = '';
                $dParams = [$delivStatus];
                if ($driverId !== null) {
                    $dExtra .= ', driver_id = ?, accepted_at = COALESCE(accepted_at, NOW())';
                    $dParams[] = $driverId;
                }
                if ($delivStatus === 'picked_up') {
                    $dExtra .= ', picked_up_at = NOW()';
                }
                if ($delivStatus === 'delivered') {
                    $dExtra .= ', delivered_at = NOW()';
                }
                $dParams[] = $orderId;
                $pdo->prepare("UPDATE deliveries SET status = ?{$dExtra} WHERE order_id = ?")->execute($dParams);

                $delivId = $pdo->query('SELECT id FROM deliveries WHERE order_id = ' . (int)$orderId)->fetchColumn();
                if ($delivId) {
                    $pdo->prepare('INSERT INTO delivery_status_histories (delivery_id, status, notes) VALUES (?, ?, ?)')
                        ->execute([$delivId, $delivStatus, $notes ?: order_status_label($toStatus)]);
                }
            }

            if ($driverId !== null) {
                $pdo->prepare('UPDATE commissions SET driver_id = ? WHERE order_id = ?')->execute([$driverId, $orderId]);
            }

            if ($toStatus === 'selesai') {
                // Tandai COD verified saat selesai dan catat transaksi keuangan terpisah
                if ($order['payment_method'] === 'cod') {
                    $pdo->prepare('UPDATE payments SET status = "verified", paid_at = COALESCE(paid_at, NOW()) WHERE order_id = ?')
                        ->execute([$orderId]);
                }

                if (!empty($order['driver_id'])) {
                    $pdo->prepare('UPDATE drivers SET total_deliveries = total_deliveries + 1 WHERE id = ?')->execute([$order['driver_id']]);
                }

                // Catat ke tabel transactions (order_payment, merchant_payout, driver_earning, platform_fee)
                $mUser = $pdo->query('SELECT user_id FROM merchants WHERE id = ' . (int)$order['merchant_id'])->fetchColumn();
                $trxExists = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE order_id = ?');
                $trxExists->execute([$orderId]);
                if ((int)$trxExists->fetchColumn() === 0) {
                    $isDemo = (int)$order['is_demo'];
                    $trxStmt = $pdo->prepare(
                        'INSERT INTO transactions (transaction_code, order_id, user_id, type, amount, direction, description, status, is_demo)
                         VALUES (?, ?, ?, ?, ?, ?, ?, "settled", ?)'
                    );
                    $trxStmt->execute(['TRX-' . $orderId . '-PAY', $orderId, $order['user_id'], 'order_payment', $order['total_amount'], 'credit', 'Pembayaran selesai pesanan ' . $order['order_code'], $isDemo]);
                    if ($mUser) {
                        $trxStmt->execute(['TRX-' . $orderId . '-MRC', $orderId, $mUser, 'merchant_payout', $order['merchant_net_income'], 'credit', 'Pendapatan bersih merchant pesanan ' . $order['order_code'], $isDemo]);
                    }
                    if (!empty($order['driver_id'])) {
                        $dUser = $pdo->query('SELECT user_id FROM drivers WHERE id = ' . (int)$order['driver_id'])->fetchColumn();
                        if ($dUser) {
                            $trxStmt->execute(['TRX-' . $orderId . '-DRV', $orderId, $dUser, 'driver_earning', $order['driver_net_income'], 'credit', 'Pendapatan kurir pesanan ' . $order['order_code'], $isDemo]);
                        }
                    }
                    $trxStmt->execute(['TRX-' . $orderId . '-PLT', $orderId, 1, 'platform_fee', (float)$order['platform_commission'] + (float)$order['service_fee'], 'credit', 'Komisi + biaya layanan pesanan ' . $order['order_code'], $isDemo]);
                }
            }

            $pdo->commit();
            notify_user((int)$order['user_id'], 'Pembaruan Pesanan #' . $order['order_code'], 'Status pesanan berubah menjadi: ' . order_status_label($toStatus), route_url('user/order-detail', ['id' => $orderId]));
            log_activity('UPDATE_ORDER_STATUS', 'orders', "Pesanan {$order['order_code']}: {$fromStatus} -> {$toStatus}", $actorId, $actorRole);

            return ['ok' => true, 'message' => 'Status pesanan diperbarui ke ' . order_status_label($toStatus)];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()];
        }
    }
}

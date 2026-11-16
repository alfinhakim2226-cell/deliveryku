<?php
declare(strict_types=1);

final class PricingService
{
    public static function calculate(
        array $cartItems,
        array $merchant,
        ?array $district,
        float $distanceKm,
        ?array $voucher = null
    ): array {
        $subtotal = 0.0;
        $merchantDiscount = 0.0;

        foreach ($cartItems as $item) {
            $qty = (int)$item['quantity'];
            $origPrice = (float)$item['price'];
            $promoPrice = isset($item['promo_price']) && $item['promo_price'] !== null && (float)$item['promo_price'] > 0
                ? (float)$item['promo_price']
                : $origPrice;
            $variantAdd = (float)($item['additional_price'] ?? 0);

            $unitPrice = $promoPrice + $variantAdd;
            $subtotal += $unitPrice * $qty;
            if ($promoPrice < $origPrice) {
                $merchantDiscount += ($origPrice - $promoPrice) * $qty;
            }
        }

        $baseDelivery = $district ? (float)$district['base_delivery_fee'] : (float)get_setting('base_delivery_fee', '6000');
        $perKmFee = $district ? (float)$district['per_km_fee'] : (float)get_setting('per_km_delivery_fee', '2500');
        $extraKm = max(0.0, $distanceKm - 1.0);
        $deliveryFee = round($baseDelivery + ($extraKm * $perKmFee), -2);

        $serviceFee = (float)get_setting('service_fee_amount', '2000');

        $voucherDiscount = 0.0;
        if ($voucher !== null && $subtotal >= (float)$voucher['min_transaction']) {
            if ($voucher['discount_type'] === 'percent') {
                $voucherDiscount = round($subtotal * ((float)$voucher['discount_value'] / 100.0), 0);
                if (!empty($voucher['max_discount']) && $voucherDiscount > (float)$voucher['max_discount']) {
                    $voucherDiscount = (float)$voucher['max_discount'];
                }
            } else {
                $voucherDiscount = (float)$voucher['discount_value'];
            }
            $voucherDiscount = min($voucherDiscount, $subtotal);
        }

        $commissionRate = isset($merchant['commission_rate']) && $merchant['commission_rate'] !== null
            ? (float)$merchant['commission_rate']
            : (float)get_setting('default_commission_rate', '15');
        $driverShareRate = (float)get_setting('driver_share_rate', '85');

        $platformCommission = round($subtotal * ($commissionRate / 100.0), 2);
        $merchantNetIncome = round($subtotal - $platformCommission, 2);
        $driverNetIncome = round($deliveryFee * ($driverShareRate / 100.0), 2);
        $platformDeliveryCut = round($deliveryFee - $driverNetIncome, 2);

        $totalAmount = max(0.0, round($subtotal - $voucherDiscount + $deliveryFee + $serviceFee, 2));

        return [
            'subtotal' => $subtotal,
            'merchant_discount' => $merchantDiscount,
            'voucher_discount' => $voucherDiscount,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'commission_rate' => $commissionRate,
            'platform_commission' => $platformCommission,
            'merchant_net_income' => $merchantNetIncome,
            'driver_share_rate' => $driverShareRate,
            'driver_net_income' => $driverNetIncome,
            'platform_delivery_cut' => $platformDeliveryCut,
            'total_amount' => $totalAmount,
        ];
    }
}

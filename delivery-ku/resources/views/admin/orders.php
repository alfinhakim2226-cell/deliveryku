<h4 class="fw-bold mb-3">Pemantauan Pesanan</h4>
<div class="dk-card p-3">
    <div class="table-responsive"><table class="table table-sm small align-middle"><thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Merchant</th><th>Total</th><th>Status</th><th>Perbarui</th></tr></thead><tbody>
        <?php foreach ($orders as $o): $nx = ORDER_STATUS_TRANSITIONS[$o['status']] ?? []; ?>
            <tr>
                <td class="fw-semibold"><?= e($o['order_code']) ?></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= e($o['store_name']) ?></td>
                <td><?= e(rupiah($o['total_amount'])) ?></td>
                <td><?= order_status_badge($o['status']) ?></td>
                <td><?php if (!empty($nx)): ?>
                    <form method="POST" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <select name="to_status" class="form-select form-select-sm"><?php foreach ($nx as $st): ?><option value="<?= e($st) ?>"><?= e(order_status_label($st)) ?></option><?php endforeach; ?></select>
                        <button class="btn btn-primary btn-sm">Simpan</button>
                    </form>
                <?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</div>

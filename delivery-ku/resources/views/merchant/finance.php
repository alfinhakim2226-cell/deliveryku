<h4 class="fw-bold mb-3">Rekap Penjualan Harian & Bulanan</h4>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Rekap Harian (30 Hari Terakhir)</h6>
            <div class="table-responsive"><table class="table table-sm small"><thead class="table-light"><tr><th>Tanggal</th><th>Pesanan</th><th>Omzet Kotor</th><th>Komisi Platform</th><th>Pendapatan Bersih</th></tr></thead><tbody>
                <?php if (empty($daily)): ?><tr><td colspan="5" class="text-center text-muted">Belum ada transaksi selesai.</td></tr><?php else: foreach ($daily as $d): ?>
                    <tr><td><?= e($d['tgl']) ?></td><td><?= (int)$d['jml'] ?></td><td><?= e(rupiah($d['omzet'])) ?></td><td class="text-danger"><?= e(rupiah($d['komisi'])) ?></td><td class="text-success fw-bold"><?= e(rupiah($d['bersih'])) ?></td></tr>
                <?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Rekap Bulanan</h6>
            <div class="table-responsive"><table class="table table-sm small"><thead class="table-light"><tr><th>Bulan</th><th>Order</th><th>Omzet</th><th>Bersih</th></tr></thead><tbody>
                <?php foreach ($monthly as $m): ?>
                    <tr><td><?= e($m['bulan']) ?></td><td><?= (int)$m['jml'] ?></td><td><?= e(rupiah($m['omzet'])) ?></td><td class="text-success fw-bold"><?= e(rupiah($m['bersih'])) ?></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
</div>

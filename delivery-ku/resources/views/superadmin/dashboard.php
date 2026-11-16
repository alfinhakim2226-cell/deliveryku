<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold mb-0">Pusat Kendali Superadmin</h4>
        <small class="text-muted">Ringkasan aktual operasional platform Delivery Ku (Mode Laporan: <?= $includeDemo ? 'Termasuk Data Demo' : 'Produksi Murni' ?>)</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(route_url('superadmin/reports')) ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Laporan Keuangan</a>
        <a href="<?= e(route_url('superadmin/backup', ['download' => 1])) ?>" class="btn btn-dk-green btn-sm"><i class="bi bi-database-down me-1"></i>Backup SQL</a>
    </div>
</div>

<!-- KPI Utama -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Total Pelanggan', number_format($stats['total_customers']), 'bi-people-fill', 'primary'],
        ['Merchant Aktif', number_format($stats['active_merchants']), 'bi-shop', 'success'],
        ['Driver Aktif', number_format($stats['active_drivers']), 'bi-scooter', 'info'],
        ['Pesanan Hari Ini', number_format($stats['orders_today']), 'bi-bag-check-fill', 'primary'],
        ['Menunggu Konfirmasi', number_format($stats['orders_waiting']), 'bi-hourglass-split', 'warning'],
        ['Sedang Diproses', number_format($stats['orders_processing']), 'bi-gear-fill', 'secondary'],
        ['Dalam Pengiriman', number_format($stats['orders_delivering']), 'bi-truck', 'info'],
        ['Pesanan Selesai', number_format($stats['orders_completed']), 'bi-check-circle-fill', 'success'],
        ['Pesanan Dibatalkan', number_format($stats['orders_cancelled']), 'bi-x-circle-fill', 'danger'],
        ['Transaksi Hari Ini', rupiah($stats['transactions_today']), 'bi-cash-stack', 'success'],
        ['Total Omzet Platform', rupiah($stats['platform_revenue']), 'bi-wallet2', 'primary'],
        ['Total Komisi Platform', rupiah($stats['platform_commission']), 'bi-percent', 'success'],
    ];
    foreach ($kpis as $k): ?>
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card dk-stat-card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small"><?= e($k[0]) ?></div>
                        <div class="fs-5 fw-bold mt-1"><?= e((string)$k[1]) ?></div>
                    </div>
                    <div class="rounded-3 p-2 bg-<?= e($k[3]) ?> bg-opacity-10 text-<?= e($k[3]) ?>">
                        <i class="bi <?= e($k[2]) ?> fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 3 Grafik Chart.js -->
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="dk-card p-3 h-100">
            <h6 class="fw-bold mb-3">Grafik Pertumbuhan Transaksi (7 Hari)</h6>
            <canvas id="chartDailyTrx" height="190"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dk-card p-3 h-100">
            <h6 class="fw-bold mb-3">Pendapatan & Komisi Bulanan</h6>
            <canvas id="chartMonthlyRev" height="190"></canvas>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="dk-card p-3 h-100">
            <h6 class="fw-bold mb-3">Jumlah Pengguna per Role</h6>
            <canvas id="chartUserRoles" height="190"></canvas>
        </div>
    </div>
</div>

<!-- Daftar Terbaru & Peringkat -->
<div class="row g-3">
    <div class="col-lg-7">
        <div class="dk-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Pesanan Terbaru</h6>
                <a href="<?= e(route_url('superadmin/orders')) ?>" class="small text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Kode</th><th>Pelanggan</th><th>Merchant</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi produksi.</td></tr>
                    <?php else: foreach ($recentOrders as $o): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($o['order_code']) ?></td>
                            <td><?= e($o['customer_name']) ?></td>
                            <td><?= e($o['store_name']) ?></td>
                            <td><?= e(rupiah($o['total_amount'])) ?></td>
                            <td><?= order_status_badge($o['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="dk-card p-3 mb-3">
            <h6 class="fw-bold mb-2">Merchant Paling Produktif</h6>
            <ul class="list-group list-group-flush small">
                <?php foreach ($topMerchants as $tm): ?>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span><i class="bi bi-shop text-primary me-1"></i><?= e($tm['store_name']) ?></span>
                        <span class="fw-semibold"><?= (int)$tm['total_orders'] ?> order (<?= e(rupiah($tm['total_net'])) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Driver Pengiriman Terbanyak</h6>
            <ul class="list-group list-group-flush small">
                <?php foreach ($topDrivers as $td): ?>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span><i class="bi bi-scooter text-success me-1"></i><?= e($td['full_name']) ?> (<?= e($td['license_plate']) ?>)</span>
                        <span class="fw-semibold"><?= (int)$td['total_deliveries'] ?> antar • ★ <?= e((string)$td['rating_avg']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-12">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Aktivitas Pengguna Terbaru</h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0 small">
                    <thead class="table-light"><tr><th>Waktu</th><th>Pengguna</th><th>Role</th><th>Aksi</th><th>Deskripsi</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentLogs as $l): ?>
                        <tr>
                            <td><?= e(format_indo_date($l['created_at'])) ?></td>
                            <td><?= e($l['full_name'] ?? 'Sistem') ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($l['role_slug']) ?></span></td>
                            <td class="fw-semibold"><?= e($l['action']) ?></td>
                            <td><?= e($l['description']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const dailyData = <?= json_encode($chartTrx) ?>;
new Chart(document.getElementById('chartDailyTrx'), {
    type: 'line',
    data: {
        labels: dailyData.map(x => x.tgl),
        datasets: [{
            label: 'Jumlah Pesanan',
            data: dailyData.map(x => Number(x.total_orders)),
            borderColor: '#1677E8',
            backgroundColor: 'rgba(22,119,232,0.12)',
            fill: true,
            tension: 0.35
        }]
    }
});

const monthlyData = <?= json_encode(array_reverse($chartMonthly)) ?>;
new Chart(document.getElementById('chartMonthlyRev'), {
    type: 'bar',
    data: {
        labels: monthlyData.map(x => x.bulan),
        datasets: [
            { label: 'Omzet (Rp)', data: monthlyData.map(x => Number(x.omzet)), backgroundColor: '#1677E8' },
            { label: 'Komisi (Rp)', data: monthlyData.map(x => Number(x.komisi)), backgroundColor: '#22B573' }
        ]
    }
});

const roleData = <?= json_encode($userComposition) ?>;
new Chart(document.getElementById('chartUserRoles'), {
    type: 'doughnut',
    data: {
        labels: roleData.map(x => x.role_name),
        datasets: [{
            data: roleData.map(x => Number(x.total)),
            backgroundColor: ['#123B70','#1677E8','#22B573','#0DCAF0','#FFC107']
        }]
    }
});
</script>

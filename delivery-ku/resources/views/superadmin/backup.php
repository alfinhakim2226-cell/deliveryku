<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Backup Database MySQL (Ekspor SQL)</h4>
    <a href="<?= e(route_url('superadmin/backup', ['download' => 1])) ?>" class="btn btn-dk-green">
        <i class="bi bi-download me-1"></i> Unduh File .SQL Lengkap
    </a>
</div>
<div class="dk-card p-3">
    <p class="small text-muted">Kompatibel dengan phpMyAdmin InfinityFree tanpa membutuhkan akses shell <code>mysqldump</code>.</p>
    <div class="table-responsive">
        <table class="table table-sm small">
            <thead class="table-light"><tr><th>Nama Tabel</th><th>Engine</th><th>Estimasi Baris</th><th>Ukuran Data</th></tr></thead>
            <tbody>
            <?php foreach ($tables as $t): ?>
                <tr>
                    <td class="fw-semibold"><?= e($t['Name']) ?></td>
                    <td><?= e($t['Engine']) ?></td>
                    <td><?= number_format((int)$t['Rows']) ?></td>
                    <td><?= number_format(((int)$t['Data_length'] + (int)$t['Index_length']) / 1024, 1) ?> KB</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

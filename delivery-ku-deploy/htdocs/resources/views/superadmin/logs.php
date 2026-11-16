<h4 class="fw-bold mb-3">Log Aktivitas Sistem & Administrator</h4>
<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle small">
            <thead class="table-light"><tr><th>Waktu</th><th>Pengguna</th><th>Role</th><th>Aksi</th><th>Modul</th><th>Deskripsi</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td><?= e(format_indo_date($l['created_at'])) ?></td>
                    <td><?= e($l['full_name'] ?? 'Guest') ?><br><small class="text-muted"><?= e($l['email'] ?? '') ?></small></td>
                    <td><span class="badge text-bg-secondary"><?= e($l['role_slug']) ?></span></td>
                    <td class="fw-bold"><?= e($l['action']) ?></td>
                    <td><?= e($l['module']) ?></td>
                    <td><?= e($l['description']) ?></td>
                    <td><code><?= e($l['ip_address']) ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

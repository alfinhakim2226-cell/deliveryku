<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="fw-bold mb-0">Manajemen Pengguna, Admin & Hak Akses</h4>
    <button class="btn btn-dk-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
        <i class="bi bi-person-plus me-1"></i> Tambah Akun Baru
    </button>
</div>

<div class="dk-card p-3 mb-3">
    <form class="row g-2" method="GET" action="<?= e(base_url('index.php')) ?>">
        <input type="hidden" name="route" value="superadmin/users">
        <div class="col-md-4">
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Cari nama, email, atau HP...">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm">
                <option value="">Semua Role</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= e($r['slug']) ?>" <?= $roleFilter === $r['slug'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
        </div>
    </form>
</div>

<div class="dk-card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light">
                <tr><th>ID</th><th>Nama</th><th>Email / HP</th><th>Role</th><th>Status</th><th>Tipe</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= (int)$u['id'] ?></td>
                    <td class="fw-semibold"><?= e($u['full_name']) ?></td>
                    <td><?= e($u['email']) ?><br><span class="text-muted"><?= e($u['phone']) ?></span></td>
                    <td><span class="badge text-bg-primary"><?= e($u['role_name']) ?></span></td>
                    <td>
                        <span class="badge text-bg-<?= $u['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($u['status']) ?></span>
                    </td>
                    <td><?= (int)$u['is_demo'] === 1 ? '<span class="badge text-bg-warning">Demo</span>' : '<span class="badge text-bg-light border text-dark">Produksi</span>' ?></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="new_status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                                <button class="btn btn-outline-secondary btn-sm" title="Aktif/Nonaktif">
                                    <?= $u['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" data-confirm="Reset kata sandi akun ini ke DeliveryKu2026!?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="new_password" value="DeliveryKu2026!">
                                <button class="btn btn-outline-warning btn-sm">Reset Pass</button>
                            </form>
                            <?php if ($u['role_slug'] === 'admin'): ?>
                                <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalPerm<?= (int)$u['id'] ?>">Izin Modul</button>
                            <?php endif; ?>
                            <?php if ((int)$u['id'] !== (int)current_user()['id']): ?>
                                <form method="POST" class="d-inline" data-confirm="Hapus akun ini secara soft delete?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($u['role_slug'] === 'admin'): ?>
                            <div class="modal fade" id="modalPerm<?= (int)$u['id'] ?>" tabindex="-1">
                                <div class="modal-dialog"><div class="modal-content">
                                    <form method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update_admin_perms">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <div class="modal-header"><h6 class="modal-title fw-bold">Hak Akses Modul Admin: <?= e($u['full_name']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <?php $assigned = $adminPerms[$u['id']] ?? []; foreach ($permissions as $p): ?>
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= (int)$p['id'] ?>" id="ap<?= (int)$u['id'] ?>_<?= (int)$p['id'] ?>" <?= in_array((int)$p['id'], $assigned, true) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="ap<?= (int)$u['id'] ?>_<?= (int)$p['id'] ?>"><?= e($p['name']) ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="modal-footer"><button type="submit" class="btn btn-dk-primary btn-sm">Simpan Hak Akses</button></div>
                                    </form>
                                </div></div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="modal-header"><h6 class="modal-title fw-bold">Tambah Akun Baru</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2"><label class="form-label">Role</label><select name="role_id" class="form-select"><?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Nama Lengkap</label><input type="text" name="full_name" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">No. HP</label><input type="text" name="phone" class="form-control" required></div>
                <div class="mb-2"><label class="form-label">Kata Sandi</label><input type="password" name="password" class="form-control" value="DeliveryKu2026!" required></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-dk-primary">Simpan Akun</button></div>
        </form>
    </div></div>
</div>

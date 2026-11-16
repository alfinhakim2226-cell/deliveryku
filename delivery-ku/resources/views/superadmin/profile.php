<h4 class="fw-bold mb-3">Profil Superadmin</h4>
<div class="dk-card p-4" style="max-width:540px">
    <form method="POST">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" name="full_name" value="<?= e($user['full_name']) ?>" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" value="<?= e($user['email']) ?>" class="form-control" disabled></div>
        <div class="mb-3"><label class="form-label">Nomor HP</label><input type="text" name="phone" value="<?= e($user['phone']) ?>" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Kata Sandi Baru (kosongkan jika tidak diganti)</label><input type="password" name="new_password" class="form-control"></div>
        <button class="btn btn-dk-primary">Simpan Perubahan</button>
    </form>
</div>

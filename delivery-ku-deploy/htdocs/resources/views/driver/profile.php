<h4 class="fw-bold mb-3">Profil, Kendaraan & Dokumen Verifikasi Kurir</h4>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="dk-card p-4">
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-2"><label class="form-label small">Nama Kurir</label><input type="text" value="<?= e($driver['full_name']) ?>" class="form-control form-control-sm" disabled></div>
                <div class="mb-2"><label class="form-label small">Nomor HP / WhatsApp</label><input type="text" name="phone" value="<?= e($driver['phone']) ?>" class="form-control form-control-sm" required></div>
                <div class="row g-2 mb-2">
                    <div class="col-4"><label class="form-label small">Kendaraan</label><select name="vehicle_type" class="form-select form-select-sm"><option value="Motor" <?= $driver['vehicle_type']==='Motor'?'selected':'' ?>>Motor</option><option value="Mobil" <?= $driver['vehicle_type']==='Mobil'?'selected':'' ?>>Mobil</option><option value="Sepeda Listrik" <?= $driver['vehicle_type']==='Sepeda Listrik'?'selected':'' ?>>Sepeda Listrik</option></select></div>
                    <div class="col-4"><label class="form-label small">Merk</label><input type="text" name="vehicle_brand" value="<?= e($driver['vehicle_brand'] ?? '') ?>" class="form-control form-control-sm"></div>
                    <div class="col-4"><label class="form-label small">No. Polisi</label><input type="text" name="license_plate" value="<?= e($driver['license_plate']) ?>" class="form-control form-control-sm" required></div>
                </div>
                <div class="mb-2"><label class="form-label small">Wilayah Kecamatan</label><select name="district_id" class="form-select form-select-sm"><?php foreach ($districts as $d): ?><option value="<?= (int)$d['id'] ?>" <?= (int)$driver['district_id']===(int)$d['id']?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
                <hr>
                <h6 class="fw-bold small">Unggah Dokumen Verifikasi (KTP / SIM / STNK)</h6>
                <div class="row g-2 mb-3">
                    <div class="col-5"><select name="doc_type" class="form-select form-select-sm"><option value="ktp">KTP</option><option value="sim">SIM C/A</option><option value="stnk">STNK</option><option value="foto_kendaraan">Foto Kendaraan</option></select></div>
                    <div class="col-7"><input type="file" name="doc_file" accept="image/*" class="form-control form-control-sm"></div>
                </div>
                <button class="btn btn-dk-primary btn-sm">Simpan Profil & Unggah</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="dk-card p-3">
            <h6 class="fw-bold mb-2">Riwayat Dokumen Diunggah</h6>
            <?php if (empty($docs)): ?><p class="text-muted small mb-0">Belum ada dokumen diunggah.</p><?php endif; ?>
            <?php foreach ($docs as $doc): ?>
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center small">
                    <span><strong><?= e(strtoupper($doc['doc_type'])) ?></strong> • <span class="badge text-bg-secondary"><?= e($doc['status']) ?></span></span>
                    <a href="<?= e(upload_url($doc['file_path'])) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Lihat</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

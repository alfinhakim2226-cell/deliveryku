<?php
declare(strict_types=1);

final class UploadService
{
    public static function uploadImage(array $file, string $subDir): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Tidak ada file valid yang diunggah.'];
        }

        $security = require BASE_PATH . '/app/config/security.php';
        if ((int)$file['size'] > $security['max_upload_bytes']) {
            return ['ok' => false, 'message' => 'Ukuran file melebihi batas maksimal 2 MB.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = $security['allowed_image_mimes'];
        if (!isset($allowed[$mime])) {
            return ['ok' => false, 'message' => 'Format file harus JPG, PNG, atau WEBP.'];
        }

        $ext = $allowed[$mime];
        $safeDir = preg_replace('/[^a-z0-9_-]/i', '', $subDir) ?: 'misc';
        $targetDir = BASE_PATH . '/public/uploads/' . $safeDir;
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $targetDir . '/' . $randomName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'message' => 'Gagal menyimpan file ke server.'];
        }

        return ['ok' => true, 'path' => $safeDir . '/' . $randomName];
    }
}

<?php
declare(strict_types=1);

final class ExportService
{
    public static function downloadCsv(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        // UTF-8 BOM agar dibuka rapi di Microsoft Excel
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public static function renderPrintableReport(string $title, array $headers, array $rows, array $summary = []): never
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>' . e($title) . '</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<style>@media print {.no-print{display:none!important;}}</style></head><body class="p-4">';
        echo '<div class="d-flex justify-content-between align-items-center mb-3">';
        echo '<div><h4 class="fw-bold text-primary mb-0">Delivery Ku — ' . e($title) . '</h4><small class="text-muted">Dicetak pada ' . date('d M Y H:i') . ' WIB</small></div>';
        echo '<button onclick="window.print()" class="btn btn-primary no-print">Cetak / Simpan sebagai PDF</button></div>';
        if (!empty($summary)) {
            echo '<div class="row g-2 mb-3">';
            foreach ($summary as $k => $v) {
                echo '<div class="col-md-3"><div class="border rounded p-2"><div class="small text-muted">' . e($k) . '</div><div class="fw-bold">' . e((string)$v) . '</div></div></div>';
            }
            echo '</div>';
        }
        echo '<table class="table table-bordered table-sm"><thead><tr>';
        foreach ($headers as $h) {
            echo '<th>' . e($h) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($r as $cell) {
                echo '<td>' . e((string)$cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }
}

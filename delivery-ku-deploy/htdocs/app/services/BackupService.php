<?php
declare(strict_types=1);

final class BackupService
{
    public static function downloadSqlBackup(): never
    {
        $pdo = Database::connection();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $filename = 'deliveryku_backup_' . date('Ymd_His') . '.sql';
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "-- Delivery Ku MySQL Backup\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . " WIB\n\n";
        echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $safeTable = str_replace('`', '', (string)$table);
            $createRow = $pdo->query("SHOW CREATE TABLE `{$safeTable}`")->fetch();
            $createSql = $createRow['Create Table'] ?? '';
            echo "DROP TABLE IF EXISTS `{$safeTable}`;\n";
            echo $createSql . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$safeTable}`")->fetchAll();
            if (!empty($rows)) {
                $cols = array_map(fn($c) => "`{$c}`", array_keys($rows[0]));
                $colList = implode(', ', $cols);
                foreach ($rows as $row) {
                    $vals = array_map(function ($v) use ($pdo) {
                        if ($v === null) {
                            return 'NULL';
                        }
                        return $pdo->quote((string)$v);
                    }, array_values($row));
                    echo "INSERT INTO `{$safeTable}` ({$colList}) VALUES (" . implode(', ', $vals) . ");\n";
                }
                echo "\n";
            }
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        log_activity('BACKUP_DB', 'system', "Ekspor backup SQL database ({$filename}).");
        exit;
    }
}

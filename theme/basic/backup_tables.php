<?php
include_once('./_common.php');

// Check Admin
if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

$backup_dir = G5_DATA_PATH . '/backup_customer_sync_' . date('YmdHis');
if (!is_dir($backup_dir)) {
    @mkdir($backup_dir, G5_DIR_PERMISSION, true);
}

// Tables to backup
$tables = ['g5_quote', 'g5_customer', 'g5_customer_status'];

echo "<pre>\n";
echo "Backup Directory: $backup_dir\n\n";

foreach ($tables as $table) {
    // Check if table exists
    $check = sql_fetch("SHOW TABLES LIKE '$table'");
    if (!$check) {
        echo "Table $table does not exist. Skipping.\n";
        continue;
    }

    $sql = " SELECT * FROM $table ";
    $result = sql_query($sql);

    $rows = [];
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }

    $file = $backup_dir . '/' . $table . '.json';
    $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    if (file_put_contents($file, $json)) {
        echo "[$table] Backup Success: " . count($rows) . " rows saved to " . basename($file) . "\n";
    } else {
        echo "[$table] Backup Failed: Could not write file.\n";
    }
}

echo "\nAll backups completed.\n</pre>";
?>
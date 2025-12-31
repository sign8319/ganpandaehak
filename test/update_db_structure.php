<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
define('G5_DISPLAY_SQL_ERROR', false);
include_once('../common.php');

$target_boards = ['beforeafter', 'ca_portfolio', 'type_portfolio'];

foreach ($target_boards as $bo_table) {
    $write_table = $g5['write_prefix'] . $bo_table;

    // Check if table exists
    $result = sql_query(" SHOW TABLES LIKE '{$write_table}' ");
    if (!sql_fetch_array($result)) {
        echo "[SKIP] Table '{$write_table}' does not exist.\n";
        continue;
    }

    // Check if column exists
    $result = sql_query(" SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_11' ");
    if (sql_fetch_array($result)) {
        echo "[SKIP] 'wr_11' already exists in '{$write_table}'.\n";
    } else {
        // Add column
        $sql = " ALTER TABLE `{$write_table}` ADD `wr_11` VARCHAR(255) NOT NULL DEFAULT '' AFTER `wr_10` ";
        sql_query($sql);
        echo "[OK] Added 'wr_11' to '{$write_table}'.\n";
    }
}
?>
<?php
include_once('./_common.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if column exists
$row = sql_fetch(" SHOW COLUMNS FROM `{$g5['write_prefix']}consult` LIKE 'portfolio_ids' ");
if ($row) {
    echo "Column 'portfolio_ids' already exists.";
} else {
    $sql = " ALTER TABLE `{$g5['write_prefix']}consult` ADD COLUMN `portfolio_ids` VARCHAR(255) NULL COMMENT 'Selected Portfolio IDs' AFTER `wr_content` ";
    sql_query($sql);
    echo "Column 'portfolio_ids' added successfully.";
}
?>
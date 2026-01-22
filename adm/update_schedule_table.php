<?php
$sub_menu = '100990';
include_once('./_common.php');

if ($is_admin != 'super')
    die('최고관리자만 접근 가능합니다.');

$table_name = G5_TABLE_PREFIX . 'schedule';

// Add is_done
$row = sql_fetch(" SHOW COLUMNS FROM {$table_name} LIKE 'is_done' ");
if (!$row) {
    sql_query(" ALTER TABLE {$table_name} ADD `is_done` tinyint(4) NOT NULL DEFAULT '0' AFTER `color` ");
    echo "Added is_done column.<br>";
} else {
    echo "is_done column already exists.<br>";
}

// Add alarm_minutes
$row = sql_fetch(" SHOW COLUMNS FROM {$table_name} LIKE 'alarm_minutes' ");
if (!$row) {
    sql_query(" ALTER TABLE {$table_name} ADD `alarm_minutes` int(11) NOT NULL DEFAULT '0' AFTER `is_done` ");
    echo "Added alarm_minutes column.<br>";
} else {
    echo "alarm_minutes column already exists.<br>";
}

echo "Database update completed.";
?>
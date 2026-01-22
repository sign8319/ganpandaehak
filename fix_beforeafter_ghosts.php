<?php
include_once('./_common.php');

// Security: Only Admin
if (!$is_admin) {
    die("관리자만 실행 가능합니다.");
}

echo "<h1>Clean Up Before&After Ghosts</h1>";

$bo_table = 'beforeafter';
$write_table = $g5['write_prefix'] . $bo_table;
$board_new_table = $g5['board_new_table'];

// 1. Check Table
if (!sql_fetch(" SHOW TABLES LIKE '{$write_table}' ")) {
    die("Table {$write_table} not found.");
}

// 2. Find Ghosts directly
$sql = " SELECT a.bn_id, a.wr_id FROM {$board_new_table} a 
         LEFT JOIN {$write_table} b ON a.wr_id = b.wr_id 
         WHERE a.bo_table = '{$bo_table}' AND b.wr_id IS NULL ";
$result = sql_query($sql);

$count = 0;
while ($row = sql_fetch_array($result)) {
    echo "Deleting Ghost: bn_id={$row['bn_id']}, wr_id={$row['wr_id']}<br>";
    sql_query(" DELETE FROM {$board_new_table} WHERE bn_id = '{$row['bn_id']}' ");
    $count++;
}

if ($count == 0) {
    echo "<div>No ghost records found for {$bo_table}.</div>";
} else {
    echo "<div>Deleted {$count} ghost records for {$bo_table}.</div>";
}

echo "<h2>Done.</h2>";
?>
<?php
include_once('./_common.php');

// Security: Only Admin
if (!$is_admin) {
    die("관리자만 실행 가능합니다.");
}

echo "<h1>Rebuild Board New (Consult)</h1>";

$bo_table = 'consult';
$write_table = $g5['write_prefix'] . $bo_table;
$board_new_table = $g5['board_new_table'];

// 1. Delete all 'consult' entries from g5_board_new
$sql = " DELETE FROM {$board_new_table} WHERE bo_table = '{$bo_table}' ";
sql_query($sql);
echo "<div>Deleted all '{$bo_table}' entries from board_new.</div>";

// 2. Scan g5_write_consult and Insert into g5_board_new
$sql = " SELECT wr_id, wr_parent, wr_datetime, mb_id FROM {$write_table} ORDER BY wr_id ASC ";
$result = sql_query($sql);

$count = 0;
while ($row = sql_fetch_array($result)) {
    $wr_id = $row['wr_id'];
    $wr_parent = $row['wr_parent'];
    $bn_datetime = $row['wr_datetime'];
    $mb_id = $row['mb_id'];

    $ins_sql = " INSERT INTO {$board_new_table}
                 SET bo_table = '{$bo_table}',
                     wr_id = '{$wr_id}',
                     wr_parent = '{$wr_parent}',
                     bn_datetime = '{$bn_datetime}',
                     mb_id = '{$mb_id}' ";
    sql_query($ins_sql);
    $count++;
}

echo "<div>Inserted {$count} entries into board_new.</div>";
echo "<h2>Done. Check Admin Page.</h2>";
?>
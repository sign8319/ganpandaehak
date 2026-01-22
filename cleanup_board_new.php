<?php
include_once('./_common.php');
echo "<h1>Cleanup Orphan Records from g5_board_new</h1>";

$write_table = $g5['write_prefix'] . 'consult';
$board_new_table = $g5['board_new_table'];

// Check Logic
$sql = " SELECT a.bn_id, a.wr_id FROM {$board_new_table} a 
         LEFT JOIN {$write_table} b ON a.wr_id = b.wr_id 
         WHERE a.bo_table = 'consult' AND b.wr_id IS NULL ";
$result = sql_query($sql);
$count = 0;
while ($row = sql_fetch_array($result)) {
    echo "Deleting Orphan: bn_id={$row['bn_id']}, wr_id={$row['wr_id']}<br>";
    sql_query(" DELETE FROM {$board_new_table} WHERE bn_id = '{$row['bn_id']}' ");
    $count++;
}

echo "<div>Deleted {$count} orphan records.</div>";
echo "<h1>Done.</h1>";
?>
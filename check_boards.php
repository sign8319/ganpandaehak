<?php
include_once('./_common.php');
$sql = " select bo_table, bo_subject from {$g5['board_table']} ";
$result = sql_query($sql);
while($row = sql_fetch_array($result)) {
    echo $row['bo_table'] . " : " . $row['bo_subject'] . "\n";
}
?>

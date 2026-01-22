<?php
include_once('./_common.php');
global $g5;
echo "Board Table Name: " . $g5['board_table'] . "\n";
$sql = "select * from {$g5['board_table']} where bo_table='consult'";
$result = sql_query($sql);
$board = sql_fetch_array($result);

if ($board) {
    echo "Board Found!\n";
    echo "bo_skin: " . $board['bo_skin'] . "\n";
    echo "bo_theme: " . $board['bo_theme'] . "\n";
    echo "bo_mobile_skin: " . $board['bo_mobile_skin'] . "\n";
    echo "bo_mobile_theme: " . $board['bo_mobile_theme'] . "\n";
} else {
    echo "Board NOT Found for 'consult'.\n";
    echo "SQL: $sql\n";
    echo "Error: " . sql_error_info() . "\n";
}
?>
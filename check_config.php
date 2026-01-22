<?php
include_once('./_common.php');
echo "<h1>Config Check</h1>";
echo "write_prefix: " . $g5['write_prefix'] . "<br>";
echo "board_new_table: " . $g5['board_new_table'] . "<br>";

$target = $g5['write_prefix'] . 'consult';
$sql = " SHOW TABLES LIKE '$target' ";
$res = sql_query($sql, false);
$row = sql_fetch_array($res);
echo "Table Check '$target': " . ($row ? "FOUND" : "NOT FOUND") . "<br>";

if (!$row) {
    echo "Detail Error: " . sql_error_info() . "<br>"; // If available usually mysql_error() or something
}
?>
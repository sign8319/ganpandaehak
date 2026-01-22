<?php
include_once('./_common.php');

echo "<h1>G5 Write Notice Schema v2</h1>";
$sql = " SHOW CREATE TABLE {$g5['write_prefix']}notice ";
$result = sql_query($sql);
$row = sql_fetch_array($result);
echo "<pre>";
print_r($row);
echo "</pre>";
?>
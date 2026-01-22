<?php
include_once('./_common.php');
$res = sql_query('DESCRIBE g5_quote');
if (!$res) {
    echo "Query failed: " . sql_error_info();
    exit;
}
while ($row = sql_fetch_array($res)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Default'] . "\n";
}
?>
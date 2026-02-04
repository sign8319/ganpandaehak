<?php
include_once('./_common.php');
if (!$is_admin)
    die('admin only');

echo "<h2>g5_customer Columns</h2>";
$res = sql_query("SHOW COLUMNS FROM g5_customer");
echo "<table border=1>";
while ($row = sql_fetch_array($res)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
}
echo "</table>";

echo "<h2>g5_quote Columns</h2>";
$res = sql_query("SHOW COLUMNS FROM g5_quote");
echo "<table border=1>";
while ($row = sql_fetch_array($res)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
}
echo "</table>";
?>
<?php
include_once('./_common.php');

echo "<h1>Board List</h1>";
echo "<table border=1><tr><th>bo_table</th><th>bo_subject</th><th>write_table</th><th>Table Exists?</th></tr>";
$sql = " SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_table ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $write_table = $g5['write_prefix'] . $row['bo_table'];
    $exists = sql_fetch(" SHOW TABLES LIKE '$write_table' ") ? "YES" : "NO";
    echo "<tr>";
    echo "<td>{$row['bo_table']}</td>";
    echo "<td>{$row['bo_subject']}</td>";
    echo "<td>{$write_table}</td>";
    echo "<td>{$exists}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h1>Board New (Latest 50 by bn_id)</h1>";
echo "<table border=1><tr><th>bn_id</th><th>bo_table</th><th>wr_id</th><th>bn_date</th><th>Is Orphan?</th></tr>";
$sql = " SELECT * FROM {$g5['board_new_table']} ORDER BY bn_id DESC LIMIT 50 ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $write_table = $g5['write_prefix'] . $row['bo_table'];
    // Check if orphan
    $orphan = "UNKNOWN";
    if (sql_fetch(" SHOW TABLES LIKE '$write_table' ")) {
        $check = sql_fetch(" SELECT count(*) as cnt FROM $write_table WHERE wr_id = '{$row['wr_id']}' ");
        $orphan = ($check['cnt'] > 0) ? "NO" : "<b style='color:red'>YES</b>";
    } else {
        $orphan = "Table Missing";
    }

    echo "<tr>";
    echo "<td>{$row['bn_id']}</td>";
    echo "<td>{$row['bo_table']}</td>";
    echo "<td>{$row['wr_id']}</td>";
    echo "<td>{$row['bn_datetime']}</td>";
    echo "<td>{$orphan}</td>";
    echo "</tr>";
}
echo "</table>";
?>
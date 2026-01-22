<?php
include_once('./_common.php');
echo "<h1>Recent Consult Board Posts</h1>";
$write_table = $g5['write_prefix'] . 'consult';
$sql = " SELECT * FROM {$write_table} ORDER BY wr_id DESC LIMIT 5 ";
$result = sql_query($sql);
echo "<table border=1><tr><th>wr_id</th><th>mb_id</th><th>Subject</th><th>Name</th><th>Time</th></tr>";
while ($row = sql_fetch_array($result)) {
    echo "<tr>";
    echo "<td>" . $row['wr_id'] . "</td>";
    echo "<td>[" . $row['mb_id'] . "]</td>";
    echo "<td>" . $row['wr_subject'] . "</td>";
    echo "<td>" . $row['wr_name'] . "</td>";
    echo "<td>" . $row['wr_datetime'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
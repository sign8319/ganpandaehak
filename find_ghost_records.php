<?php
include_once('./_common.php');
echo "<h1>Find Ghost Records</h1>";

// Simulate Admin Page Query
$sql = " SELECT * FROM {$g5['board_new_table']} ORDER BY bn_datetime DESC LIMIT 20 ";
$result = sql_query($sql);

echo "<table border=1>
<tr>
    <th>bn_id</th>
    <th>bo_table</th>
    <th>wr_id</th>
    <th>bn_datetime</th>
    <th>Target Table</th>
    <th>Row Exist?</th>
    <th>Row Data Dump</th>
</tr>";

while ($row = sql_fetch_array($result)) {
    $bo_table = $row['bo_table'];
    $wr_id = $row['wr_id'];
    $write_table = $g5['write_prefix'] . $bo_table;

    // Check Row
    $row2 = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}' ");

    $exist = $row2 ? "YES" : "<b style='color:red'>NO (Ghosts)</b>";
    $dump = $row2 ? htmlspecialchars(mb_strimwidth(print_r($row2, true), 0, 100, "...")) : "";

    echo "<tr>
        <td>{$row['bn_id']}</td>
        <td>{$bo_table}</td>
        <td>{$wr_id}</td>
        <td>{$row['bn_datetime']}</td>
        <td>{$write_table}</td>
        <td>{$exist}</td>
        <td>{$dump}</td>
    </tr>";
}
echo "</table>";
?>
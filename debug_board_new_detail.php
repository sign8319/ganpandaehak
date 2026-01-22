<?php
include_once('./_common.php');
echo "<h1>Debug Board New Detail</h1>";

// 1. Check entries in g5_board_new that are likely broken
$sql = " SELECT * FROM {$g5['board_new_table']} ORDER BY bn_datetime DESC LIMIT 20 ";
$result = sql_query($sql);

echo "<table border=1>
<tr>
    <th>bn_id</th>
    <th>bo_table</th>
    <th>wr_id</th>
    <th>wr_parent</th>
    <th>bn_datetime</th>
    <th>mb_id</th>
    <th>Target Table Status</th>
</tr>";

while ($row = sql_fetch_array($result)) {
    $bo_table = $row['bo_table'];
    $wr_id = $row['wr_id'];
    $write_table = $g5['write_prefix'] . $bo_table;

    // Check if write table exists
    $tbl_check = sql_fetch(" SHOW TABLES LIKE '{$write_table}' ");

    $status = "";
    if (!$tbl_check) {
        $status = "<span style='color:red'>Table Not Found</span>";
    } else {
        // Check if article exists
        $art_check = sql_fetch(" SELECT count(*) as cnt FROM {$write_table} WHERE wr_id = '{$wr_id}' ");
        if ($art_check['cnt'] > 0) {
            $status = "OK";
        } else {
            $status = "<span style='color:red'>Article Not Found</span>";
        }
    }

    echo "<tr>
        <td>{$row['bn_id']}</td>
        <td>{$bo_table}</td>
        <td>{$wr_id}</td>
        <td>{$row['wr_parent']}</td>
        <td>{$row['bn_datetime']}</td>
        <td>{$row['mb_id']}</td>
        <td>{$status}</td>
    </tr>";
}
echo "</table>";

// 2. Count "Article Not Found" by board
echo "<h2>Broken Entries Summary</h2>";
// This is a bit complex in pure SQL without stored procedure if tables are dynamic, 
// so we iterate PHP side logic conceptually or just do per known board.
$boards = ['consult', 'beforeafter', 'gallery', 'notice', 'qa']; // Candidates
foreach ($boards as $bo) {
    $write_table = $g5['write_prefix'] . $bo;
    if (!sql_fetch(" SHOW TABLES LIKE '{$write_table}' "))
        continue;

    $sql = " SELECT count(*) as cnt FROM {$g5['board_new_table']} a 
             LEFT JOIN {$write_table} b ON a.wr_id = b.wr_id 
             WHERE a.bo_table = '{$bo}' AND b.wr_id IS NULL ";
    $row = sql_fetch($sql);
    if ($row['cnt'] > 0) {
        echo "Board '{$bo}': {$row['cnt']} broken entries.<br>";
    }
}
?>
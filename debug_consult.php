<?php
include_once('./_common.php');

echo "<h1>Debug Consult Board 2</h1>\n";

$write_table = $g5['write_prefix'] . 'consult';
echo "Target Table: {$write_table}<br>\n";

// 1. Force Select
echo "<h2>Selecting from {$write_table}</h2>\n";
$sql = " select * from {$write_table} order by wr_id desc limit 10 ";
$result = sql_query($sql, false);
// false to not die on error? sql_query impl might vary. 
// Standard G5 sql_query normally dies on error unless logic handles it.
// Let's try-catch if possible or assume it works if table exists.

if ($result) {
    echo "<table border=1><tr><th>wr_id</th><th>wr_subject</th><th>wr_name</th><th>wr_datetime</th></tr>\n";
    while ($row = sql_fetch_array($result)) {
        echo "<tr>";
        echo "<td>{$row['wr_id']}</td>";
        echo "<td>{$row['wr_subject']}</td>";
        echo "<td>{$row['wr_name']}</td>";
        echo "<td>{$row['wr_datetime']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "Select failed (Table might not exist)<br>\n";
}

// 2. Cross Check
echo "<h2>Cross Check (g5_board_new vs g5_write_consult)</h2>\n";
$sql = " select a.bn_id, a.wr_id, b.wr_subject, a.bn_datetime
         from {$g5['board_new_table']} a 
         left join {$write_table} b on a.wr_id = b.wr_id 
         where a.bo_table = 'consult' 
         order by a.bn_datetime desc limit 10 ";
$result = sql_query($sql);
echo "<table border=1><tr><th>bn_id</th><th>wr_id</th><th>Status</th><th>Time</th></tr>\n";
while ($row = sql_fetch_array($result)) {
    $status = $row['wr_subject'] ? "Found: " . $row['wr_subject'] : "<span style='color:red;'>NOT FOUND (wr_id={$row['wr_id']})</span>";
    echo "<tr>";
    echo "<td>{$row['bn_id']}</td>";
    echo "<td>{$row['wr_id']}</td>";
    echo "<td>{$status}</td>";
    echo "<td>{$row['bn_datetime']}</td>";
    echo "</tr>\n";
}
echo "</table>\n";
?>
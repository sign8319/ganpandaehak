<?php
include_once('./_common.php');
echo "<h1>Recent Quotes</h1>";
$sql = " SELECT * FROM g5_quote ORDER BY qa_id DESC LIMIT 5 ";
$result = sql_query($sql);
echo "<table border=1><tr><th>qa_id</th><th>mb_id</th><th>Subject</th><th>Name</th><th>Time</th></tr>";
while ($row = sql_fetch_array($result)) {
    echo "<tr>";
    echo "<td>" . $row['qa_id'] . "</td>";
    echo "<td>[" . $row['mb_id'] . "]</td>";
    echo "<td>" . $row['qa_subject'] . "</td>";
    echo "<td>" . $row['qa_client_name'] . "</td>";
    echo "<td>" . $row['qa_datetime'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
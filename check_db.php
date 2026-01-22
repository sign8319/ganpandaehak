<?php
include_once('./_common.php');

$table = $g5['write_prefix'] . 'payment';
echo "Checking table: $table\n";

// Check columns
$sql = "SHOW COLUMNS FROM $table";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    echo "Column: " . $row['Field'] . "\n";
}

// Check first row
$sql = "SELECT * FROM $table LIMIT 1";
$row = sql_fetch($sql);
echo "\nFirst Row Data:\n";
print_r($row);
?>
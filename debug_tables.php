<?php
include_once('./_common.php');
echo "<h1>Table List Debug</h1>";
$sql = " SHOW TABLES LIKE '{$g5['write_prefix']}%' ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
}

// Check specific common tables
$candidates = ['g5_write_notice', 'g5_write_free', 'g5_write_qa', 'g5_write_portfolio_v2', 'g5_write_portfolio_v5', 'g5_write_basic_consult'];
echo "<h2>Check Candidates</h2>";
foreach ($candidates as $table) {
    if (sql_fetch("SHOW TABLES LIKE '$table'")) {
        echo "$table EXISTS<br>";
    } else {
        echo "$table NOT FOUND<br>";
    }
}
?>
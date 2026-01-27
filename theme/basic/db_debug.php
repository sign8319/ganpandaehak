<?php
include_once('./_common.php');

if (!$is_admin)
    die('Access Denied');

echo "<h2>DB Inspection</h2>";

// 1. Check g5_quote structure
echo "<h3>1. g5_quote Columns</h3>";
$result = sql_query(" DESCRIBE g5_quote ");
echo "<ul>";
while ($row = sql_fetch_array($result)) {
    if (strpos($row['Field'], 'tax') !== false || strpos($row['Field'], 'cond') !== false || strpos($row['Field'], 'sect') !== false || strpos($row['Field'], 'biz') !== false) {
        echo "<li>{$row['Field']} ({$row['Type']})</li>";
    }
}
echo "</ul>";

// 2. Count Stats
$total = sql_fetch(" SELECT count(*) as cnt FROM g5_quote ")['cnt'];
$linked = sql_fetch(" SELECT count(*) as cnt FROM g5_quote WHERE qa_customer_id > 0 ")['cnt'];
$with_tax = sql_fetch(" SELECT count(*) as cnt FROM g5_quote WHERE qa_tax_company_name != '' ")['cnt'];
$with_condition = sql_fetch(" SELECT count(*) as cnt FROM g5_quote WHERE qa_tax_condition != '' ")['cnt'];

echo "<h3>2. Statistics</h3>";
echo "<ul>";
echo "<li>Total Quotes: $total</li>";
echo "<li>Quotes linked to Customer (qa_customer_id > 0): $linked</li>";
echo "<li>Quotes with Tax Company Name: $with_tax</li>";
echo "<li>Quotes with Tax Condition: $with_condition</li>";
echo "</ul>";

// 3. Sample Data (Top 5 with tax info)
echo "<h3>3. Sample Data (Quotes with Tax Info)</h3>";
$res = sql_query(" SELECT * FROM g5_quote WHERE qa_tax_company_name != '' LIMIT 5 ");
while ($row = sql_fetch_array($res)) {
    echo "<pre>";
    print_r([
        'qa_id' => $row['qa_id'],
        'qa_tax_company_name' => $row['qa_tax_company_name'],
        'qa_tax_condition' => $row['qa_tax_condition'],
        'qa_tax_sector' => $row['qa_tax_sector'],
        'qa_tax_biz_num' => $row['qa_tax_biz_num']
    ]);
    echo "</pre>";
}
?>
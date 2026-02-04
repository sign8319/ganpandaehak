<?php
include_once('./_common.php');

if (!$is_admin) {
    die('접근 권한이 없습니다.');
}

echo "<h1>Tax Info Migration (Quote -> Customer)</h1>";

// 1. Get all quotes that have tax info
$sql = " SELECT qa_customer_id, qa_tax_biz_num, qa_tax_condition, qa_tax_sector, qa_tax_ceo_name, qa_tax_addr, qa_tax_email, qa_tax_type, qa_tax_item_name, qa_tax_company_name
         FROM g5_quote 
         WHERE qa_customer_id > 0 
           AND (qa_tax_biz_num != '' OR qa_tax_condition != '' OR qa_tax_sector != '')
         ORDER BY qa_id DESC ";
$result = sql_query($sql);

$updated_count = 0;
$processed_customers = [];

while ($row = sql_fetch_array($result)) {
    $cust_id = $row['qa_customer_id'];

    // Only process the latest quote for each customer
    if (in_array($cust_id, $processed_customers))
        continue;
    $processed_customers[] = $cust_id;

    // Check if customer needs update
    $cust = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$cust_id' ");
    if (!$cust)
        continue;

    $upd = [];

    // Map fields
    $fields = [
        'tax_biz_num' => 'qa_tax_biz_num',
        'tax_condition' => 'qa_tax_condition',
        'tax_sector' => 'qa_tax_sector',
        'tax_ceo_name' => 'qa_tax_ceo_name',
        'tax_addr' => 'qa_tax_addr',
        'tax_email' => 'qa_tax_email',
        'tax_type' => 'qa_tax_type',
        'tax_item_name' => 'qa_tax_item_name',
        'tax_company_name' => 'qa_tax_company_name'
    ];

    $needs_update = false;
    $set_sql = "";

    foreach ($fields as $c_col => $q_col) {
        if (!empty($row[$q_col]) && empty($cust[$c_col])) {
            $val = addslashes($row[$q_col]);
            $set_sql .= " , $c_col = '$val' ";
            $needs_update = true;
        }
    }

    if ($needs_update) {
        sql_query(" UPDATE g5_customer SET " . trim($set_sql, ',') . " WHERE customer_id = '$cust_id' ");
        $updated_count++;
        echo "Customer ID $cust_id updated.<br>";
    }
}

echo "<hr>";
echo "Total $updated_count customers updated.";
?>
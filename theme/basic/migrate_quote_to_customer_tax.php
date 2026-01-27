<?php
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>[Migration] Customer Tax Info Sync</h1>";
echo "<p>기존 견적서(g5_quote)의 세금계산서 정보를 고객 테이블(g5_customer)로 마이그레이션 합니다.</p>";
echo "<hr>";

// 1. Fetch quotes with valid tax info and customer_id
$sql = " SELECT * FROM g5_quote 
         WHERE qa_customer_id > 0 
           AND (qa_tax_biz_num != '' OR qa_tax_company_name != '')
         ORDER BY qa_id ASC "; // Oldest first, so newer overwrites older (or check logic below)

$result = sql_query($sql);
$count = 0;
$updated = 0;

echo "<ul>";

while ($row = sql_fetch_array($result)) {
    $cust_id = $row['qa_customer_id'];

    // Check current customer data
    $cust = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$cust_id' ");
    if (!$cust)
        continue;

    $update_needed = false;
    $set_sql = "";

    // Field Mapping: Customer Field => Quote Field
    $map = [
        'tax_biz_num' => 'qa_tax_biz_num',
        'tax_company_name' => 'qa_tax_company_name',
        'tax_ceo_name' => 'qa_tax_ceo_name',
        'tax_addr' => 'qa_tax_addr',
        'tax_email' => 'qa_tax_email',
        // 'tax_condition' => 'qa_tax_condition', // These might not exist in quote yet if just created
        // 'tax_sector' => 'qa_tax_sector',       // Check if they exist in $row
        'tax_item_name' => 'qa_tax_item_name',
        // 'payment_method' => 'qa_payment_method'
    ];

    // Check newly added columns if they exist in quote row (from previous recent DB updates)
    if (isset($row['qa_tax_condition'])) {
        $map['tax_condition'] = 'qa_tax_condition';
    }
    if (isset($row['qa_tax_sector'])) {
        $map['tax_sector'] = 'qa_tax_sector';
    }

    foreach ($map as $c_col => $q_col) {
        $q_val = trim($row[$q_col]);
        $c_val = trim($cust[$c_col] ?? '');

        // Only update if Customer field is EMPTY and Quote field HAS VALUE
        // OR simply overwrite? User said: "기존 견적서에 입력해 두었던... 옮겨지도록"
        // Let's assume overwrite if customer field is empty, OR update if it's better data?
        // Safest: Update if customer field IS EMPTY.
        // User created columns manually, so they are likely empty.

        if (!empty($q_val) && empty($c_val)) {
            $set_sql .= " , $c_col = '" . addslashes($q_val) . "' ";
            $update_needed = true;
        }
    }

    if ($update_needed) {
        sql_query(" UPDATE g5_customer SET " . trim($set_sql, ',') . " WHERE customer_id = '$cust_id' ");
        echo "<li>[O] Customer ID {$cust_id} Updated using Quote ID {$row['qa_id']}</li>";
        $updated++;
    } else {
        // echo "<li>[-] Customer ID {$cust_id} Skipped (No new data or already set)</li>";
    }
    $count++;
}

echo "</ul>";
echo "<hr>";
echo "<p><strong>Total Scanned Quotes:</strong> $count</p>";
echo "<p><strong>Customers Updated:</strong> $updated</p>";
?>
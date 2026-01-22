<?php
include_once('./_common.php');
include_once(G5_THEME_PATH . '/includes/quote_functions.php');

// Check Admin
if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>Customer Data Sync Fix Tool</h1>";
echo "<pre>\n";

// Fetch Target Quotes
$sql = " SELECT * FROM g5_quote WHERE qa_customer_id = 0 ORDER BY qa_id ASC ";
$result = sql_query($sql);

$total = sql_num_rows($result);
echo "Found $total quotes with qa_customer_id = 0.\n\n";

$processed = 0;
$linked = 0;
$created = 0;

while ($row = sql_fetch_array($result)) {
    $processed++;
    $qa_id = $row['qa_id'];
    $name = $row['qa_client_name'];
    $hp = $row['qa_client_hp'];
    $email = $row['qa_client_email'];
    $addr = $row['qa_client_addr'];

    // Check pre-existing customers (just to report stats)
    $safe_hp = normalize_hp($hp);
    $exists = false;
    if ($safe_hp) {
        $check = sql_fetch(" SELECT customer_id FROM g5_customer WHERE replace(customer_hp, '-', '') = '$safe_hp' ");
        if ($check['customer_id'])
            $exists = true;
    }

    // Run Logic
    $cust_id = find_or_create_customer($name, $hp, $email, $addr);

    if ($cust_id) {
        sql_query(" UPDATE g5_quote SET qa_customer_id = '$cust_id' WHERE qa_id = '$qa_id' ");

        $status_msg = $exists ? "Linked to Existing ($cust_id)" : "Created New ($cust_id)";
        echo "[$qa_id] $name / $hp -> $status_msg\n";

        if ($exists)
            $linked++;
        else
            $created++;
    } else {
        echo "[$qa_id] $name / $hp -> FAILED to get Customer ID. Error: " . sql_error_info()['message'] . "\n";
    }
}

echo "\n----------------------------------------\n";
echo "Total Processed: $processed\n";
echo "Newly Created: $created\n";
echo "Linked Existing: $linked\n";
echo "</pre>";

// Final Verification Query
$v_sql = " SELECT 
  SUM(qa_customer_id=0) AS zero_cnt,
  SUM(qa_customer_id<>0) AS linked_cnt,
  COUNT(*) AS total
FROM g5_quote ";
$v_row = sql_fetch($v_sql);

echo "<h3>Final Verification</h3>";
echo "<pre>";
print_r($v_row);
echo "</pre>";
?>
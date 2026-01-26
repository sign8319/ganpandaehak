<?php
// Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('./_common.php');

if (!$is_admin) {
    die("Admin Only");
}

echo "<h1>Migration: Sync Tax Company Name to Customer Company</h1>";

// 1. Ensure Table Exists
if (!sql_query(" DESCRIBE g5_customer ", false)) {
    die("❌ Error: g5_customer table does not exist. Please visit admin_customer.php first.");
}

// 2. Ensure Columns Exist
$cols_needed = [
    'customer_company' => "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '상호명'",
    'tax_company_name' => "VARCHAR(100) NOT NULL DEFAULT ''"
];

foreach ($cols_needed as $col => $def) {
    $row = sql_fetch(" SHOW COLUMNS FROM g5_customer LIKE '$col' ");
    if (!$row) {
        echo "Adding missing column: $col...<br>";
        sql_query(" ALTER TABLE g5_customer ADD `$col` $def ", true);
    }
}

// 3. Execute Update
$sql = " UPDATE g5_customer 
         SET customer_company = tax_company_name 
         WHERE (customer_company = '' OR customer_company IS NULL) 
           AND tax_company_name != '' ";

$result = sql_query($sql, false); // passing false to handle error manually if needed

if ($result) {
    $affected = function_exists('sql_affected_rows') ? sql_affected_rows() : 'many';
    echo "<h2 style='color:green'>✅ Migration Success!</h2>";
    echo "<p>Updated <strong>$affected</strong> customer records.</p>";
    echo "<p>Rows where `customer_company` was empty but `tax_company_name` existed have been synced.</p>";
} else {
    echo "<h2 style='color:red'>❌ Migration Failed</h2>";
    echo "<p>SQL Error: " . sql_error_info() . "</p>";
}

echo "<br><a href='./admin_quote.php'>Go Back to Quote Admin</a>";
?>
<?php
include_once('./_common.php');

if (!$is_admin) {
    die("Admin Only");
}

$sql = " UPDATE g5_customer 
         SET customer_company = tax_company_name 
         WHERE (customer_company = '' OR customer_company IS NULL) 
           AND tax_company_name != '' ";
$result = sql_query($sql);

if ($result) {
    echo "Migration Success: Updated " . sql_affected_rows() . " rows.";
} else {
    echo "Migration Failed.";
}
?>
<?php
include_once('./_common.php');
include_once('./includes/quote_functions.php');
include_once('./includes/quote_db_schema.php');

// 1. Admin Check
if (!$is_admin) {
    die('Access Denied');
}

// 2. Ensure Table Exists
init_quote_tables();

// 3. Clear existing work data (Optional: for fresh migration)
sql_query(" TRUNCATE TABLE g5_work ");

// 4. Migrate Quotes
$sql = " SELECT * FROM g5_quote ORDER BY qa_id ASC ";
$result = sql_query($sql);

$count = 0;
while ($row = sql_fetch_array($result)) {
    $qa_id = $row['qa_id'];
    $subject = addslashes($row['qa_subject']);
    $status = $row['qa_status'];
    $created_at = $row['qa_datetime'];
    $updated_at = $row['qa_datetime']; // or now

    // Find linked customer if any
    $customer_id = (int) $row['qa_customer_id'];

    // Insert into g5_work
    $sql_ins = " INSERT INTO g5_work SET
                    work_subject = '$subject',
                    work_status = '$status',
                    qa_id = '$qa_id',
                    customer_id = '$customer_id',
                    created_at = '$created_at',
                    updated_at = '$updated_at' ";
    sql_query($sql_ins);
    $count++;
}

echo "Migration Complete. $count records moved to g5_work.";
?>
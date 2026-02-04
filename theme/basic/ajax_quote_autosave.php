<?php
include_once('./_common.php');

if (!$is_admin) {
    die(json_encode(['success' => false, 'message' => 'Forbidden']));
}

$qa_id = isset($_POST['qa_id']) ? (int) $_POST['qa_id'] : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';
$value = isset($_POST['value']) ? trim($_POST['value']) : '';

if (!$field) {
    die(json_encode(['success' => false, 'message' => 'Missing field']));
}

// Allowable fields for sync
$allowed_fields = [
    'qa_subject',
    'qa_client_name',
    'qa_client_hp',
    'qa_client_contact',
    'qa_client_email',
    'qa_client_addr',
    'qa_client_addr2',
    'qa_tax_company_name',
    'qa_status',
    'qa_memo',
    'qa_memo_user',
    'qa_tax_biz_num',
    'qa_tax_ceo_name',
    'qa_tax_addr',
    'qa_tax_email',
    'qa_construct_date',
    'qa_deposit_status',
    'qa_payment_method'
];

if (!in_array($field, $allowed_fields)) {
    die(json_encode(['success' => false, 'message' => 'Invalid field: ' . $field]));
}

if (!$qa_id) {
    // Create new quote if it doesn't exist
    // [보안] 중복 방지 헬퍼 함수 사용 (Race Condition 방지)
    $new_code = generate_unique_quote_code();

    $sql = " INSERT INTO g5_quote SET
             qa_code = '$new_code', 
             qa_datetime = '" . G5_TIME_YMDHIS . "',
             $field = '" . sql_real_escape_string($value) . "' ";
    sql_query($sql);
    $qa_id = sql_insert_id();
    echo json_encode(['success' => true, 'qa_id' => $qa_id, 'is_new' => true]);
} else {
    $sql = " UPDATE g5_quote SET $field = '" . sql_real_escape_string($value) . "' WHERE qa_id = '$qa_id' ";
    sql_query($sql);
    echo json_encode(['success' => true, 'qa_id' => $qa_id]);
}
?>
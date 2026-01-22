<?php
include_once('./_common.php');

if (!$is_admin) {
    die(json_encode(['success' => false, 'message' => 'Forbidden']));
}

$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';

if ($w == 'search') {
    $q = isset($_REQUEST['q']) ? trim($_REQUEST['q']) : '';
    $sql_search = "";
    if ($q) {
        $qs = sql_real_escape_string($q);
        $sql_search = " WHERE customer_name LIKE '%$qs%' OR customer_hp LIKE '%$qs%' OR customer_addr LIKE '%$qs%' ";
    }

    $sql = " SELECT * FROM g5_customer $sql_search ORDER BY updated_at DESC LIMIT 20 ";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    echo json_encode(['success' => true, 'list' => $list]);
    exit;
}

if ($w == 'recent') {
    // Recent 5 customers (simplified: just last 5 updated customers)
    $sql = " SELECT * FROM g5_customer ORDER BY updated_at DESC LIMIT 5 ";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    echo json_encode(['success' => true, 'list' => $list]);
    exit;
}

if ($w == 'history') {
    $customer_name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
    $customer_hp = isset($_REQUEST['hp']) ? trim($_REQUEST['hp']) : '';

    if (!$customer_name && !$customer_hp) {
        die(json_encode(['success' => false]));
    }

    $where = [];
    if ($customer_name)
        $where[] = "qa_client_name = '" . sql_real_escape_string($customer_name) . "'";
    if ($customer_hp)
        $where[] = "qa_client_hp = '" . sql_real_escape_string($customer_hp) . "'";

    $sql_where = implode(" OR ", $where);
    $sql = " SELECT qa_id, qa_subject, qa_datetime, qa_price_total, qa_status 
             FROM g5_quote 
             WHERE $sql_where 
             ORDER BY qa_datetime DESC LIMIT 10 ";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $row['date'] = date('Y-m-d', strtotime($row['qa_datetime']));
        $row['price_fmt'] = number_format($row['qa_price_total']);
        $list[] = $row;
    }
    echo json_encode(['success' => true, 'list' => $list]);
    exit;
}
?>
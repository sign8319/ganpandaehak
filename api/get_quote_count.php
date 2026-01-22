<?php
include_once('../_common.php');

header('Content-Type: application/json');

if (!$is_member) {
    echo json_encode(['count' => 0]);
    exit;
}

// quotes 테이블 존재 여부 확인 (에러 방지)
// g5_quote 테이블 존재 여부 확인 (에러 방지)
$table_check = sql_fetch("SHOW TABLES LIKE 'g5_quote'");
if (!$table_check) {
    echo json_encode(['count' => 0]);
    exit;
}

// [Modified] Admin sees unprocessed count, Member sees own active count
if ($member['mb_level'] >= 5) {
    // Admin: Count unprocessed quotes (Status '신청완료')
    $sql = " SELECT COUNT(*) as cnt FROM g5_quote WHERE qa_status = '신청완료' ";
} else {
    // Member: Count my active quotes
    $sql = " SELECT COUNT(*) as cnt FROM g5_quote 
             WHERE mb_id = '{$member['mb_id']}' 
             AND qa_status != '취소' 
             AND qa_status != 'draft' ";
}
$row = sql_fetch($sql);


echo json_encode(['count' => (int) $row['cnt']]);
?>
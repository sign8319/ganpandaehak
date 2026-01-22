<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.');
}

$id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;

if (!$id) {
    alert('잘못된 접근입니다.');
}

// 본인 확인
$sql = " SELECT mb_id, qa_status FROM g5_quote WHERE qa_id = '{$id}' ";
$row = sql_fetch($sql);

if (!$row['mb_id']) {
    alert('존재하지 않는 견적입니다.');
}

if ($row['mb_id'] !== $member['mb_id'] && !$is_admin) {
    alert('삭제 권한이 없습니다.');
}

// [DRAFT SYSTEM] Draft quotes are hard deleted, active quotes are soft deleted
if ($row['qa_status'] == 'draft') {
    // Hard delete draft quotes (no user input, just temporary records)
    $sql = " DELETE FROM g5_quote WHERE qa_id = '{$id}' ";
    sql_query($sql);

    // Also delete related data
    sql_query(" DELETE FROM g5_quote_item WHERE qa_id = '{$id}' ");
    sql_query(" DELETE FROM g5_quote_measure WHERE qa_id = '{$id}' ");
} else {
    // Soft delete active quotes (change status to '취소')
    $sql = " UPDATE g5_quote SET qa_status = '취소' WHERE qa_id = '{$id}' ";
    sql_query($sql);
}

alert('견적 내역이 삭제되었습니다.', './quote_history.php');
?>
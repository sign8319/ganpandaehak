<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

// 1. 필수 파라미터 확인
$qa_id = isset($_POST['qa_id']) ? (int) $_POST['qa_id'] : 0;
$mb_id = $member['mb_id'];

if (!$qa_id) {
    alert('잘못된 접근입니다.');
}

// 2. 권한 및 상태 확인
$sql = " SELECT * FROM g5_quote WHERE qa_id = '{$qa_id}' AND mb_id = '{$mb_id}' ";
$quote = sql_fetch($sql);

if (!$quote) {
    alert('존재하지 않거나 수정 권한이 없는 견적입니다.');
}

if (!in_array($quote['qa_status'], ['작성중', '견적발송'])) {
    alert('이미 진행 중이거나 완료된 견적은 수정할 수 없습니다.');
}

// 3. 데이터 정리
$qa_client_name = isset($_POST['qa_client_name']) ? trim($_POST['qa_client_name']) : '';
$qa_client_hp = isset($_POST['qa_client_hp']) ? trim($_POST['qa_client_hp']) : '';
$qa_client_addr = isset($_POST['qa_client_addr']) ? trim($_POST['qa_client_addr']) : '';
$qa_client_addr2 = isset($_POST['qa_client_addr2']) ? trim($_POST['qa_client_addr2']) : '';
$qa_content_raw = isset($_POST['qa_content']) ? trim($_POST['qa_content']) : '';
$budget = isset($_POST['budget_range']) ? trim($_POST['budget_range']) : '';
$schedule = isset($_POST['expected_date']) ? trim($_POST['expected_date']) : '';
$sign_type = isset($_POST['sign_type']) ? trim($_POST['sign_type']) : '';

// 4. 내용 조합 (메모 필드에 예산/일정 통합)
$qa_memo_user = $qa_content_raw;
if ($budget)
    $qa_memo_user .= "\n\n[예산] " . $budget;
if ($schedule)
    $qa_memo_user .= "\n[오픈예정일] " . $schedule;

// 5. Update g5_quote
$sql = " UPDATE g5_quote
            SET qa_client_name = '{$qa_client_name}',
                qa_client_hp = '{$qa_client_hp}',
                qa_client_addr = '{$qa_client_addr}',
                qa_client_addr2 = '{$qa_client_addr2}',
                qa_memo_user = '{$qa_memo_user}',
                qa_subject = '{$sign_type} 상세 견적 요청 (수정됨)' 
            WHERE qa_id = '{$qa_id}' ";
sql_query($sql);

// 6. Update g5_quote_item (간판 종류)
// 아이템이 없으면 새로 추가, 있으면 수정
$item_check = sql_fetch(" SELECT count(*) as cnt FROM g5_quote_item WHERE qa_id = '{$qa_id}' ");
if ($item_check['cnt'] > 0) {
    $sql_item = " UPDATE g5_quote_item SET qi_item = '{$sign_type}' WHERE qa_id = '{$qa_id}' ";
    sql_query($sql_item);
} else {
    $sql_item = " INSERT INTO g5_quote_item
                    SET qa_id = '{$qa_id}',
                        qi_index = 0,
                        qi_item = '{$sign_type}',
                        qi_qty = 1,
                        qi_price = 0,
                        qi_amount = 0,
                        qi_note = '수정 시 추가됨' ";
    sql_query($sql_item);
}

alert('견적 요청 내용이 수정되었습니다.', './quote_detail.php?id=' . $qa_id);
?>
<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

// 1. 필수 파라미터 확인 (숫자는 int 캐스팅)
$qa_id = isset($_POST['qa_id']) ? (int) $_POST['qa_id'] : 0;

if ($qa_id <= 0) {
    alert('잘못된 접근입니다.');
}

// 2. 권한 및 상태 확인 (mb_id도 escape 처리)
$mb_id_safe = sql_escape_string($member['mb_id']);
$sql = " SELECT * FROM g5_quote WHERE qa_id = {$qa_id} AND mb_id = '{$mb_id_safe}' ";
$quote = sql_fetch($sql);

if (!$quote) {
    alert('존재하지 않거나 수정 권한이 없는 견적입니다.');
}

// 2-1. 소유자 일치 여부 이중 체크 (보안 강화)
if ($quote['mb_id'] !== $member['mb_id']) {
    error_log("[SECURITY] quote_edit_update.php - Ownership mismatch: qa_id={$qa_id}, owner={$quote['mb_id']}, requester={$member['mb_id']}");
    alert('수정 권한이 없습니다.');
}

if (!in_array($quote['qa_status'], ['작성중', '견적발송'])) {
    alert('이미 진행 중이거나 완료된 견적은 수정할 수 없습니다.');
}

// 3. 데이터 정리 (trim만 적용, escape는 쿼리 직전에 수행)
$qa_client_name = trim($_POST['qa_client_name'] ?? '');
$qa_client_hp = trim($_POST['qa_client_hp'] ?? '');
$qa_client_addr = trim($_POST['qa_client_addr'] ?? '');
$qa_client_addr2 = trim($_POST['qa_client_addr2'] ?? '');
$qa_content_raw = trim($_POST['qa_content'] ?? '');
$budget = trim($_POST['budget_range'] ?? '');
$schedule = trim($_POST['expected_date'] ?? '');
$sign_type = trim($_POST['sign_type'] ?? '');

// 4. 내용 조합 (메모 필드에 예산/일정 통합) - 실제 줄바꿈 사용
$qa_memo_user = $qa_content_raw;
if ($budget)
    $qa_memo_user .= "\n\n[예산] " . $budget;
if ($schedule)
    $qa_memo_user .= "\n[오픈예정일] " . $schedule;

// 5. SQL Escape 처리 (UPDATE 직전에 1회만 적용, 이중 escape 방지)
$qa_client_name_safe = sql_escape_string($qa_client_name);
$qa_client_hp_safe = sql_escape_string($qa_client_hp);
$qa_client_addr_safe = sql_escape_string($qa_client_addr);
$qa_client_addr2_safe = sql_escape_string($qa_client_addr2);
$qa_memo_user_safe = sql_escape_string($qa_memo_user);
$sign_type_safe = sql_escape_string($sign_type);
$qa_subject_safe = sql_escape_string($sign_type . ' 상세 견적 요청 (수정됨)');

// 6. Update g5_quote
$sql = " UPDATE g5_quote
            SET qa_client_name = '{$qa_client_name_safe}',
                qa_client_hp = '{$qa_client_hp_safe}',
                qa_client_addr = '{$qa_client_addr_safe}',
                qa_client_addr2 = '{$qa_client_addr2_safe}',
                qa_memo_user = '{$qa_memo_user_safe}',
                qa_subject = '{$qa_subject_safe}'
            WHERE qa_id = {$qa_id} AND mb_id = '{$mb_id_safe}' ";
$result = sql_query($sql, false);

if (!$result) {
    error_log("[DB_ERROR] quote_edit_update.php - UPDATE g5_quote failed: qa_id={$qa_id}, mb_id={$member['mb_id']}");
    alert('저장 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.');
}

// 7. Update g5_quote_item (간판 종류)
// [보안] g5_quote_item에는 mb_id 컬럼이 없음.
// 소유권 검증: qa_id가 현재 사용자 소유인지 서브쿼리로 확인하여 우회 방지
$item_check = sql_fetch(" SELECT count(*) as cnt FROM g5_quote_item WHERE qa_id = {$qa_id} ");
if ($item_check['cnt'] > 0) {
    // UPDATE: qa_id가 현재 사용자 소유일 때만 수정 (EXISTS 서브쿼리로 소유권 재검증)
    $sql_item = " UPDATE g5_quote_item
                    SET qi_item = '{$sign_type_safe}'
                    WHERE qa_id = {$qa_id}
                      AND EXISTS (SELECT 1 FROM g5_quote WHERE qa_id = {$qa_id} AND mb_id = '{$mb_id_safe}') ";
    $result_item = sql_query($sql_item, false);
} else {
    // INSERT: qa_id가 현재 사용자 소유일 때만 추가 (SELECT로 소유권 확인 후 INSERT)
    $ownership_check = sql_fetch(" SELECT qa_id FROM g5_quote WHERE qa_id = {$qa_id} AND mb_id = '{$mb_id_safe}' ");
    if ($ownership_check && $ownership_check['qa_id']) {
        $sql_item = " INSERT INTO g5_quote_item
                        SET qa_id = {$qa_id},
                            qi_index = 0,
                            qi_item = '{$sign_type_safe}',
                            qi_qty = 1,
                            qi_price = 0,
                            qi_amount = 0,
                            qi_note = '수정 시 추가됨' ";
        $result_item = sql_query($sql_item, false);
    } else {
        // 소유권 검증 실패 - 위에서 이미 체크됐으므로 이론상 도달 불가
        error_log("[SECURITY] quote_edit_update.php - item INSERT ownership check failed: qa_id={$qa_id}");
        $result_item = false;
    }
}

if (!$result_item) {
    error_log("[DB_ERROR] quote_edit_update.php - quote_item query failed: qa_id={$qa_id}");
    // 메인 견적은 저장됐으므로 item 실패는 경고만
}

alert('견적 요청 내용이 수정되었습니다.', './quote_detail.php?id=' . $qa_id);
?>
<?php
if (!defined("_GNUBOARD_"))
    exit;

// [7단계] 상담신청(글쓰기) 완료 후 quotes 테이블에도 데이터 저장
// 게시판: g5_write_consult -> 마이페이지: quotes 테이블 연동


// 디버깅 변수 초기화 (tail.skin.php 로 전달)
$quote_debug_info = array();
$quote_debug_info['skin_loaded'] = 'Y';
$quote_debug_info['w_value'] = $w;
$quote_debug_info['is_member'] = $is_member ? 'Y' : 'N';
$quote_debug_info['mb_id'] = $member['mb_id'];

// 디버깅 로그 (조건문 밖으로 이동)
$log_path = G5_DATA_PATH . '/quote_debug.log';
$log_msg = date('Y-m-d H:i:s') . " - write_update.skin.php loaded\n";
$log_msg .= "w: " . $w . ", is_member: " . ($is_member ? 'true' : 'false') . "\n";
file_put_contents($log_path, $log_msg, FILE_APPEND);

if ($w == '' && $is_member) { // 새 글 작성이고, 로그인한 회원인 경우

    $quote_debug_info['in_if_block'] = 'Y';
    $log_msg = "Inside If Block\n";
    $log_msg .= "Member ID: " . $member['mb_id'] . "\n";
    file_put_contents($log_path, $log_msg, FILE_APPEND);


    // quotes 테이블 스키마에 맞춰 데이터 정리
    $param = array();
    $param['mb_id'] = $member['mb_id'];
    $param['sign_type'] = isset($_POST['wr_1']) ? clean_xss_tags(trim($_POST['wr_1'])) : '';
    $param['content'] = isset($_POST['wr_5']) ? clean_xss_tags(trim($_POST['wr_5'])) : '';

    // location, width, height는 현재 폼에 없으므로 공란 또는 추후 확장
    $param['location'] = '';
    $param['width'] = '';
    $param['height'] = '';

    // -------------------------------------------------------------------------
    // [Mod] g5_quote 테이블로 통합 (admin_quote.php 와 연동)
    // -------------------------------------------------------------------------



    // 1. 견적 코드 생성 (Q-YYYYMMDD-SEQ)
    $today_prefix = 'Q-' . date('Ymd') . '-';
    $row = sql_fetch(" select count(*) as cnt from g5_quote where qa_code like '{$today_prefix}%' ");
    // $row가 false일 경우 대비
    $cnt = isset($row['cnt']) ? (int) $row['cnt'] : 0;
    $seq = $cnt + 1;
    $new_code = $today_prefix . sprintf('%03d', $seq);

    // 2. Insert g5_quote
    $qa_subject = $param['sign_type'] . " 상세 견적 요청"; // 제목 자동 생성 (기본값)
    if (isset($_POST['wr_subject']) && $_POST['wr_subject']) {
        $qa_subject = $_POST['wr_subject'];
    }

    // safe variables
    $safe_wr_subject = sql_real_escape_string(strip_tags($qa_subject));
    // wr_phone은 write_update.php 표준 변수가 아니므로 POST에서 직접 수신
    $safe_wr_phone = isset($_POST['wr_phone']) ? sql_real_escape_string(trim($_POST['wr_phone'])) : (isset($member['mb_hp']) ? $member['mb_hp'] : '');
    $safe_wr_name = isset($wr_name) ? sql_real_escape_string($wr_name) : (isset($member['mb_name']) ? sql_real_escape_string($member['mb_name']) : '');
    $safe_wr_email = isset($wr_email) ? sql_real_escape_string($wr_email) : (isset($member['mb_email']) ? sql_real_escape_string($member['mb_email']) : '');

    // 내용 정리 (DB 컬럼 부족으로 메모 필드에 통합)
    $safe_wr_content = isset($_POST['wr_content']) ? $_POST['wr_content'] : $param['content'];
    if (isset($_POST['wr_3']) && $_POST['wr_3'])
        $safe_wr_content .= "\n\n[예산] " . $_POST['wr_3'];
    if (isset($_POST['wr_4']) && $_POST['wr_4'])
        $safe_wr_content .= "\n[오픈예정일] " . $_POST['wr_4'];

    $safe_wr_content = sql_real_escape_string($safe_wr_content);

    // portfolio_ids 처리 (배열일 수 있음)
    $portfolio_ids = '';
    if (isset($_POST['portfolio_ids'])) {
        if (is_array($_POST['portfolio_ids'])) {
            $portfolio_ids = implode(',', $_POST['portfolio_ids']);
        } else {
            $portfolio_ids = $_POST['portfolio_ids'];
        }
    }
    $safe_portfolio_ids = sql_real_escape_string($portfolio_ids);

    // qa_content, qa_budget_range, qa_expected_date 컬럼이 없으므로 제거
    // wr_id 컬럼도 g5_quote에 없으므로 제거
    $sql = " INSERT INTO g5_quote
                SET qa_code = '{$new_code}',
                    mb_id = '{$param['mb_id']}', 
                    qa_status = '작성중',
                    qa_subject = '{$safe_wr_subject}',
                    qa_client_name = '{$safe_wr_name}',
                    qa_client_contact = '{$safe_wr_phone}',
                    qa_client_hp = '{$safe_wr_phone}',
                    qa_client_email = '{$safe_wr_email}',
                    qa_client_addr = '', 
                    qa_memo = '',
                    qa_memo_user = '{$safe_wr_content}',
                    qa_datetime = '" . G5_TIME_YMDHIS . "' ";


    $result = sql_query($sql, false);
    $quote_debug_info['insert_success'] = $result ? 'Y' : 'N';

    // Enhanced error logging
    if (!$result) {
        $error_msg = 'SQL Query Failed';
        if (function_exists('sql_error')) {
            $error_msg .= ': ' . sql_error();
        }
        $quote_debug_info['sql_error'] = $error_msg;

        // Log to file for debugging
        $error_log = date('Y-m-d H:i:s') . " - SQL Error\n";
        $error_log .= "Query: " . $sql . "\n";
        $error_log .= "Error: " . $error_msg . "\n";
        file_put_contents($log_path, $error_log, FILE_APPEND);

        // Display error to user (only in development)
        die("데이터 저장 중 오류가 발생했습니다. 관리자에게 문의해주세요.<br>Error: " . htmlspecialchars($error_msg));
    }

    if ($result) {
        $qa_id = sql_insert_id();

        // 3. Insert Items
        if ($param['sign_type']) {
            $safe_sign_type = sql_real_escape_string($param['sign_type']);
            $sql_item = " INSERT INTO g5_quote_item
                            SET qa_id = '{$qa_id}',
                                qi_index = 0,
                                qi_item = '{$safe_sign_type}',
                                qi_qty = 1,
                                qi_price = 0,
                                qi_amount = 0,
                                qi_note = '상담 신청 시 선택한 종류' ";
            sql_query($sql_item);
        }
    }

    $quote_debug_info['insert_success'] = 'COMPLETED';


}

// -----------------------------------------------------------------------------
// [SMS] 새 글 등록 시 관리자에게 SMS 알림 발송
// -----------------------------------------------------------------------------
if ($w == '' && $config['cf_sms_use'] == 'icode') {

    // (선택) 블록 진입 확인용
    @file_put_contents(
        G5_DATA_PATH . '/log/consult_sms.step',
        date('c') . " ENTER w={$w} wr_id={$wr_id}\n",
        FILE_APPEND
    );

    // 디버그 로그 강제 출력
    ini_set('log_errors', '1');
    ini_set('error_log', G5_DATA_PATH . '/log/consult_sms.log');
    error_log('[consult SMS] entered. w=' . $w . ' wr_id=' . $wr_id);

    try {
        include_once(G5_LIB_PATH . '/icode.sms.lib.php');

        $sms_recv_number = preg_replace('/[^0-9]/', '', '01097979768'); // 수신(관리자)
        $sms_send_number = preg_replace('/[^0-9]/', '', '16008319'); // 발신
        $sms_message = "[새 문의]\nhttps://간판대학.com/c.php?id={$wr_id}";

        if (!$sms_recv_number || !$sms_send_number) {
            error_log('[consult SMS] Skip: recv=' . ($sms_recv_number ?: 'empty') . ', send=' . ($sms_send_number ?: 'empty'));

            @file_put_contents(
                G5_DATA_PATH . '/log/consult_sms.step',
                date('c') . " SKIP empty number\n",
                FILE_APPEND
            );

        } else {

            $SMS = new SMS;

            // iCode 서버 연결
            $SMS->SMS_con(
                $config['cf_icode_server_ip'],
                $config['cf_icode_id'],
                $config['cf_icode_pw'],
                $config['cf_icode_server_port']
            );

            // 메시지 등록
            $SMS->Add(
                $sms_recv_number,
                $sms_send_number,
                $config['cf_icode_id'],
                iconv('utf-8', 'euc-kr//IGNORE', $sms_message),
                ''
            );

            // 실제 전송 + 결과 로그 (여기서만 Send 호출 1번!)
            $send_result = $SMS->Send();

            @file_put_contents(
                G5_DATA_PATH . '/log/consult_sms.step',
                date('c') . " SEND_RESULT: " . print_r($send_result, true) . "\n",
                FILE_APPEND
            );

            error_log('[consult SMS] SEND_RESULT: ' . print_r($send_result, true));
        }

    } catch (Throwable $e) {
        error_log('[consult SMS] EXCEPTION: ' . $e->getMessage());

        @file_put_contents(
            G5_DATA_PATH . '/log/consult_sms.step',
            date('c') . " EXCEPTION: " . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }
}

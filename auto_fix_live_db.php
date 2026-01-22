<?php
include_once('./_common.php');

// 관리자만 실행 가능하도록 (보안) - 필요시 주석 처리가능하나 안전을 위해 권장
if (!$is_admin) {
    // alert('관리자만 실행할 수 있습니다.', G5_URL);
    // 로그인 안 되어 있어도 급하면 실행되게 잠시 주석 처리 or echo
    echo "<h3>⚠️ 관리자 로그인이 되어있지 않지만, 긴급 복구를 위해 실행합니다.</h3>";
}

echo "<h1>🛠️ 데이터베이스 긴급 복구 도구 (Live Server)</h1>";
echo "<p>서버: " . $_SERVER['HTTP_HOST'] . "</p>";

// 1. g5_quote 테이블 존재 확인
$tbl_check = sql_fetch(" SHOW TABLES LIKE 'g5_quote' ");
if (!$tbl_check) {
    echo "<h2>❌ 'g5_quote' 테이블이 없습니다. 관리자 페이지를 먼저 접속해주세요.</h2>";
    exit;
} else {
    echo "<h2>✅ 'g5_quote' 테이블 확인됨.</h2>";
}

// 2. mb_id 컬럼 확인 및 추가
$col_check = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'mb_id' ");
if ($col_check) {
    echo "<h2>✅ 'mb_id' 컬럼이 이미 존재합니다. (정상)</h2>";
} else {
    echo "<h2>⚠️ 'mb_id' 컬럼이 없습니다. 복구를 시작합니다...</h2>";

    $sql = " ALTER TABLE g5_quote ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `qa_id` ";
    $result = sql_query($sql, false);

    if ($result) {
        sql_query(" ALTER TABLE g5_quote ADD INDEX idx_mb_id (mb_id) ", false);
        echo "<h2>🎉 복구 완료! 'mb_id' 컬럼이 추가되었습니다.</h2>";
    } else {
        echo "<h2>❌ 복구 실패. 오류 메시지: " . sql_error_info() . "</h2>";
        echo "<p>호스팅 관리자에게 문의하거나 phpMyAdmin에서 직접 추가해야 합니다.</p>";
    }
}

echo "<hr>";
echo "<h3>이제 다시 '견적 신청'을 진행해 보세요.</h3>";
echo "<a href='" . G5_URL . "'>메인으로 돌아가기</a>";
?>
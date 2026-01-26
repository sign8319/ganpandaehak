<?php
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 마스터 DB 구조 수정</h1>";

// 1. g5_quote_options 테이블에서 product_id 컬럼 삭제
$check = sql_fetch("SHOW COLUMNS FROM g5_quote_options LIKE 'product_id'");
if ($check) {
    // 혹시 모를 데이터를 위해 백업 테이블 생성해둠
    sql_query("CREATE TABLE IF NOT EXISTS g5_quote_options_backup_final SELECT * FROM g5_quote_options");

    // 컬럼 삭제
    $sql = "ALTER TABLE g5_quote_options DROP COLUMN product_id";
    $result = sql_query($sql);

    if ($result) {
        echo "<p style='color: blue; font-weight: bold;'>✅ 성공: product_id 컬럼을 삭제했습니다.</p>";
        echo "<p>이제 옵션 추가 시 'product_id 없음' 오류가 발생하지 않습니다.</p>";
    } else {
        echo "<p style='color: red;'>❌ 실패: " . sql_error_info() . "</p>";
    }
} else {
    echo "<p style='color: green;'>ℹ️ 이미 product_id 컬럼이 삭제된 상태입니다.</p>";
}

echo "<hr>";
echo "<a href='admin_quote_options_master.php'>[옵션 관리 페이지로 돌아가기]</a>";
?>
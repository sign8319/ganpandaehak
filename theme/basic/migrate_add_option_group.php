<?php
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 그룹 기능 추가 (DB 업데이트)</h1>";

// g5_quote_options 테이블에 group_name 컬럼 추가
$check = sql_fetch("SHOW COLUMNS FROM g5_quote_options LIKE 'group_name'");
if (!$check) {
    $sql = "ALTER TABLE g5_quote_options ADD COLUMN `group_name` VARCHAR(50) NOT NULL DEFAULT '기본' AFTER `name`";
    $result = sql_query($sql);

    if ($result) {
        echo "<p style='color: blue; font-weight: bold;'>✅ 성공: group_name 컬럼을 추가했습니다.</p>";
        // 기존 데이터를 적절히 업데이트 (예시: 이름에 '미싱'이 들어가면 그룹을 '미싱'으로)
        sql_query("UPDATE g5_quote_options SET group_name = '가공' WHERE name LIKE '%미싱%' OR name LIKE '%타공%'");
        sql_query("UPDATE g5_quote_options SET group_name = '부자재' WHERE name LIKE '%큐방%' OR name LIKE '%끈%' OR name LIKE '%고리%'");
        echo "<p>기본 그룹 데이터 자동 분류 완료.</p>";
    } else {
        echo "<p style='color: red;'>❌ 실패: " . sql_error_info() . "</p>";
    }
} else {
    echo "<p style='color: green;'>ℹ️ 이미 group_name 컬럼이 존재합니다.</p>";
}

echo "<hr>";
echo "<a href='admin_quote_options_master.php'>[옵션 관리 페이지로 돌아가기]</a>";
?>
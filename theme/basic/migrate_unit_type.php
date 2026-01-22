<?php
/**
 * unit_type 컬럼 마이그레이션
 * 기존 g5_quote_options 테이블에 unit_type 컬럼 추가
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 테이블 마이그레이션 - unit_type 컬럼 추가</h1>";

// 컬럼 존재 여부 확인
$check_sql = "SHOW COLUMNS FROM g5_quote_options LIKE 'unit_type'";
$check_result = sql_query($check_sql);

if (sql_num_rows($check_result) > 0) {
    echo "<p style='color: green;'>✅ unit_type 컬럼이 이미 존재합니다.</p>";
} else {
    // 컬럼 추가
    $alter_sql = "ALTER TABLE g5_quote_options ADD COLUMN `unit_type` ENUM('fixed', 'per_m2') DEFAULT 'fixed' AFTER `discount`";
    sql_query($alter_sql);

    if (sql_error()) {
        echo "<p style='color: red;'>❌ 오류 발생: " . sql_error() . "</p>";
    } else {
        echo "<p style='color: green;'>✅ unit_type 컬럼 추가 완료!</p>";
    }
}

// 현재 테이블 구조 확인
echo "<h2>현재 g5_quote_options 테이블 구조</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

$desc_result = sql_query("DESCRIBE g5_quote_options");
while ($row = sql_fetch_array($desc_result)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><p><a href='admin_quote_manager.php'>← 설정 관리로 돌아가기</a></p>";
?>
<?php
/**
 * Pricing Mode 마이그레이션
 * g5_quote_products 테이블에 pricing_mode 컬럼 추가
 * 
 * 값: DEFAULT (기존 로직), AREA_TIER (면적 구간 요금), WIDTH (폭별 단가)
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>제품 테이블 마이그레이션 - Pricing Mode 지원</h1>";

$migrations = [
    [
        'name' => 'pricing_mode 컬럼 추가 (계산 방식 선택)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'pricing_mode'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `pricing_mode` ENUM('DEFAULT', 'AREA_TIER', 'WIDTH') DEFAULT 'DEFAULT' AFTER `use_width_pricing`"
    ]
];

echo "<ul>";
foreach ($migrations as $migration) {
    $check_result = sql_query($migration['check']);

    if (sql_num_rows($check_result) > 0) {
        echo "<li style='color: green;'>✅ {$migration['name']} - 이미 적용됨</li>";
    } else {
        sql_query($migration['sql']);
        if (sql_error()) {
            echo "<li style='color: red;'>❌ {$migration['name']} - 오류: " . sql_error() . "</li>";
        } else {
            echo "<li style='color: blue;'>🔄 {$migration['name']} - 적용 완료!</li>";
        }
    }
}
echo "</ul>";

// 테이블 구조 확인
echo "<h2>현재 g5_quote_products 테이블 구조</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Default</th></tr>";

$desc_result = sql_query("DESCRIBE g5_quote_products");
while ($row = sql_fetch_array($desc_result)) {
    $highlight = ($row['Field'] === 'pricing_mode') ? "style='background: #e6ffe6; font-weight: bold;'" : "";
    echo "<tr {$highlight}>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>📋 Pricing Mode 값 설명</h3>";
echo "<ul>";
echo "<li><strong>DEFAULT</strong>: 기존 로직 그대로 (use_width_pricing 플래그 참조)</li>";
echo "<li><strong>AREA_TIER</strong>: 면적 구간 요금 (수성현수막 전용)</li>";
echo "<li><strong>WIDTH</strong>: 폭별 단가 강제 사용</li>";
echo "</ul>";

echo "<h3>💡 AREA_TIER 면적 구간 요금표</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>조건</th><th>요금</th></tr>";
echo "<tr><td>area < 1㎡</td><td>장당 3,000원</td></tr>";
echo "<tr><td>1㎡ ≤ area < 3㎡</td><td>장당 6,000원</td></tr>";
echo "<tr><td>area ≥ 3㎡</td><td>㎡당 2,000원</td></tr>";
echo "<tr><td>roll_width ≥ 1800mm AND area ≥ 3㎡</td><td>㎡당 +1,000원 추가</td></tr>";
echo "</table>";

echo "<br><p><a href='admin_quote_manager.php'>← 설정 관리로 돌아가기</a></p>";
?>
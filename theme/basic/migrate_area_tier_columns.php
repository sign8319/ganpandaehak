<?php
/**
 * AREA_TIER 면적구간 요금 마이그레이션
 * g5_quote_products 테이블에 면적구간 요금 관련 컬럼 추가
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>제품 테이블 마이그레이션 - 면적구간 요금 지원</h1>";

$migrations = [
    [
        'name' => 'area_tier_piece_under_1 컬럼 추가 (1㎡ 미만 장당)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'area_tier_piece_under_1'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `area_tier_piece_under_1` INT NOT NULL DEFAULT 0 AFTER `pricing_mode`"
    ],
    [
        'name' => 'area_tier_piece_1_to_3 컬럼 추가 (1~3㎡ 장당)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'area_tier_piece_1_to_3'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `area_tier_piece_1_to_3` INT NOT NULL DEFAULT 0 AFTER `area_tier_piece_under_1`"
    ],
    [
        'name' => 'area_tier_m2_over_3 컬럼 추가 (3㎡ 이상 ㎡당)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'area_tier_m2_over_3'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `area_tier_m2_over_3` INT NOT NULL DEFAULT 0 AFTER `area_tier_piece_1_to_3`"
    ],
    [
        'name' => 'area_tier_surcharge_1800 컬럼 추가 (폭 1800 이상 ㎡당 추가)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'area_tier_surcharge_1800'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `area_tier_surcharge_1800` INT NOT NULL DEFAULT 0 AFTER `area_tier_m2_over_3`"
    ]
];

echo "<ul>";
foreach ($migrations as $migration) {
    $check_result = sql_query($migration['check']);

    if (sql_num_rows($check_result) > 0) {
        echo "<li style='color: green;'>✅ {$migration['name']} - 이미 적용됨</li>";
    } else {
        sql_query($migration['sql']);
        echo "<li style='color: blue;'>🔄 {$migration['name']} - 적용 완료!</li>";
    }
}
echo "</ul>";

// 테이블 구조 확인
echo "<h2>현재 g5_quote_products 테이블 - AREA_TIER 관련 컬럼</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Default</th></tr>";

$desc_result = sql_query("DESCRIBE g5_quote_products");
while ($row = sql_fetch_array($desc_result)) {
    $highlight = (strpos($row['Field'], 'area_tier') !== false || $row['Field'] === 'pricing_mode') ?
        "style='background: #e6ffe6; font-weight: bold;'" : "";
    echo "<tr {$highlight}>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>📋 AREA_TIER 컬럼 설명</h3>";
echo "<ul>";
echo "<li><strong>area_tier_piece_under_1</strong>: 1㎡ 미만 - 장당 요금 (원)</li>";
echo "<li><strong>area_tier_piece_1_to_3</strong>: 1~3㎡ - 장당 요금 (원)</li>";
echo "<li><strong>area_tier_m2_over_3</strong>: 3㎡ 이상 - ㎡당 요금 (원)</li>";
echo "<li><strong>area_tier_surcharge_1800</strong>: 폭 1800mm 이상 + 3㎡ 이상 시 - ㎡당 추가 요금 (원)</li>";
echo "</ul>";

echo "<h3>💡 예시 설정 (수성현수막)</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>컬럼</th><th>값</th><th>의미</th></tr>";
echo "<tr><td>area_tier_piece_under_1</td><td>3000</td><td>1㎡ 미만: 장당 3,000원</td></tr>";
echo "<tr><td>area_tier_piece_1_to_3</td><td>6000</td><td>1~3㎡: 장당 6,000원</td></tr>";
echo "<tr><td>area_tier_m2_over_3</td><td>2000</td><td>3㎡ 이상: ㎡당 2,000원</td></tr>";
echo "<tr><td>area_tier_surcharge_1800</td><td>1000</td><td>폭 할증: ㎡당 1,000원 추가</td></tr>";
echo "</table>";

echo "<br><p><a href='admin_quote_manager.php'>← 설정 관리로 돌아가기</a></p>";
?>
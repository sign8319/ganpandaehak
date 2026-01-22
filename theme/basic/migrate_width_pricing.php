<?php
/**
 * 폭별 단가 마이그레이션
 * g5_quote_products 테이블에 폭별 단가 관련 컬럼 추가
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>제품 테이블 마이그레이션 - 폭별 단가 지원</h1>";

$migrations = [
    [
        'name' => 'price_small 컬럼 추가 (단폭 단가)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'price_small'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `price_small` DECIMAL(10,2) DEFAULT NULL AFTER `unit_price`"
    ],
    [
        'name' => 'price_large 컬럼 추가 (장폭 단가)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'price_large'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `price_large` DECIMAL(10,2) DEFAULT NULL AFTER `price_small`"
    ],
    [
        'name' => 'price_xlarge 컬럼 추가 (초장폭 단가)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'price_xlarge'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `price_xlarge` DECIMAL(10,2) DEFAULT NULL AFTER `price_large`"
    ],
    [
        'name' => 'width_surcharge_1800 컬럼 추가 (폭 할증)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'width_surcharge_1800'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `width_surcharge_1800` DECIMAL(10,2) DEFAULT 0 AFTER `price_xlarge`"
    ],
    [
        'name' => 'use_width_pricing 컬럼 추가 (폭별 단가 사용 여부)',
        'check' => "SHOW COLUMNS FROM g5_quote_products LIKE 'use_width_pricing'",
        'sql' => "ALTER TABLE g5_quote_products ADD COLUMN `use_width_pricing` TINYINT(1) DEFAULT 0 AFTER `width_surcharge_1800`"
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
    $highlight = in_array($row['Field'], ['price_small', 'price_large', 'price_xlarge', 'width_surcharge_1800', 'use_width_pricing']) ?
        "style='background: #e6ffe6;'" : "";
    echo "<tr {$highlight}>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>📋 폭 판정 기준</h3>";
echo "<ul>";
echo "<li><strong>단폭</strong>: roll_width ≤ 2100mm</li>";
echo "<li><strong>장폭</strong>: 2100 < roll_width ≤ 3100mm</li>";
echo "<li><strong>초장폭</strong>: 3100 < roll_width ≤ 4800mm</li>";
echo "<li><strong>제작불가</strong>: roll_width > 4800mm</li>";
echo "<li><em>roll_width = min(가로, 세로)</em></li>";
echo "</ul>";

echo "<br><p><a href='admin_quote_manager.php'>← 설정 관리로 돌아가기</a></p>";
?>
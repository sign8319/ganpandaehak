<?php
/**
 * 수량형 옵션 마이그레이션
 * g5_quote_options 테이블에 quantity 관련 컬럼 추가
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 테이블 마이그레이션 - 수량형 옵션 지원</h1>";

$migrations = [
    [
        'name' => 'unit_type에 quantity 추가',
        'check' => "SHOW COLUMNS FROM g5_quote_options WHERE Field = 'unit_type' AND Type LIKE '%quantity%'",
        'sql' => "ALTER TABLE g5_quote_options MODIFY COLUMN `unit_type` ENUM('fixed', 'per_m2', 'quantity') DEFAULT 'fixed'"
    ],
    [
        'name' => 'free_qty 컬럼 추가',
        'check' => "SHOW COLUMNS FROM g5_quote_options LIKE 'free_qty'",
        'sql' => "ALTER TABLE g5_quote_options ADD COLUMN `free_qty` INT(11) DEFAULT 0 AFTER `unit_type`"
    ],
    [
        'name' => 'qty_unit_price 컬럼 추가',
        'check' => "SHOW COLUMNS FROM g5_quote_options LIKE 'qty_unit_price'",
        'sql' => "ALTER TABLE g5_quote_options ADD COLUMN `qty_unit_price` DECIMAL(10,2) DEFAULT 0 AFTER `free_qty`"
    ],
    [
        'name' => 'default_qty 컬럼 추가',
        'check' => "SHOW COLUMNS FROM g5_quote_options LIKE 'default_qty'",
        'sql' => "ALTER TABLE g5_quote_options ADD COLUMN `default_qty` INT(11) DEFAULT 0 AFTER `qty_unit_price`"
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

// 현재 테이블 구조 확인
echo "<h2>현재 g5_quote_options 테이블 구조</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

$desc_result = sql_query("DESCRIBE g5_quote_options");
while ($row = sql_fetch_array($desc_result)) {
    $highlight = in_array($row['Field'], ['unit_type', 'free_qty', 'qty_unit_price', 'default_qty']) ?
        "style='background: #e6ffe6;'" : "";
    echo "<tr {$highlight}>";
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
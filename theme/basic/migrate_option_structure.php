<?php
/**
 * 옵션 구조 리팩토링 마이그레이션
 * 1. 매핑 테이블 생성 (g5_quote_product_options)
 * 2. 기존 옵션 데이터를 매핑 테이블로 이관
 * 3. 기존 옵션 테이블의 product_id 의존성 제거 준비 (데이터는 유지)
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 구조 리팩토링 마이그레이션</h1>";

// 1. 매핑 테이블 생성
$sql = "SHOW TABLES LIKE 'g5_quote_product_options'";
$row = sql_fetch($sql);

if (!$row) {
    echo "<li>매핑 테이블 생성 중...</li>";
    $sql = "CREATE TABLE IF NOT EXISTS `g5_quote_product_options` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `option_id` int(11) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `product_id` (`product_id`),
        KEY `option_id` (`option_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    sql_query($sql);
    echo "<li style='color: blue;'>✅ g5_quote_product_options 테이블 생성 완료</li>";
} else {
    echo "<li style='color: green;'>✅ g5_quote_product_options 테이블 이미 존재함</li>";
}

// 2. 데이터 이관 (기존 product_id가 있는 옵션들을 매핑 테이블로 복사)
// 이미 매핑된 데이터가 있는지 확인하여 중복 실행 방지
$sql = "SELECT COUNT(*) as cnt FROM g5_quote_product_options";
$row = sql_fetch($sql);

if ($row['cnt'] == 0) {
    echo "<li>기존 데이터 이관 중...</li>";
    $sql = "INSERT INTO g5_quote_product_options (product_id, option_id, sort_order)
            SELECT product_id, id, sort_order 
            FROM g5_quote_options 
            WHERE product_id > 0";
    $result = sql_query($sql);
    echo "<li style='color: blue;'>✅ 데이터 이관 완료 (" . sql_affected_rows() . "건)</li>";
} else {
    echo "<li style='color: green;'>✅ 데이터 이관 이미 완료됨 ({$row['cnt']}건)</li>";
}

// 3. g5_quote_options의 product_id 컬럼 속성 변경 (NULL 허용)
// 기존 데이터 보존을 위해 컬럼 삭제는 하지 않음
$sql = "ALTER TABLE g5_quote_options MODIFY COLUMN product_id INT(11) NULL DEFAULT 0";
sql_query($sql);
echo "<li style='color: blue;'>✅ g5_quote_options.product_id 컬럼 속성 변경 (NULL 허용)</li>";

echo "<br><h3>테이블 구조 확인</h3>";
echo "<h4>g5_quote_product_options</h4>";
$desc = sql_query("DESCRIBE g5_quote_product_options");
echo "<table border='1'><tr><th>Field</th><th>Type</th></tr>";
while ($r = sql_fetch_array($desc)) {
    echo "<tr><td>{$r['Field']}</td><td>{$r['Type']}</td></tr>";
}
echo "</table>";

echo "<br><p>마이그레이션이 완료되었습니다.</p>";
echo "<p><a href='admin_quote_options.php'>옵션 관리로 돌아가기</a></p>";
?>
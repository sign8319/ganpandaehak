<?php
/**
 * 옵션 시스템 마이그레이션 (옵션 마스터 도입)
 * 1. g5_quote_product_options 연결 테이블 생성
 * 2. 기존 옵션 데이터 백업 (g5_quote_options_backup)
 * 3. 중복 옵션 통합하여 g5_quote_options 마스터 데이터 생성
 * 4. 기존 테이블 스키마 변경 (product_id 제거)
 */
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>옵션 시스템 마이그레이션 - 마스터 구조 전환</h1>";

// 1. 연결 테이블 생성
$sql = "CREATE TABLE IF NOT EXISTS `g5_quote_product_options` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `option_id` INT NOT NULL,
    `is_active` TINYINT DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_product_option` (`product_id`, `option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

if (sql_query($sql)) {
    echo "<p style='color: blue;'>✅ 연결 테이블(g5_quote_product_options) 생성 또는 확인 완료</p>";
} else {
    echo "<p style='color: red;'>❌ 연결 테이블 생성 실패: " . sql_error_info() . "</p>";
    exit;
}

// 2. 백업 테이블 생성 (데이터 보존용)
$check_backup = sql_fetch("SHOW TABLES LIKE 'g5_quote_options_backup'");
if (!$check_backup) {
    sql_query("CREATE TABLE g5_quote_options_backup SELECT * FROM g5_quote_options");
    echo "<p style='color: blue;'>✅ 기존 옵션 데이터 백업 완료 (g5_quote_options_backup)</p>";
} else {
    echo "<p style='color: green;'>ℹ️ 백업 테이블이 이미 존재합니다.</p>";
}

// 3. 마이그레이션 실행 여부 확인 (product_id 컬럼 유무로 판단)
$col_check = sql_fetch("SHOW COLUMNS FROM g5_quote_options LIKE 'product_id'");
if (!$col_check) {
    echo "<p style='color: green;'>✅ 이미 마이그레이션이 완료된 상태입니다. (product_id 컬럼 없음)</p>";
    echo "<p><a href='admin_quote_manager.php'>설정 관리로 돌아가기</a></p>";
    exit;
}

echo "<hr>";
echo "<h3>데이터 이관 및 구조 변경 진행</h3>";

// 4. 유니크 옵션 추출 (이름, 가격, 단위, 타입이 같은 것끼리 그룹핑)
$sql_unique = "SELECT name, price, unit, type, is_active 
               FROM g5_quote_options 
               GROUP BY name, price, unit, type 
               ORDER BY id ASC";
$result = sql_query($sql_unique);
$unique_options = [];
while ($row = sql_fetch_array($result)) {
    $unique_options[] = $row;
}

$count_total = sql_fetch("SELECT count(*) as cnt FROM g5_quote_options")['cnt'];
$count_unique = count($unique_options);

echo "<p>📊 기존 옵션: <strong>{$count_total}개</strong> → 통합된 마스터 옵션: <strong>{$count_unique}개</strong></p>";

// 5. 테이블 비우기 및 마스터 데이터 삽입
sql_query("TRUNCATE TABLE g5_quote_options");

$success_cnt = 0;
foreach ($unique_options as $opt) {
    $ins_sql = "INSERT INTO g5_quote_options 
                SET name = '" . sql_real_escape_string($opt['name']) . "',
                    price = '{$opt['price']}',
                    unit = '" . sql_real_escape_string($opt['unit']) . "',
                    type = '" . sql_real_escape_string($opt['type']) . "',
                    is_active = 1"; // 마스터 옵션은 일단 모두 활성화
    if (sql_query($ins_sql)) {
        $success_cnt++;
    }
}
echo "<p style='color: blue;'>✅ 마스터 데이터 {$success_cnt}건 생성 완료</p>";

// 6. 스키마 변경 (product_id 컬럼 삭제)
$alter_sql = "ALTER TABLE g5_quote_options DROP COLUMN product_id";
if (sql_query($alter_sql)) {
    echo "<p style='color: blue;'>✅ g5_quote_options 테이블 스키마 변경 완료 (product_id 삭제)</p>";
} else {
    echo "<p style='color: red;'>❌ 스키마 변경 실패: " . sql_error_info() . "</p>";
}

echo "<hr>";
echo "<h3>🎉 마이그레이션이 성공적으로 완료되었습니다!</h3>";
echo "<p>이제 제품 관리 화면에서 옵션을 직접 연결해주세요.</p>";
echo "<p><a href='admin_quote_manager.php'>설정 관리로 돌아가기</a></p>";
?>
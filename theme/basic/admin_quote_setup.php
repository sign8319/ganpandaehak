<?php
include_once('./_common.php');

if (!$is_admin) {
  die('관리자만 접근 가능합니다.');
}

echo "<h1>견적 시스템 DB 업데이트 (올림 설정 추가)</h1>";
echo "<p>apply_rounding 컬럼을 추가합니다...</p>";
echo "<hr>";

// 1. 기존 테이블 삭제
$old_tables = ['quote_options', 'quote_products', 'quote_subcategories', 'quote_categories', 'quote_history'];
foreach ($old_tables as $table) {
  sql_query("DROP TABLE IF EXISTS `{$table}`");
  echo "<p class='text-gray-500'>🗑️ 기존 테이블 삭제: {$table}</p>";
}

// 2. 새 테이블 생성 (apply_rounding 추가)
$tables = [
  "CREATE TABLE IF NOT EXISTS `g5_quote_categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `category_key` varchar(50) NOT NULL,
      `name` varchar(100) NOT NULL,
      `icon` varchar(255) DEFAULT NULL,
      `sort_order` int(11) DEFAULT 0,
      `is_active` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`id`),
      UNIQUE KEY `category_key` (`category_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

  "CREATE TABLE IF NOT EXISTS `g5_quote_subcategories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `category_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `sort_order` int(11) DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

  "CREATE TABLE IF NOT EXISTS `g5_quote_products` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `subcategory_id` int(11) NOT NULL,
      `name` varchar(200) NOT NULL,
      `unit_price` decimal(10,2) NOT NULL,
      `unit` varchar(20) NOT NULL,
      `calc_type` enum('area','text','length','fixed') NOT NULL,
      `apply_rounding` tinyint(1) DEFAULT 0,
      `min_area` decimal(10,2) DEFAULT NULL,
      `base_size` int(11) DEFAULT NULL,
      `description` text,
      `sort_order` int(11) DEFAULT 0,
      `is_active` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

  "CREATE TABLE IF NOT EXISTS `g5_quote_options` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `price` decimal(10,2) DEFAULT 0,
      `discount` decimal(5,4) DEFAULT NULL,
      `sort_order` int(11) DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

  "CREATE TABLE IF NOT EXISTS `g5_quote_history` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `transaction_count` int(11) DEFAULT 0,
      `avg_spec` varchar(100) DEFAULT NULL,
      `avg_price` decimal(10,2) DEFAULT 0,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($tables as $sql) {
  sql_query($sql);
}
echo "<p>✨ 새 테이블 생성 완료 (apply_rounding 컬럼 포함)</p>";

// 3. TRUNCATE
sql_query("TRUNCATE TABLE g5_quote_categories");
sql_query("TRUNCATE TABLE g5_quote_subcategories");
sql_query("TRUNCATE TABLE g5_quote_products");
sql_query("TRUNCATE TABLE g5_quote_options");

// 4. 카테고리 데이터
$categories = [
  ['channel', '채널', '[CH]', 1],
  ['flex', '플렉스/천갈이', '[FL]', 2],
  ['frame', '프레임', '[FR]', 3],
  ['print', '실사출력', '[PR]', 4],
  ['awning', '어닝', '[AW]', 5],
  ['parts', '부자재', '[PT]', 6]
];

foreach ($categories as $cat) {
  sql_query("INSERT INTO g5_quote_categories (category_key, name, icon, sort_order, is_active) VALUES ('{$cat[0]}', '{$cat[1]}', '{$cat[2]}', {$cat[3]}, 1)");
}

echo "<hr>";
echo "<h2>✅ 업데이트 완료!</h2>";
echo "<p><a href='admin_quote_insert_data.php'>데이터 입력하기</a></p>";
?>
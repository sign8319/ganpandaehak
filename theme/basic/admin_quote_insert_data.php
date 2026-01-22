<?php
include_once('./_common.php');
if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>견적 시스템 초기 데이터 입력</h1>";
echo "<p>제품 및 옵션 데이터를 자동으로 입력합니다 (올림 설정 포함)...</p>";
echo "<hr>";

$categories = [];
$cat_result = sql_query("SELECT id, category_key FROM g5_quote_categories");
while ($row = sql_fetch_array($cat_result)) {
    $categories[$row['category_key']] = $row['id'];
}

// 채널
echo "<h2>📌 채널 데이터 입력</h2>";
$cat_id = $categories['channel'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '알루미늄', 1)");
$sub_al = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, base_size, apply_rounding, description, sort_order, is_active) VALUES ($sub_al, 'A/L캡타카', 50000, '자', 'text', 450, 0, '알루미늄 캡타카 채널', 1, 1)");
$prod_al = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($prod_al, 'LED모듈', 15000, 1)");
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '갈바', 2)");
$sub_galva = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, base_size, apply_rounding, description, sort_order, is_active) VALUES ($sub_galva, '갈바후광채널', 220000, '자', 'text', 450, 0, '갈바 후광 채널', 1, 1)");
$prod_g1 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($prod_g1, 'LED모듈', 30000, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, base_size, apply_rounding, description, sort_order, is_active) VALUES ($sub_galva, '갈바전광채널', 280000, '자', 'text', 450, 0, '갈바 전광 채널', 2, 1)");
$prod_g2 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($prod_g2, 'LED모듈', 40000, 1)");
echo "<p>✓ 채널 3개 제품 입력 완료</p>";

// 플렉스/천갈이
echo "<hr><h2>📌 플렉스/천갈이 데이터 입력</h2>";
$cat_id = $categories['flex'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '출력만', 1)");
$sub_out = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_out, '양면Flex', 8500, '㎡', 'area', 0.5, 1, '양면 인쇄 플렉스', 1, 1)");
$pf1 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf1, '텐션', 0, 1)");
sql_query("INSERT INTO g5_quote_options (product_id, name, price, discount, sort_order) VALUES ($pf1, '오전할인 (6%)', 0, 0.06, 2)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_out, 'UV조명Flex', 7000, '㎡', 'area', 0.5, 1, 'UV 조명용 플렉스', 2, 1)");
$pf2 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf2, '텐션', 0, 1)");
sql_query("INSERT INTO g5_quote_options (product_id, name, price, discount, sort_order) VALUES ($pf2, '오전할인 (6%)', 0, 0.06, 2)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_out, 'UV비조명Flex', 6500, '㎡', 'area', 0.5, 1, 'UV 비조명용 플렉스', 3, 1)");
$pf3 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf3, '텐션', 0, 1)");
sql_query("INSERT INTO g5_quote_options (product_id, name, price, discount, sort_order) VALUES ($pf3, '오전할인 (6%)', 0, 0.06, 2)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_out, '현수막', 3500, '㎡', 'area', 0.5, 1, '일반 현수막', 4, 1)");
$pb = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, discount, sort_order) VALUES ($pb, '오전할인 (6%)', 0, 0.06, 1)");

sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '프레임 포함', 2)");
$sub_frame = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_frame, '전면조명', 36000, '㎡', 'area', 0.5, 1, '전면 조명형 간판', 1, 1)");
$pf = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf, 'LED추가', 50000, 1)");
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf, '도장', 30000, 2)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_frame, '전면비조명', 26000, '㎡', 'area', 0.5, 1, '전면 비조명형 간판', 2, 1)");
$pf2 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf2, '도장', 30000, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sub_frame, '돌출조명', 62000, '㎡', 'area', 0.5, 1, '돌출형 조명 간판', 3, 1)");
$pd = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pd, 'LED추가', 80000, 1)");
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pd, '도장', 50000, 2)");
echo "<p>✓ 플렉스 7개 제품 입력 완료</p>";

// 프레임
echo "<hr><h2>📌 프레임 데이터 입력</h2>";
$cat_id = $categories['frame'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '조명형', 1)");
$sl = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sl, '전면조명 단독', 36000, '㎡', 'area', 0.5, 1, '프레임 조명형', 1, 1)");
$pf1 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($pf1, 'LED모듈추가', 50000, 1)");
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '비조명형', 2)");
$snl = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($snl, '전면비조명 단독', 26000, '㎡', 'area', 0.5, 1, '프레임 비조명형', 1, 1)");
echo "<p>✓ 프레임 2개 제품 입력 완료</p>";

// 실사출력
echo "<hr><h2>📌 실사출력 데이터 입력</h2>";
$cat_id = $categories['print'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '일반', 1)");
$sp = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sp, 'UV백색', 7000, '㎡', 'area', 0.5, 1, 'UV 백색 인쇄', 1, 1)");
$puv1 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($puv1, '코팅', 3000, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, min_area, apply_rounding, description, sort_order, is_active) VALUES ($sp, 'UV투명', 7500, '㎡', 'area', 0.5, 1, 'UV 투명 인쇄', 2, 1)");
$puv2 = sql_insert_id();
sql_query("INSERT INTO g5_quote_options (product_id, name, price, sort_order) VALUES ($puv2, '코팅', 3000, 1)");
echo "<p>✓ 실사출력 2개 제품 입력 완료</p>";

// 어닝
echo "<hr><h2>📌 어닝 데이터 입력</h2>";
$cat_id = $categories['awning'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '국산', 1)");
$sd = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sd, '국산수동 3M', 300000, '개', 'fixed', 0, '국산 수동 차양막 3M', 1, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sd, '국산수동 4M', 450000, '개', 'fixed', 0, '국산 수동 차양막 4M', 2, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sd, '국산수동 5M', 600000, '개', 'fixed', 0, '국산 수동 차양막 5M', 3, 1)");
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '천갈이', 2)");
$sr = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sr, '천갈이 3M', 42000, '개', 'fixed', 0, '차양막 천 교체 3M', 1, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sr, '천갈이 4M', 96000, '개', 'fixed', 0, '차양막 천 교체 4M', 2, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sr, '천갈이 5M', 150000, '개', 'fixed', 0, '차양막 천 교체 5M', 3, 1)");
echo "<p>✓ 어닝 6개 제품 입력 완료</p>";

// 부자재
echo "<hr><h2>📌 부자재 데이터 입력</h2>";
$cat_id = $categories['parts'];
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, 'LED', 1)");
$sled = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sled, 'LED모듈', 250, '개', 'fixed', 0, 'LED 모듈', 1, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sled, 'SMPS 60W', 15000, '개', 'fixed', 0, 'LED용 전원공급장치 60W', 2, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sled, 'SMPS 120W', 25000, '개', 'fixed', 0, 'LED용 전원공급장치 120W', 3, 1)");
sql_query("INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($cat_id, '프레임부자재', 2)");
$sfp = sql_insert_id();
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sfp, '까치발 1000mm', 5000, '개', 'fixed', 0, '간판 지지대 1000mm', 1, 1)");
sql_query("INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, description, sort_order, is_active) VALUES ($sfp, '까치발 1500mm', 7000, '개', 'fixed', 0, '간판 지지대 1500mm', 2, 1)");
echo "<p>✓ 부자재 5개 제품 입력 완료</p>";

echo "<hr>";
echo "<h2>✅ 모든 데이터 입력 완료!</h2>";
echo "<p><strong>총:</strong> 카테고리 6개 / 서브카테고리 12개 / 제품 27개 / 옵션 29개</p>";
echo "<p><strong>올림 설정:</strong> 면적 계산 제품(플렉스, 프레임, 실사출력)은 올림 적용 ✅</p>";
echo "<hr>";
echo "<p><a href='admin_quote_calc.php' style='padding:10px 20px; background:#2563eb; color:white; text-decoration:none; border-radius:5px;'>계산기로 이동</a></p>";
?>
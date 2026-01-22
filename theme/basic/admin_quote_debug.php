<?php
include_once('./_common.php');

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

echo "<h1>견적 시스템 DB 디버그 (g5_quote_*)</h1>";
echo "<hr>";

// 1. 카테고리 확인
echo "<h2>📁 g5_quote_categories 테이블</h2>";
$result = sql_query("SELECT * FROM g5_quote_categories ORDER BY sort_order");
$cat_count = 0;
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Key</th><th>Name</th><th>Icon</th><th>Sort</th><th>Active</th></tr>";
while ($row = sql_fetch_array($result)) {
    $cat_count++;
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['category_key']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['icon']}</td>";
    echo "<td>{$row['sort_order']}</td>";
    echo "<td>{$row['is_active']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>총 {$cat_count}개 카테고리</strong></p>";

// 2. 서브카테고리 확인
echo "<hr><h2>📂 g5_quote_subcategories 테이블</h2>";
$result = sql_query("SELECT s.*, c.name as cat_name FROM g5_quote_subcategories s LEFT JOIN g5_quote_categories c ON s.category_id = c.id ORDER BY s.category_id, s.sort_order");
$sub_count = 0;
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Category ID</th><th>Category Name</th><th>Subcategory Name</th><th>Sort</th></tr>";
while ($row = sql_fetch_array($result)) {
    $sub_count++;
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['category_id']}</td>";
    echo "<td>" . ($row['cat_name'] ?: '<span style="color:red">NULL (문제!)</span>') . "</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['sort_order']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>총 {$sub_count}개 서브카테고리</strong></p>";

// 3. 제품 수
echo "<hr><h2>📦 g5_quote_products 테이블</h2>";
$prod_count = sql_fetch("SELECT COUNT(*) as cnt FROM g5_quote_products");
echo "<p><strong>총 {$prod_count['cnt']}개 제품</strong></p>";

// 4. 옵션 수
echo "<hr><h2>🔧 g5_quote_options 테이블</h2>";
$opt_count = sql_fetch("SELECT COUNT(*) as cnt FROM g5_quote_options");
echo "<p><strong>총 {$opt_count['cnt']}개 옵션</strong></p>";

echo "<hr>";
echo "<h2>🛠️ 문제 해결 도구</h2>";

if ($cat_count < 6) {
    echo "<p style='color:red; font-weight:bold;'>❌ 카테고리가 6개 미만입니다 (현재 {$cat_count}개).</p>";
    echo "<p>아래 버튼을 눌러 초기화를 진행하세요 (데이터가 모두 삭제되고 초기화됩니다)</p>";
} else {
    echo "<p style='color:green;'>✅ 카테고리 개수 정상 (6개)</p>";
}

echo "<div style='margin-top:20px;'>";
echo "<a href='admin_quote_setup.php' style='padding:10px 20px; background:#dc2626; color:white; text-decoration:none; border-radius:5px; margin-right:10px;'>🚨 DB 완전 초기화 (Setup)</a>";
echo "<a href='admin_quote_insert_data.php' style='padding:10px 20px; background:#2563eb; color:white; text-decoration:none; border-radius:5px;'>📥 데이터 재입력 (Insert)</a>";
echo "</div>";

// 바로가기 링크
echo "<hr>";
echo "<h3>바로가기</h3>";
echo "<ul>";
echo "<li><a href='admin_quote_calc.php'>견적 계산기</a></li>";
echo "<li><a href='admin_quote_manager.php'>설정 관리</a></li>";
echo "</ul>";
?>
<?php
include_once('./_common.php');
if (!$is_admin) {echo json_encode(['success' => false, 'message' => '권한이 없습니다']);exit;}
header('Content-Type: application/json; charset=utf-8');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
try {
    switch ($action) {
        case 'get_categories':
            $sql = "SELECT * FROM g5_quote_categories WHERE is_active = 1 ORDER BY sort_order ASC";
            $result = sql_query($sql);
            $categories = [];
            while ($row = sql_fetch_array($result)) {$categories[] = $row;}
            echo json_encode($categories);
            break;
        case 'get_category':
            $id = (int)$_GET['id'];
            $row = sql_fetch("SELECT * FROM g5_quote_categories WHERE id = $id");
            echo json_encode($row);
            break;
        case 'add_category':
            $name = sql_real_escape_string($_POST['name']);
            $key = sql_real_escape_string($_POST['category_key']);
            $icon = sql_real_escape_string($_POST['icon']);
            $order = (int)$_POST['sort_order'];
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "INSERT INTO g5_quote_categories (category_key, name, icon, sort_order, is_active) VALUES ('$key', '$name', '$icon', $order, $active)";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '카테고리가 추가되었습니다']);
            break;
        case 'update_category':
            $id = (int)$_POST['category_id'];
            $name = sql_real_escape_string($_POST['name']);
            $key = sql_real_escape_string($_POST['category_key']);
            $icon = sql_real_escape_string($_POST['icon']);
            $order = (int)$_POST['sort_order'];
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "UPDATE g5_quote_categories SET category_key = '$key', name = '$name', icon = '$icon', sort_order = $order, is_active = $active WHERE id = $id";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '카테고리가 수정되었습니다']);
            break;
        case 'delete_category':
            $id = (int)$_POST['id'];
            sql_query("DELETE FROM g5_quote_categories WHERE id = $id");
            echo json_encode(['success' => true, 'message' => '카테고리가 삭제되었습니다']);
            break;
        case 'move_category':
            $id = (int)$_POST['id'];
            $direction = $_POST['direction'];
            $current = sql_fetch("SELECT sort_order FROM g5_quote_categories WHERE id = $id");
            $currentOrder = $current['sort_order'];
            if ($direction === 'up') {
                $target = sql_fetch("SELECT id, sort_order FROM g5_quote_categories WHERE sort_order < $currentOrder ORDER BY sort_order DESC LIMIT 1");
            } else {
                $target = sql_fetch("SELECT id, sort_order FROM g5_quote_categories WHERE sort_order > $currentOrder ORDER BY sort_order ASC LIMIT 1");
            }
            if ($target) {
                sql_query("UPDATE g5_quote_categories SET sort_order = {$target['sort_order']} WHERE id = $id");
                sql_query("UPDATE g5_quote_categories SET sort_order = $currentOrder WHERE id = {$target['id']}");
            }
            echo json_encode(['success' => true]);
            break;
        case 'get_subcategories':
            $category_id = (int)$_GET['category_id'];
            $sql = "SELECT * FROM g5_quote_subcategories WHERE category_id = $category_id ORDER BY sort_order ASC";
            $result = sql_query($sql);
            $subcategories = [];
            while ($row = sql_fetch_array($result)) {$subcategories[] = $row;}
            echo json_encode($subcategories);
            break;
        case 'add_subcategory':
            $category_id = (int)$_POST['category_id'];
            $name = sql_real_escape_string($_POST['name']);
            $sql = "INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($category_id, '$name', 0)";
            sql_query($sql);
            $new_id = sql_insert_id();
            echo json_encode(['success' => true, 'id' => $new_id, 'message' => '서브카테고리가 추가되었습니다']);
            break;
        case 'get_products':
            $where = "1=1";
            if (isset($_GET['category_id']) && $_GET['category_id']) {
                $category_id = (int)$_GET['category_id'];
                $where .= " AND s.category_id = $category_id";
            }
            if (isset($_GET['subcategory_id']) && $_GET['subcategory_id']) {
                $subcategory_id = (int)$_GET['subcategory_id'];
                $where .= " AND p.subcategory_id = $subcategory_id";
            }
            $sql = "SELECT p.*, s.name as subcategory_name, s.category_id, c.name as category_name FROM g5_quote_products p LEFT JOIN g5_quote_subcategories s ON p.subcategory_id = s.id LEFT JOIN g5_quote_categories c ON s.category_id = c.id WHERE $where ORDER BY c.sort_order, s.sort_order, p.sort_order";
            $result = sql_query($sql);
            $products = [];
            while ($row = sql_fetch_array($result)) {$products[] = $row;}
            echo json_encode($products);
            break;
        case 'get_product':
            $id = (int)$_GET['id'];
            $row = sql_fetch("SELECT p.*, s.category_id FROM g5_quote_products p LEFT JOIN g5_quote_subcategories s ON p.subcategory_id = s.id WHERE p.id = $id");
            echo json_encode($row);
            break;
        case 'add_product':
            $subcategory_id = (int)$_POST['subcategory_id'];
            $name = sql_real_escape_string($_POST['name']);
            $unit_price = (float)$_POST['unit_price'];
            $unit = sql_real_escape_string($_POST['unit']);
            $calc_type = sql_real_escape_string($_POST['calc_type']);
            $apply_rounding = isset($_POST['apply_rounding']) ? 1 : 0;
            $min_area = isset($_POST['min_area']) && $_POST['min_area'] ? (float)$_POST['min_area'] : null;
            $base_size = isset($_POST['base_size']) && $_POST['base_size'] ? (int)$_POST['base_size'] : null;
            $description = sql_real_escape_string($_POST['description']);
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "INSERT INTO g5_quote_products (subcategory_id, name, unit_price, unit, calc_type, apply_rounding, min_area, base_size, description, is_active, sort_order) VALUES ($subcategory_id, '$name', $unit_price, '$unit', '$calc_type', $apply_rounding, " . ($min_area ? $min_area : 'NULL') . ", " . ($base_size ? $base_size : 'NULL') . ", '$description', $active, 0)";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '제품이 추가되었습니다']);
            break;
        case 'update_product':
            $id = (int)$_POST['product_id'];
            $subcategory_id = (int)$_POST['subcategory_id'];
            $name = sql_real_escape_string($_POST['name']);
            $unit_price = (float)$_POST['unit_price'];
            $unit = sql_real_escape_string($_POST['unit']);
            $calc_type = sql_real_escape_string($_POST['calc_type']);
            $apply_rounding = isset($_POST['apply_rounding']) ? 1 : 0;
            $min_area = isset($_POST['min_area']) && $_POST['min_area'] ? (float)$_POST['min_area'] : null;
            $base_size = isset($_POST['base_size']) && $_POST['base_size'] ? (int)$_POST['base_size'] : null;
            $description = sql_real_escape_string($_POST['description']);
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "UPDATE g5_quote_products SET subcategory_id = $subcategory_id, name = '$name', unit_price = $unit_price, unit = '$unit', calc_type = '$calc_type', apply_rounding = $apply_rounding, min_area = " . ($min_area ? $min_area : 'NULL') . ", base_size = " . ($base_size ? $base_size : 'NULL') . ", description = '$description', is_active = $active WHERE id = $id";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '제품이 수정되었습니다']);
            break;
        case 'delete_product':
            $id = (int)$_POST['id'];
            sql_query("DELETE FROM g5_quote_products WHERE id = $id");
            sql_query("DELETE FROM g5_quote_options WHERE product_id = $id");
            echo json_encode(['success' => true, 'message' => '제품이 삭제되었습니다']);
            break;
        case 'get_options':
            $where = "1=1";
            if (isset($_GET['product_id']) && $_GET['product_id']) {
                $product_id = (int)$_GET['product_id'];
                $where .= " AND o.product_id = $product_id";
            }
            $sql = "SELECT o.*, p.name as product_name FROM g5_quote_options o LEFT JOIN g5_quote_products p ON o.product_id = p.id WHERE $where ORDER BY o.sort_order";
            $result = sql_query($sql);
            $options = [];
            while ($row = sql_fetch_array($result)) {$options[] = $row;}
            echo json_encode($options);
            break;
        case 'get_option':
            $id = (int)$_GET['id'];
            $row = sql_fetch("SELECT * FROM g5_quote_options WHERE id = $id");
            echo json_encode($row);
            break;
        case 'add_option':
            $product_id = (int)$_POST['product_id'];
            $name = sql_real_escape_string($_POST['name']);
            $price = (float)$_POST['price'];
            $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0;
            $order = (int)$_POST['sort_order'];
            $sql = "INSERT INTO g5_quote_options (product_id, name, price, discount, sort_order) VALUES ($product_id, '$name', $price, $discount, $order)";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '옵션이 추가되었습니다']);
            break;
        case 'update_option':
            $id = (int)$_POST['option_id'];
            $product_id = (int)$_POST['product_id'];
            $name = sql_real_escape_string($_POST['name']);
            $price = (float)$_POST['price'];
            $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0;
            $order = (int)$_POST['sort_order'];
            $sql = "UPDATE g5_quote_options SET product_id = $product_id, name = '$name', price = $price, discount = $discount, sort_order = $order WHERE id = $id";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '옵션이 수정되었습니다']);
            break;
        case 'delete_option':
            $id = (int)$_POST['id'];
            sql_query("DELETE FROM g5_quote_options WHERE id = $id");
            echo json_encode(['success' => true, 'message' => '옵션이 삭제되었습니다']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
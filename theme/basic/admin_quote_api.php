<?php
include_once('./_common.php');
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다']);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
try {
    switch ($action) {
        case 'get_categories':
            $sql = "SELECT * FROM g5_quote_categories WHERE is_active = 1 ORDER BY sort_order ASC";
            $result = sql_query($sql);
            $categories = [];
            while ($row = sql_fetch_array($result)) {
                $categories[] = $row;
            }
            echo json_encode($categories);
            break;
        case 'get_category':
            $id = (int) $_GET['id'];
            $row = sql_fetch("SELECT * FROM g5_quote_categories WHERE id = $id");
            echo json_encode($row);
            break;
        case 'add_category':
            $name = sql_real_escape_string($_POST['name']);
            $key = sql_real_escape_string($_POST['category_key']);
            $icon = sql_real_escape_string($_POST['icon']);
            $order = (int) $_POST['sort_order'];
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "INSERT INTO g5_quote_categories (category_key, name, icon, sort_order, is_active) VALUES ('$key', '$name', '$icon', $order, $active)";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '카테고리가 추가되었습니다']);
            break;
        case 'update_category':
            $id = (int) $_POST['category_id'];
            $name = sql_real_escape_string($_POST['name']);
            $key = sql_real_escape_string($_POST['category_key']);
            $icon = sql_real_escape_string($_POST['icon']);
            $order = (int) $_POST['sort_order'];
            $active = isset($_POST['is_active']) ? 1 : 0;
            $sql = "UPDATE g5_quote_categories SET category_key = '$key', name = '$name', icon = '$icon', sort_order = $order, is_active = $active WHERE id = $id";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '카테고리가 수정되었습니다']);
            break;
        case 'delete_category':
            $id = (int) $_POST['id'];
            sql_query("DELETE FROM g5_quote_categories WHERE id = $id");
            echo json_encode(['success' => true, 'message' => '카테고리가 삭제되었습니다']);
            break;
        case 'move_category':
            $id = (int) $_POST['id'];
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
            $category_id = (int) $_GET['category_id'];
            $sql = "SELECT * FROM g5_quote_subcategories WHERE category_id = $category_id ORDER BY sort_order ASC";
            $result = sql_query($sql);
            $subcategories = [];
            while ($row = sql_fetch_array($result)) {
                $subcategories[] = $row;
            }
            echo json_encode($subcategories);
            break;
        case 'add_subcategory':
            $category_id = (int) $_POST['category_id'];
            $name = sql_real_escape_string($_POST['name']);
            $sql = "INSERT INTO g5_quote_subcategories (category_id, name, sort_order) VALUES ($category_id, '$name', 0)";
            sql_query($sql);
            $new_id = sql_insert_id();
            echo json_encode(['success' => true, 'id' => $new_id, 'message' => '서브카테고리가 추가되었습니다']);
            break;
        case 'get_products':
            $where = "1=1";
            if (isset($_GET['category_id']) && $_GET['category_id']) {
                $category_id = (int) $_GET['category_id'];
                $where .= " AND s.category_id = $category_id";
            }
            if (isset($_GET['subcategory_id']) && $_GET['subcategory_id']) {
                $subcategory_id = (int) $_GET['subcategory_id'];
                $where .= " AND p.subcategory_id = $subcategory_id";
            }
            $sql = "SELECT p.*, s.name as subcategory_name, s.category_id, c.name as category_name FROM g5_quote_products p LEFT JOIN g5_quote_subcategories s ON p.subcategory_id = s.id LEFT JOIN g5_quote_categories c ON s.category_id = c.id WHERE $where ORDER BY c.sort_order, s.sort_order, p.sort_order";
            $result = sql_query($sql);
            $products = [];
            while ($row = sql_fetch_array($result)) {
                $products[] = $row;
            }
            echo json_encode($products);
            break;
        case 'get_product':
            $id = (int) $_GET['id'];
            $row = sql_fetch("SELECT p.*, s.category_id, s.name as subcategory_name FROM g5_quote_products p LEFT JOIN g5_quote_subcategories s ON p.subcategory_id = s.id WHERE p.id = $id");
            echo json_encode($row);
            break;
        case 'add_product':
            $subcategory_id = (int) $_POST['subcategory_id'];
            $name = sql_real_escape_string($_POST['name']);
            $unit_price = (float) $_POST['unit_price'];
            $unit = sql_real_escape_string($_POST['unit']);
            $calc_type = sql_real_escape_string($_POST['calc_type']);
            $apply_rounding = isset($_POST['apply_rounding']) ? 1 : 0;
            $min_area = isset($_POST['min_area']) && $_POST['min_area'] ? (float) $_POST['min_area'] : null;
            $base_size = isset($_POST['base_size']) && $_POST['base_size'] ? (int) $_POST['base_size'] : null;
            $description = sql_real_escape_string($_POST['description']);
            $active = isset($_POST['is_active']) ? 1 : 0;
            // 폭별 단가 필드 - NULL 대신 0 기본값 사용 (DB NOT NULL 제약 대응)
            $use_width_pricing = isset($_POST['use_width_pricing']) ? 1 : 0;
            $price_small = isset($_POST['price_small']) && $_POST['price_small'] ? (float) $_POST['price_small'] : 0;
            $price_large = isset($_POST['price_large']) && $_POST['price_large'] ? (float) $_POST['price_large'] : 0;
            $price_xlarge = isset($_POST['price_xlarge']) && $_POST['price_xlarge'] ? (float) $_POST['price_xlarge'] : 0;
            $width_surcharge_1800 = isset($_POST['width_surcharge_1800']) ? (float) $_POST['width_surcharge_1800'] : 0;

            // pricing_mode 필드 - 명시적으로 받음
            $pricing_mode = isset($_POST['pricing_mode']) ? sql_real_escape_string($_POST['pricing_mode']) : 'DEFAULT';

            // AREA_TIER 면적구간 요금 필드
            $area_tier_piece_under_1 = isset($_POST['area_tier_piece_under_1']) ? (int) $_POST['area_tier_piece_under_1'] : 0;
            $area_tier_piece_1_to_3 = isset($_POST['area_tier_piece_1_to_3']) ? (int) $_POST['area_tier_piece_1_to_3'] : 0;
            $area_tier_m2_over_3 = isset($_POST['area_tier_m2_over_3']) ? (int) $_POST['area_tier_m2_over_3'] : 0;
            $area_tier_surcharge_1800 = isset($_POST['area_tier_surcharge_1800']) ? (int) $_POST['area_tier_surcharge_1800'] : 0;

            // 디버그 로그
            error_log("ADD PRODUCT Mode: $pricing_mode");

            $sql = "INSERT INTO g5_quote_products (subcategory_id, name, unit_price, price_small, price_large, price_xlarge, width_surcharge_1800, use_width_pricing, pricing_mode, area_tier_piece_under_1, area_tier_piece_1_to_3, area_tier_m2_over_3, area_tier_surcharge_1800, unit, calc_type, apply_rounding, min_area, base_size, description, is_active, sort_order) VALUES ($subcategory_id, '$name', $unit_price, $price_small, $price_large, $price_xlarge, $width_surcharge_1800, $use_width_pricing, '$pricing_mode', $area_tier_piece_under_1, $area_tier_piece_1_to_3, $area_tier_m2_over_3, $area_tier_surcharge_1800, '$unit', '$calc_type', $apply_rounding, " . ($min_area ? $min_area : 'NULL') . ", " . ($base_size ? $base_size : 'NULL') . ", '$description', $active, 0)";

            $result = sql_query($sql);
            if (!$result) {
                echo json_encode(['success' => false, 'message' => '제품 추가 실패: ' . sql_error_info()]);
                exit;
            }

            echo json_encode(['success' => true, 'message' => '제품이 추가되었습니다']);
            break;
        case 'update_product':
            $id = (int) $_POST['product_id'];
            $subcategory_id = (int) $_POST['subcategory_id'];
            $name = sql_real_escape_string($_POST['name']);
            $unit_price = (float) $_POST['unit_price'];
            $unit = sql_real_escape_string($_POST['unit']);
            $calc_type = sql_real_escape_string($_POST['calc_type']);
            $apply_rounding = isset($_POST['apply_rounding']) ? 1 : 0;
            $min_area = isset($_POST['min_area']) && $_POST['min_area'] ? (float) $_POST['min_area'] : null;
            $base_size = isset($_POST['base_size']) && $_POST['base_size'] ? (int) $_POST['base_size'] : null;
            $description = sql_real_escape_string($_POST['description']);
            $active = isset($_POST['is_active']) ? 1 : 0;

            // 폭별 단가 필드 - NULL 대신 0 기본값 사용 (DB NOT NULL 제약 대응)
            $use_width_pricing = isset($_POST['use_width_pricing']) ? 1 : 0;
            $price_small = isset($_POST['price_small']) && $_POST['price_small'] ? (float) $_POST['price_small'] : 0;
            $price_large = isset($_POST['price_large']) && $_POST['price_large'] ? (float) $_POST['price_large'] : 0;
            $price_xlarge = isset($_POST['price_xlarge']) && $_POST['price_xlarge'] ? (float) $_POST['price_xlarge'] : 0;
            $width_surcharge_1800 = isset($_POST['width_surcharge_1800']) ? (float) $_POST['width_surcharge_1800'] : 0;

            // pricing_mode 필드 - 명시적으로 받음
            $pricing_mode = isset($_POST['pricing_mode']) ? sql_real_escape_string($_POST['pricing_mode']) : 'DEFAULT';

            // AREA_TIER 면적구간 요금 필드
            $area_tier_piece_under_1 = isset($_POST['area_tier_piece_under_1']) ? (int) $_POST['area_tier_piece_under_1'] : 0;
            $area_tier_piece_1_to_3 = isset($_POST['area_tier_piece_1_to_3']) ? (int) $_POST['area_tier_piece_1_to_3'] : 0;
            $area_tier_m2_over_3 = isset($_POST['area_tier_m2_over_3']) ? (int) $_POST['area_tier_m2_over_3'] : 0;
            $area_tier_surcharge_1800 = isset($_POST['area_tier_surcharge_1800']) ? (int) $_POST['area_tier_surcharge_1800'] : 0;

            // 디버그를 위해 로그 남기기
            error_log("UPDATE PRODUCT ID: $id / Mode: $pricing_mode / Tier1: $area_tier_piece_under_1");

            $sql = "UPDATE g5_quote_products SET 
                subcategory_id = $subcategory_id, 
                name = '$name', 
                unit_price = $unit_price, 
                price_small = $price_small, 
                price_large = $price_large, 
                price_xlarge = $price_xlarge, 
                width_surcharge_1800 = $width_surcharge_1800, 
                use_width_pricing = $use_width_pricing, 
                pricing_mode = '$pricing_mode', 
                area_tier_piece_under_1 = $area_tier_piece_under_1, 
                area_tier_piece_1_to_3 = $area_tier_piece_1_to_3, 
                area_tier_m2_over_3 = $area_tier_m2_over_3, 
                area_tier_surcharge_1800 = $area_tier_surcharge_1800, 
                unit = '$unit', 
                calc_type = '$calc_type', 
                apply_rounding = $apply_rounding, 
                min_area = " . ($min_area ? $min_area : 'NULL') . ", 
                base_size = " . ($base_size ? $base_size : 'NULL') . ", 
                description = '$description', 
                is_active = $active 
                WHERE id = $id";

            // 쿼리 실행 결과 확인
            $result = sql_query($sql);
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'DB 업데이트 실패: ' . sql_error_info()]);
                exit;
            }

            // 옵션 연결 정보 업데이트 (g5_quote_product_options)
            // 기존 연결 모두 삭제 후 재등록
            sql_query("DELETE FROM g5_quote_product_options WHERE product_id = $id");

            // 1. 상세 설정(정렬, 활성)이 포함된 JSON 데이터가 있는 경우
            if (isset($_POST['linked_details']) && $_POST['linked_details']) {
                $details = json_decode($_POST['linked_details'], true);
                if (is_array($details)) {
                    foreach ($details as $idx => $opt) {
                        $opt_id = (int) $opt['option_id'];
                        $is_active = isset($opt['is_active']) ? (int) $opt['is_active'] : 1;
                        $sort = isset($opt['sort_order']) ? (int) $opt['sort_order'] : ($idx * 10);

                        sql_query("INSERT INTO g5_quote_product_options (product_id, option_id, is_active, sort_order) VALUES ($id, $opt_id, $is_active, $sort)");
                    }
                }
            }
            // 2. 단순 체크박스 배열만 있는 경우 (기존 호환)
            else if (isset($_POST['linked_options']) && is_array($_POST['linked_options'])) {
                foreach ($_POST['linked_options'] as $idx => $opt_id) {
                    $opt_id = (int) $opt_id;
                    $sort = $idx * 10;
                    sql_query("INSERT INTO g5_quote_product_options (product_id, option_id, is_active, sort_order) VALUES ($id, $opt_id, 1, $sort)");
                }
            }

            echo json_encode(['success' => true, 'message' => '제품이 수정되었습니다']);
            break;
        case 'delete_product':
            $id = (int) $_POST['id'];
            sql_query("DELETE FROM g5_quote_products WHERE id = $id");
            // 옵션 마스터는 지우지 않고 연결 정보만 삭제
            sql_query("DELETE FROM g5_quote_product_options WHERE product_id = $id");
            echo json_encode(['success' => true, 'message' => '제품이 삭제되었습니다']);
            break;
        case 'get_options':
            if (isset($_GET['product_id'])) {
                // 특정 제품에 연결된 옵션 ID 목록 반환
                $product_id = (int) $_GET['product_id'];
                // 상세 정보 포함
                $sql = "SELECT option_id, is_active, sort_order FROM g5_quote_product_options WHERE product_id = $product_id ORDER BY sort_order";
                $result = sql_query($sql);
                $linked = [];
                $details = [];
                while ($row = sql_fetch_array($result)) {
                    $linked[] = $row['option_id'];
                    $details[] = $row;
                }
                echo json_encode(['linked_options' => $linked, 'linked_details' => $details]);
            } else {
                // 전체 옵션 마스터 목록 반환 (관리용)
                // 그룹별 정렬 추가
                $sql = "SELECT * FROM g5_quote_options ORDER BY group_name, sort_order, id";
                $result = sql_query($sql);
                $options = [];
                while ($row = sql_fetch_array($result)) {
                    $options[] = $row;
                }
                echo json_encode($options);
            }
            break;
        case 'get_option':
            $id = (int) $_GET['id'];
            $row = sql_fetch("SELECT * FROM g5_quote_options WHERE id = $id");
            echo json_encode($row);
            break;
        case 'add_option':
            // product_id 제거 (마스터 옵션 생성)
            $name = sql_real_escape_string($_POST['name']);
            $group_name = isset($_POST['group_name']) ? sql_real_escape_string($_POST['group_name']) : '기본';
            $price = (float) $_POST['price'];
            $discount = isset($_POST['discount']) ? (float) $_POST['discount'] : 0;
            $order = (int) $_POST['sort_order'];
            $unit_type = isset($_POST['unit_type']) ? sql_real_escape_string($_POST['unit_type']) : 'fixed';
            $free_qty = isset($_POST['free_qty']) ? (int) $_POST['free_qty'] : 0;
            $qty_unit_price = isset($_POST['qty_unit_price']) ? (float) $_POST['qty_unit_price'] : 0;
            $default_qty = isset($_POST['default_qty']) ? (int) $_POST['default_qty'] : 0;

            $sql = "INSERT INTO g5_quote_options (group_name, name, price, discount, unit_type, free_qty, qty_unit_price, default_qty, sort_order) VALUES ('$group_name', '$name', $price, $discount, '$unit_type', $free_qty, $qty_unit_price, $default_qty, $order)";

            $result = sql_query($sql);
            if (!$result) {
                echo json_encode(['success' => false, 'message' => '옵션 추가 실패: ' . sql_error_info()]);
                exit;
            }

            echo json_encode(['success' => true, 'message' => '옵션이 추가되었습니다']);
            break;
        case 'update_option':
            $id = (int) $_POST['option_id'];
            // product_id 제거
            $name = sql_real_escape_string($_POST['name']);
            $group_name = isset($_POST['group_name']) ? sql_real_escape_string($_POST['group_name']) : '기본';
            $price = (float) $_POST['price'];
            $discount = isset($_POST['discount']) ? (float) $_POST['discount'] : 0;
            $order = (int) $_POST['sort_order'];
            $unit_type = isset($_POST['unit_type']) ? sql_real_escape_string($_POST['unit_type']) : 'fixed';
            $free_qty = isset($_POST['free_qty']) ? (int) $_POST['free_qty'] : 0;
            $qty_unit_price = isset($_POST['qty_unit_price']) ? (float) $_POST['qty_unit_price'] : 0;
            $default_qty = isset($_POST['default_qty']) ? (int) $_POST['default_qty'] : 0;
            $sql = "UPDATE g5_quote_options SET group_name = '$group_name', name = '$name', price = $price, discount = $discount, unit_type = '$unit_type', free_qty = $free_qty, qty_unit_price = $qty_unit_price, default_qty = $default_qty, sort_order = $order WHERE id = $id";
            sql_query($sql);
            echo json_encode(['success' => true, 'message' => '옵션이 수정되었습니다']);
            break;
        case 'delete_option':
            $id = (int) $_POST['id'];
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
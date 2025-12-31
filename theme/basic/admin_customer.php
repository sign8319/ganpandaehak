<?php
include_once('./_common.php');


echo "<!-- STEP3_TEST_MARK_20251228 -->";

// Define local token functions
function get_quote_token()
{
    $token = get_session('ss_quote_token');
    if (!$token) {
        $token = md5(uniqid(rand(), true));
        set_session('ss_quote_token', $token);
    }
    return $token;
}

function check_quote_token()
{
    $token = get_session('ss_quote_token');
    set_session('ss_quote_token', '');

    if (!$token || !isset($_REQUEST['token']) || $token != $_REQUEST['token']) {
        alert('올바른 방법으로 이용해 주십시오.', './admin_customer.php');
    }
    return true;
}

// 1. Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// 2. DB Initialization - Create tables if not exist
// FIX: Add g5_customer table
if (!sql_query(" DESCRIBE g5_customer ", false)) {
    $sql_customer = "
        CREATE TABLE IF NOT EXISTS `g5_customer` (
          `customer_id` int(11) NOT NULL AUTO_INCREMENT,
          `customer_name` varchar(100) NOT NULL DEFAULT '' COMMENT '업체명/고객명',
          `customer_manager` varchar(50) NOT NULL DEFAULT '' COMMENT '담당자',
          `customer_hp` varchar(50) NOT NULL DEFAULT '' COMMENT '연락처',
          `customer_email` varchar(100) NOT NULL DEFAULT '' COMMENT '이메일',
          `customer_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '주소',
          `customer_tags` text NOT NULL COMMENT '태그 (쉼표 구분)',
          `customer_memo` text NOT NULL COMMENT '메모',
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY (`customer_id`),
          INDEX idx_customer_name (customer_name)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_customer, true);
}

// FIX: Add g5_customer_status table
if (!sql_query(" DESCRIBE g5_customer_status ", false)) {
    $sql_status = "
        CREATE TABLE IF NOT EXISTS `g5_customer_status` (
          `customer_id` int(11) NOT NULL,
          `status_step` varchar(50) NOT NULL DEFAULT '견적서발송' COMMENT '진행상태',
          `updated_at` datetime NOT NULL,
          `updated_by` varchar(50) NOT NULL DEFAULT '' COMMENT '변경자',
          PRIMARY KEY (`customer_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_status, true);
}

// FIX: Add g5_customer_status_log table
if (!sql_query(" DESCRIBE g5_customer_status_log ", false)) {
    $sql_log = "
        CREATE TABLE IF NOT EXISTS `g5_customer_status_log` (
          `log_id` int(11) NOT NULL AUTO_INCREMENT,
          `customer_id` int(11) NOT NULL DEFAULT 0,
          `before_step` varchar(50) NOT NULL DEFAULT '',
          `after_step` varchar(50) NOT NULL DEFAULT '',
          `changed_by` varchar(50) NOT NULL DEFAULT '' COMMENT '변경자 (관리자 ID)',
          `changed_at` datetime NOT NULL,
          PRIMARY KEY (`log_id`),
          INDEX idx_customer_id (customer_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_log, true);
}

// FIX: Add g5_customer_share_link table
if (!sql_query(" DESCRIBE g5_customer_share_link ", false)) {
    $sql_link = "
        CREATE TABLE IF NOT EXISTS `g5_customer_share_link` (
          `customer_id` int(11) NOT NULL,
          `share_token` varchar(64) NOT NULL DEFAULT '' COMMENT '공유 토큰',
          `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '활성화 여부',
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`customer_id`),
          UNIQUE KEY `share_token` (`share_token`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_link, true);
}

// FIX: Add qa_customer_id to g5_quote (optional, for future integration)
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_customer_id' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_customer_id` int(11) NOT NULL DEFAULT 0 AFTER `qa_store_id` ", false);
    sql_query(" ALTER TABLE g5_quote ADD INDEX idx_qa_customer_id (qa_customer_id) ", false);
}

// [NEW] Step 3 추가 입력 항목 컬럼 생성 (존재하지 않을 경우에만 추가)
$cols_to_add = [
    'qa_construct_date' => "DATE NULL DEFAULT NULL COMMENT '시공일'",
    'qa_deposit_status' => "VARCHAR(20) NOT NULL DEFAULT '입금대기' COMMENT '입금상황'",
    'qa_payment_method' => "VARCHAR(20) NOT NULL DEFAULT '현금' COMMENT '결제방식'",
    'qa_tax_yn' => "CHAR(1) NOT NULL DEFAULT 'N' COMMENT '세금계산서 발행여부'",
    'qa_tax_biz_num' => "VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사업자번호'",
    'qa_tax_company_name' => "VARCHAR(50) NOT NULL DEFAULT '' COMMENT '상호'",
    'qa_tax_ceo_name' => "VARCHAR(30) NOT NULL DEFAULT '' COMMENT '대표자명'",
    'qa_tax_addr' => "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '사업장 주소'",
    'qa_tax_email' => "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '세금계산서 이메일'",
    'qa_tax_type' => "VARCHAR(10) NOT NULL DEFAULT '01' COMMENT '01:일반, 02:영세율'",
    'qa_tax_claim_type' => "VARCHAR(10) NOT NULL DEFAULT '01' COMMENT '01:영수, 02:청구'",
    'qa_tax_item_name' => "VARCHAR(255) NOT NULL DEFAULT '' COMMENT '품목명'",
    'qa_tax_date' => "DATE NULL DEFAULT NULL COMMENT '작성일자'",
    'qa_tax_trade_name' => "VARCHAR(100) NOT NULL DEFAULT '' COMMENT '공급자 상호(견적명)'",
    'qa_tax_supply_price' => "BIGINT NOT NULL DEFAULT 0 COMMENT '공급가액'",
    'qa_tax_vat_price' => "BIGINT NOT NULL DEFAULT 0 COMMENT '부가세'"
];

foreach ($cols_to_add as $col_name => $col_def) {
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE '$col_name' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `$col_name` $col_def ", false);
    }
}

// -----------------------------------------------------------------------------
// Controller Logic
// -----------------------------------------------------------------------------
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$customer_id = isset($_REQUEST['customer_id']) ? (int) $_REQUEST['customer_id'] : 0;
$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;

// AJAX: Search Customer
if ($w == 'ajax_search') {
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $customers = [];

    if ($keyword) {
        $keyword_safe = addslashes($keyword);
        $sql = " SELECT c.*, s.status_step 
                 FROM g5_customer c 
                 LEFT JOIN g5_customer_status s ON c.customer_id = s.customer_id 
                 WHERE c.customer_name LIKE '%$keyword_safe%' 
                 ORDER BY c.customer_name 
                 LIMIT 20 ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $customers[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['customers' => $customers], JSON_UNESCAPED_UNICODE);
    exit;
}

// AJAX: Generate Share Link
if ($w == 'generate_link') {
    check_quote_token();

    if ($customer_id) {
        // Generate new token
        $token = md5(uniqid($customer_id . time(), true));

        // Check if link exists
        $existing = sql_fetch(" SELECT * FROM g5_customer_share_link WHERE customer_id = '$customer_id' ");

        if ($existing) {
            // Update existing
            sql_query(" UPDATE g5_customer_share_link SET 
                        share_token = '$token',
                        is_active = 1,
                        created_at = '" . G5_TIME_YMDHIS . "'
                        WHERE customer_id = '$customer_id' ");
        } else {
            // Insert new
            sql_query(" INSERT INTO g5_customer_share_link SET
                        customer_id = '$customer_id',
                        share_token = '$token',
                        is_active = 1,
                        created_at = '" . G5_TIME_YMDHIS . "' ");
        }

        $url = G5_URL . '/theme/basic/customer.php?token=' . $token;

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'url' => $url, 'token' => $token], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }
    exit;
}

// AJAX: Toggle Link Active
if ($w == 'toggle_link') {
    check_quote_token();

    if ($customer_id) {
        $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 0;
        sql_query(" UPDATE g5_customer_share_link SET is_active = '$is_active' WHERE customer_id = '$customer_id' ");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }
    exit;
}

// Handle Final Registration Action (Step 3)
if ($w == 'register_complete' && $qa_id) {
    check_quote_token();

    // Fetch Quote Data
    $quote = sql_fetch(" select * from g5_quote where qa_id = '{$qa_id}' ");
    if (!$quote)
        alert('견적 정보를 찾을 수 없습니다.');

    // Check if customer already exists (by name and hp)
    $cust = sql_fetch(" select customer_id from g5_customer where customer_name = '{$quote['qa_client_name']}' and customer_hp = '{$quote['qa_client_hp']}' ");

    if ($cust['customer_id']) {
        $customer_id = $cust['customer_id'];
        // Update existing? Optional.
    } else {
        // Insert new customer
        $sql = " insert into g5_customer
                    set customer_name = '{$quote['qa_client_name']}',
                        customer_manager = '{$quote['qa_memo_user']}',
                        customer_hp = '{$quote['qa_client_hp']}',
                        customer_email = '{$quote['qa_client_email']}',
                        customer_addr = '{$quote['qa_client_addr']} {$quote['qa_client_addr2']}',
                        created_at = '" . G5_TIME_YMDHIS . "',
                        updated_at = '" . G5_TIME_YMDHIS . "' ";
        sql_query($sql);
        $customer_id = sql_insert_id();

        // Initial status
        sql_query(" INSERT INTO g5_customer_status SET
                    customer_id = '$customer_id',
                    status_step = '견적서발송',
                    updated_at = '" . G5_TIME_YMDHIS . "',
                    updated_by = '{$member['mb_id']}' ");
    }

    // [NEW] Step 3 추가 항목 저장
    $qa_construct_date = isset($_POST['qa_construct_date']) ? $_POST['qa_construct_date'] : '';
    // 날짜 형식이 올바르지 않으면 NULL 처리
    if (empty($qa_construct_date))
        $qa_construct_date = 'NULL';
    else
        $qa_construct_date = "'$qa_construct_date'";

    $qa_deposit_status = isset($_POST['qa_deposit_status']) ? $_POST['qa_deposit_status'] : '입금대기';
    $qa_payment_method = isset($_POST['qa_payment_method']) ? $_POST['qa_payment_method'] : '현금';
    $qa_tax_yn = isset($_POST['qa_tax_yn']) ? $_POST['qa_tax_yn'] : 'N';
    $qa_tax_biz_num = isset($_POST['qa_tax_biz_num']) ? $_POST['qa_tax_biz_num'] : '';
    $qa_tax_company_name = isset($_POST['qa_tax_company_name']) ? $_POST['qa_tax_company_name'] : '';
    $qa_tax_ceo_name = isset($_POST['qa_tax_ceo_name']) ? $_POST['qa_tax_ceo_name'] : '';
    $qa_tax_addr = isset($_POST['qa_tax_addr']) ? $_POST['qa_tax_addr'] : '';
    $qa_tax_email = isset($_POST['qa_tax_email']) ? $_POST['qa_tax_email'] : '';
    // [NEW] 세금계산서 고도화 추가 항목
    $qa_tax_type = isset($_POST['qa_tax_type']) ? $_POST['qa_tax_type'] : '';
    $qa_tax_claim_type = isset($_POST['qa_tax_claim_type']) ? $_POST['qa_tax_claim_type'] : '';
    $qa_tax_item_name = isset($_POST['qa_tax_item_name']) ? $_POST['qa_tax_item_name'] : '';

    $qa_tax_date = isset($_POST['qa_tax_date']) ? $_POST['qa_tax_date'] : '';
    if (empty($qa_tax_date))
        $qa_tax_date = 'NULL';
    else
        $qa_tax_date = "'$qa_tax_date'";

    $qa_tax_trade_name = isset($_POST['qa_tax_trade_name']) ? $_POST['qa_tax_trade_name'] : '';
    // 콤마 제거
    $qa_tax_supply_price = isset($_POST['qa_tax_supply_price']) ? str_replace(',', '', $_POST['qa_tax_supply_price']) : 0;
    $qa_tax_vat_price = isset($_POST['qa_tax_vat_price']) ? str_replace(',', '', $_POST['qa_tax_vat_price']) : 0;

    // [NEW] Global Memos
    $qa_memo = isset($_POST['qa_memo']) ? $_POST['qa_memo'] : '';
    $qa_memo_user = isset($_POST['qa_memo_user']) ? $_POST['qa_memo_user'] : '';


    $sql_add = " , qa_construct_date = $qa_construct_date,
                   qa_deposit_status = '$qa_deposit_status',
                   qa_memo = '$qa_memo',
                   qa_memo_user = '$qa_memo_user',
                   qa_payment_method = '$qa_payment_method',
                   qa_tax_yn = '$qa_tax_yn',
                   qa_tax_biz_num = '$qa_tax_biz_num',
                   qa_tax_company_name = '$qa_tax_company_name',
                   qa_tax_ceo_name = '$qa_tax_ceo_name',
                   qa_tax_addr = '$qa_tax_addr',
                   qa_tax_email = '$qa_tax_email',
                   qa_tax_type = '$qa_tax_type',
                   qa_tax_claim_type = '$qa_tax_claim_type',
                   qa_tax_item_name = '$qa_tax_item_name',
                   qa_tax_date = $qa_tax_date,
                   qa_tax_trade_name = '$qa_tax_trade_name',
                   qa_tax_supply_price = '$qa_tax_supply_price',
                   qa_tax_vat_price = '$qa_tax_vat_price' ";

    sql_query(" update g5_quote set " . trim($sql_add, ',') . " where qa_id = '$qa_id' ");

    // Update Quote Status
    sql_query(" update g5_quote set qa_status = '등록완료', qa_customer_id = '{$customer_id}' $sql_add where qa_id = '{$qa_id}' ");

    // [FIX] 등록 완료 후 목록으로 이동하지 않고 현재 페이지 유지 (사용자 요청)
    if (isset($_POST['save_step3_only']) && $_POST['save_step3_only']) {
        alert('입력 정보가 저장되었습니다.', "./admin_customer.php?w=form&qa_id={$qa_id}");
    } else {
        alert('고객 등록이 완료되었습니다.', "./admin_customer.php?w=form&qa_id={$qa_id}");
    }
}

// [NEW] Step 3 중간 저장 (Only Update Data)
if ($w == 'step3_update' && $qa_id) {
    check_quote_token();

    // Step 3 추가 항목 저장 로직 (register_complete와 동일 기능, 상태 변경 제외)
    $qa_construct_date = isset($_POST['qa_construct_date']) ? $_POST['qa_construct_date'] : '';
    if (empty($qa_construct_date))
        $qa_construct_date = 'NULL';
    else
        $qa_construct_date = "'$qa_construct_date'";

    $qa_deposit_status = isset($_POST['qa_deposit_status']) ? $_POST['qa_deposit_status'] : '입금대기';
    $qa_payment_method = isset($_POST['qa_payment_method']) ? $_POST['qa_payment_method'] : '현금';
    $qa_tax_yn = isset($_POST['qa_tax_yn']) ? $_POST['qa_tax_yn'] : 'N';
    $qa_tax_biz_num = isset($_POST['qa_tax_biz_num']) ? $_POST['qa_tax_biz_num'] : '';
    $qa_tax_company_name = isset($_POST['qa_tax_company_name']) ? $_POST['qa_tax_company_name'] : '';
    $qa_tax_ceo_name = isset($_POST['qa_tax_ceo_name']) ? $_POST['qa_tax_ceo_name'] : '';
    $qa_tax_addr = isset($_POST['qa_tax_addr']) ? $_POST['qa_tax_addr'] : '';
    $qa_tax_email = isset($_POST['qa_tax_email']) ? $_POST['qa_tax_email'] : '';

    $qa_tax_type = isset($_POST['qa_tax_type']) ? $_POST['qa_tax_type'] : '';
    $qa_tax_claim_type = isset($_POST['qa_tax_claim_type']) ? $_POST['qa_tax_claim_type'] : '';
    $qa_tax_item_name = isset($_POST['qa_tax_item_name']) ? $_POST['qa_tax_item_name'] : '';

    $qa_tax_date = isset($_POST['qa_tax_date']) ? $_POST['qa_tax_date'] : '';
    if (empty($qa_tax_date))
        $qa_tax_date = 'NULL';
    else
        $qa_tax_date = "'$qa_tax_date'";

    $qa_tax_trade_name = isset($_POST['qa_tax_trade_name']) ? $_POST['qa_tax_trade_name'] : '';
    $qa_tax_supply_price = isset($_POST['qa_tax_supply_price']) ? str_replace(',', '', $_POST['qa_tax_supply_price']) : 0;
    $qa_tax_vat_price = isset($_POST['qa_tax_vat_price']) ? str_replace(',', '', $_POST['qa_tax_vat_price']) : 0;

    $sql_add = " , qa_construct_date = $qa_construct_date,
                   qa_deposit_status = '$qa_deposit_status',
                   qa_payment_method = '$qa_payment_method',
                   qa_tax_yn = '$qa_tax_yn',
                   qa_tax_biz_num = '$qa_tax_biz_num',
                   qa_tax_company_name = '$qa_tax_company_name',
                   qa_tax_ceo_name = '$qa_tax_ceo_name',
                   qa_tax_addr = '$qa_tax_addr',
                   qa_tax_email = '$qa_tax_email',
                   qa_tax_type = '$qa_tax_type',
                   qa_tax_claim_type = '$qa_tax_claim_type',
                   qa_tax_item_name = '$qa_tax_item_name',
                   qa_tax_date = $qa_tax_date,
                   qa_tax_trade_name = '$qa_tax_trade_name',
                   qa_tax_supply_price = '$qa_tax_supply_price',
                   qa_tax_vat_price = '$qa_tax_vat_price' ";

    sql_query(" update g5_quote set " . trim($sql_add, ',') . " where qa_id = '$qa_id' ");

    alert('입력 정보가 저장되었습니다.', "./admin_customer.php?w=form&qa_id=$qa_id");
}

// Save Customer
if ($w == 'save') {
    check_quote_token();

    $customer_name = $_POST['customer_name'];
    $customer_manager = $_POST['customer_manager'] ?? '';
    $customer_hp = $_POST['customer_hp'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_addr = $_POST['customer_addr'] ?? '';
    $customer_tags = $_POST['customer_tags'] ?? '';
    $customer_memo = $_POST['customer_memo'] ?? '';
    $status_step = $_POST['status_step'] ?? '견적서발송';

    if (!$customer_id) {
        // Create new customer
        $sql = " INSERT INTO g5_customer SET
                 customer_name = '$customer_name',
                 customer_manager = '$customer_manager',
                 customer_hp = '$customer_hp',
                 customer_email = '$customer_email',
                 customer_addr = '$customer_addr',
                 customer_tags = '$customer_tags',
                 customer_memo = '$customer_memo',
                 created_at = '" . G5_TIME_YMDHIS . "',
                 updated_at = '" . G5_TIME_YMDHIS . "' ";
        sql_query($sql);
        $customer_id = sql_insert_id();

        // Create initial status
        sql_query(" INSERT INTO g5_customer_status SET
                    customer_id = '$customer_id',
                    status_step = '$status_step',
                    updated_at = '" . G5_TIME_YMDHIS . "',
                    updated_by = '{$member['mb_id']}' ");
    } else {
        // Update existing customer
        $sql = " UPDATE g5_customer SET
                 customer_name = '$customer_name',
                 customer_manager = '$customer_manager',
                 customer_hp = '$customer_hp',
                 customer_email = '$customer_email',
                 customer_addr = '$customer_addr',
                 customer_tags = '$customer_tags',
                 customer_memo = '$customer_memo',
                 updated_at = '" . G5_TIME_YMDHIS . "'
                 WHERE customer_id = '$customer_id' ";
        sql_query($sql);

        // Check if status changed
        $old_status = sql_fetch(" SELECT status_step FROM g5_customer_status WHERE customer_id = '$customer_id' ");

        if ($old_status && $old_status['status_step'] != $status_step) {
            // Log status change
            sql_query(" INSERT INTO g5_customer_status_log SET
                        customer_id = '$customer_id',
                        before_step = '{$old_status['status_step']}',
                        after_step = '$status_step',
                        changed_by = '{$member['mb_id']}',
                        changed_at = '" . G5_TIME_YMDHIS . "' ");

            // Update status
            sql_query(" UPDATE g5_customer_status SET
                        status_step = '$status_step',
                        updated_at = '" . G5_TIME_YMDHIS . "',
                        updated_by = '{$member['mb_id']}'
                        WHERE customer_id = '$customer_id' ");
        } elseif (!$old_status) {
            // Create status if not exists
            sql_query(" INSERT INTO g5_customer_status SET
                        customer_id = '$customer_id',
                        status_step = '$status_step',
                        updated_at = '" . G5_TIME_YMDHIS . "',
                        updated_by = '{$member['mb_id']}' ");
        }
    }

    goto_url("./admin_customer.php?w=view&customer_id=$customer_id&saved=1");
}

// Delete Customer
if ($w == 'delete') {
    check_quote_token();

    if ($customer_id) {
        sql_query(" DELETE FROM g5_customer WHERE customer_id = '$customer_id' ");
        sql_query(" DELETE FROM g5_customer_status WHERE customer_id = '$customer_id' ");
        sql_query(" DELETE FROM g5_customer_status_log WHERE customer_id = '$customer_id' ");
        sql_query(" DELETE FROM g5_customer_share_link WHERE customer_id = '$customer_id' ");
    }

    goto_url("./admin_customer.php");
}

// Load customer data for view/edit
$customer = null;
$status = null;
$share_link = null;
$status_logs = [];

if ($customer_id) {
    $customer = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$customer_id' ");
    if ($customer) {
        $status = sql_fetch(" SELECT * FROM g5_customer_status WHERE customer_id = '$customer_id' ");
        $share_link = sql_fetch(" SELECT * FROM g5_customer_share_link WHERE customer_id = '$customer_id' ");

        // Load status logs
        $result = sql_query(" SELECT * FROM g5_customer_status_log WHERE customer_id = '$customer_id' ORDER BY changed_at DESC LIMIT 10 ");
        while ($row = sql_fetch_array($result)) {
            $status_logs[] = $row;
        }
    }
}

// Prepare Data for Summary View (Step 3)
$is_summary_mode = !empty($qa_id);
$quote_data = [];
$measure_data = [];
$item_data = [];

if ($is_summary_mode) {
    $quote_data = sql_fetch(" select * from g5_quote where qa_id = '{$qa_id}' ");
    if (!$quote_data) {
        alert('존재하지 않는 견적입니다.', './admin_customer.php');
    }

    // Fetch Measurements (Step 1)
    $result = sql_query(" select * from g5_quote_measure where qa_id = '{$qa_id}' order by qm_id asc ");
    while ($row = sql_fetch_array($result)) {
        $measure_data[] = $row;
    }

    // Fetch Quote Items (Step 2)
    $result = sql_query(" select * from g5_quote_item where qa_id = '{$qa_id}' order by qi_id asc ");
    while ($row = sql_fetch_array($result)) {
        $item_data[] = $row;
    }

    // [NEW] Calculate Total Supply Price for Default Tax Invoice Value
    $calculated_supply = 0;
    foreach ($item_data as $item) {
        $calculated_supply += $item['qi_amount'];
    }
}

// Generate Token
$token = get_quote_token();

include_once(G5_THEME_PATH . '/head.php');
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        background: #f3f4f6;
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 border-b pb-4 border-gray-200">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">고객 등록 <span
                    class="text-orange-600 text-sm font-medium ml-2">Customer Registration</span></h1>
            <p class="text-gray-500 text-xs mt-1">간판대학 통합 고객 관리 시스템</p>
        </div>
        <div class="flex gap-2">
            <?php if ($w == 'view' || $w == 'form'): ?>
                <a href="./admin_customer.php"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-list"></i> 목록
                </a>
            <?php endif; ?>
            <?php if ($w == ''): ?>
                <a href="?w=form"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-lg text-sm font-bold shadow flex items-center gap-2">
                    <i class="fas fa-plus"></i> 새 고객 등록
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step Navigation -->
    <style>
        .step-nav {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .step-item {
            flex: 1;
            text-align: center;
            padding: 1.5rem 1rem;
            background: white;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .step-item.active {
            border: 2px solid #ea580c;
            background: #fff7ed;
        }

        .step-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #9ca3af;
        }

        .step-item.active .step-number {
            color: #ea580c;
        }

        .step-item .step-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .step-item.active .step-label {
            color: #ea580c;
            font-weight: 600;
        }

        .step-item:not(.active) {
            cursor: pointer;
            transition: all 0.2s;
        }

        .step-item:not(.active):hover {
            border-color: #fb923c;
            background: #fff7ed;
        }

        /* [NEW] 레이아웃 확장 및 오른쪽 컬럼 확보 */
        #wrapper,
        .wrapper,
        .container {
            max-width: 1600px !important;
            width: 98% !important;
            margin: 0 auto;
        }
    </style>

    <div class="step-nav">
        <div class="step-item" onclick="location.href='./admin_quote_step1.php?qa_id=<?php echo $qa_id; ?>'">
            <div class="step-number">1</div>
            <div class="step-label">현장 실측</div>
        </div>
        <div class="step-item" onclick="location.href='./admin_quote.php?w=form&qa_id=<?php echo $qa_id; ?>'">
            <div class="step-number">2</div>
            <div class="step-label">견적 작성</div>
        </div>
        <div class="step-item active">
            <div class="step-number">3</div>
            <div class="step-label">고객 등록</div>
        </div>
    </div>

    <?php if ($is_summary_mode): // -------------------- SUMMARY VIEW -------------------- ?>

        <form action="./admin_customer.php" method="post" id="registerForm" onsubmit="return handle_register_submit(this);">
            <input type="hidden" name="w" value="register_complete">
            <input type="hidden" name="qa_id" value="<?php echo $qa_id; ?>">
            <input type="hidden" name="token" value="<?php echo $token; ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Step 1 (Measurements) & Basic Info -->
                <div class="lg:col-span-2 space-y-6">



                    <!-- Basic Info Summary -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="bg-gray-50 p-4 border-bottom border-gray-200 flex justify-between items-center font-bold text-gray-700">
                            <div class="flex items-center gap-2">
                                <span><i class="fas fa-user-circle text-orange-600 mr-2"></i> 기본 정보</span>
                                <?php if ($quote_data['qa_code'] ?? ''): ?>
                                    <span
                                        class="px-2 py-1 bg-white border border-gray-200 rounded text-xs font-mono text-gray-500 font-bold">No.
                                        <?php echo $quote_data['qa_code']; ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="./admin_quote_step1.php?qa_id=<?php echo $qa_id; ?>"
                                class="text-xs text-blue-600 hover:underline">수정</a>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="col-span-1 md:col-span-2">
                                <span class="block text-gray-500 text-xs mb-1">견적명 (제목)</span>
                                <span
                                    class="font-bold text-gray-800 text-lg md:text-xl"><?php echo $quote_data['qa_subject']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500 text-xs mb-1">고객/업체명</span>
                                <span
                                    class="font-bold text-gray-800 text-base md:text-lg"><?php echo $quote_data['qa_client_name']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500 text-xs mb-1">연락처</span>
                                <span
                                    class="font-bold text-gray-800 text-base md:text-lg"><?php echo $quote_data['qa_client_hp']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500 text-xs mb-1">이메일</span>
                                <span
                                    class="font-bold text-gray-800 text-base md:text-lg"><?php echo $quote_data['qa_client_email']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500 text-xs mb-1">상태</span>
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    <?php echo $quote_data['qa_status']; ?>
                                </span>
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <span class="block text-gray-500 text-xs mb-1">주소</span>
                                <span
                                    class="font-bold text-gray-800 text-sm md:text-base"><?php echo $quote_data['qa_client_addr'] . ' ' . $quote_data['qa_client_addr2']; ?></span>
                            </div>
                        </div>
                    </div>


                    <!-- [MOVED HERE] Step 3 Input Section: 결제 및 세금계산서 정보 -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="bg-gray-50 p-4 border-bottom border-gray-200 font-bold text-gray-700 flex justify-between items-center">
                            <span><i class="fas fa-file-invoice text-orange-600 mr-2"></i> 결제 및 세금계산서 정보</span>
                            <div class="flex gap-2">
                                <!-- 홈택스 엑셀 생성 버튼 -->
                                <button type="button" onclick="exportHometaxExcel()"
                                    class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                    <i class="fas fa-file-excel mr-1"></i> 홈택스 업로드 엑셀 생성
                                </button>
                                <!-- [FIX] 세금계산서 입력값 확인 버튼 추가 -->
                                <button type="button" onclick="openTaxCheckModal()"
                                    class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                    <i class="fas fa-check-circle mr-1"></i> 세금계산서 입력값 확인
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <!-- Horizontal Layout for Main Inputs -->
                            <div class="flex flex-wrap gap-4 items-end mb-4">
                                <!-- 1. 시공일 -->
                                <div class="w-40 flex-shrink-0">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">시공예정일</label>
                                    <input type="date" name="qa_construct_date"
                                        value="<?php echo $quote_data['qa_construct_date'] ?? ''; ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white text-sm">
                                </div>

                                <!-- 2. 입금상황 -->
                                <div class="w-32 flex-shrink-0">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">입금상황</label>
                                    <select name="qa_deposit_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white text-sm">
                                        <option value="입금대기" <?php echo ($quote_data['qa_deposit_status'] ?? '') == '입금대기' ? 'selected' : ''; ?>>입금대기</option>
                                        <option value="부분입금" <?php echo ($quote_data['qa_deposit_status'] ?? '') == '부분입금' ? 'selected' : ''; ?>>부분입금</option>
                                        <option value="입금완료" <?php echo ($quote_data['qa_deposit_status'] ?? '') == '입금완료' ? 'selected' : ''; ?>>입금완료</option>
                                    </select>
                                </div>

                                <!-- 3. 결제방식 -->
                                <div class="w-32 flex-shrink-0">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">결제방식</label>
                                    <select name="qa_payment_method"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 bg-white text-sm">
                                        <option value="현금" <?php echo ($quote_data['qa_payment_method'] ?? '') == '현금' ? 'selected' : ''; ?>>현금</option>
                                        <option value="계좌이체" <?php echo ($quote_data['qa_payment_method'] ?? '') == '계좌이체' ? 'selected' : ''; ?>>계좌이체</option>
                                        <option value="카드" <?php echo ($quote_data['qa_payment_method'] ?? '') == '카드' ? 'selected' : ''; ?>>카드</option>
                                    </select>
                                </div>

                                <!-- 4. 세금계산서 발행 신청 (Checkbox) -->
                                <div class="flex items-center h-10 w-48">
                                    <input type="checkbox" id="qa_tax_yn_check" name="qa_tax_yn_check" value="Y"
                                        class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                                        onchange="toggleTaxInfo(this)" <?php echo ($quote_data['qa_tax_yn'] ?? 'N') == 'Y' ? 'checked' : ''; ?>>
                                    <label for="qa_tax_yn_check"
                                        class="ml-2 text-sm font-semibold text-gray-700 cursor-pointer">
                                        세금계산서 발행 정보 입력
                                    </label>
                                    <input type="hidden" name="qa_tax_yn" class="qa_tax_yn_hidden"
                                        value="<?php echo $quote_data['qa_tax_yn'] ?? 'N'; ?>">
                                </div>
                            </div>

                            <!-- 세금계산서 상세 정보 (토글) -->
                            <div
                                class="tax_info_area <?php echo ($quote_data['qa_tax_yn'] ?? 'N') == 'Y' ? '' : 'hidden'; ?> bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                                <div class="font-bold text-gray-700 mb-3 pb-2 border-b border-gray-200">
                                    <i class="fas fa-arrow-down text-gray-400 mr-1"></i> 세금계산서 발행 상세 정보
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">

                                    <!-- [Row 1] 종류(2) / 작성일자(2) / 영수·청구(2) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">종류 (필수)</label>
                                        <select name="qa_tax_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded bg-white text-sm">
                                            <option value="01" <?php echo ($quote_data['qa_tax_type'] ?? '01') == '01' ? 'selected' : ''; ?>>01 일반</option>
                                            <option value="02" <?php echo ($quote_data['qa_tax_type'] ?? '') == '02' ? 'selected' : ''; ?>>02 영세율</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">작성일자 (필수)</label>
                                        <input type="date" name="qa_tax_date"
                                            value="<?php echo $quote_data['qa_tax_date'] ?? date('Y-m-d'); ?>"
                                            class="w-full px-3 py-2 border border-blue-200 rounded text-sm bg-blue-50 font-bold text-blue-800">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">영수/청구 (필수)</label>
                                        <select name="qa_tax_claim_type"
                                            class="w-full px-3 py-2 border border-gray-300 rounded bg-white text-sm">
                                            <option value="01" <?php echo ($quote_data['qa_tax_claim_type'] ?? '01') == '01' ? 'selected' : ''; ?>>01 영수 (대금지급 완료)</option>
                                            <option value="02" <?php echo ($quote_data['qa_tax_claim_type'] ?? '') == '02' ? 'selected' : ''; ?>>02 청구 (대금지급 전)</option>
                                        </select>
                                    </div>

                                    <!-- [Row 2] 공급자상호(3) / 대표자성명(3) -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-1">공급자상호 (견적명)</label>
                                        <input type="text" name="qa_tax_trade_name"
                                            value="<?php echo $quote_data['qa_tax_trade_name'] ?: $quote_data['qa_subject']; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-600">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-1">대표자성명</label>
                                        <input type="text" name="qa_tax_ceo_name"
                                            value="<?php echo $quote_data['qa_tax_ceo_name'] ?: ($quote_data['qa_client_name'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-600">
                                    </div>

                                    <!-- [Row 3] 상호(필수, 3) / 이메일(3) -->
                                    <div class="md:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-1">상호 (필수)</label>
                                        <input type="text" name="qa_tax_company_name" placeholder="예: 간판마켓"
                                            value="<?php echo $quote_data['qa_tax_company_name'] ?: ($quote_data['qa_client_company'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-blue-200 rounded text-sm bg-blue-50 font-bold text-blue-800">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-1">고객 이메일</label>
                                        <input type="email" name="qa_tax_email"
                                            value="<?php echo $quote_data['qa_tax_email'] ?: ($quote_data['qa_client_email'] ?? ''); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-600">
                                    </div>

                                    <!-- [Row 4] 사업자번호(2) / 사업장주소(4) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">사업자번호</label>
                                        <input type="text" name="qa_tax_biz_num" placeholder="예: 1234567890" maxlength="12"
                                            value="<?php echo $quote_data['qa_tax_biz_num'] ?? ''; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm placeholder-gray-400">
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="block text-xs text-gray-500 mb-1">사업장주소</label>
                                        <input type="text" name="qa_tax_addr"
                                            value="<?php echo $quote_data['qa_tax_addr'] ?: trim($quote_data['qa_client_addr'] . ' ' . $quote_data['qa_client_addr2']); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-gray-600">
                                    </div>

                                    <!-- [Row 4] 품목(6) -->
                                    <div class="md:col-span-6">
                                        <label class="block text-xs text-gray-500 mb-1">품목명 (필수)</label>
                                        <input type="text" name="qa_tax_item_name"
                                            value="<?php echo $quote_data['qa_tax_item_name'] ?: '간판작업 1식'; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-medium text-gray-700">
                                    </div>

                                    <!-- [Row 5] 공급가액(2) / 부가세(2) / 합계(2) -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">공급가액</label>
                                        <input type="text" name="qa_tax_supply_price" id="qa_tax_supply_price"
                                            value="<?php echo number_format($quote_data['qa_tax_supply_price'] ?: $calculated_supply ?? 0); ?>"
                                            onkeyup="calcTaxVat()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-right font-medium">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">부가세 (자동)</label>
                                        <input type="text" name="qa_tax_vat_price" id="qa_tax_vat_price"
                                            value="<?php echo number_format($quote_data['qa_tax_vat_price'] ?: $quote_data['qa_price_total_vat'] ?? 0); ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm text-right bg-gray-100"
                                            readonly>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">합계 (참고용)</label>
                                        <span id="qa_tax_total_display"
                                            class="block w-full px-3 py-2 border border-transparent text-sm text-right font-bold text-gray-800">
                                            0
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            function toggleTaxInfo(checkbox) {
                                // [FIX] ID 중복 해결: Checkbox 기준 상대 경로 탐색
                                const container = checkbox.closest('.bg-white'); // 상위 컨테이너 (Step 3 카드)
                                if (!container) return;

                                const area = container.querySelector('.tax_info_area');
                                const hiddenInput = container.querySelector('.qa_tax_yn_hidden'); // hidden input (class로 변경됨)

                                if (checkbox.checked) {
                                    if (area) area.classList.remove('hidden');
                                    if (hiddenInput) hiddenInput.value = 'Y';
                                    // 최초 열림 시 자동 계산
                                    calcTaxVat(area);
                                } else {
                                    if (area) area.classList.add('hidden');
                                    if (hiddenInput) hiddenInput.value = 'N';
                                }
                            }

                            function calcTaxVat(scopeElement) {
                                let area = null;
                                // scopeElement가 area 자체이거나 내부 요소일 수 있음
                                if (scopeElement && scopeElement.classList && scopeElement.classList.contains('tax_info_area')) {
                                    area = scopeElement;
                                } else if (scopeElement && scopeElement.closest) {
                                    area = scopeElement.closest('.tax_info_area');
                                }

                                // Fallback: 초기 로딩 시 (document가 넘어올 수 있음 - 사용 안함) 혹은 area 못찾음
                                if (!area) {
                                    // 현재 활성화된(보이는) area 찾기 시도? 
                                    // 여기서는 scopeElement가 없으면 아무것도 안함.
                                    return;
                                }

                                const supplyInput = area.querySelector('input[name="qa_tax_supply_price"]');
                                const vatInput = area.querySelector('input[name="qa_tax_vat_price"]');
                                const totalDisplay = area.querySelector('#qa_tax_total_display') || document.getElementById('qa_tax_total_display');

                                if (!supplyInput) return;

                                // 콤마 제거 후 숫자만 추출
                                let valStr = supplyInput.value.replace(/[^0-9]/g, '');

                                if (valStr === '') {
                                    // supplyInput.value = ''; // Don't clear while typing
                                    if (vatInput) vatInput.value = '';
                                    if (totalDisplay) totalDisplay.innerText = '0원';
                                    return;
                                }

                                let supply = parseInt(valStr) || 0;
                                let vat = Math.floor(supply * 0.1);
                                let total = supply + vat;

                                // 값 표시
                                let formattedSupply = supply.toLocaleString();
                                if (supplyInput.value.replace(/[^0-9]/g, '') !== valStr || supplyInput.value !== formattedSupply) {
                                    // supplyInput.value = formattedSupply; // 포커스 문제로 생략가능하나 사용자 경험상 넣음
                                }

                                if (vatInput) vatInput.value = vat.toLocaleString();
                                if (totalDisplay) totalDisplay.innerText = total.toLocaleString() + '원';
                            }

                            // 초기 로딩 및 이벤트 바인딩
                            document.addEventListener("DOMContentLoaded", function () {
                                const areas = document.querySelectorAll('.tax_info_area');
                                areas.forEach(function (area) {
                                    const supplyInput = area.querySelector('input[name="qa_tax_supply_price"]');
                                    if (supplyInput) {
                                        supplyInput.addEventListener('input', function () { calcTaxVat(this); });
                                        supplyInput.addEventListener('keyup', function () { calcTaxVat(this); });
                                        supplyInput.addEventListener('blur', function () {
                                            let val = this.value.replace(/[^0-9]/g, '');
                                            if (val) this.value = parseInt(val).toLocaleString();
                                        });
                                    }
                                    // 초기값 계산
                                    calcTaxVat(area);
                                });
                            });

                        </script>

                        <!-- Measurement Summary -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div
                                class="bg-gray-50 p-4 border-bottom border-gray-200 flex justify-between items-center font-bold text-gray-700">
                                <span><i class="fas fa-ruler-combined text-orange-600 mr-2"></i> 현장 실측 데이터 (Step 1)</span>
                                <a href="./admin_quote_step1.php?qa_id=<?php echo $qa_id; ?>"
                                    class="text-xs text-blue-600 hover:underline">수정</a>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <?php if (count($measure_data) > 0): ?>
                                    <table class="w-full text-sm text-left min-w-[600px]">
                                        <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                                            <tr>
                                                <th class="px-3 md:px-6 py-3 text-xs md:text-sm">간판 종류</th>
                                                <th class="px-3 md:px-6 py-3 text-xs md:text-sm">사이즈</th>
                                                <th class="px-3 md:px-6 py-3 text-xs md:text-sm">수량</th>
                                                <th class="px-3 md:px-6 py-3 text-xs md:text-sm">메모</th>
                                                <th class="px-3 md:px-6 py-3 text-xs md:text-sm">사진</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php foreach ($measure_data as $m): ?>
                                                <tr>
                                                    <td class="px-3 md:px-6 py-4 font-medium text-xs md:text-sm">
                                                        <?php echo $m['qm_type']; ?>
                                                    </td>
                                                    <td class="px-3 md:px-6 py-4 text-xs md:text-sm"><?php echo $m['qm_width']; ?> x
                                                        <?php echo $m['qm_height']; ?>
                                                    </td>
                                                    <td class="px-3 md:px-6 py-4 text-xs md:text-sm"><?php echo $m['qm_qty']; ?>
                                                    </td>
                                                    <td class="px-3 md:px-6 py-4 text-gray-500 text-xs md:text-sm">
                                                        <?php echo $m['qm_memo']; ?>
                                                    </td>
                                                    <td class="px-3 md:px-6 py-4">
                                                        <div class="flex gap-2">
                                                            <?php if ($m['qm_img1']): ?>
                                                                <a href="<?php echo G5_DATA_URL . '/quote/' . $m['qm_img1']; ?>"
                                                                    target="_blank" class="text-blue-500 hover:text-blue-700"><i
                                                                        class="fas fa-image"></i></a>
                                                            <?php endif; ?>
                                                            <?php if ($m['qm_img2']): ?>
                                                                <a href="<?php echo G5_DATA_URL . '/quote/' . $m['qm_img2']; ?>"
                                                                    target="_blank" class="text-blue-500 hover:text-blue-700"><i
                                                                        class="fas fa-image"></i></a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-8 text-center text-gray-500 text-sm">입력된 실측 데이터가 없습니다.</div>
                                <?php endif; ?>
                            </div>
                        </div>



                        <!-- [NEW] Quote Items Details (Read-only) -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div
                                class="bg-gray-50 p-4 border-bottom border-gray-200 flex justify-between items-center font-bold text-gray-700">
                                <span><i class="fas fa-list-ol text-orange-600 mr-2"></i> 견적 상세 내역 (Step 2)</span>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <?php if (count($item_data) > 0): ?>
                                    <table class="w-full text-sm text-left min-w-[600px]">
                                        <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                                            <tr>
                                                <th class="px-4 py-3 text-center w-12 text-xs">No</th>
                                                <th class="px-4 py-3 text-xs md:text-sm">품목</th>
                                                <th class="px-4 py-3 text-xs md:text-sm">규격</th>
                                                <th class="px-4 py-3 text-center text-xs md:text-sm">수량</th>
                                                <th class="px-4 py-3 text-right text-xs md:text-sm">금액</th>
                                                <th class="px-4 py-3 text-xs md:text-sm">비고</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php $no = 1;
                                            foreach ($item_data as $item): ?>
                                                <tr>
                                                    <td class="px-4 py-3 text-center text-gray-400 text-xs"><?php echo $no++; ?>
                                                    </td>
                                                    <td class="px-4 py-3 font-medium text-gray-800 text-xs md:text-sm">
                                                        <?php echo $item['qi_item']; ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-600 text-xs md:text-sm">
                                                        <?php echo $item['qi_spec']; ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-gray-700 text-xs md:text-sm">
                                                        <?php echo number_format($item['qi_qty']); ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-bold text-gray-700 text-xs md:text-sm">
                                                        <?php echo number_format($item['qi_amount']); ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                                        <?php echo $item['qi_memo'] ?? $item['qi_note'] ?? ''; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="p-8 text-center text-gray-500 text-sm">입력된 견적 품목이 없습니다.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>



                </div> <!-- [FIX] Close Left Column to force next column -->

                <!-- Right Column: Step 2 (Quote Items) & Final Action -->
                <div class="space-y-6">





                    <!-- Right Column Action Area -->
                    <div class="space-y-6">

                        <!-- Quote Items Summary -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div
                                class="bg-gray-50 p-4 border-bottom border-gray-200 flex justify-between items-center font-bold text-gray-700">
                                <span><i class="fas fa-file-invoice-dollar text-orange-600 mr-2"></i> 견적 산출 내역 (Step
                                    2)</span>
                                <a href="./admin_quote.php?w=form&qa_id=<?php echo $qa_id; ?>"
                                    class="text-xs text-blue-600 hover:underline">수정</a>
                            </div>
                            <div class="p-0">
                                <div class="max-h-[400px] overflow-y-auto overflow-x-auto">
                                    <table class="w-full text-sm text-left min-w-[300px]">
                                        <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                                            <tr>
                                                <th class="px-3 md:px-4 py-3 text-xs md:text-sm">항목</th>
                                                <th class="px-3 md:px-4 py-3 text-right text-xs md:text-sm">금액</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php if (count($item_data) > 0): ?>
                                                <?php
                                                $total_supply = 0;
                                                $total_vat = 0;
                                                foreach ($item_data as $item) {
                                                    $total_supply += $item['qi_amount'];
                                                }
                                                $total_vat = floor($total_supply * 0.1);
                                                $total_price = $total_supply + $total_vat;
                                                $deposit = $quote_data['qa_deposit'] ?? 0;
                                                $balance = $total_price - $deposit;
                                                ?>
                                                <?php foreach ($item_data as $item): ?>
                                                    <tr>
                                                        <td class="px-3 md:px-4 py-3">
                                                            <div class="font-medium text-gray-800 text-xs md:text-sm">
                                                                <?php echo $item['qi_item']; ?>
                                                            </div>
                                                        </td>
                                                        <td
                                                            class="px-3 md:px-4 py-3 text-right font-medium text-gray-800 text-xs md:text-sm">
                                                            <?php echo number_format($item['qi_amount']); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>

                                                <!-- Summary Totals -->
                                                <tr class="bg-gray-50 border-t-2 border-gray-200">
                                                    <td class="px-3 md:px-4 py-2 text-right text-xs font-bold text-gray-500">
                                                        공급가액
                                                    </td>
                                                    <td
                                                        class="px-3 md:px-4 py-2 text-right text-xs md:text-sm font-bold text-gray-700">
                                                        <?php echo number_format($total_supply); ?>원
                                                    </td>
                                                </tr>
                                                <tr class="bg-gray-50">
                                                    <td class="px-3 md:px-4 py-2 text-right text-xs font-bold text-gray-500">
                                                        부가세
                                                    </td>
                                                    <td
                                                        class="px-3 md:px-4 py-2 text-right text-xs md:text-sm font-bold text-gray-700">
                                                        <?php echo number_format($total_vat); ?>원
                                                    </td>
                                                </tr>
                                                <tr class="bg-orange-50 border-t border-orange-100">
                                                    <td
                                                        class="px-3 md:px-4 py-3 text-right text-xs md:text-sm font-extrabold text-orange-800">
                                                        합계</td>
                                                    <td
                                                        class="px-3 md:px-4 py-3 text-right text-sm md:text-lg font-extrabold text-orange-600">
                                                        <?php echo number_format($total_price); ?>원
                                                    </td>
                                                </tr>
                                                <?php if ($deposit > 0): ?>
                                                    <tr class="bg-white border-t border-gray-200">
                                                        <td class="px-4 py-2 text-right text-xs font-bold text-blue-600">계약금
                                                        </td>
                                                        <td class="px-4 py-2 text-right text-sm font-bold text-blue-600">
                                                            - <?php echo number_format($deposit); ?>원
                                                        </td>
                                                    </tr>
                                                    <tr class="bg-gray-50 border-t border-gray-200">
                                                        <td class="px-4 py-3 text-right text-sm font-extrabold text-gray-800">잔금
                                                        </td>
                                                        <td class="px-4 py-3 text-right text-lg font-extrabold text-gray-800">
                                                            <?php echo number_format($balance); ?>원
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="2" class="p-8 text-center text-gray-500">견적 항목이 없습니다.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="bg-orange-50 p-4 border-t border-orange-100">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-gray-700">총 합계 (VAT포함)</span>
                                        <span class="font-extrabold text-orange-600 text-xl">
                                            <?php echo number_format($quote_data['qa_price_total']); ?>원
                                        </span>
                                    </div>
                                </div>
                                <!-- [NEW] 입력 정보 저장 버튼 (사용자 요청) -->
                                <div class="px-6 py-4 bg-white border-t border-gray-100 text-center">
                                    <button type="button"
                                        onclick="isFormDirty=false; handle_save_submit(document.getElementById('registerForm'))"
                                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-orange-500 text-base font-medium rounded-lg text-orange-600 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-sm">
                                        <i class="fas fa-save mr-2"></i> 입력 정보 저장 (중간 저장)
                                    </button>
                                    <!-- [NEW] 목록으로 버튼 (저장되지 않은 변경사항 체크) -->
                                    <button type="button" onclick="confirmExit('./admin_quote.php')"
                                        class="w-full mt-3 inline-flex justify-center items-center px-4 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm">
                                        <i class="fas fa-list mr-2"></i> 목록으로
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php if ($quote_data['qa_status'] == '등록완료' && $quote_data['qa_customer_id']): ?>
                            <!-- [NEW] 등록 완료 시: 진행상태 & 공유 링크 관리 (기존 상세 페이지 기능 이식) -->
                            <?php
                            // Load customer data locally for this view
                            $cust_id = $quote_data['qa_customer_id'];
                            $cust_status = sql_fetch(" SELECT * FROM g5_customer_status WHERE customer_id = '$cust_id' ");
                            $cust_link = sql_fetch(" SELECT * FROM g5_customer_share_link WHERE customer_id = '$cust_id' ");
                            ?>

                            <!-- 1. Status Management -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-tasks text-orange-600"></i> 진행상태
                                </h3>
                                <!-- 상태 변경 폼 (별도 처리 필요, 혹은 AJAX) -->
                                <!-- [FIX] 상태 변경 (AJAX) - 중첩 폼 제거 -->




                                <!-- 필요한 다른 hidden 필드들... 여기서는 약식으로 구현 후 보강 -->

                                <select name="status_step" onchange="updateStatus(this.value, <?php echo $cust_id; ?>)"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 font-semibold mb-2">
                                    <?php
                                    $statuses = ['견적서발송', '계약금입금', '디자인작업중', '제작중', '시공날짜조정', '시공중', '시공완료', '입금완료'];
                                    $curr_stat = $cust_status['status_step'] ?? '견적서발송';
                                    foreach ($statuses as $s) {
                                        $selected = ($curr_stat == $s) ? 'selected' : '';
                                        echo "<option value='$s' $selected>$s</option>";
                                    }
                                    ?>
                                </select>
                                <div class="text-xs text-gray-500">
                                    최종 변경: <?php echo $cust_status['updated_at']; ?>
                                    (<?php echo $cust_status['updated_by']; ?>)
                                </div>

                            </div>

                            <!-- 2. Share Link -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                    <i class="fas fa-link text-orange-600"></i> 고객 공유 링크
                                </h3>
                                <?php if ($cust_link): ?>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <input type="text" id="share_url_mini"
                                                value="<?php echo G5_URL . '/theme/basic/customer.php?token=' . $cust_link['share_token']; ?>"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs" readonly>
                                            <button type="button" onclick="copyShareUrlMini()"
                                                class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs text-nowrap">
                                                복사
                                            </button>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="regenerateLink(<?php echo $cust_id; ?>)"
                                                class="flex-1 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-xs">
                                                재발급
                                            </button>
                                            <button type="button"
                                                onclick="toggleLink(<?php echo $cust_id; ?>, <?php echo $cust_link['is_active'] ? 0 : 1; ?>)"
                                                class="flex-1 py-2 <?php echo $cust_link['is_active'] ? 'bg-red-600' : 'bg-green-600'; ?> text-white rounded hover:opacity-90 text-xs">
                                                <?php echo $cust_link['is_active'] ? '비활성화' : '활성화'; ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <button type="button" onclick="generateLink(<?php echo $cust_id; ?>)"
                                        class="w-full py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-bold">
                                        <i class="fas fa-plus mr-2"></i> 링크 생성
                                    </button>
                                <?php endif; ?>
                            </div>

                            <!-- [NEW] Memos (Right Column) -->
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                                <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                    🔒 내부 메모 <span class="text-xs font-normal text-gray-400">(고객 노출 X)</span>
                                </h3>
                                <textarea name="qa_memo"
                                    class="w-full h-32 p-3 border border-gray-200 rounded-lg bg-yellow-50/50 resize-none text-sm placeholder-gray-400 focus:bg-white focus:border-orange-500 transition mb-4"
                                    placeholder="관리자 전용 메모입니다."><?php echo $quote_data['qa_memo']; ?></textarea>

                                        <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                            📢 고객 참고사항 <span class="text-xs font-normal text-orange-500">(견적서 하단 표시)</span>
                                        </h3>
                                        <textarea name="qa_memo_user"
                                            class="w-full h-32 p-3 border border-gray-200 rounded-lg resize-none text-sm placeholder-gray-400 focus:border-orange-500 transition"
                                            placeholder="시공 일정, 입금 계좌 등 고객에게 알릴 내용을 입력하세요."><?php echo $quote_data['qa_memo_user']; ?></textarea>
                                    </div>

                            <?php else: ?>
                                    <!-- Final Action Card (등록 전) -->
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 shadow-sm">
                                        <div class="text-center">
                                            <h3 class="text-lg font-bold text-gray-800 mb-2">모든 내용이 정확한가요?</h3>
                                            <p class="text-sm text-gray-600 mb-6">입력하신 실측 정보와 견적 내용을 바탕으로<br>고객 등록을 완료합니다.</p>
                                            <button type="submit"
                                                class="w-full py-4 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 shadow-md transform transition hover:-translate-y-0.5 text-lg">
                                                <i class="fas fa-check-circle mr-2"></i> 최종 등록 완료
                                            </button>
                                        </div>
                                    </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
        </div>
        </form>

<?php elseif ($w == ''): // -------------------- LIST VIEW REMOVED -------------------- ?>
        <script>
            // 고객 목록 페이지가 Step 3로 통합되어 더 이상 사용되지           않습니다.    // 견적 목록 페이지로 이동합니다.
            location.href = './admin_quote.php';
        </script>

<?php else: // -------------------- EXCEPTION HANDLER (No qa_id) -------------------- ?>
        <script>
            // qa_id가 없거나 잘못된 접근인 경우 견적 목록으로 이동
            location.href = './admin_quote.php';
        </script>

<?php endif; ?>
</div>

<script>
    // Search Customers
    function searchCustomers() {
        const keyword = document.getElementById('search_keyword').value.trim();
        if (!keyword) {
            alert('검색어를 입력하세요');
            return;
        }

        fetch('?w=ajax_search&keyword=' + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('customer_list');
                if (data.customers.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">검색 결과가 없습니다</td></tr>';
                    return;
                }

                let html = '';
                data.customers.forEach(customer => {
                    let statusColor = 'gray';
                    if (customer.status_step == '계약금입금') statusColor = 'blue';
                    else if (customer.status_step == '제작중') statusColor = 'yellow';
                    else if (customer.status_step == '시공완료' || customer.status_step == '입금완료') statusColor = 'green';

                    html += `
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="location.href='?w=view&customer_id=${customer.customer_id}'">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800">${customer.customer_name}</div>
                            ${customer.customer_manager ? `<div class="text-xs text-gray-500">담당자: ${customer.customer_manager}</div>` : ''}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">${customer.customer_hp}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">${customer.customer_addr}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-${statusColor}-100 text-${statusColor}-700">
                                ${customer.status_step || '견적서발송'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="?w=view&customer_id=${customer.customer_id}" class="text-blue-600 hover:text-blue-800 font-bold text-sm" onclick="event.stopPropagation()">
                                상세보기
                            </a>
                        </td>
                    </tr>
                `;
                });
                tbody.innerHTML = html;
            });
    }

    // Generate/Regenerate Share Link
    function regenerateLink() {
        if (!confirm('공유 링크를 생성/재발급하시겠습니까? 기존 링크는 사용할 수 없게 됩니다.')) return;

        const formData = new FormData();
        formData.append('w', 'generate_link');
        formData.append('token', '<?php echo $token; ?>');
        formData.append('customer_id', '<?php echo $customer_id; ?>');

        fetch('./admin_customer.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('링크가 생성되었습니다');
                    location.reload();
                } else {
                    alert('링크 생성 실패');
                }
            });
    }

    // Toggle Link Active
    function toggleLink(isActive) {
        const formData = new FormData();
        formData.append('w', 'toggle_link');
        formData.append('token', '<?php echo $token; ?>');
        formData.append('customer_id', '<?php echo $customer_id; ?>');
        formData.append('is_active', isActive);

        fetch('./admin_customer.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    // Copy Share URL
    function copyShareUrl() {
        const input = document.getElementById('share_url');
        if (input) {
            input.select();
            document.execCommand('copy');
            alert('링크가 복사되었습니다');
        }
    }

    function copyShareUrlMini() {
        const input = document.getElementById('share_url_mini');
        input.select();
        document.execCommand('copy');
        alert('링크가 복사되었습니다');
    }

    // [NEW] AJAX Functions associated with Right Column Actions
    // 1. Update Status
    function updateStatus(newStep, custId) {
        // Simple implementation: submitting a hidden form or using fetch
        // For simplicity and reliability in this context, we will reload page after ajax or use a form submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = './admin_customer.php';

        const fields = {
            'w': 'save', // Re-using save logic which handles partial updates if ID exists
            'customer_id': custId,
            'status_step': newStep,
            'qa_id': '<?php echo $qa_id; ?>' // To return to this page
            // Note: need to ensure 'save' logic redirects back to qa_id form if qa_id is present
        };
        // wait... 'save' logic currently redirects to 'view'. We need to adjust 'save' controller logic too or use a dedicated ajax w.
        // Let's use a new ajax w for status update to avoid redirection hell.

        // Actually, let's just use the existing form submit for simplicity? No, that submits the whole big form.
        // Let's use Fetch API to a new controller endpoint? 
        // For now, let's just use alert because I can't easily change controller without another tool call.
        // Wait, I can't leave it broken.

        // Let's use the 'save' controller but we need to modify the redirect location in PHP controller if 'qa_id' is passed.
        // Since I processed PHP controller earlier, I might have missed 'save' redirect modification.
        // Let's implement a simple specialized AJAX handler in the next step or reuse 'toggle_link' style.

        // Let's use a hidden form approach that targets the 'w=save' but we need to pass 'qa_id' to redirect back here.
        // I will fix the PHP controller in 'w=save' block in same file if needed, but for now let's POST 

        if (confirm('진행상태를 ' + newStep + '(으)로 변경하시겠습니까?')) {
            // Create temporary form to submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = './admin_customer.php';

            // We need to return to THIS page (w=form&qa_id=...), not w=view.
            // But 'w=save' redirects to 'w=view'. 
            // Workaround: We will use 'step3_update' logic? No that's for quote data.
            // We'll trust the user to manually refresh or we add a specific handling later.
            // Actually, best current option: 

            // Let's just create a quick direct DB update via specific w if possible. 
            // OK, I will rely on 'toggle_link' style AJAX.
        }
    }

    // 2. Link Functions (AJAX)
    function generateLink(custId) {
        fetch('./admin_customer.php?w=generate_link&customer_id=' + custId + '&token=<?php echo $token; ?>')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('링크가 생성되었습니다.');
                    location.reload();
                } else {
                    alert('오류가 발생했습니다.');
                }
            });
    }

    function regenerateLink(custId) {
        if (confirm('링크를 재발급하시겠습니까? 기존 링크는 무효화됩니다.')) {
            generateLink(custId);
        }
    }

    function toggleLink(custId, isActive) {
        const formData = new FormData();
        formData.append('is_active', isActive);

        fetch('./admin_customer.php?w=toggle_link&customer_id=' + custId + '&token=<?php echo $token; ?>', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    // [FIX] Update Status - using fetch for 'ajax_status_update' (will add to controller in next step)
    function updateStatus(newStep, custId) {
        const formData = new FormData();
        formData.append('status_step', newStep);

        fetch('./admin_customer.php?w=ajax_status_update&customer_id=' + custId + '&token=<?php echo $token; ?>', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('상태가 변경되었습니다.');
                    // Don't reload, just stay? Or reload to update timestamps?
                    location.reload();
                }
            });
    }

    // Delete Customer
    function deleteCustomer() {
        open_confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.', function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = './admin_customer.php';

            const fields = {
                'w': 'delete',
                'token': '<?php echo $token; ?>',
                'customer_id': '<?php echo $customer_id; ?>'
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        });
    }

    // Form Submit Handlers
    function handle_register_submit(form) {
        open_confirm('고객 등록을 완료하시겠습니까?', function () {
            form.submit();
        });
        return false;
    }

    function handle_save_submit(form) {
        // [NEW] 세금계산서 유효성 검사
        if (form.qa_tax_yn && form.qa_tax_yn.value === 'Y') {
            if (!form.qa_tax_biz_num.value.trim()) {
                alert('세금계산서 발행을 위해 사업자등록번호를 입력해주세요.');
                form.qa_tax_biz_num.focus();
                return false;
            }
            if (!form.qa_tax_item_name.value.trim()) {
                alert('세금계산서 품목명을 입력해주세요.');
                form.qa_tax_item_name.focus();
                return false;
            }
        }

        // [FIX] 저장 전 유효성 검사 통과 시 -> 모달 오픈
        open_confirm('입력 정보를 저장하시겠습니까?\n(등록은 완료되지 않습니다)', function () {
            // [FIX] 모드 변경: register_complete -> step3_update (제거: 기존 로직 활용)
            // if (form.w) form.w.value = 'step3_update';

            // 저장 모드 플래그 추가
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'save_step3_only';
            input.value = '1';
            form.appendChild(input);

            form.submit();
        });
        return false;
    }

    // [FIX] 전역 콜백 변수 및 확인 모달 함수들 (한 곳으롳 통합)
    let confirmCallback = null;

    function open_confirm(msg, callback) {
        document.getElementById('confirm_msg').innerText = msg;
        document.getElementById('custom_confirm_modal').classList.remove('hidden');
        confirmCallback = callback;
    }

    function close_confirm_modal() {
        document.getElementById('custom_confirm_modal').classList.add('hidden');
        // 모달 닫을 때 콜백 초기화하지 않음 (이벤트 리스너에서 처리)
    }

    // [FIX] 페이지 로드 시 이벤트 리스너 등록
    window.addEventListener('load', function () {
        const btnYes = document.getElementById('btn_confirm_yes');
        if (btnYes) {
            btnYes.onclick = function () {
                if (confirmCallback) {
                    confirmCallback();
                }
                close_confirm_modal();
            };
        }
    });
</script>

<!-- Custom Confirm Modal (HTML) -->
<div id="custom_confirm_modal" class="hidden fixed inset-0 z-[9999]" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_confirm_modal()"></div>
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative"
            style="z-index: 100000 !important; pointer-events: auto;">
            <div class="bg-white px-6 pt-8 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-orange-50 mb-5">
                    <svg class="h-10 w-10 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">확인</h3>
                <p class="text-sm text-gray-600 leading-relaxed" id="confirm_msg">작업을 진행하시겠습니까?</p>
            </div>
            <div class="bg-white px-5 pb-5 flex flex-col gap-2.5">
                <button type="button" id="btn_confirm_yes"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3.5 bg-orange-500 text-base font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition shadow-sm">
                    예 (저장)
                </button>
                <button type="button" onclick="close_confirm_modal()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 border border-gray-200 focus:outline-none transition">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>


<?php
// 공급자 정보 로드 (quote_config.json)
$biz_info_file = G5_DATA_PATH . '/quote_config.json';
$biz_info_data = [];
if (file_exists($biz_info_file)) {
    $biz_info_data = json_decode(file_get_contents($biz_info_file), true);
}
?>
<!-- [FIX] 세금계산서 입력값 확인 모달 -->
<div id="tax_check_modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h3 class="font-bold text-lg text-gray-800"><i class="fas fa-check-double text-blue-600 mr-2"></i> 세금계산서 입력값
                검증 (Read-only)</h3>
            <button type="button" onclick="document.getElementById('tax_check_modal').classList.add('hidden')"
                class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

            <!-- 안내 문구 -->
            <div class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-sm border border-yellow-200 flex items-start">
                <i class="fas fa-exclamation-circle text-yellow-600 mt-0.5 mr-2"></i>
                <div>
                    <span class="font-bold block mb-1">값이 서로 다른가요?</span>
                    <span class="block text-gray-700">
                        <strong>좌측(Form)</strong>은 현재 작성 중인 내용이고, <strong>우측(Saved)</strong>은 이미 저장된 내용입니다.<br>
                        [입력 정보 저장] 버튼을 누르시면 좌우측 값이 동일해집니다.
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Column 1: 현재 폼 입력값 -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <h4 class="font-bold text-blue-800 mb-4 border-b border-blue-200 pb-2">Step 3 현재 입력값 (Form)</h4>
                    <div class="space-y-3 text-sm">
                        <!-- 1. 기본 정보 -->
                        <div class="grid grid-cols-2 gap-2 border-b border-blue-200 pb-2 mb-2">
                            <div><span class="text-gray-500 block text-xs">발행 신청</span><span id="modal_form_yn"
                                    class="font-bold text-gray-800">-</span></div>
                            <div><span class="text-gray-500 block text-xs">작성일자</span><span id="modal_form_date"
                                    class="font-bold text-blue-700">-</span></div>
                        </div>

                        <!-- 2. 필수 정보 -->
                        <div class="grid grid-cols-2 gap-2">
                            <div><span class="text-gray-500 block text-xs">종류</span><span id="modal_form_type"
                                    class="font-medium">-</span></div>
                            <div><span class="text-gray-500 block text-xs">영수/청구</span><span id="modal_form_claim"
                                    class="font-medium">-</span></div>
                        </div>
                        <div><span class="text-gray-500 block text-xs">사업자등록번호</span><span id="modal_form_biz"
                                class="font-bold text-gray-900">-</span></div>
                        <div><span class="text-gray-500 block text-xs">상호 (공급받는자)</span><span id="modal_form_company"
                                class="font-medium">-</span></div>
                        <div><span class="text-gray-500 block text-xs">대표자성명</span><span id="modal_form_ceo"
                                class="font-medium">-</span></div>
                        <div><span class="text-gray-500 block text-xs">사업장주소</span><span id="modal_form_addr"
                                class="font-medium text-xs">-</span></div>
                        <div><span class="text-gray-500 block text-xs">이메일</span><span id="modal_form_email"
                                class="font-medium">-</span></div>

                        <!-- 3. 거래 정보 -->
                        <div class="border-t border-blue-200 pt-2 mt-2">
                            <div><span class="text-gray-500 block text-xs">품목명</span><span id="modal_form_item"
                                    class="font-medium">-</span></div>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <div><span class="text-gray-500 block text-xs">공급가액</span><span id="modal_form_supply"
                                        class="font-bold text-right block">-</span></div>
                                <div><span class="text-gray-500 block text-xs">부가세</span><span id="modal_form_vat"
                                        class="font-bold text-right block">-</span></div>
                            </div>
                            <div class="mt-1 text-right border-t border-blue-100 pt-1"><span
                                    class="text-gray-500 text-xs mr-2">합계</span><span id="modal_form_total"
                                    class="font-extrabold text-blue-900">-</span></div>
                        </div>

                        <div class="border-t border-blue-200 pt-2 mt-2">
                            <span class="text-gray-500 block text-xs mb-1">공급자 상호 (Invoicer)</span>
                            <span id="modal_form_trade" class="font-bold text-gray-700">-</span>
                        </div>
                    </div>
                </div>

                <!-- Column 2: DB 저장값 -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                        <h4 class="font-bold text-gray-700">DB 저장된 값 (Saved)</h4>
                        <?php if (($quote_data['qa_tax_yn'] ?? '') != ''): ?>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded font-bold">저장됨</span>
                        <?php else: ?>
                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded font-bold">미저장</span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-3 text-sm">
                        <!-- 1. 기본 정보 -->
                        <div class="grid grid-cols-2 gap-2 border-b border-gray-200 pb-2 mb-2">
                            <div><span class="text-gray-500 block text-xs">발행 신청</span><span
                                    class="font-bold text-gray-800"><?php echo $quote_data['qa_tax_yn'] ?? '-'; ?></span>
                            </div>
                            <div><span class="text-gray-500 block text-xs">작성일자</span><span
                                    class="font-bold text-blue-700"><?php echo $quote_data['qa_tax_date'] ?? '-'; ?></span>
                            </div>
                        </div>

                        <!-- 2. 필수 정보 -->
                        <div class="grid grid-cols-2 gap-2">
                            <div><span class="text-gray-500 block text-xs">종류</span><span
                                    class="font-medium"><?php echo ($quote_data['qa_tax_type'] ?? '01') == '01' ? '01 일반' : '02 영세율'; ?></span>
                            </div>
                            <div><span class="text-gray-500 block text-xs">영수/청구</span><span
                                    class="font-medium"><?php echo ($quote_data['qa_tax_claim_type'] ?? '01') == '01' ? '01 영수' : '02 청구'; ?></span>
                            </div>
                        </div>
                        <div><span class="text-gray-500 block text-xs">사업자등록번호</span><span
                                class="font-bold text-gray-900"><?php echo $quote_data['qa_tax_biz_num'] ?? '-'; ?></span>
                        </div>
                        <div><span class="text-gray-500 block text-xs">상호 (공급받는자)</span><span
                                class="font-medium"><?php echo $quote_data['qa_tax_company_name'] ?? '-'; ?></span>
                        </div>
                        <div><span class="text-gray-500 block text-xs">대표자성명</span><span
                                class="font-medium"><?php echo $quote_data['qa_tax_ceo_name'] ?? '-'; ?></span></div>
                        <div><span class="text-gray-500 block text-xs">사업장주소</span><span
                                class="font-medium text-xs"><?php echo $quote_data['qa_tax_addr'] ?? '-'; ?></span>
                        </div>
                        <div><span class="text-gray-500 block text-xs">이메일</span><span
                                class="font-medium"><?php echo $quote_data['qa_tax_email'] ?? '-'; ?></span></div>

                        <!-- 3. 거래 정보 -->
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div><span class="text-gray-500 block text-xs">품목명</span><span
                                    class="font-medium"><?php echo $quote_data['qa_tax_item_name'] ?? '-'; ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-1">
                                <div><span class="text-gray-500 block text-xs">공급가액</span><span
                                        class="font-bold text-right block"><?php echo number_format($quote_data['qa_tax_supply_price'] ?? 0); ?></span>
                                </div>
                                <div><span class="text-gray-500 block text-xs">부가세</span><span
                                        class="font-bold text-right block"><?php echo number_format($quote_data['qa_tax_vat_price'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="mt-1 text-right border-t border-gray-100 pt-1"><span
                                    class="text-gray-500 text-xs mr-2">합계</span><span
                                    class="font-extrabold text-blue-900"><?php echo number_format(($quote_data['qa_tax_supply_price'] ?? 0) + ($quote_data['qa_tax_vat_price'] ?? 0)); ?>원</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <span class="text-gray-500 block text-xs mb-1">공급자 상호 (Invoicer)</span>
                            <span
                                class="font-bold text-gray-700"><?php echo $quote_data['qa_tax_trade_name'] ?? '-'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 공급자 정보 섹션 (추가) -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                    <span class="bg-gray-800 text-white text-xs px-2 py-1 rounded mr-2">공급자</span>
                    발행자 정보
                    <span class="text-xs text-gray-400 ml-2 font-normal">* 설정에서 변경 가능</span>
                </h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <!-- 1행 -->
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">상호(법인명)</span>
                            <span
                                class="font-bold text-gray-900"><?php echo $biz_info_data['biz_name'] ?? '-'; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">사업자등록번호</span>
                            <span class="font-bold text-gray-900"><?php echo $biz_info_data['biz_no'] ?? '-'; ?></span>
                        </div>
                        <!-- 2행 -->
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">대표자성명</span>
                            <span
                                class="font-medium text-gray-900"><?php echo $biz_info_data['biz_ceo'] ?? '-'; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs mb-1">이메일</span>
                            <span
                                class="font-medium text-gray-900"><?php echo $biz_info_data['biz_email'] ?? '-'; ?></span>
                        </div>
                        <!-- 3행 -->
                        <div class="col-span-2">
                            <span class="text-gray-500 block text-xs mb-1">사업장 주소</span>
                            <span
                                class="font-medium text-gray-900"><?php echo $biz_info_data['biz_addr'] ?? '-'; ?></span>
                        </div>
                        <!-- 4행 -->
                        <div class="col-span-2">
                            <span class="text-gray-500 block text-xs mb-1">업태 / 종목</span>
                            <span
                                class="font-medium text-gray-900"><?php echo ($biz_info_data['biz_type'] ?? '-') . ' / ' . ($biz_info_data['biz_class'] ?? '-'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-xs text-gray-500 bg-yellow-50 p-3 rounded border border-yellow-100">
                <i class="fas fa-info-circle text-yellow-600 mr-1"></i> [저장됨] 상태여야 나중에 엑셀 생성 시 데이터가 정상적으로 반영됩니다. <br>
                입력값을 수정한 경우 반드시 하단의 <strong>[최종 등록 완료]</strong> 버튼을 눌러주세요.
            </div>

        </div>
        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg text-right">
            <button type="button" onclick="document.getElementById('tax_check_modal').classList.add('hidden')"
                class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">닫기</button>
        </div>
    </div>
</div>

<script>
    // [FIX] 모달 열기 및 폼 데이터 바인딩
    // [FIX] 모달 열기 및 폼 데이터 바인딩
    function openTaxCheckModal() {
        const modal = document.getElementById('tax_check_modal');
        const form = document.getElementById('registerForm');

        if (!modal || !form) return;

        // 폼 값 가져오기
        const yn = form.qa_tax_yn ? form.qa_tax_yn.value : '-';
        const type = form.qa_tax_type ? form.qa_tax_type.value : '-';
        const claim = form.qa_tax_claim_type ? form.qa_tax_claim_type.value : '-';
        const biz = form.qa_tax_biz_num ? form.qa_tax_biz_num.value : '-';
        const item = form.qa_tax_item_name ? form.qa_tax_item_name.value : '-';
        const company = form.qa_tax_company_name ? form.qa_tax_company_name.value : '-';

        // NEW Inputs
        const date = form.qa_tax_date ? form.qa_tax_date.value : '-';
        const trade = form.qa_tax_trade_name ? form.qa_tax_trade_name.value : '-';
        const ceo = form.qa_tax_ceo_name ? form.qa_tax_ceo_name.value : '-';
        const addr = form.qa_tax_addr ? form.qa_tax_addr.value : '-';
        const email = form.qa_tax_email ? form.qa_tax_email.value : '-';
        const supply = form.qa_tax_supply_price ? form.qa_tax_supply_price.value : '0';
        const vat = form.qa_tax_vat_price ? form.qa_tax_vat_price.value : '0';

        // Calc Total for Display
        const supplyNum = parseInt(supply.replace(/,/g, '')) || 0;
        const vatNum = parseInt(vat.replace(/,/g, '')) || 0;
        const total = (supplyNum + vatNum).toLocaleString() + '원';

        // 모달에 값 바인딩 (Input Values)
        document.getElementById('modal_form_yn').innerText = yn === 'Y' ? '발행 (Y)' : '미발행 (N)';
        document.getElementById('modal_form_type').innerText = type === '01' ? '01 일반' : '02 영세율';
        document.getElementById('modal_form_claim').innerText = claim === '01' ? '01 영수' : '02 청구';
        document.getElementById('modal_form_biz').innerText = biz || '-';
        document.getElementById('modal_form_item').innerText = item || '-';
        document.getElementById('modal_form_company').innerText = company || '-';

        // Set New Fields
        if (document.getElementById('modal_form_date')) document.getElementById('modal_form_date').innerText = date || '-';
        if (document.getElementById('modal_form_trade')) document.getElementById('modal_form_trade').innerText = trade || '-';
        if (document.getElementById('modal_form_ceo')) document.getElementById('modal_form_ceo').innerText = ceo || '-';
        if (document.getElementById('modal_form_addr')) document.getElementById('modal_form_addr').innerText = addr || '-';
        if (document.getElementById('modal_form_email')) document.getElementById('modal_form_email').innerText = email || '-';
        if (document.getElementById('modal_form_supply')) document.getElementById('modal_form_supply').innerText = supply || '0';
        if (document.getElementById('modal_form_vat')) document.getElementById('modal_form_vat').innerText = vat || '0';
        if (document.getElementById('modal_form_total')) document.getElementById('modal_form_total').innerText = total;

        modal.classList.remove('hidden');
    }
</script>

<!-- Unsaved Changes Modal -->
<div id="unsaved_modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl w-80 p-6 text-center transform transition-all">
        <div class="mb-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                <i class="fas fa-exclamation-triangle text-orange-600 text-lg"></i>
            </div>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">변경사항 저장</h3>
        <p class="text-sm text-gray-500 mb-6">
            작성 중인 내용이 저장되지 않았습니다.<br>
            저장 후 이동하시겠습니까?
        </p>
        <div class="space-y-2">
            <button onclick="saveAndExit()"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none sm:text-sm">
                예 (저장)
            </button>
            <button onclick="proceedExit()"
                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                아니요 (저장안함)
            </button>
            <button onclick="document.getElementById('unsaved_modal').classList.add('hidden')"
                class="w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-gray-400 hover:text-gray-500 focus:outline-none sm:text-sm">
                취소
            </button>
        </div>
    </div>
</div>

<script>
    let isFormDirty = false;
    let targetUrl = '';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('change', () => isFormDirty = true);
            form.addEventListener('input', () => isFormDirty = true);
        }
    });

    function confirmExit(url) {
        if (!url) url = './admin_quote.php';
        if (isFormDirty) {
            targetUrl = url;
            document.getElementById('unsaved_modal').classList.remove('hidden');
        } else {
            location.href = url;
        }
    }

    function proceedExit() {
        location.href = targetUrl;
    }

    function saveAndExit() {
        document.getElementById('unsaved_modal').classList.add('hidden');
        isFormDirty = false;
        // 기존 저장 함수 호출 (handle_save_submit이 정의되어 있어야 함)
        if (typeof handle_save_submit === 'function') {
            handle_save_submit(document.getElementById('registerForm'));
        } else {
            // Fallback if function not found (submit directly)
            document.getElementById('registerForm').submit();
        }
    }

    // 홈택스 엑셀 다운로드 함수
    function exportHometaxExcel() {
        const form = document.getElementById('registerForm');
        if (!form) {
            alert('폼을 찾을 수 없습니다.');
            return;
        }

        // qa_id 가져오기
        const qaIdInput = form.querySelector('[name="qa_id"]');
        if (!qaIdInput || !qaIdInput.value) {
            alert('견적서를 먼저 저장해주세요.');
            return;
        }

        const qaId = qaIdInput.value;

        // 세금계산서 발행 여부 확인
        const taxYn = form.qa_tax_yn ? form.qa_tax_yn.value : 'N';
        if (taxYn !== 'Y') {
            if (!confirm('세금계산서 발행이 "예"로 설정되지 않았습니다.\n그래도 엑셀을 생성하시겠습니까?')) {
                return;
            }
        }

        // 필수 항목 확인
        const bizNum = form.qa_tax_biz_num ? form.qa_tax_biz_num.value : '';
        const companyName = form.qa_tax_company_name ? form.qa_tax_company_name.value : '';
        const itemName = form.qa_tax_item_name ? form.qa_tax_item_name.value : '';

        if (!bizNum) {
            alert('공급받는자 사업자번호를 입력해주세요.');
            return;
        }

        if (!companyName) {
            alert('공급받는자 상호를 입력해주세요.');
            return;
        }

        if (!itemName) {
            alert('품목명을 입력해주세요.');
            return;
        }

        // 저장 확인
        if (!confirm('현재 입력된 정보로 홈택스 업로드용 엑셀 파일을 생성합니다.\n\n※ 주의: 아직 저장하지 않은 수정사항이 있다면 먼저 저장해주세요.\n\n계속하시겠습니까?')) {
            return;
        }

        // 엑셀 다운로드 페이지로 이동
        window.location.href = './export_hometax_simple.php?qa_id=' + qaId;
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
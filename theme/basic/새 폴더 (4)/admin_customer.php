<?php
include_once('./_common.php');

// Define local token functions
function get_quote_token()
{
    $token = md5(uniqid(rand(), true));
    set_session('ss_quote_token', $token);
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

    // Update Quote Status
    sql_query(" update g5_quote set qa_status = '등록완료', qa_customer_id = '{$customer_id}' where qa_id = '{$qa_id}' ");

    alert('고객 등록이 완료되었습니다.', './admin_customer.php');
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
                            <div class="col-span-1 md:col-span-2">
                                <span class="block text-gray-500 text-xs mb-1">주소</span>
                                <span
                                    class="font-bold text-gray-800 text-sm md:text-base"><?php echo $quote_data['qa_client_addr'] . ' ' . $quote_data['qa_client_addr2']; ?></span>
                            </div>
                            <div>
                                <span class="block text-gray-500 text-xs mb-1">상태</span>
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    <?php echo $quote_data['qa_status']; ?>
                                </span>
                            </div>
                        </div>
                    </div>

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
                                                <td class="px-3 md:px-6 py-4 font-medium text-xs md:text-sm"><?php echo $m['qm_type']; ?></td>
                                                <td class="px-3 md:px-6 py-4 text-xs md:text-sm"><?php echo $m['qm_width']; ?> x <?php echo $m['qm_height']; ?>
                                                </td>
                                                <td class="px-3 md:px-6 py-4 text-xs md:text-sm"><?php echo $m['qm_qty']; ?></td>
                                                <td class="px-3 md:px-6 py-4 text-gray-500 text-xs md:text-sm"><?php echo $m['qm_memo']; ?></td>
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

                </div>

                <!-- Right Column: Step 2 (Quote Items) & Final Action -->
                <div class="space-y-6">

                    <!-- Quote Items Summary -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div
                            class="bg-gray-50 p-4 border-bottom border-gray-200 flex justify-between items-center font-bold text-gray-700">
                            <span><i class="fas fa-file-invoice-dollar text-orange-600 mr-2"></i> 견적 산출 내역 (Step 2)</span>
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
                                                        <div class="font-medium text-gray-800 text-xs md:text-sm"><?php echo $item['qi_item']; ?></div>
                                                    </td>
                                                    <td class="px-3 md:px-4 py-3 text-right font-medium text-gray-800 text-xs md:text-sm">
                                                        <?php echo number_format($item['qi_amount']); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                            <!-- Summary Totals -->
                                            <tr class="bg-gray-50 border-t-2 border-gray-200">
                                                <td class="px-3 md:px-4 py-2 text-right text-xs font-bold text-gray-500">공급가액</td>
                                                <td class="px-3 md:px-4 py-2 text-right text-xs md:text-sm font-bold text-gray-700">
                                                    <?php echo number_format($total_supply); ?>원
                                                </td>
                                            </tr>
                                            <tr class="bg-gray-50">
                                                <td class="px-3 md:px-4 py-2 text-right text-xs font-bold text-gray-500">부가세</td>
                                                <td class="px-3 md:px-4 py-2 text-right text-xs md:text-sm font-bold text-gray-700">
                                                    <?php echo number_format($total_vat); ?>원
                                                </td>
                                            </tr>
                                            <tr class="bg-orange-50 border-t border-orange-100">
                                                <td class="px-3 md:px-4 py-3 text-right text-xs md:text-sm font-extrabold text-orange-800">합계</td>
                                                <td class="px-3 md:px-4 py-3 text-right text-sm md:text-lg font-extrabold text-orange-600">
                                                    <?php echo number_format($total_price); ?>원
                                                </td>
                                            </tr>
                                            <?php if ($deposit > 0): ?>
                                                <tr class="bg-white border-t border-gray-200">
                                                    <td class="px-4 py-2 text-right text-xs font-bold text-blue-600">계약금</td>
                                                    <td class="px-4 py-2 text-right text-sm font-bold text-blue-600">
                                                        - <?php echo number_format($deposit); ?>원
                                                    </td>
                                                </tr>
                                                <tr class="bg-gray-50 border-t border-gray-200">
                                                    <td class="px-4 py-3 text-right text-sm font-extrabold text-gray-800">잔금</td>
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
                        </div>
                    </div>

                    <!-- Final Action Card -->
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

                </div>
            </div>
        </form>

    <?php elseif ($w == ''): // -------------------- LIST VIEW -------------------- ?>
        <!-- Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" id="search_keyword" placeholder="고객명/업체명 검색..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    <span class="absolute left-3 top-2.5 text-gray-400"><i class="fas fa-search"></i></span>
                </div>
                <button onclick="searchCustomers()"
                    class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 font-medium">
                    검색
                </button>
            </div>
        </div>

        <!-- List Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">고객명/업체명</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">연락처</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">주소</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">진행상태</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">관리</th>
                    </tr>
                </thead>
                <tbody id="customer_list" class="divide-y divide-gray-100">
                    <?php
                    $sql = " SELECT c.*, s.status_step 
                             FROM g5_customer c 
                             LEFT JOIN g5_customer_status s ON c.customer_id = s.customer_id 
                             ORDER BY c.created_at DESC ";
                    $result = sql_query($sql);
                    $count = 0;

                    while ($row = sql_fetch_array($result)):
                        $count++;
                        $status_color = 'gray';
                        if ($row['status_step'] == '계약금입금')
                            $status_color = 'blue';
                        elseif ($row['status_step'] == '제작중')
                            $status_color = 'yellow';
                        elseif ($row['status_step'] == '시공완료' || $row['status_step'] == '입금완료')
                            $status_color = 'green';
                        ?>
                        <tr class="hover:bg-gray-50 cursor-pointer"
                            onclick="location.href='?w=view&customer_id=<?php echo $row['customer_id']; ?>'">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo $row['customer_name']; ?></div>
                                <?php if ($row['customer_manager']): ?>
                                    <div class="text-xs text-gray-500">담당자: <?php echo $row['customer_manager']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $row['customer_hp']; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $row['customer_addr']; ?></td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-<?php echo $status_color; ?>-100 text-<?php echo $status_color; ?>-700">
                                    <?php echo $row['status_step'] ?: '견적서발송'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="?w=view&customer_id=<?php echo $row['customer_id']; ?>"
                                    class="text-blue-600 hover:text-blue-800 font-bold text-sm"
                                    onclick="event.stopPropagation()">
                                    상세보기
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if ($count == 0): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                등록된 고객이 없습니다. "새 고객 등록" 버튼을 클릭하여 추가하세요.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($w == 'view' || $w == 'form'): // -------------------- DETAIL VIEW -------------------- ?>

        <form method="post" action="./admin_customer.php" id="customerForm" onsubmit="return handle_save_submit(this);">
            <input type="hidden" name="w" value="save">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Customer Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                        <h2 class="text-base md:text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-building text-orange-600"></i>
                            기본 정보
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">고객명/업체명 *</label>
                                <input type="text" name="customer_name"
                                    value="<?php echo $customer['customer_name'] ?? ''; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">담당자</label>
                                <input type="text" name="customer_manager"
                                    value="<?php echo $customer['customer_manager'] ?? ''; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">연락처 *</label>
                                <input type="text" name="customer_hp" value="<?php echo $customer['customer_hp'] ?? ''; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base"
                                    required>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">이메일</label>
                                <input type="email" name="customer_email"
                                    value="<?php echo $customer['customer_email'] ?? ''; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base">
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">주소</label>
                                <input type="text" name="customer_addr"
                                    value="<?php echo $customer['customer_addr'] ?? ''; ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base">
                            </div>
                        </div>
                    </div>

                    <!-- Tags & Memo -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                        <h2 class="text-base md:text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-tags text-orange-600"></i>
                            태그 & 메모
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">태그 (쉼표로 구분)</label>
                                <input type="text" name="customer_tags"
                                    value="<?php echo $customer['customer_tags'] ?? ''; ?>" placeholder="예: 까다로움,가격민감,선택느림"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">메모</label>
                                <textarea name="customer_memo" rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm md:text-base"><?php echo $customer['customer_memo'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Status & Actions -->
                <div class="space-y-6">
                    <!-- Status -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                        <h2 class="text-base md:text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-tasks text-orange-600"></i>
                            <span class="hidden md:inline">진행상태</span>
                            <span class="md:hidden">상태</span>
                        </h2>

                        <select name="status_step"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 font-semibold">
                            <?php
                            $statuses = ['견적서발송', '계약금입금', '디자인작업중', '제작중', '시공날짜조정', '시공중', '시공완료', '입금완료'];
                            $current_status = $status['status_step'] ?? '견적서발송';
                            foreach ($statuses as $s):
                                ?>
                                <option value="<?php echo $s; ?>" <?php echo $current_status == $s ? 'selected' : ''; ?>>
                                    <?php echo $s; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ($status): ?>
                            <div class="mt-4 text-xs text-gray-500">
                                <div>최종 변경: <?php echo date('Y-m-d H:i', strtotime($status['updated_at'])); ?></div>
                                <div>변경자: <?php echo $status['updated_by']; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Share Link -->
                    <?php if ($customer_id): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                            <h2 class="text-base md:text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-link text-orange-600"></i>
                                <span class="hidden sm:inline">고객 공유 링크</span>
                                <span class="sm:hidden">공유링크</span>
                            </h2>

                            <?php if ($share_link): ?>
                                <div class="space-y-3">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                        <input type="text" id="share_url"
                                            value="<?php echo G5_URL . '/theme/basic/customer.php?token=' . $share_link['share_token']; ?>"
                                            class="flex-1 px-3 py-2 border border-gray-300 rounded text-xs break-all" readonly>
                                        <button type="button" onclick="copyShareUrl()"
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs whitespace-nowrap">
                                            <i class="fas fa-copy"></i> 복사
                                        </button>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <button type="button" onclick="regenerateLink()"
                                            class="flex-1 px-3 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 text-xs">
                                            <i class="fas fa-sync"></i> 재발급
                                        </button>
                                        <button type="button" onclick="toggleLink(<?php echo $share_link['is_active'] ? 0 : 1; ?>)"
                                            class="flex-1 px-3 py-2 <?php echo $share_link['is_active'] ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?> text-white rounded text-xs">
                                            <?php echo $share_link['is_active'] ? '비활성화' : '활성화'; ?>
                                        </button>
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        상태: <span
                                            class="font-bold <?php echo $share_link['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $share_link['is_active'] ? '활성' : '비활성'; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <button type="button" onclick="regenerateLink()"
                                    class="w-full px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm">
                                    <i class="fas fa-plus"></i> 링크 생성
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="space-y-2">
                        <button type="submit"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold text-sm md:text-base">
                            <i class="fas fa-save"></i> 저장
                        </button>

                        <?php if ($customer_id): ?>
                            <button type="button" onclick="deleteCustomer()"
                                class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold text-sm md:text-base">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>

        <!-- Status Logs -->
        <?php if ($customer_id && count($status_logs) > 0): ?>
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-orange-600"></i>
                    상태 변경 이력
                </h2>

                <div class="space-y-2">
                    <?php foreach ($status_logs as $log): ?>
                        <div class="flex items-center gap-3 text-sm border-b border-gray-100 pb-2">
                            <div class="text-gray-500"><?php echo date('Y-m-d H:i', strtotime($log['changed_at'])); ?></div>
                            <div class="flex-1">
                                <span class="font-semibold text-red-600"><?php echo $log['before_step']; ?></span>
                                <i class="fas fa-arrow-right text-gray-400 mx-2"></i>
                                <span class="font-semibold text-green-600"><?php echo $log['after_step']; ?></span>
                            </div>
                            <div class="text-gray-500 text-xs"><?php echo $log['changed_by']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

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
        input.select();
        document.execCommand('copy');
        alert('링크가 복사되었습니다');
    }

    // Delete Customer
    function deleteCustomer() {
        open_confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.', function() {
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
        open_confirm('고객 등록을 완료하시겠습니까?', function() {
            form.submit();
        });
        return false;
    }

    function handle_save_submit(form) {
        open_confirm('저장하시겠습니까?', function() {
            form.submit();
        });
        return false;
    }

    // Custom Confirm Modal Functions
    var confirmCallback = null;

    function open_confirm(msg, callback) {
        document.getElementById('confirm_msg').innerText = msg;
        document.getElementById('custom_confirm_modal').classList.remove('hidden');
        confirmCallback = callback;
    }

    function close_confirm_modal() {
        document.getElementById('custom_confirm_modal').classList.add('hidden');
        confirmCallback = null;
    }

    document.getElementById('btn_confirm_yes').addEventListener('click', function() {
        if (confirmCallback) confirmCallback();
        close_confirm_modal();
    });
</script>

<!-- Custom Confirm Modal -->
<div id="custom_confirm_modal" class="hidden fixed inset-0 z-[9999]" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <!-- Flex Container for Centering -->
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">
        
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_confirm_modal()"></div>

        <!-- Modal panel -->
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
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    확인
                </h3>
                <p class="text-sm text-gray-600 leading-relaxed" id="confirm_msg">
                    작업을 진행하시겠습니까?
                </p>
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
include_once(G5_THEME_PATH . '/tail.php');
?>
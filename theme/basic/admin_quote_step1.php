<?php
include_once('./_common.php');

// Define local token functions to avoid admin.lib.php side effects
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
        alert('올바른 방법으로 이용해 주십시오.', './admin_quote_step1.php');
    }
    return true;
}

// 1. Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// 2. DB Initialization - Create tables if not exist
// FIX: Add g5_store table
if (!sql_query(" DESCRIBE g5_store ", false)) {
    $sql_store = "
        CREATE TABLE IF NOT EXISTS `g5_store` (
          `st_id` int(11) NOT NULL AUTO_INCREMENT,
          `st_name` varchar(100) NOT NULL DEFAULT '' COMMENT '지점명(상호)',
          `st_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '주소',
          `st_addr_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '상세주소',
          `st_contact` varchar(50) NOT NULL DEFAULT '' COMMENT '연락처',
          `st_hp` varchar(50) NOT NULL DEFAULT '' COMMENT '핸드폰',
          `st_manager` varchar(50) NOT NULL DEFAULT '' COMMENT '담당자',
          `st_tags` text NOT NULL COMMENT '태그 (쉼표 구분)',
          `st_memo` text NOT NULL COMMENT '메모',
          `st_status` varchar(50) NOT NULL DEFAULT '견적서발송' COMMENT '진행상태',
          `st_datetime` datetime NOT NULL COMMENT '등록일시',
          PRIMARY KEY (`st_id`),
          INDEX idx_st_name (st_name)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_store, true);
}

// FIX: Add g5_store_status_log table
if (!sql_query(" DESCRIBE g5_store_status_log ", false)) {
    $sql_log = "
        CREATE TABLE IF NOT EXISTS `g5_store_status_log` (
          `ssl_id` int(11) NOT NULL AUTO_INCREMENT,
          `st_id` int(11) NOT NULL DEFAULT 0,
          `ssl_status_before` varchar(50) NOT NULL DEFAULT '',
          `ssl_status_after` varchar(50) NOT NULL DEFAULT '',
          `ssl_changed_by` varchar(50) NOT NULL DEFAULT '' COMMENT '변경자 (관리자 ID)',
          `ssl_datetime` datetime NOT NULL,
          PRIMARY KEY (`ssl_id`),
          INDEX idx_st_id (st_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_log, true);
}

// FIX: Add g5_store_share_link table
if (!sql_query(" DESCRIBE g5_store_share_link ", false)) {
    $sql_link = "
        CREATE TABLE IF NOT EXISTS `g5_store_share_link` (
          `slink_id` int(11) NOT NULL AUTO_INCREMENT,
          `st_id` int(11) NOT NULL DEFAULT 0,
          `slink_token` varchar(64) NOT NULL DEFAULT '' COMMENT '공유 토큰',
          `slink_is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '활성화 여부',
          `slink_created_at` datetime NOT NULL,
          PRIMARY KEY (`slink_id`),
          UNIQUE KEY `slink_token` (`slink_token`),
          INDEX idx_st_id (st_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_link, true);
}

// FIX: Add g5_quote_measure table
if (!sql_query(" DESCRIBE g5_quote_measure ", false)) {
    $sql_measure = "
        CREATE TABLE IF NOT EXISTS `g5_quote_measure` (
          `qm_id` int(11) NOT NULL AUTO_INCREMENT,
          `qa_id` int(11) NOT NULL DEFAULT 0 COMMENT '견적 ID',
          `qm_index` int(11) NOT NULL DEFAULT 0 COMMENT '순서',
          `qm_type` varchar(100) NOT NULL DEFAULT '' COMMENT '간판 종류',
          `qm_width` varchar(50) NOT NULL DEFAULT '' COMMENT '가로(W)',
          `qm_height` varchar(50) NOT NULL DEFAULT '' COMMENT '세로(H)',
          `qm_qty` int(11) NOT NULL DEFAULT 0 COMMENT '수량',
          `qm_img1` varchar(255) NOT NULL DEFAULT '',
          `qm_img2` varchar(255) NOT NULL DEFAULT '',
          `qm_memo` text NOT NULL COMMENT '메모',
          PRIMARY KEY (`qm_id`),
          INDEX idx_qa_id (qa_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_measure, true);
}

// FIX: Add qa_store_id to g5_quote
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_store_id' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_store_id` int(11) NOT NULL DEFAULT 0 AFTER `qa_id` ", false);
    sql_query(" ALTER TABLE g5_quote ADD INDEX idx_qa_store_id (qa_store_id) ", false);
}

// [NEW] Sync Company Name (Step 1 <> Step 2 <> Step 3)
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_company_name' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_tax_company_name` varchar(100) NOT NULL DEFAULT '' AFTER `qa_client_name` ", false);
}

// -----------------------------------------------------------------------------
// Controller Logic
// -----------------------------------------------------------------------------
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;

// AJAX: Manage Sign Types
if ($w == 'ajax_sign_types') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'list';
    $sign_types_file = G5_DATA_PATH . '/sign_types.json';

    // Load existing sign types
    $sign_types = [];
    if (file_exists($sign_types_file)) {
        $sign_types = json_decode(file_get_contents($sign_types_file), true) ?: [];
    }

    if ($action == 'list') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'types' => $sign_types], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action == 'add') {
        $new_type = trim($_POST['type_name']);
        if ($new_type && !in_array($new_type, $sign_types)) {
            $sign_types[] = $new_type;
            file_put_contents($sign_types_file, json_encode($sign_types, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'types' => $sign_types], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '이미 존재하거나 빈 값입니다'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    if ($action == 'delete') {
        $type_to_delete = $_POST['type_name'];
        $sign_types = array_values(array_filter($sign_types, function ($t) use ($type_to_delete) {
            return $t != $type_to_delete;
        }));
        file_put_contents($sign_types_file, json_encode($sign_types, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'types' => $sign_types], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action == 'update') {
        $old_name = $_POST['old_name'];
        $new_name = trim($_POST['new_name']);
        if ($new_name) {
            $key = array_search($old_name, $sign_types);
            if ($key !== false) {
                $sign_types[$key] = $new_name;
                file_put_contents($sign_types_file, json_encode($sign_types, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'types' => $sign_types], JSON_UNESCAPED_UNICODE);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
            }
        }
        exit;
    }
}

// Save Quote Step 1
if ($w == 'save') {
    check_quote_token();

    $qa_subject = $_POST['qa_subject'];
    $qa_client_name = $_POST['qa_client_name'];
    $qa_client_hp = $_POST['qa_client_hp'];
    $qa_client_contact = $_POST['qa_client_contact'];
    $qa_client_email = $_POST['qa_client_email'];
    $qa_client_addr = $_POST['qa_client_addr'];
    $qa_client_addr2 = $_POST['qa_client_addr2'];
    $qa_status = $_POST['qa_status'] ?? '작성중';

    // Measure items
    $qm_type = $_POST['qm_type'] ?? [];
    $qm_width = $_POST['qm_width'] ?? [];
    $qm_height = $_POST['qm_height'] ?? [];
    $qm_qty = $_POST['qm_qty'] ?? [];
    $qm_memo = $_POST['qm_memo'] ?? [];

    // [NEW] Global Memos
    $qa_memo = $_POST['qa_memo'] ?? '';
    $qa_memo_user = $_POST['qa_memo_user'] ?? '';
    // [NEW] Company Name
    $qa_tax_company_name = $_POST['qa_tax_company_name'] ?? '';

    // Image Upload Dir
    $upload_dir = G5_DATA_PATH . '/quote';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, G5_DIR_PERMISSION, true);
        @chmod($upload_dir, G5_DIR_PERMISSION);
    }

    $sql_common = " qa_subject = '$qa_subject',
                    qa_client_name = '$qa_client_name',
                    qa_client_hp = '$qa_client_hp',
                    qa_client_contact = '$qa_client_contact',
                    qa_client_email = '$qa_client_email',
                    qa_client_addr = '$qa_client_addr',
                    qa_client_addr2 = '$qa_client_addr2',
                    qa_status = '$qa_status',
                    qa_memo = '$qa_memo',
                    qa_memo_user = '$qa_memo_user',
                    qa_tax_company_name = '$qa_tax_company_name' ";

    if (!$qa_id) {
        // Create new quote
        $today_prefix = 'Q-' . date('Ymd') . '-';
        $row = sql_fetch(" SELECT count(*) as cnt FROM g5_quote WHERE qa_code LIKE '{$today_prefix}%' ");
        $seq = $row['cnt'] + 1;
        $new_code = $today_prefix . sprintf('%03d', $seq);

        $sql = " INSERT INTO g5_quote SET 
                 qa_code = '$new_code', 
                 qa_datetime = '" . G5_TIME_YMDHIS . "',
                 qa_price_supply = 0,
                 qa_price_vat = 0,
                 qa_price_total = 0,
                 $sql_common ";
        sql_query($sql);
        $qa_id = sql_insert_id();
    } else {
        // Update existing quote
        $sql = " UPDATE g5_quote SET $sql_common WHERE qa_id = '$qa_id' ";
        sql_query($sql);

        // Delete old measure data
        sql_query(" DELETE FROM g5_quote_measure WHERE qa_id = '$qa_id' ");
    }

    // Save measure items
    $measure_data = [];
    for ($i = 0; $i < count($qm_type); $i++) {
        if (!trim($qm_type[$i]))
            continue;

        // Image Handling
        $img_files = [];
        for ($m = 1; $m <= 2; $m++) {
            $f_name = "qm_img$m";
            $del_name = "qm_img{$m}_del";
            $prev_name = "qm_img{$m}_prev";

            $saved_file = $_POST[$prev_name][$i] ?? '';

            // Delete?
            if (isset($_POST[$del_name][$i]) && $_POST[$del_name][$i] == '1') {
                if ($saved_file && file_exists($upload_dir . '/' . $saved_file)) {
                    @unlink($upload_dir . '/' . $saved_file);
                }
                $saved_file = '';
            }

            // Upload?
            if (isset($_FILES[$f_name]['name'][$i]) && $_FILES[$f_name]['name'][$i]) {
                $ext = strtolower(pathinfo($_FILES[$f_name]['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $new_name = 'measure_' . date('YmdHis') . "_" . $i . "_" . $m . "_" . rand(1000, 9999) . "." . $ext;
                    if (move_uploaded_file($_FILES[$f_name]['tmp_name'][$i], $upload_dir . '/' . $new_name)) {
                        // Delete old if replaced
                        if ($saved_file && $saved_file != $new_name && file_exists($upload_dir . '/' . $saved_file)) {
                            @unlink($upload_dir . '/' . $saved_file);
                        }
                        $saved_file = $new_name;
                    }
                }
            }
            $img_files[$m] = $saved_file;
        }

        $measure_data[] = [
            'type' => $qm_type[$i],
            'width' => $qm_width[$i],
            'height' => $qm_height[$i],
            'qty' => (int) $qm_qty[$i],
            'memo' => $qm_memo[$i] ?? '',
            'img1' => $img_files[1],
            'img2' => $img_files[2]
        ];
    }

    foreach ($measure_data as $k => $v) {
        $sql = " INSERT INTO g5_quote_measure SET
                 qa_id = '$qa_id',
                 qm_index = '$k',
                 qm_type = '{$v['type']}',
                 qm_width = '{$v['width']}',
                 qm_height = '{$v['height']}',
                 qm_qty = '{$v['qty']}',
                 qm_memo = '{$v['memo']}',
                 qm_img1 = '{$v['img1']}',
                 qm_img2 = '{$v['img2']}' ";
        sql_query($sql);
    }

    // Redirect
    $next_step = isset($_POST['next_step']) ? (int) $_POST['next_step'] : 0;
    $redirect_to_list = isset($_POST['redirect_to_list']) ? (int) $_POST['redirect_to_list'] : 0;
    $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';

    if ($next_step == 2) {
        goto_url("./admin_quote.php?w=form&qa_id=$qa_id&from_step1=1");
    } elseif ($redirect_to_list) {
        goto_url("./admin_quote.php");
    } elseif ($redirect_url) {
        goto_url($redirect_url);
    } else {
        goto_url("./admin_quote_step1.php?w=form&qa_id=$qa_id&saved=1");
    }
}

// Load existing quote data
$quote = null;
$measures = [];

if ($qa_id) {
    $quote = sql_fetch(" SELECT * FROM g5_quote WHERE qa_id = '$qa_id' ");
    if ($quote) {
        // Load measure data
        $result = sql_query(" SELECT * FROM g5_quote_measure WHERE qa_id = '$qa_id' ORDER BY qm_index ");
        while ($row = sql_fetch_array($result)) {
            $measures[] = $row;
        }
    }
}

// Load business config for status list
$biz_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];

// Load sign types
$sign_types_file = G5_DATA_PATH . '/sign_types.json';
$sign_types = [];
if (file_exists($sign_types_file)) {
    $sign_types = json_decode(file_get_contents($sign_types_file), true) ?: [];
}
if (empty($sign_types)) {
    $sign_types = ['채널간판', '플렉스', '유리시트', '현수막', '기타'];
}

// Generate Token
$token = get_quote_token();

include_once(G5_THEME_PATH . '/head.php');
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        background: #f3f4f6;
    }

    .step-nav {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .step-item {
        flex: 1;
        padding: 1rem;
        background: white;
        border-radius: 0.5rem;
        text-align: center;
        border: 2px solid #e5e7eb;
    }

    .step-item.active {
        border-color: #ea580c;
        background: #fff7ed;
    }

    .step-item .step-number {
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

    .step-item:not(.active) {
        cursor: pointer;
        transition: all 0.2s;
    }

    .step-item:not(.active):hover {
        border-color: #fb923c;
        background: #fff7ed;
    }

    /* Compact input styles */
    .compact-input {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 border-b pb-4 border-gray-200">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">현장 실측 <span
                    class="text-orange-600 text-sm font-medium ml-2">Site Measurement</span></h1>
            <p class="text-gray-500 text-xs mt-1">간판대학 통합 고객 관리 시스템</p>
        </div>
        <div class="flex gap-2">
            <a href="./admin_quote.php"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm flex items-center gap-2">
                <i class="fas fa-list"></i> 목록으로
            </a>
        </div>
    </div>

    <!-- Step Navigation -->
    <div class="step-nav">
        <div class="step-item active">
            <div class="step-number">1</div>
            <div class="step-label">현장 실측</div>
        </div>
        <div class="step-item" onclick="navigateToPage('./admin_quote.php?w=form&qa_id=<?php echo $qa_id; ?>')">
            <div class="step-number">2</div>
            <div class="step-label">견적 작성</div>
        </div>
        <div class="step-item" onclick="navigateToPage('./admin_customer.php?qa_id=<?php echo $qa_id; ?>')">
            <div class="step-number">3</div>
            <div class="step-label">고객 등록</div>
        </div>
    </div>

    <!-- Main Form -->
    <form id="step1Form" method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit()">
        <input type="hidden" name="w" value="save">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="hidden" name="qa_id" value="<?php echo $qa_id; ?>">
        <input type="hidden" name="next_step" id="next_step" value="0">

        <!-- Basic Info Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b pb-4 gap-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-info-circle text-orange-600"></i>
                    기본 정보
                </h2>
                <div class="flex flex-wrap items-center gap-3">
                    <?php if (isset($quote['qa_code']) && $quote['qa_code']): ?>
                        <span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-500 font-bold">No.
                            <?php echo $quote['qa_code']; ?></span>
                    <?php endif; ?>

                    <!-- Compact Status Field -->
                    <div class="flex items-center gap-2 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">
                        <label class="text-xs font-bold text-orange-700">상태</label>
                        <div class="relative w-32">
                            <select id="qa_status_select" onchange="handle_status_change(this)"
                                class="w-full text-xs p-1 border border-orange-200 rounded focus:ring-1 focus:ring-orange-500 font-bold bg-white text-gray-700">
                                <?php
                                $default_statuses = '작성중,견적발송,연락두절,계약완료,작업중,작업완료,취소';
                                $status_list = explode(',', $biz_info['custom_statuses'] ?? $default_statuses);
                                $current_status = $quote['qa_status'] ?? '작성중';
                                $is_custom = !in_array($current_status, array_map('trim', $status_list));

                                foreach ($status_list as $status) {
                                    $status = trim($status);
                                    if (empty($status))
                                        continue;
                                    $selected = ($status == $current_status && !$is_custom) ? 'selected' : '';
                                    echo "<option value='{$status}' {$selected}>{$status}</option>";
                                }
                                ?>
                                <option value="__custom__" <?php echo $is_custom ? 'selected' : ''; ?>>➕ 직접 입력...
                                </option>
                            </select>
                            <input type="text" name="qa_status" id="qa_status_input"
                                value="<?php echo $current_status; ?>" placeholder="상태 입력"
                                class="<?php echo $is_custom ? '' : 'hidden'; ?> absolute inset-0 w-full text-xs p-1 border border-orange-200 rounded focus:ring-1 focus:ring-orange-500 font-bold bg-white text-gray-700">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <!-- Quote Subject -->
                <div class="col-span-12">
                    <label class="block text-xs font-bold text-gray-600 mb-1">견적명 (제목)</label>
                    <input type="text" name="qa_subject" value="<?php echo $quote['qa_subject'] ?? ''; ?>"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm font-bold"
                        placeholder="예: 강남점 외부 간판 제작 및 시공" required>
                </div>

                <!-- [NEW] Company Name (상호) - Moved First -->
                <div class="col-span-12 lg:col-span-5">
                    <label class="block text-xs font-bold text-gray-600 mb-1">상호 (업체명)</label>
                    <input type="text" name="qa_tax_company_name"
                        value="<?php echo $quote['qa_tax_company_name'] ?? ''; ?>"
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                        placeholder="예: 간판대학 강남점">
                </div>

                <!-- Client Name - Smaller -->
                <div class="col-span-12 lg:col-span-3">
                    <label class="block text-xs font-bold text-gray-600 mb-1">고객명 (담당자) <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="qa_client_name" value="<?php echo $quote['qa_client_name'] ?? ''; ?>"
                        required
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                        placeholder="홍길동">
                </div>

                <!-- Phone - Remaining space -->
                <div class="col-span-12 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">연락처(HP) <span
                            class="text-red-500">*</span></label>
                    <input type="tel" name="qa_client_hp" id="qa_client_hp"
                        value="<?php echo $quote['qa_client_hp'] ?? ''; ?>" required
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                        placeholder="010-1234-5678" onkeyup="formatPhoneNumber(this)">
                </div>


                <!-- Contact (hidden) -->
                <div class="col-span-12 lg:col-span-6 hidden">
                    <label class="block text-xs font-bold text-gray-600 mb-1">기타 연락처</label>
                    <input type="text" name="qa_client_contact" value="<?php echo $quote['qa_client_contact'] ?? ''; ?>"
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                </div>

                <!-- Address (Reduced width) -->
                <div class="col-span-12 lg:col-span-8">
                    <label class="block text-xs font-bold text-gray-600 mb-1">주소</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="flex gap-2">
                            <input type="text" name="qa_client_addr" id="qa_client_addr"
                                value="<?php echo $quote['qa_client_addr'] ?? ''; ?>"
                                class="w-full compact-input border border-gray-300 rounded-lg bg-gray-50 focus:bg-white text-sm"
                                placeholder="주소 검색" readonly>
                            <button type="button" onclick="execDaumPostcode()"
                                class="bg-gray-700 text-white px-4 rounded-lg text-sm font-bold hover:bg-gray-800 whitespace-nowrap shadow-sm">검색</button>
                        </div>
                        <input type="text" name="qa_client_addr2" id="qa_client_addr2"
                            value="<?php echo $quote['qa_client_addr2'] ?? ''; ?>"
                            class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                            placeholder="상세주소 (동/호수/층)">
                    </div>
                </div>

                <!-- Email (Moved next to Address) -->
                <div class="col-span-12 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-400 mb-1">이메일</label>
                    <input type="email" name="qa_client_email" value="<?php echo $quote['qa_client_email'] ?? ''; ?>"
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-xs py-2"
                        placeholder="example@email.com">
                </div>
                <!-- Daum Layer -->
                <div id="wrap"
                    style="display:none;border:1px solid;width:500px;height:300px;margin:5px 0;position:relative">
                    <img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnFoldWrap"
                        style="cursor:pointer;position:absolute;right:0px;top:-1px;z-index:1"
                        onclick="foldDaumPostcode()" alt="접기 버튼">
                </div>
            </div>
        </div>


        <!-- Measure Items Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-ruler-combined text-orange-600"></i>
                    현장 측정 기록
                </h2>
                <button type="button" onclick="addMeasureRow()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <i class="fas fa-plus"></i> 항목 추가
                </button>
            </div>

            <div id="measure_items">
                <?php if (count($measures) > 0): ?>
                    <?php foreach ($measures as $idx => $m): ?>
                        <div class="measure-row border border-gray-200 rounded-lg p-4 mb-3 bg-gray-50"
                            data-index="<?php echo $idx; ?>">
                            <div class="flex flex-col md:flex-row items-start gap-3">
                                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 w-full">
                                    <!-- Type -->
                                    <div class="col-span-2">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            간판 종류
                                            <button type="button" onclick="openSignTypeModal()"
                                                class="ml-2 text-orange-600 hover:text-orange-700" title="간판 종류 관리">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        </label>
                                        <div class="relative">
                                            <input type="text" name="qm_type[]" value="<?php echo $m['qm_type']; ?>"
                                                class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base focus:ring-2 focus:ring-orange-500"
                                                placeholder="직접 입력 (예: 스타렉스 7호차)">
                                        </div>
                                    </div>

                                    <!-- Width -->
                                    <div class="col-span-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                                        <input type="text" name="qm_width[]" value="<?php echo $m['qm_width']; ?>"
                                            placeholder="450"
                                            class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                    </div>

                                    <!-- Height -->
                                    <div class="col-span-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                                        <input type="text" name="qm_height[]" value="<?php echo $m['qm_height']; ?>"
                                            placeholder="450"
                                            class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                    </div>

                                    <!-- Qty -->
                                    <div class="col-span-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                                        <input type="number" name="qm_qty[]" value="<?php echo $m['qm_qty']; ?>"
                                            class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                    </div>

                                    <!-- Memo -->
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                                        <input type="text" name="qm_memo[]" value="<?php echo $m['qm_memo']; ?>"
                                            placeholder="메모"
                                            class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                    </div>
                                </div>

                                <button type="button" onclick="removeMeasureRow(this)"
                                    class="w-full md:w-auto mt-2 md:mt-6 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    <i class="fas fa-trash"></i> <span class="md:hidden">삭제</span>
                                </button>
                            </div>

                            <!-- Images -->
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <?php for ($img_idx = 1; $img_idx <= 2; $img_idx++): ?>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-2">사진
                                            <?php echo $img_idx; ?></label>
                                        <input type="hidden" name="qm_img<?php echo $img_idx; ?>_prev[]"
                                            value="<?php echo $m['qm_img' . $img_idx]; ?>">
                                        <input type="hidden" name="qm_img<?php echo $img_idx; ?>_del[]" value="0"
                                            class="img-del-flag">
                                        <label class="cursor-pointer block">
                                            <span
                                                class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                                <i class="fas fa-camera mr-2"></i>
                                                <span class="filename-display-<?php echo $img_idx; ?>">파일 선택</span>
                                            </span>
                                            <input type="file" name="qm_img<?php echo $img_idx; ?>[]" accept="image/*"
                                                class="hidden img-file-input" data-index="<?php echo $img_idx; ?>">
                                        </label>
                                        <?php if ($m['qm_img' . $img_idx]): ?>
                                            <div class="mt-2 relative inline-block">
                                                <img src="<?php echo G5_DATA_URL . '/quote/' . $m['qm_img' . $img_idx]; ?>"
                                                    class="w-20 h-20 object-cover rounded border">
                                                <button type="button" onclick="deleteImage(this)"
                                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs hover:bg-red-600">×</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-500 py-8">
                        "항목 추가" 버튼을 클릭하여 현장 측정 데이터를 입력하세요
                    </div>
                <?php endif; ?>
            </div>
        </div>



        <!-- Section 3: Memos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        🔒 내부 메모 <span class="text-xs font-normal text-gray-400">(고객 노출 X)</span>
                    </h3>
                    <textarea name="qa_memo"
                        class="w-full h-32 p-3 border border-gray-200 rounded-lg bg-yellow-50/50 resize-none text-sm placeholder-gray-400 focus:bg-white focus:border-orange-500 transition"
                        placeholder="관리자 전용 메모입니다."><?php echo $quote['qa_memo']; ?></textarea>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        📢 고객 참고사항 <span class="text-xs font-normal text-orange-500">(견적서 하단 표시)</span>
                    </h3>
                    <textarea name="qa_memo_user"
                        class="w-full h-32 p-3 border border-gray-200 rounded-lg resize-none text-sm placeholder-gray-400 focus:border-orange-500 transition"
                        placeholder="시공 일정, 입금 계좌 등 고객에게 알릴 내용을 입력하세요."><?php echo $quote['qa_memo_user']; ?></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-end">
            <button type="button" onclick="go_list_safe()"
                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-list"></i> 목록
            </button>
            <button type="button" onclick="open_save_confirm()"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-save"></i> 저장
            </button>
            <button type="button" onclick="goToStep2()"
                class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                다음 (2단계로) <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<script>
    let hasUnsavedChanges = false;

    // Track changes
    document.getElementById('step1Form').addEventListener('input', function () {
        hasUnsavedChanges = true;
    });

    // Search Store
    function searchStore() {
        const keyword = document.getElementById('store_search').value.trim();
        if (!keyword) {
            alert('검색어를 입력하세요');
            return;
        }

        fetch('?w=ajax_search_store&keyword=' + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                const resultsDiv = document.getElementById('store_results');
                if (data.stores.length === 0) {
                    resultsDiv.innerHTML = '<div class="text-sm text-gray-500 p-3">검색 결과가 없습니다</div>';
                    return;
                }

                let html = '<div class="border border-gray-200 rounded-lg divide-y">';
                data.stores.forEach(store => {
                    html += `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer" onclick="selectStore(${store.st_id}, '${store.st_name}', '${store.st_addr}')">
                        <div class="font-semibold text-gray-800">${store.st_name}</div>
                        <div class="text-sm text-gray-600">${store.st_addr}</div>
                    </div>
                `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            });
    }

    // Select Store
    function selectStore(id, name, addr) {
        document.getElementById('qa_store_id').value = id;
        document.getElementById('selected_store').innerHTML = `
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
            <div class="font-semibold text-orange-800">${name}</div>
            <div class="text-sm text-gray-600">${addr}</div>
        </div>
    `;
        document.getElementById('store_results').innerHTML = '';
        document.getElementById('store_search').value = '';
        hasUnsavedChanges = true;
    }

    // Add Measure Row
    function addMeasureRow() {
        const container = document.getElementById('measure_items');
        const index = container.querySelectorAll('.measure-row').length;


        const html = `
        <div class="measure-row border border-gray-200 rounded-lg p-4 mb-3 bg-gray-50" data-index="${index}">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 w-full">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            간판 종류
                            <button type="button" onclick="openSignTypeModal()"
                                class="ml-2 text-orange-600 hover:text-orange-700" title="간판 종류 관리">
                                <i class="fas fa-cog"></i>
                            </button>
                        </label>
                        <div class="relative">
                            <input type="text" name="qm_type[]" 
                                class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base focus:ring-2 focus:ring-orange-500"
                                placeholder="직접 입력 (예: 스타렉스 7호차)">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                        <input type="text" name="qm_width[]" placeholder="450" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                        <input type="text" name="qm_height[]" placeholder="450" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                        <input type="number" name="qm_qty[]" value="1" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                        <input type="text" name="qm_memo[]" placeholder="메모" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                </div >
            <button type="button" onclick="removeMeasureRow(this)" class="w-full md:w-auto mt-2 md:mt-6 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                <i class="fas fa-trash"></i> <span class="md:hidden">삭제</span>
            </button>
            </div >
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">사진 1</label>
                    <input type="hidden" name="qm_img1_prev[]" value="">
                        <input type="hidden" name="qm_img1_del[]" value="0" class="img-del-flag">
                            <label class="cursor-pointer block">
                                <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                    <i class="fas fa-camera mr-2"></i>
                                    <span>파일 선택</span>
                                </span>
                                <input type="file" name="qm_img1[]" accept="image/*" class="hidden">
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">사진 2</label>
                            <input type="hidden" name="qm_img2_prev[]" value="">
                                <input type="hidden" name="qm_img2_del[]" value="0" class="img-del-flag">
                                    <label class="cursor-pointer block">
                                        <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                                            <i class="fas fa-camera mr-2"></i>
                                            <span>파일 선택</span>
                                        </span>
                                        <input type="file" name="qm_img2[]" accept="image/*" class="hidden">
                                    </label>
                                </div>
                        </div>
                </div>
                `;

        container.insertAdjacentHTML('beforeend', html);
        hasUnsavedChanges = true;
    }

    // Remove Measure Row
    function removeMeasureRow(btn) {
        open_confirm('이 항목을 삭제하시겠습니까?', function () {
            btn.closest('.measure-row').remove();
            hasUnsavedChanges = true;
        });
    }

    // Delete Image
    function deleteImage(btn) {
        open_confirm('이미지를 삭제하시겠습니까?', function () {
            const row = btn.closest('.measure-row');
            const imgContainer = btn.closest('div').closest('div');
            imgContainer.querySelector('.img-del-flag').value = '1';
            btn.closest('div').remove();
            hasUnsavedChanges = true;
        });
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

    // Go to Step 2
    function goToStep2() {
        if (hasUnsavedChanges) {
            if (!confirm('저장하지 않은 변경사항이 있습니다. 저장하고 다음 단계로 이동하시겠습니까?')) {
                return;
            }
        }
        document.getElementById('next_step').value = '2';
        document.getElementById('step1Form').submit();
    }

    // Before Submit
    function beforeSubmit() {
        hasUnsavedChanges = false;
        return true;
    }

    // Prevent accidental navigation
    window.addEventListener('beforeunload', function (e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Navigate to page with unsaved changes check
    var pendingNavUrl = null;

    function navigateToPage(url) {
        if (hasUnsavedChanges) {
            pendingNavUrl = url;
            document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
        } else {
            location.href = url;
        }
    }

    function close_back_confirm() {
        document.getElementById('back_confirm_modal_safe').classList.add('hidden');
        pendingNavUrl = null;
    }

    function confirm_back_save() {
        // For Step 1, saving typically means going to Step 2 or just submitting current form?
        // Step 1 usually saves data then moves to Step 2.
        // But if user clicked "Back to List", they might want to just save.
        // However, Step 1 form submission mimics "Next".
        // Let's assume for now "Save" means submit form.

        // If we are navigating away, maybe we just want to submit?
        // Actually, let's keep it simple: If they say "Yes (Save)", we invoke the save logic then redirect?
        // Or just submit the form. If 'redirect_url' is needed, we might need a hidden field.

        var form = document.getElementById('step1Form');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'redirect_after_save';
        input.value = pendingNavUrl;
        form.appendChild(input);

        // Use existing save logic?
        // For Step 1, it seems goToStep2() is the main save.
        // But we might be going to list.
        // Let's just submit the form. Admin backend should handle it.
        // Adding a 'w' or 'mode' might be needed if not standard.
        // Step 1 usually posts to itself or next step.
        document.getElementById('next_step').value = '1'; // Stay on step 1 or save?
        // Let's try submitting.
        form.submit();

        close_back_confirm();
    }

    function confirm_back_nosave() {
        if (pendingNavUrl) {
            location.href = pendingNavUrl;
        }
        close_back_confirm();
    }

    // Phone number formatting
    function formatPhoneNumber(input) {
        var numbers = input.value.replace(/[^0-9]/g, '');
        var formatted = '';

        if (numbers.startsWith('02')) {
            // Seoul area code
            if (numbers.length < 3) {
                formatted = numbers;
            } else if (numbers.length < 6) {
                formatted = numbers.slice(0, 2) + '-' + numbers.slice(2);
            } else if (numbers.length < 10) {
                formatted = numbers.slice(0, 2) + '-' + numbers.slice(2, 5) + '-' + numbers.slice(5);
            } else {
                formatted = numbers.slice(0, 2) + '-' + numbers.slice(2, 6) + '-' + numbers.slice(6, 10);
            }
        } else if (numbers.startsWith('01')) {
            // Mobile
            if (numbers.length < 4) {
                formatted = numbers;
            } else if (numbers.length < 8) {
                formatted = numbers.slice(0, 3) + '-' + numbers.slice(3);
            } else {
                formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
            }
        } else {
            // Other area codes
            if (numbers.length < 4) {
                formatted = numbers;
            } else if (numbers.length < 7) {
                formatted = numbers.slice(0, 3) + '-' + numbers.slice(3);
            } else if (numbers.length < 11) {
                formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 6) + '-' + numbers.slice(6);
            } else {
                formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
            }
        }

        input.value = formatted;
    }

    // Daum Postcode API
    var element_wrap = document.getElementById('wrap');

    function foldDaumPostcode() {
        element_wrap.style.display = 'none';
    }

    function execDaumPostcode() {
        new daum.Postcode({
            oncomplete: function (data) {
                var addr = data.address;
                document.getElementById('qa_client_addr').value = addr;
                document.getElementById('qa_client_addr2').focus();
                foldDaumPostcode();
            },
            width: '100%',
            height: '100%'
        }).embed(element_wrap);
        element_wrap.style.display = 'block';
    }

    // Status selection handler
    function handle_status_change(select) {
        var input = document.getElementById('qa_status_input');
        var value = select.value;

        if (value === '__custom__') {
            // Show input, hide select
            select.classList.add('hidden');
            input.classList.remove('hidden');
            input.value = '';
            input.focus();
        } else {
            // Use selected value
            input.value = value;
        }
    }

    // When input loses focus, check if empty to show select again
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('qa_status_input');
        var select = document.getElementById('qa_status_select');

        if (input && select) {
            input.addEventListener('blur', function () {
                if (!input.value.trim()) {
                    // If empty, go back to select
                    input.classList.add('hidden');
                    select.classList.remove('hidden');
                    select.value = select.options[0].value;
                    input.value = select.value;
                }
            });

            // Allow clicking to edit custom status
            input.addEventListener('click', function () {
                if (!input.classList.contains('hidden')) {
                    input.select();
                }
            });
        }
    });

    // Sign Type Management
    let currentSignTypes = [];

    function openSignTypeModal() {
        document.getElementById('signTypeModal').classList.remove('hidden');
        loadSignTypes();
    }

    function closeSignTypeModal() {
        document.getElementById('signTypeModal').classList.add('hidden');
    }

    function loadSignTypes() {
        fetch('?w=ajax_sign_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentSignTypes = data.types;
                    renderSignTypeList();
                    updateAllSignTypeSelects(data.types);
                }
            });
    }

    function renderSignTypeList() {
        const container = document.getElementById('signTypeList');
        if (currentSignTypes.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 py-4">등록된 간판 종류가 없습니다</div>';
            return;
        }

        let html = '';
        currentSignTypes.forEach((type, index) => {
            html += `
                <div class="flex items-center justify-between p-3 border-b border-gray-200 hover:bg-gray-50">
                    <span class="font-medium text-gray-800">${type}</span>
                    <div class="flex gap-2">
                        <button type="button" onclick="editSignType(${index}, '${type}')" 
                            class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-edit"></i> 수정
                        </button>
                        <button type="button" onclick="deleteSignType('${type}')" 
                            class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function addSignType() {
        const input = document.getElementById('newSignTypeName');
        const typeName = input.value.trim();

        if (!typeName) {
            alert('간판 종류명을 입력하세요');
            return;
        }

        fetch('?w=ajax_sign_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&type_name=${encodeURIComponent(typeName)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadSignTypes();
                } else {
                    alert(data.message || '추가 실패');
                }
            });
    }

    function editSignType(index, oldName) {
        const newName = prompt('새 이름을 입력하세요', oldName);
        if (!newName || newName === oldName) return;

        fetch('?w=ajax_sign_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&old_name=${encodeURIComponent(oldName)}&new_name=${encodeURIComponent(newName)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadSignTypes();
                } else {
                    alert('수정 실패');
                }
            });
    }

    function deleteSignType(typeName) {
        if (!confirm(`"${typeName}"을(를) 삭제하시겠습니까?`)) return;

        fetch('?w=ajax_sign_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&type_name=${encodeURIComponent(typeName)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadSignTypes();
                }
            });
    }

    function updateAllSignTypeSelects(types) {
        const selects = document.querySelectorAll('.sign-type-select');
        selects.forEach(select => {
            const currentValue = select.value;
            // Check if current value is custom (not in types)
            const isCustom = currentValue && !types.includes(currentValue) && currentValue !== '__custom__';

            let html = '<option value="">선택</option>';
            types.forEach(type => {
                const selected = type === currentValue ? 'selected' : '';
                html += `<option value="${type}" ${selected}>${type}</option>`;
            });
            html += `<option value="__custom__" ${isCustom ? 'selected' : ''}>+ 직접 입력...</option>`;

            select.innerHTML = html;

            // Handle Direct Input visibility
            const container = select.closest('.relative');
            const input = container ? container.querySelector('.sign-type-input') : null;

            if (input) {
                if (isCustom || select.value === '__custom__') {
                    select.classList.add('hidden');
                    input.classList.remove('hidden');
                    if (isCustom) input.value = currentValue;
                } else {
                    select.classList.remove('hidden');
                    input.classList.add('hidden');
                    input.value = '';
                }
            }
        });
    }

    // Handle Sign Type Change (Select vs Direct Input)
    function handleSignTypeChange(select) {
        const container = select.parentElement;
        const input = container.querySelector('.sign-type-input');

        if (select.value === '__custom__') {
            select.classList.add('hidden');
            input.classList.remove('hidden');
            input.value = '';
            input.focus();
        } else {
            input.value = select.value;
        }
    }

    // Initialize inputs for direct entry behaviors
    document.addEventListener('focusout', function (e) {
        if (e.target.classList.contains('sign-type-input')) {
            const input = e.target;
            const container = input.parentElement;
            const select = container.querySelector('.sign-type-select');

            if (!input.value.trim()) {
                input.classList.add('hidden');
                select.classList.remove('hidden');
                select.value = '';
            }
        }
    });

</script>

<!-- Sign Type Management Modal -->
<div id="signTypeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-[500px]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">간판 종류 관리</h3>
            <button type="button" onclick="closeSignTypeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex gap-2 mb-4">
            <input type="text" id="newSignTypeName" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm"
                placeholder="새 간판 종류 이름" onkeypress="if(event.key === 'Enter') addSignType()">
            <button type="button" onclick="addSignType()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-bold">
                <i class="fas fa-plus"></i> 추가
            </button>
        </div>

        <div id="signTypeList" class="border border-gray-200 rounded max-h-[300px] overflow-y-auto mb-4">
            <!-- List items will be loaded here -->
        </div>

        <div class="text-right">
            <button type="button" onclick="closeSignTypeModal()"
                class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 text-sm font-bold">
                닫기
            </button>
        </div>
    </div>
</div>
</div>

<!-- Custom Confirm Modal (3 Buttons: Save/Don't Save/Cancel) -->
<div id="back_confirm_modal" class="hidden fixed inset-0 z-[10001] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="close_back_confirm()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">변경사항 저장</h3>
                <p class="text-sm text-gray-500">
                    작성 중인 내용이 저장되지 않았습니다.<br>
                    저장 후 이동하시겠습니까?
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="confirm_back_save()"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:w-auto sm:text-sm">
                    예 (저장)
                </button>
                <button type="button" onclick="confirm_back_nosave()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 sm:mt-0 sm:w-auto sm:text-sm">
                    아니요 (저장안함)
                </button>
                <button type="button" onclick="close_back_confirm()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Simple Confirm Modal (Save Action) -->
<div id="save_confirm_modal" class="hidden fixed inset-0 z-[10002]" role="dialog" aria-modal="true">
    <!-- Flex Container for Centering -->
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_save_confirm()"></div>

        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative"
            style="z-index: 100000 !important; pointer-events: auto;">
            <div class="bg-white px-6 pt-8 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-orange-50 mb-5">
                    <svg class="h-10 w-10 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">저장 확인</h3>
                <p class="text-sm text-gray-600 leading-relaxed">작성하신 내용을 저장하시겠습니까?</p>
            </div>
            <div class="bg-white px-5 pb-5 flex flex-col gap-2.5">
                <button type="button" onclick="execute_save()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3.5 bg-orange-500 text-base font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition shadow-sm">
                    예 (저장)
                </button>
                <button type="button" onclick="close_save_confirm()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 border border-gray-200 focus:outline-none transition">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Custom Alert Logic Overrides ---
    var pendingNavUrl = null;

    // 1. Save Confirm Modal Logic
    function open_save_confirm() {
        document.getElementById('save_confirm_modal').classList.remove('hidden');
    }

    function close_save_confirm() {
        document.getElementById('save_confirm_modal').classList.add('hidden');
    }

    function execute_save() {
        document.getElementById('save_confirm_modal').classList.add('hidden');
        hasUnsavedChanges = false; // Prevent dirty check
        document.getElementById('step1Form').submit();
    }

    // 2. Unsaved Changes (Back) Modal Logic - UPDATED
    var pendingNavUrl = null;

    function close_back_confirm() {
        document.getElementById('back_confirm_modal_safe').classList.add('hidden');
        pendingNavUrl = null;
    }

    function confirm_back_save() {
        // Save and Go Logic
        document.getElementById('back_confirm_modal_safe').classList.add('hidden');
        hasUnsavedChanges = false;

        var form = document.getElementById('step1Form');

        if (pendingNavUrl === 'NEXT_STEP_2') {
            document.getElementById('next_step').value = '2';
        } else if (pendingNavUrl) {
            // If navigating to list or other page, handled by backend redirect usually
            // but here we just submit. If specific url needed, add hidden input.
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'redirect_url';
            input.value = pendingNavUrl;
            form.appendChild(input);
        }

        form.submit();
    }

    function confirm_back_nosave() {
        document.getElementById('back_confirm_modal_safe').classList.add('hidden');
        hasUnsavedChanges = false;

        if (pendingNavUrl === 'NEXT_STEP_2') {
            // Cannot go next without saving usually, but if 'No Save' clicked?
            // Actually 'No Save' implies discard changes. 
            // But going to next step REQUIRES saving data from step 1 typically.
            // If user says "No Save" -> They probably stay here or go without saving?
            // Use original logic: Alert or just move. 
            // Original: alert('...cannot move...').

            // Check if we have an ID.
            var qa_id = document.querySelector('input[name="qa_id"]').value;
            if (qa_id && qa_id != 0) {
                location.href = './admin_quote.php?w=form&qa_id=' + qa_id + '&step=2'; // Direct link assuming saved
            } else {
                alert('저장되지 않은 견적은 다음 단계로 이동할 수 없습니다.');
            }
        } else if (pendingNavUrl) {
            location.href = pendingNavUrl;
        }
    }

    // Overrides
    function navigateToPage(url) {
        if (hasUnsavedChanges) {
            pendingNavUrl = url;
            document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
        } else {
            location.href = url;
        }
    }

    function go_list_safe() {
        if (hasUnsavedChanges) {
            pendingNavUrl = './admin_quote.php';
            document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
        } else {
            location.href = './admin_quote.php';
        }
    }

    function goToStep2() {
        if (hasUnsavedChanges) {
            pendingNavUrl = 'NEXT_STEP_2';
            document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
        } else {
            document.getElementById('next_step').value = '2';
            document.getElementById('step1Form').submit();
        }
    }
</script>

<!-- Custom Confirm Modal (Updated Design) -->
<div id="back_confirm_modal_safe" class="hidden fixed inset-0 z-[99999]" role="dialog" aria-modal="true">

    <!-- Flex Container for Centering -->
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">

        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_back_confirm()"></div>

        <!-- Modal Panel -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative"
            style="z-index: 100000 !important; pointer-events: auto;">
            <div class="bg-white px-6 pt-8 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-orange-50 mb-5">
                    <svg class="h-10 w-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">변경사항 저장</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    작성 중인 내용이 저장되지 않았습니다.<br>
                    저장 후 이동하시겠습니까?
                </p>
            </div>
            <div class="bg-white px-5 pb-5 flex flex-col gap-2.5">
                <button type="button" onclick="confirm_back_save()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3.5 bg-orange-500 text-base font-bold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition shadow-sm">
                    예 (저장)
                </button>
                <button type="button" onclick="confirm_back_nosave()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 border border-gray-200 focus:outline-none transition">
                    아니요 (저장안함)
                </button>
                <button type="button" onclick="close_back_confirm()"
                    class="w-full inline-flex justify-center items-center rounded-xl px-6 py-2.5 bg-white text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                    취소
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('back_confirm_modal_safe');
            if (modal) {
                document.body.appendChild(modal);
            }

            // Bind confirm button
            var btnConfirm = document.getElementById('btn_confirm_yes');
            if (btnConfirm) {
                btnConfirm.addEventListener('click', function () {
                    if (confirmCallback) confirmCallback();
                    close_confirm_modal();
                });
            }
        });
    </script>
</div>

<!-- Custom Confirm Modal (일반 확인용) -->
<div id="custom_confirm_modal" class="hidden fixed inset-0 z-[9999]" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <!-- Flex Container for Centering -->
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">

        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_confirm_modal()"></div>

        <!-- Modal panel -->
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-sm relative"
            style="z-index: 100000 !important; pointer-events: auto;">
            <div class="bg-white px-6 pt-6 pb-4 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-50 mb-4">
                    <!-- Icon -->
                    <svg class="h-8 w-8 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-3">
                    확인
                </h3>
                <p class="text-sm text-gray-500 leading-relaxed" id="confirm_msg">
                    작업을 진행하시겠습니까?
                </p>
            </div>
            <div class="bg-white px-4 pb-4 flex flex-col gap-2">
                <button type="button" id="btn_confirm_yes"
                    class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-3 bg-orange-600 text-base font-bold text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition">
                    예 (저장)
                </button>
                <button type="button" onclick="close_confirm_modal()"
                    class="w-full inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
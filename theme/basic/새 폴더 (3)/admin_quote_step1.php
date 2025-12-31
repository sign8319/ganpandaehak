<?php
include_once('./_common.php');

// Define local token functions to avoid admin.lib.php side effects
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
                    qa_status = '$qa_status' ";

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
    if ($next_step == 2) {
        goto_url("./admin_quote.php?w=form&qa_id=$qa_id&from_step1=1");
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

    /* Compact input styles */
    .compact-input {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-6xl">
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
        <div class="step-item" onclick="navigateToPage('./admin_customer.php')">
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
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-orange-600"></i>
                기본 정보
            </h2>

            <div class="grid grid-cols-12 gap-4">
                <!-- Quote Subject -->
                <div class="col-span-12">
                    <label class="block text-xs font-bold text-gray-600 mb-1">견적명 (제목)</label>
                    <input type="text" name="qa_subject" value="<?php echo $quote['qa_subject'] ?? ''; ?>"
                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm font-bold"
                        placeholder="예: 강남점 외부 간판 제작 및 시공" required>
                </div>

                <!-- Status -->
                <div class="col-span-12 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">상태 🏷️</label>
                    <div class="relative">
                        <select id="qa_status_select" onchange="handle_status_change(this)"
                            class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm font-bold">
                            <?php
                            $default_statuses = '작성중,견적발송,연락두절,계약완료,작업중,작업완료,취소';
                            $status_list = explode(',', $biz_info['custom_statuses'] ?? $default_statuses);
                            $current_status = $quote['qa_status'] ?? '작성중';

                            // Check if current status is in the list
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
                        <input type="text" name="qa_status" id="qa_status_input" value="<?php echo $current_status; ?>"
                            placeholder="상태를 입력하세요"
                            class="<?php echo $is_custom ? '' : 'hidden'; ?> absolute inset-0 w-full compact-input border-2 border-orange-500 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm font-bold bg-white">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">💡 목록에서 선택하거나 직접 입력 가능</p>
                </div>

                <!-- Client Name -->
                <div class="col-span-12 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">고객/업체명 <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="qa_client_name" value="<?php echo $quote['qa_client_name'] ?? ''; ?>"
                        required
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                </div>

                <!-- Email -->
                <div class="col-span-12 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-600 mb-1">이메일</label>
                    <input type="email" name="qa_client_email" value="<?php echo $quote['qa_client_email'] ?? ''; ?>"
                        class="w-full compact-input border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                        placeholder="example@email.com">
                </div>

                <!-- Phone -->
                <div class="col-span-12 lg:col-span-6">
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

                <!-- Address -->
                <div class="col-span-12">
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
                    <!-- Daum Layer -->
                    <div id="wrap"
                        style="display:none;border:1px solid;width:500px;height:300px;margin:5px 0;position:relative">
                        <img src="//t1.daumcdn.net/postcode/resource/images/close.png" id="btnFoldWrap"
                            style="cursor:pointer;position:absolute;right:0px;top:-1px;z-index:1"
                            onclick="foldDaumPostcode()" alt="접기 버튼">
                    </div>
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
                            <div class="flex items-start gap-3">
                                <div class="flex-1 grid grid-cols-6 gap-3">
                                    <!-- Type -->
                                    <div class="col-span-2">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            간판 종류
                                            <button type="button" onclick="openSignTypeModal()"
                                                class="ml-2 text-orange-600 hover:text-orange-700" title="간판 종류 관리">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        </label>
                                        <select name="qm_type[]"
                                            class="sign-type-select w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                            <option value="">선택</option>
                                            <?php foreach ($sign_types as $type): ?>
                                                <option value="<?php echo $type; ?>" <?php echo $m['qm_type'] == $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Width -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                                        <input type="text" name="qm_width[]" value="<?php echo $m['qm_width']; ?>"
                                            placeholder="450" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>

                                    <!-- Height -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                                        <input type="text" name="qm_height[]" value="<?php echo $m['qm_height']; ?>"
                                            placeholder="450" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>

                                    <!-- Qty -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                                        <input type="number" name="qm_qty[]" value="<?php echo $m['qm_qty']; ?>"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>

                                    <!-- Memo -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                                        <input type="text" name="qm_memo[]" value="<?php echo $m['qm_memo']; ?>"
                                            placeholder="메모" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                    </div>
                                </div>

                                <button type="button" onclick="removeMeasureRow(this)"
                                    class="mt-6 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Images -->
                            <div class="mt-3 flex gap-3">
                                <?php for ($img_idx = 1; $img_idx <= 2; $img_idx++): ?>
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">사진
                                            <?php echo $img_idx; ?></label>
                                        <input type="hidden" name="qm_img<?php echo $img_idx; ?>_prev[]"
                                            value="<?php echo $m['qm_img' . $img_idx]; ?>">
                                        <input type="hidden" name="qm_img<?php echo $img_idx; ?>_del[]" value="0"
                                            class="img-del-flag">
                                        <input type="file" name="qm_img<?php echo $img_idx; ?>[]" accept="image/*"
                                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                                        <?php if ($m['qm_img' . $img_idx]): ?>
                                            <div class="mt-2 relative inline-block">
                                                <img src="<?php echo G5_DATA_URL . '/quote/' . $m['qm_img' . $img_idx]; ?>"
                                                    class="w-20 h-20 object-cover rounded border">
                                                <button type="button" onclick="deleteImage(this)"
                                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs">×</button>
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

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-end">
            <button type="button" onclick="location.href='./admin_quote.php'"
                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                <i class="fas fa-list"></i> 목록
            </button>
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
            <div class="flex items-start gap-3">
                <div class="flex-1 grid grid-cols-6 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">간판 종류</label>
                        <select name="qm_type[]" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                            <option value="">선택</option>
                            <option value="채널간판">채널간판</option>
                            <option value="플렉스">플렉스</option>
                            <option value="유리시트">유리시트</option>
                            <option value="현수막">현수막</option>
                            <option value="기타">기타</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                        <input type="text" name="qm_width[]" placeholder="450" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                        <input type="text" name="qm_height[]" placeholder="450" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                        <input type="number" name="qm_qty[]" value="1" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                        <input type="text" name="qm_memo[]" placeholder="메모" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                    </div>
                </div>
                <button type="button" onclick="removeMeasureRow(this)" class="mt-6 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="mt-3 flex gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">사진 1</label>
                    <input type="hidden" name="qm_img1_prev[]" value="">
                    <input type="hidden" name="qm_img1_del[]" value="0" class="img-del-flag">
                    <input type="file" name="qm_img1[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">사진 2</label>
                    <input type="hidden" name="qm_img2_prev[]" value="">
                    <input type="hidden" name="qm_img2_del[]" value="0" class="img-del-flag">
                    <input type="file" name="qm_img2[]" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                </div>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', html);
        hasUnsavedChanges = true;
    }

    // Remove Measure Row
    function removeMeasureRow(btn) {
        if (confirm('이 항목을 삭제하시겠습니까?')) {
            btn.closest('.measure-row').remove();
            hasUnsavedChanges = true;
        }
    }

    // Delete Image
    function deleteImage(btn) {
        if (confirm('이미지를 삭제하시겠습니까?')) {
            const row = btn.closest('.measure-row');
            const imgContainer = btn.closest('div').closest('div');
            imgContainer.querySelector('.img-del-flag').value = '1';
            btn.closest('div').remove();
            hasUnsavedChanges = true;
        }
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
    function navigateToPage(url) {
        if (hasUnsavedChanges) {
            if (confirm('\uc800\uc7a5\ud558\uc9c0 \uc54a\uc740 \ubcc0\uacbd\uc0ac\ud56d\uc774 \uc788\uc2b5\ub2c8\ub2e4. \ud398\uc774\uc9c0\ub97c \uc774\ub3d9\ud558\uc2dc\uaca0\uc2b5\ub2c8\uae4c?')) {
                location.href = url;
            }
        } else {
            location.href = url;
        }
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
                }
            });
    }

    function renderSignTypeList() {
        const container = document.getElementById('signTypeList');
        if (currentSignTypes.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-500 py-4">\ub4f1\ub85d\ub41c \uac04\ud310 \uc885\ub958\uac00 \uc5c6\uc2b5\ub2c8\ub2e4</div>';
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
                            <i class="fas fa-edit"></i> \uc218\uc815
                        </button>
                        <button type="button" onclick="deleteSignType('${type}')" 
                            class="text-red-600 hover:text-red-800 text-sm">
                            <i class="fas fa-trash"></i> \uc0ad\uc81c
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
            alert('\uac04\ud310 \uc885\ub958\uba85\uc744 \uc785\ub825\ud558\uc138\uc694');
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
                    updateAllSignTypeSelects(data.types);
                } else {
                    alert(data.message || '\ucd94\uac00 \uc2e4\ud328');
                }
            });
    }

    function editSignType(index, oldName) {
        const newName = prompt('\uc0c8 \uc774\ub984\uc744 \uc785\ub825\ud558\uc138\uc694', oldName);
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
                    updateAllSignTypeSelects(data.types);
                } else {
                    alert('\uc218\uc815 \uc2e4\ud328');
                }
            });
    }

    function deleteSignType(typeName) {
        if (!confirm(`"${typeName}"\uc744(\ub97c) \uc0ad\uc81c\ud558\uc2dc\uaca0\uc2b5\ub2c8\uae4c?`)) return;

        fetch('?w=ajax_sign_types', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&type_name=${encodeURIComponent(typeName)}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadSignTypes();
                    updateAllSignTypeSelects(data.types);
                }
            });
    }

    function updateAllSignTypeSelects(types) {
        const selects = document.querySelectorAll('.sign-type-select');
        selects.forEach(select => {
            const currentValue = select.value;
            select.innerHTML = '<option value="">\uc120\ud0dd</option>';
            types.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                if (type === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        });
    }
</script>

<!-- Sign Type Management Modal -->
<div id="signTypeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-cog text-orange-600"></i> \uac04\ud310 \uc885\ub958 \uad00\ub9ac
            </h3>
            <button type="button" onclick="closeSignTypeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-4">
            <!-- Add New -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">\uc0c8 \uac04\ud310 \uc885\ub958
                    \ucd94\uac00</label>
                <div class="flex gap-2">
                    <input type="text" id="newSignTypeName" placeholder="\uac04\ud310 \uc885\ub958\uba85 \uc785\ub825"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
                        onkeypress="if(event.key==='Enter') addSignType()">
                    <button type="button" onclick="addSignType()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-bold">
                        <i class="fas fa-plus"></i> \ucd94\uac00
                    </button>
                </div>
            </div>

            <!-- List -->
            <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                <div id="signTypeList"></div>
            </div>
        </div>

        <div class="p-4 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeSignTypeModal()"
                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                \ub2eb\uae30
            </button>
        </div>
    </div>
</div>


<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
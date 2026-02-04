<?php
include_once('./_common.php');


// ============================================================================
// Load Common Libraries
// ============================================================================
include_once('./includes/quote_functions.php');
include_once('./includes/quote_db_schema.php');

// ============================================================================
// DB Initialization
// ============================================================================
init_quote_tables();

// ============================================================================
// Admin Check (moved here for clarity)
// ============================================================================
// 1. Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// 2. DB Initialization - Create tables if not exist

// -----------------------------------------------------------------------------
// Controller Logic
// -----------------------------------------------------------------------------
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;
$customer_id = isset($_REQUEST['customer_id']) ? (int) $_REQUEST['customer_id'] : 0;

// [NEW] Auto-create quote if customer_id exists but qa_id doesn't
if ($customer_id && !$qa_id) {
    // Load customer data
    $customer = sql_fetch("SELECT * FROM g5_customer WHERE customer_id = '$customer_id'");
    
    if ($customer) {
        // Create new quote with customer info
        // [보안] 중복 방지 헬퍼 함수 사용 (Race Condition 방지)
        $new_code = generate_unique_quote_code();
        
        $sql = " INSERT INTO g5_quote SET 
                 qa_code = '$new_code', 
                 qa_datetime = '" . G5_TIME_YMDHIS . "',
                 qa_subject = '',
                 qa_tax_company_name = '" . sql_real_escape_string($customer['customer_company'] ?: $customer['customer_name']) . "',
                 qa_client_name = '" . sql_real_escape_string($customer['customer_name']) . "',
                 qa_client_hp = '" . sql_real_escape_string($customer['customer_hp']) . "',
                 qa_client_email = '" . sql_real_escape_string($customer['customer_email']) . "',
                 qa_client_addr = '" . sql_real_escape_string($customer['customer_addr']) . "',
                 qa_customer_id = '$customer_id',
                 qa_status = '작성중',
                 qa_price_supply = 0,
                 qa_price_vat = 0,
                 qa_price_total = 0 ";
        
        $result = sql_query($sql);
        if ($result) {
            $qa_id = sql_insert_id();
            
            // Sync to work table
            save_work_from_quote($qa_id);
            
            // Redirect with qa_id
            goto_url("./admin_quote_step1.php?w=form&qa_id=$qa_id&customer_id=$customer_id");
        }
    }
}

// Load Common AJAX Handlers
include_once('./includes/quote_ajax.php');

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

    $qa_subject = clean_sql($_POST['qa_subject'] ?? '');
    $qa_client_name = clean_sql($_POST['qa_client_name'] ?? '');
    $qa_client_hp = clean_sql($_POST['qa_client_hp'] ?? '');
    $qa_client_contact = clean_sql($_POST['qa_client_contact'] ?? '');
    $qa_client_email = clean_sql($_POST['qa_client_email'] ?? '');
    $qa_client_addr = clean_sql($_POST['qa_client_addr'] ?? '');
    $qa_client_addr2 = clean_sql($_POST['qa_client_addr2'] ?? '');
    $qa_status = clean_sql($_POST['qa_status'] ?? '실측중');

    // Measure items
    $qm_type = $_POST['qm_type'] ?? [];
    $qm_width = $_POST['qm_width'] ?? [];
    $qm_height = $_POST['qm_height'] ?? [];
    $qm_qty = $_POST['qm_qty'] ?? [];
    $qm_memo = $_POST['qm_memo'] ?? [];

    // [NEW] Global Memos
    $qa_memo = clean_sql($_POST['qa_memo'] ?? '');
    $qa_memo_user = clean_sql($_POST['qa_memo_user'] ?? '');
    // [NEW] Company Name
    $qa_tax_company_name = clean_sql($_POST['qa_tax_company_name'] ?? '');

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
                    qa_tax_company_name = '$qa_tax_company_name',
                    qa_customer_id = '$customer_id' ";

    if (!$qa_id) {
        // Create new quote
        // [보안] 중복 방지 헬퍼 함수 사용 (Race Condition 방지)
        $new_code = generate_unique_quote_code();

        $sql = " INSERT INTO g5_quote SET
                 qa_code = '$new_code', 
                 qa_datetime = '" . G5_TIME_YMDHIS . "',
                 qa_price_supply = 0,
                 qa_price_vat = 0,
                 qa_price_total = 0,
                 $sql_common ";
        $result = sql_query($sql);
        if (!$result) {
            alert('저장 중 오류가 발생했습니다. 관리자에게 문의하세요.');
        }
        $qa_id = sql_insert_id();

        // [NEW] Sync Customer (Step 1 Save)
        // Find or Create Customer to ensure we have an ID
        $customer_id = find_or_create_customer(
            $qa_client_name, 
            $qa_client_hp, 
            $qa_client_email, 
            $qa_client_addr . ' ' . $qa_client_addr2, 
            $qa_tax_company_name
        );
        
        // Update Quote with new Customer ID
        sql_query(" UPDATE g5_quote SET qa_customer_id = '$customer_id' WHERE qa_id = '$qa_id' ");

        // [NEW] Status Log (Initial)
        if ($customer_id) {
             sql_query(" INSERT INTO g5_customer_status_log SET
                        customer_id = '$customer_id',
                        before_step = '작성중',
                        after_step = '$qa_status',
                        changed_by = '{$member['mb_id']}',
                        changed_at = '" . G5_TIME_YMDHIS . "' ");
             
             // Update Current Status Table
             sql_query(" INSERT INTO g5_customer_status SET
                        customer_id = '$customer_id',
                        status_step = '$qa_status',
                        updated_at = '" . G5_TIME_YMDHIS . "',
                        updated_by = '{$member['mb_id']}' 
                        ON DUPLICATE KEY UPDATE status_step = '$qa_status', updated_at = '" . G5_TIME_YMDHIS . "', updated_by = '{$member['mb_id']}' ");
        }
    } else {
        // Update existing quote
        // [NEW] Check Status Change
        $old_quote = sql_fetch(" SELECT qa_status, qa_customer_id FROM g5_quote WHERE qa_id = '$qa_id' ");
        $old_status = $old_quote['qa_status'];
        
        // [NEW] Sync Customer
        $customer_id = find_or_create_customer(
            $qa_client_name, 
            $qa_client_hp, 
            $qa_client_email, 
            $qa_client_addr . ' ' . $qa_client_addr2, 
            $qa_tax_company_name
        );

        // Update SQL with valid customer_id
        // Re-build sql_common with customer_id
        $sql_common .= ", qa_customer_id = '$customer_id' ";

        $sql = " UPDATE g5_quote SET $sql_common WHERE qa_id = '$qa_id' ";
        $result = sql_query($sql);
        if (!$result) {
            alert('저장 중 오류가 발생했습니다. 관리자에게 문의하세요.');
        }

        // [NEW] Status Log (Change)
        if ($customer_id && $old_status != $qa_status) {
             sql_query(" INSERT INTO g5_customer_status_log SET
                        customer_id = '$customer_id',
                        before_step = '$old_status',
                        after_step = '$qa_status',
                        changed_by = '{$member['mb_id']}',
                        changed_at = '" . G5_TIME_YMDHIS . "' ");

             sql_query(" UPDATE g5_customer_status SET
                        status_step = '$qa_status',
                        updated_at = '" . G5_TIME_YMDHIS . "',
                        updated_by = '{$member['mb_id']}' 
                        WHERE customer_id = '$customer_id' ");
        }

        // Delete old measure data
        $del_result = sql_query(" DELETE FROM g5_quote_measure WHERE qa_id = '$qa_id' ");
        if (!$del_result) {
            alert('기존 정보를 삭제하는 중 오류가 발생했습니다.');
        }
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
                // [보안] 1) 확장자 화이트리스트 검사
                $ext = strtolower(pathinfo($_FILES[$f_name]['name'][$i], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed_ext)) {
                    error_log("[UPLOAD_SECURITY] admin_quote_step1.php - Blocked extension: {$ext}, file: {$_FILES[$f_name]['name'][$i]}");
                    continue; // 허용되지 않은 확장자는 건너뜀
                }

                // [보안] 2) MIME 타입 검증 (finfo_file 사용) - 기존 validate_image_upload() 활용
                $file_to_validate = [
                    'tmp_name' => $_FILES[$f_name]['tmp_name'][$i],
                    'error' => $_FILES[$f_name]['error'][$i],
                    'size' => $_FILES[$f_name]['size'][$i]
                ];
                $validation = validate_image_upload($file_to_validate);
                if (!$validation['success']) {
                    error_log("[UPLOAD_SECURITY] admin_quote_step1.php - MIME validation failed: {$validation['message']}, file: {$_FILES[$f_name]['name'][$i]}");
                    continue; // MIME 검증 실패 시 건너뜀
                }

                // [보안] 3) 확장자-MIME 불일치 검사 (위장 파일 방지)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected_mime = finfo_file($finfo, $_FILES[$f_name]['tmp_name'][$i]);
                finfo_close($finfo);
                $mime_ext_map = [
                    'image/jpeg' => ['jpg', 'jpeg'],
                    'image/png' => ['png'],
                    'image/gif' => ['gif'],
                    'image/webp' => ['webp']
                ];
                $valid_ext_for_mime = $mime_ext_map[$detected_mime] ?? [];
                if (!in_array($ext, $valid_ext_for_mime)) {
                    error_log("[UPLOAD_SECURITY] admin_quote_step1.php - MIME/ext mismatch: mime={$detected_mime}, ext={$ext}, file: {$_FILES[$f_name]['name'][$i]}");
                    continue; // MIME과 확장자 불일치 시 건너뜀
                }

                // 검증 통과 - 파일 저장
                $new_name = 'measure_' . date('YmdHis') . "_" . $i . "_" . $m . "_" . rand(1000, 9999) . "." . $ext;
                if (move_uploaded_file($_FILES[$f_name]['tmp_name'][$i], $upload_dir . '/' . $new_name)) {
                    // Delete old if replaced
                    if ($saved_file && $saved_file != $new_name && file_exists($upload_dir . '/' . $saved_file)) {
                        @unlink($upload_dir . '/' . $saved_file);
                    }
                    $saved_file = $new_name;
                } else {
                    error_log("[UPLOAD_ERROR] admin_quote_step1.php - move_uploaded_file failed: {$_FILES[$f_name]['name'][$i]}");
                }
            }
            $img_files[$m] = $saved_file;
        }

        $measure_data[] = [
            'type' => clean_sql($qm_type[$i]),
            'width' => clean_sql($qm_width[$i]),
            'height' => clean_sql($qm_height[$i]),
            'qty' => (int) $qm_qty[$i],
            'memo' => clean_sql($qm_memo[$i] ?? ''),
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
        $ins_result = sql_query($sql);
        if (!$ins_result) {
            alert('개별 항목 저장 중 오류가 발생했습니다.');
        }
    }

    // [NEW] Sync to Work Table
    save_work_from_quote($qa_id);

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
$quote = []; // Initialize as empty array
$measures = [];

if ($qa_id) {
    $row = sql_fetch(" SELECT * FROM g5_quote WHERE qa_id = '$qa_id' ");
    if ($row) {
        $quote = $row;
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

// Initialize $quote default values to prevent warnings
if (!isset($quote) || !is_array($quote)) {
    $quote = [
        'qa_id' => '',
        'qa_subject' => '',
        'qa_content' => '',
        'qa_name' => '',
        'qa_email' => '',
        'qa_hp' => '',
        'qa_status' => '신청완료',
        'qa_memo' => '',
        'qa_memo_user' => '',
        'qa_1' => '', // Estimated Cost
        'qa_2' => '', // Size
        'qa_3' => '', // Material
        'qa_4' => '', // Install Date
        'qa_5' => '', // Address
        'qa_6' => '', // Address Detail
        'qa_related_type' => '',
        'qa_related_id' => '',
        'qa_manager_memo' => ''
    ];
}

// Generate Token
$token = get_quote_token();

include_once(G5_THEME_PATH . '/head.php');
// Header include removed from here
?>
<!-- Sidebar Layout Wrapper -->
<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <!-- Sidebar -->
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>

    <div class="flex-1 min-w-0">

<!-- Main Form Wrapper -->
<form id="step1Form" method="post" enctype="multipart/form-data" onsubmit="return beforeSubmit()">
    <input type="hidden" name="w" value="save">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="qa_id" value="<?php echo e($qa_id); ?>">
    <input type="hidden" name="next_step" id="next_step" value="0">

    <div class="admin-container">
        <!-- Action Bar (Button Area) - Conserved for future use or removed if empty -->
        <div class="admin-action-bar mb-6 hidden"></div>

        <div class="grid grid-cols-12 gap-6 items-start">
            <!-- Main Content Area (9/12) -->
            <div class="col-span-12 lg:col-span-9">
                <!-- Header Included Inside Form -->
                <?php include(G5_THEME_PATH . '/admin_quote_header.php'); ?>
                <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
                <!-- Measure Items Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-ruler-combined text-orange-600"></i>
                            현장 측정 기록
                        </h2>
                        <button type="button" onclick="addMeasureRow()"
                            class="admin-btn bg-green-600 text-white hover:bg-green-700 shadow-sm">
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
                                                    <input type="text" name="qm_type[]" value="<?php echo e($m['qm_type']); ?>"
                                                        class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base focus:ring-2 focus:ring-orange-500"
                                                        placeholder="직접 입력 (예: 스타렉스 7호차)">
                                                </div>
                                            </div>

                                            <!-- Width -->
                                            <div class="col-span-1">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                                                <input type="text" name="qm_width[]" value="<?php echo e($m['qm_width']); ?>"
                                                    placeholder="450"
                                                    class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                            </div>

                                            <!-- Height -->
                                            <div class="col-span-1">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                                                <input type="text" name="qm_height[]" value="<?php echo e($m['qm_height']); ?>"
                                                    placeholder="450"
                                                    class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                            </div>

                                            <!-- Qty -->
                                            <div class="col-span-1">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                                                <input type="number" name="qm_qty[]" value="<?php echo e($m['qm_qty']); ?>"
                                                    class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                                            </div>

                                            <!-- Memo -->
                                            <div class="col-span-2 sm:col-span-1">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                                                <input type="text" name="qm_memo[]" value="<?php echo e($m['qm_memo']); ?>"
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
                                                        class="hidden img-file-input" data-index="<?php echo $img_idx; ?>"
                                                        onchange="handleImagePreview(this)">
                                                </label>

                                                <!-- Image Preview Container -->
                                                <div class="preview-container mt-2 relative inline-block">
                                                    <?php if ($m['qm_img' . $img_idx]): ?>
                                                        <img src="<?php echo G5_DATA_URL . '/quote/' . $m['qm_img' . $img_idx]; ?>"
                                                            class="w-20 h-20 object-cover rounded border">
                                                        <button type="button" onclick="deleteImage(this)"
                                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs hover:bg-red-600 flex items-center justify-center">×</button>
                                                    <?php endif; ?>
                                                </div>
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
                                placeholder="관리자 전용 메모입니다."><?php echo e($quote['qa_memo'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                📢 고객 참고사항 <span class="text-xs font-normal text-orange-500">(견적서 하단 표시)</span>
                            </h3>
                            <textarea name="qa_memo_user"
                                class="w-full h-32 p-3 border border-gray-200 rounded-lg resize-none text-sm placeholder-gray-400 focus:border-orange-500 transition"
                                placeholder="시공 일정, 입금 계좌 등 고객에게 알릴 내용을 입력하세요."><?php echo e($quote['qa_memo_user'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end mt-8">
                    <button type="button" onclick="go_list_safe()"
                        class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-bold shadow-sm">
                        <i class="fas fa-list mr-2"></i> 목록
                    </button>
                    <button type="button" onclick="open_save_confirm()"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold shadow-sm">
                        <i class="fas fa-save mr-2"></i> 저장
                    </button>
                    <button type="button" onclick="goToStep2()"
                        class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-bold shadow-sm">
                        다음 (2단계로) <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div> <!-- End Main Content (col-9) -->

            <!-- Sidebar (3/12) -->
            <div class="col-span-12 lg:col-span-3">
                <?php include_once(G5_THEME_PATH . '/admin_quote_sidebar.php'); ?>
            </div>
        </div><!-- /.grid -->
    </div><!-- /.max-w -->
    </form>
    </div>
</div><!-- /.wrapper -->

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


</div>


</div>

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
</div>


</div>
</div>

<!-- Load External JavaScript -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script src="./assets/js/admin_quote_step1.js"></script>

<!-- Load Modal Components -->
<?php include_once('./views/partials/modals/confirm_modal.php'); ?>
<?php include_once('./views/partials/modals/save_confirm_modal.php'); ?>
<?php include_once('./views/partials/modals/back_confirm_modal.php'); ?>
<?php include_once('./views/partials/modals/sign_type_modal.php'); ?>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
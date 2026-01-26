<?php
/**
 * Quote System - Common Functions
 * 견적 시스템 공통 함수 모음
 */

if (!defined('_GNUBOARD_'))
    exit;

// ============================================================================
// Sidebar Helper Function
// ============================================================================

/**
 * Sidebar Active Class Helper
 * 사이드바 활성화 클래스 반환
 */
if (!function_exists('get_sidebar_active_class')) {
    function get_sidebar_active_class($is_active)
    {
        if ($is_active) {
            return 'bg-orange-50 text-orange-600 border-orange-100';
        } else {
            return 'text-gray-600 hover:bg-gray-50 border-transparent';
        }
    }
}

// ============================================================================
// Token Management Functions
// ============================================================================

/**
 * Get or generate quote session token
 * @return string Token value
 */
function get_quote_token()
{
    $token = get_session('ss_quote_token');
    if (!$token) {
        $token = md5(uniqid(rand(), true));
        set_session('ss_quote_token', $token);
    }
    return $token;
}

/**
 * Check and validate quote token
 * @return bool True if valid
 */
function check_quote_token()
{
    $token = get_session('ss_quote_token');

    /* 
    [FIX] 세션 초기화 로직 잠시 유보
    사용자 작업 중 (AJAX 등) 토큰이 사라져 저장이 안 되는 현상을 방지하기 위함
    set_session('ss_quote_token', ''); 
    */

    if (!$token || !isset($_REQUEST['token']) || $token != $_REQUEST['token']) {
        alert('올바른 방법으로 이용해 주십시오.', './admin_quote.php');
    }
    return true;
}

// ============================================================================
// Status Color Mapping
// ============================================================================

/**
 * Get color code for quote status
 * @param string $status Status name
 * @return string Color name (gray, blue, red, green, yellow)
 */
function get_status_color($status)
{
    $color_map = [
        '작성중' => 'gray',
        '견적발송' => 'blue',
        '연락두절' => 'red',
        '계약완료' => 'green',
        '작업중' => 'yellow',
        '작업완료' => 'green',
        '발송완료' => 'blue',
        '취소' => 'red'
    ];
    return isset($color_map[$status]) ? $color_map[$status] : 'gray';
}

// ============================================================================
// Image Processing Functions
// ============================================================================

/**
 * Compress and resize image
 * @param string $source Source file path
 * @param string $destination Destination file path
 * @param int $quality JPEG quality (1-100)
 * @param int $max_width Maximum width in pixels
 * @return bool Success status
 */
function compress_quote_image($source, $destination, $quality, $max_width = 1000)
{
    $info = getimagesize($source);
    if (!$info)
        return false;

    // Create image from source
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    // Get dimensions
    $width = imagesx($image);
    $height = imagesy($image);

    // Resize if needed
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $image_p = imagecreatetruecolor($new_width, $new_height);

        // Preserve transparency for PNG/WEBP
        if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
            imagealphablending($image_p, false);
            imagesavealpha($image_p, true);
        }

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $image = $image_p;
    }

    // Save compressed image
    $result = false;
    switch ($info['mime']) {
        case 'image/jpeg':
            $result = imagejpeg($image, $destination, $quality);
            break;
        case 'image/gif':
            $result = imagegif($image, $destination);
            break;
        case 'image/png':
            $result = imagepng($image, $destination, 8); // PNG compression level 0-9
            break;
        case 'image/webp':
            $result = imagewebp($image, $destination, $quality);
            break;
    }

    imagedestroy($image);
    return $result;
}

// ============================================================================
// Phone Number Formatting
// ============================================================================

/**
 * Format phone number with Korean format
 * @param string $phone Raw phone number
 * @return string Formatted phone number
 */
function format_phone_number($phone)
{
    $numbers = preg_replace('/[^0-9]/', '', $phone);

    if (empty($numbers))
        return '';

    // Seoul area code (02)
    if (substr($numbers, 0, 2) == '02') {
        if (strlen($numbers) < 10) {
            return preg_replace('/([0-9]{2})([0-9]{3})([0-9]+)/', '$1-$2-$3', $numbers);
        } else {
            return preg_replace('/([0-9]{2})([0-9]{4})([0-9]+)/', '$1-$2-$3', $numbers);
        }
    }
    // Mobile (01x)
    elseif (substr($numbers, 0, 2) == '01') {
        return preg_replace('/([0-9]{3})([0-9]{4})([0-9]+)/', '$1-$2-$3', $numbers);
    }
    // Other area codes (3 digits)
    else {
        if (strlen($numbers) < 11) {
            return preg_replace('/([0-9]{3})([0-9]{3})([0-9]+)/', '$1-$2-$3', $numbers);
        } else {
            return preg_replace('/([0-9]{3})([0-9]{4})([0-9]+)/', '$1-$2-$3', $numbers);
        }
    }
}

// ============================================================================
// Quote Calculation Functions
// ============================================================================

/**
 * Calculate total amount for a quote
 * @param int $qa_id Quote ID
 * @return array ['subtotal' => int, 'tax' => int, 'total' => int]
 */
function calculate_quote_total($qa_id)
{
    global $g5;

    $qa_id = (int) $qa_id;
    if (!$qa_id) {
        return ['subtotal' => 0, 'tax' => 0, 'total' => 0];
    }

    // Get quote items
    $sql = " SELECT SUM(qi_amt) as subtotal FROM {$g5['quote_item_table']} WHERE qa_id = '$qa_id' ";
    $row = sql_fetch($sql);

    $subtotal = (int) ($row['subtotal'] ?? 0);

    // Get quote tax info
    $sql = " SELECT qa_tax_yn FROM {$g5['quote_table']} WHERE qa_id = '$qa_id' ";
    $quote = sql_fetch($sql);

    $tax = 0;
    if (isset($quote['qa_tax_yn']) && $quote['qa_tax_yn'] == 'Y') {
        $tax = round($subtotal * 0.1); // 10% VAT
    }

    $total = $subtotal + $tax;

    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total
    ];
}

// ============================================================================
// Data Sanitization Functions
// ============================================================================

/**
 * Sanitize and prepare data for SQL insertion
 * @param array $data Key-value pairs to sanitize
 * @return array Sanitized data
 */
function sanitize_quote_data($data)
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_quote_data($value);
        } else {
            $sanitized[$key] = sql_real_escape_string(trim($value));
        }
    }
    return $sanitized;
}

/**
 * Build SQL SET clause from array
 * @param array $data Key-value pairs
 * @return string SQL SET clause without leading comma
 */
function build_sql_set($data)
{
    $sql_set = "";
    foreach ($data as $key => $value) {
        $val = addslashes($value);
        $sql_set .= " , $key = '$val' ";
    }
    return trim($sql_set, ',');
}

// ============================================================================
// File Upload Helpers
// ============================================================================

/**
 * Generate unique filename for upload
 * @param string $original_name Original filename
 * @return string Unique filename
 */
function generate_unique_filename($original_name)
{
    $ext = pathinfo($original_name, PATHINFO_EXTENSION);
    $filename = date('YmdHis') . '_' . md5(uniqid(rand(), true));
    return $filename . '.' . $ext;
}

/**
 * Validate uploaded image file
 * @param array $file $_FILES array element
 * @return array ['success' => bool, 'message' => string]
 */
function validate_image_upload($file)
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => '파일이 업로드되지 않았습니다.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 오류가 발생했습니다.'];
    }

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_types)) {
        return ['success' => false, 'message' => '지원하지 않는 파일 형식입니다.'];
    }

    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => '파일 크기는 10MB를 초과할 수 없습니다.'];
    }

    return ['success' => true, 'message' => ''];
}

// ============================================================================
// Date/Time Helpers
// ============================================================================

/**
 * Format datetime for display
 * @param string $datetime MySQL datetime string
 * @param string $format PHP date format
 * @return string Formatted date string
 */
function format_quote_datetime($datetime, $format = 'Y-m-d H:i')
{
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get date range for SQL query
 * @param string $period 'today', 'week', 'month', 'year'
 * @return array ['start' => string, 'end' => string]
 */
function get_date_range($period)
{
    $today = date('Y-m-d');

    switch ($period) {
        case 'today':
            return ['start' => $today . ' 00:00:00', 'end' => $today . ' 23:59:59'];

        case 'week':
            $start = date('Y-m-d', strtotime('monday this week'));
            $end = date('Y-m-d', strtotime('sunday this week'));
            return ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59'];

        case 'month':
            $start = date('Y-m-01');
            $end = date('Y-m-t');
            return ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59'];

        case 'year':
            $start = date('Y-01-01');
            $end = date('Y-12-31');
            return ['start' => $start . ' 00:00:00', 'end' => $end . ' 23:59:59'];

        default:
            return ['start' => '', 'end' => ''];
    }
}

// ============================================================================
// Currency Formatting
// ============================================================================

/**
 * Format number as Korean currency
 * @param int|float $amount Amount to format
 * @param bool $show_won Show ₩ symbol
 * @return string Formatted currency string
 */
function format_currency($amount, $show_won = true)
{
    $formatted = number_format($amount);
    return $show_won ? '₩' . $formatted : $formatted;
}

/**
 * Parse Korean currency string to number
 * @param string $currency_str Currency string (may contain ₩, commas)
 * @return int Numeric value
 */
function parse_currency($currency_str)
{
    return (int) preg_replace('/[^0-9]/', '', $currency_str);
}

/**
 * Clean up empty draft quotes older than 24 hours
 */
function cleanup_empty_drafts()
{
    // 24 hours ago
    $threshold = date('Y-m-d H:i:s', G5_SERVER_TIME - 86400);

    // Find drafts that have no client info and no customer linked
    // We only delete those created more than 24 hours ago
    $sql = " SELECT qa_id FROM g5_quote 
             WHERE qa_status = 'draft' 
               AND qa_client_name = '' 
               AND (qa_client_hp = '' OR qa_client_hp IS NULL)
               AND (qa_customer_id = 0 OR qa_customer_id IS NULL)
               AND qa_datetime < '$threshold' ";
    $result = sql_query($sql);

    while ($row = sql_fetch_array($result)) {
        $qa_id = $row['qa_id'];
        // Double check no items/measures exist (Extra safety)
        $item_cnt = sql_fetch(" SELECT count(*) as cnt FROM g5_quote_item WHERE qa_id = '$qa_id' ");
        $measure_cnt = sql_fetch(" SELECT count(*) as cnt FROM g5_quote_measure WHERE qa_id = '$qa_id' ");

        if ($item_cnt['cnt'] == 0 && $measure_cnt['cnt'] == 0) {
            sql_query(" DELETE FROM g5_quote WHERE qa_id = '$qa_id' ");
        }
    }
}
// ============================================================================
// Customer Management Helper
// ============================================================================

/**
 * Normalize phone number (Remove non-digits)
 */
function normalize_hp($hp)
{
    return preg_replace('/[^0-9]/', '', $hp);
}

/**
 * Find or Create Customer
 * 
 * Logic:
 * 1. Normalize HP
 * 2. Search by HP in g5_customer
 * 3. Search by Email in g5_customer
 * 4. Create new customer if not found
 * 
 * @param string $name Client Name
 * @param string $hp Client Phone
 * @param string $email Client Email
 * @param string $addr Client Address
 * @param string $company Client Company (Optional)
 * @return int Customer ID
 */
function find_or_create_customer($name, $hp, $email, $addr, $company = '')
{
    global $g5;

    $safe_hp = normalize_hp($hp);
    $safe_email = $email;
    $safe_name = sql_real_escape_string($name);
    $safe_addr = sql_real_escape_string($addr);
    $safe_company = sql_real_escape_string($company);
    $now = G5_TIME_YMDHIS;
    $member_id = isset($g5['member']['mb_id']) ? $g5['member']['mb_id'] : 'system';

    $found_customer = null;

    // 1. Search by HP
    if ($safe_hp && strlen($safe_hp) >= 9) {
        $sql = " SELECT * FROM g5_customer WHERE replace(customer_hp, '-', '') = '$safe_hp' ";
        $found_customer = sql_fetch($sql);
    }

    // 2. Search by Email (if not found by HP)
    if (!$found_customer && $safe_email) {
        $safe_email_esc = sql_real_escape_string($safe_email);
        $sql = " SELECT * FROM g5_customer WHERE customer_email = '$safe_email_esc' ";
        $found_customer = sql_fetch($sql);
    }

    // 3. Update Existing Customer
    if ($found_customer) {
        $cust_id = $found_customer['customer_id'];
        $db_company = $found_customer['customer_company'];
        $db_name = $found_customer['customer_name'];
        $memo_append = "";

        // Logic: Company Update (Logged)
        // Update if DB is empty OR (User provided new value AND it's different)
        // User requested: "Existing value -> customer_memo history"
        if (!empty($safe_company) && $safe_company != $db_company) {
            $old_val = $db_company ? $db_company : '(없음)';
            $memo_append .= "\\n[" . date('Y-m-d') . "] 상호변경: $old_val -> $safe_company";

            // Perform Update
            sql_query(" UPDATE g5_customer SET customer_company = '$safe_company', updated_at = '$now' WHERE customer_id = '$cust_id' ");
        }

        // Logic: Name Mismatch (Logged only, DO NOT OVERWRITE NAME)
        if (!empty($safe_name) && $safe_name != $db_name) {
            $memo_append .= "\\n[" . date('Y-m-d') . "] 이름불일치(저장안함): $db_name (기존) vs $safe_name (입력)";
        }

        // Append Memo if needed
        if ($memo_append) {
            $safe_memo = sql_real_escape_string($memo_append);
            sql_query(" UPDATE g5_customer SET customer_memo = CONCAT(customer_memo, '$safe_memo') WHERE customer_id = '$cust_id' ");
        }

        return (int) $cust_id;
    }

    // 4. Create New Customer
    if (empty($safe_name))
        $safe_name = '고객_' . date('ymd');

    $sql_ins = " INSERT INTO g5_customer SET
                    customer_name = '$safe_name',
                    customer_hp = '$hp',
                    customer_email = '$safe_email',
                    customer_addr = '$safe_addr',
                    customer_company = '$safe_company',
                    customer_tags = '',
                    customer_memo = '',
                    created_at = '$now',
                    updated_at = '$now' ";

    $result = sql_query($sql_ins, false);
    if ($result) {
        $cust_id = sql_insert_id();

        // Init Status (Optional)
        // sql_query(" INSERT INTO g5_customer_status SET customer_id = '$cust_id', status_step = '실측중', updated_at = '$now' ", false);

        return (int) $cust_id;
    }

    return 0; // Fail safe
}

// Ensure strict file end

/**
 * Sync Quote Data to Work Table
 * 견적서 저장 시 업무 테이블(g5_work) 동기화
 */
function save_work_from_quote($qa_id)
{
    error_log("[save_work_from_quote] FUNCTION CALLED - qa_id: $qa_id");

    $qa = sql_fetch(" SELECT * FROM g5_quote WHERE qa_id = '$qa_id' ");
    if (!$qa) {
        error_log("[save_work_from_quote] ERROR - Quote not found for qa_id: $qa_id");
        return;
    }

    $subject = addslashes($qa['qa_subject']);
    $status = $qa['qa_status'];
    $customer_id = (int) $qa['qa_customer_id'];
    $now = G5_TIME_YMDHIS;

    error_log("[save_work_from_quote] Quote data - subject: $subject, status: $status, customer_id: $customer_id");

    // Check existing work linked to this quote
    $work = sql_fetch(" SELECT work_id FROM g5_work WHERE qa_id = '$qa_id' ");

    if ($work && $work['work_id']) {
        error_log("[save_work_from_quote] Found existing work_id: {$work['work_id']} - Performing UPDATE");
        $sql = " UPDATE g5_work SET
                    work_subject = '$subject',
                    work_status = '$status',
                    customer_id = '$customer_id',
                    updated_at = '$now'
                 WHERE work_id = '{$work['work_id']}' ";
        $result = sql_query($sql, false);
        error_log("[save_work_from_quote] UPDATE result: " . ($result ? 'SUCCESS' : 'FAILED'));
    } else {
        error_log("[save_work_from_quote] No existing work found - Performing INSERT");
        $sql = " INSERT INTO g5_work SET
                    work_subject = '$subject',
                    work_status = '$status',
                    qa_id = '$qa_id',
                    customer_id = '$customer_id',
                    created_at = '$now',
                    updated_at = '$now' ";
        $result = sql_query($sql, false);
        $new_id = sql_insert_id();
        error_log("[save_work_from_quote] INSERT result: " . ($result ? "SUCCESS (new work_id: $new_id)" : 'FAILED'));
    }

    error_log("[save_work_from_quote] FUNCTION COMPLETED - qa_id: $qa_id");
}

/**
 * Sync Customer Data to Work Table (Customer Only Case)
 * 고객 정보 저장 시 업무 테이블(g5_work) 동기화
 * (견적서가 연결되지 않은 순수 고객 등록 건만 대상)
 */
function save_work_from_customer($customer_id)
{
    $cust = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$customer_id' ");
    if (!$cust)
        return;

    // Check if there is an existing "Customer Only" work for this customer
    $work = sql_fetch(" SELECT work_id FROM g5_work WHERE customer_id = '$customer_id' AND qa_id = 0 ");

    $subject = addslashes($cust['customer_name']) . " (고객등록)";
    $now = G5_TIME_YMDHIS;
    $status = '고객등록';

    if ($work) {
        $sql = " UPDATE g5_work SET
                    work_subject = '$subject',
                    updated_at = '$now'
                 WHERE work_id = '{$work['work_id']}' ";
        sql_query($sql);
    } else {
        // Only insert if no other work exists? 
        // Or should we assume every "Customer Registration" intended to be a work?
        // User request: "3) Customer Info Registration Only ... should appear in list".
        // So yes, create it.
        $sql = " INSERT INTO g5_work SET
                    work_subject = '$subject',
                    work_status = '$status',
                    qa_id = 0,
                    customer_id = '$customer_id',
                    created_at = '$now',
                    updated_at = '$now' ";
        sql_query($sql);
    }
}
?>
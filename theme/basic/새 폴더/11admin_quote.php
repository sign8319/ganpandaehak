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
        alert('올바른 방법으로 이용해 주십시오.', './admin_quote.php');
    }
    return true;
}

// Init Variables to prevent Warnings
$f_month = isset($_REQUEST['f_month']) ? $_REQUEST['f_month'] : '';
$f_year = isset($_REQUEST['f_year']) ? $_REQUEST['f_year'] : date('Y');
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';

// Helper: Compress Image
function compress_quote_image($source, $destination, $quality, $max_width = 1000)
{
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source);
    elseif ($info['mime'] == 'image/webp')
        $image = imagecreatefromwebp($source);
    else
        return false;

    // Resize
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $image_p = imagecreatetruecolor($new_width, $new_height);

        // Transparency for PNG/WEBP
        if ($info['mime'] == 'image/png' || $info['mime'] == 'image/webp') {
            imagealphablending($image_p, false);
            imagesavealpha($image_p, true);
        }

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        $image = $image_p;
    }

    // Save
    if ($info['mime'] == 'image/jpeg')
        imagejpeg($image, $destination, $quality);
    elseif ($info['mime'] == 'image/gif')
        imagegif($image, $destination);
    elseif ($info['mime'] == 'image/png')
        imagepng($image, $destination, 8); // PNG uses 0-9 compression level
    elseif ($info['mime'] == 'image/webp')
        imagewebp($image, $destination, $quality);

    return true;
}

// 1. Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// 2. DB Initialization (Execute once if table missing)
if (!sql_query(" DESCRIBE g5_quote ", false)) {
    $sql_quote = "
        CREATE TABLE IF NOT EXISTS `g5_quote` (
          `qa_id` int(11) NOT NULL AUTO_INCREMENT,
          `qa_code` varchar(30) NOT NULL DEFAULT '',
          `qa_status` varchar(20) NOT NULL DEFAULT '작성중',
          `qa_subject` varchar(255) NOT NULL DEFAULT '',
          `qa_client_name` varchar(50) NOT NULL DEFAULT '',
          `qa_client_contact` varchar(50) NOT NULL DEFAULT '',
          `qa_client_email` varchar(100) NOT NULL DEFAULT '',
          `qa_client_addr` varchar(255) NOT NULL DEFAULT '',
          `qa_related_title` varchar(255) NOT NULL DEFAULT '',
          `qa_related_url` varchar(255) NOT NULL DEFAULT '',
          `qa_memo` text,
          `qa_price_supply` int(11) NOT NULL DEFAULT 0,
          `qa_price_vat` int(11) NOT NULL DEFAULT 0,
          `qa_price_total` int(11) NOT NULL DEFAULT 0,
          `qa_datetime` datetime NOT NULL,
          `qa_send_datetime` datetime DEFAULT NULL,
          PRIMARY KEY (`qa_id`),
          UNIQUE KEY `qa_code` (`qa_code`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_quote, true);

    $sql_item = "
        CREATE TABLE IF NOT EXISTS `g5_quote_item` (
          `qi_id` int(11) NOT NULL AUTO_INCREMENT,
          `qa_id` int(11) NOT NULL DEFAULT 0,
          `qi_index` int(11) NOT NULL DEFAULT 0,
          `qi_item` varchar(255) NOT NULL DEFAULT '',
          `qi_spec` varchar(255) NOT NULL DEFAULT '',
          `qi_qty` int(11) NOT NULL DEFAULT 0,
          `qi_price` int(11) NOT NULL DEFAULT 0,
          `qi_amount` int(11) NOT NULL DEFAULT 0,
          `qi_note` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`qi_id`),
          KEY `qa_id` (`qa_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_item, true);

    $sql_log = "
        CREATE TABLE IF NOT EXISTS `g5_quote_log` (
          `ql_id` int(11) NOT NULL AUTO_INCREMENT,
          `qa_id` int(11) NOT NULL DEFAULT 0,
          `ql_email` varchar(100) NOT NULL DEFAULT '',
          `ql_datetime` datetime NOT NULL,
          `ql_result` varchar(20) NOT NULL DEFAULT '',
          PRIMARY KEY (`ql_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_log, true);
}

// Add Index if not exists (Manual check or just try adding with ignore if supported, keeping it simple)
// MySQL doesn't support 'IF NOT EXISTS' for indexes easily in all versions without procedure. 
// We will skip explicit index check for now to avoid errors, or just assume small data. 
// Actually, let's just create it if the table was just created? No, table might exist.
// Let's run a safe check.
$row = sql_fetch(" SHOW INDEX FROM g5_quote WHERE Key_name = 'idx_qa_datetime' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD INDEX idx_qa_datetime (qa_datetime) ", false);
}

// Upgrade Schema: Deposit
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_deposit' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_deposit` int(11) NOT NULL DEFAULT 0 AFTER `qa_price_total` ", false);
}

// Upgrade Schema: Item Description & Images
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote_item LIKE 'qi_desc' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote_item ADD `qi_desc` text NOT NULL AFTER `qi_spec` ", false);
    sql_query(" ALTER TABLE g5_quote_item ADD `qi_img1` varchar(255) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE g5_quote_item ADD `qi_img2` varchar(255) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE g5_quote_item ADD `qi_img3` varchar(255) NOT NULL DEFAULT '' ", false);
}

// Upgrade Schema: Detail Address & Client Memo (Phase 5)
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_client_addr2' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_client_addr2` varchar(255) NOT NULL DEFAULT '' AFTER `qa_client_addr` ", false);
    sql_query(" ALTER TABLE g5_quote ADD `qa_memo_user` text NOT NULL AFTER `qa_memo` ", false);
}

// Upgrade Schema: Client HP
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_client_hp' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `qa_client_hp` varchar(50) NOT NULL DEFAULT '' AFTER `qa_client_name` ", false);
}


// -----------------------------------------------------------------------------
// Controller Logic
// -----------------------------------------------------------------------------
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';

// AJAX List Loading
if ($w == 'ajax_list') {
    $sql_search = " where 1 ";
    
    // 1. Search Query
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q) {
        // Safe escaping (assuming sql_real_escape_string is available via G5 or mysqli)
        // If strict G5, use sql_escape_string or similar. We'll use a basic clean here.
        $q_safe = addslashes($q); 
        $sql_search .= " and (qa_client_name like '%$q_safe%' or qa_code like '%$q_safe%') ";
    }
    
    // 2. Year Filter
    $f_year = isset($_GET['f_year']) ? trim($_GET['f_year']) : '';
    if ($f_year) {
        $f_year_safe = (int)$f_year;
        $sql_search .= " and year(qa_datetime) = '$f_year_safe' ";
    }
    
    // 3. Month Filter (01, 02...)
    $f_month = isset($_GET['f_month']) ? trim($_GET['f_month']) : '';
    if ($f_month && $f_month !== 'all') { // 'all' or empty string means all months
        $f_month_safe = str_pad((int)$f_month, 2, '0', STR_PAD_LEFT);
        $sql_search .= " and month(qa_datetime) = '$f_month_safe' ";
    }
    
    // 4. Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $page_rows = 30;
    
    // Count Total
    $sql_count = " select count(*) as cnt from g5_quote $sql_search ";
    $row_count = sql_fetch($sql_count);
    $total_count = $row_count['cnt'];
    $total_page  = ceil($total_count / $page_rows);
    
    $from_record = ($page - 1) * $page_rows;
    
    // Fetch Data
    $sql = " select * from g5_quote $sql_search order by qa_id desc limit $from_record, $page_rows ";
    $result = sql_query($sql);
    
    ob_start();
    $list_count = 0;
    while ($row = sql_fetch_array($result)) {
        $list_count++;
        $status_colors = ['작성중' => 'gray', '발송완료' => 'green', '취소' => 'red'];
        $color = isset($status_colors[$row['qa_status']]) ? $status_colors[$row['qa_status']] : 'gray';
        ?>
        <tr class="hover:bg-orange-50 transition cursor-pointer" onclick="if(!event.target.closest('input') && !event.target.closest('a') && !event.target.closest('button')) location.href='?w=form&qa_id=<?php echo $row['qa_id']; ?>'">
            <td class="p-4 text-center">
                <input type="checkbox" name="chk_qa_id[]" value="<?php echo $row['qa_id']; ?>"
                    id="chk_qa_id_<?php echo $row['qa_id']; ?>" class="chk_box">
            </td>
            <td class="p-4 text-center">
                <div class="font-bold text-gray-800">
                    <?php echo date('m.d', strtotime($row['qa_datetime'])); ?>
                </div>
                <div class="text-xs text-gray-400 font-mono mt-0.5"><?php echo $row['qa_code']; ?></div>
            </td>
            <td class="p-4 text-center">
                <span
                    class="inline-block px-2 py-1 rounded text-xs font-bold bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700">
                    <?php echo $row['qa_status']; ?>
                </span>
            </td>
            <td class="p-4 font-bold"><?php echo $row['qa_client_name']; ?>
                <div class="text-xs text-gray-500 font-normal"><?php echo $row['qa_client_hp']; ?></div>
            </td>
            <td class="p-4 text-gray-600"><?php echo $row['qa_subject']; ?></td>
            <td class="p-4 text-right font-bold text-orange-600">
                <?php echo number_format($row['qa_price_total']); ?>
            </td>
            <td class="p-4 text-center">
                <div class="flex gap-2 justify-center">
                    <a href="?w=form&qa_id=<?php echo $row['qa_id']; ?>"
                        class="text-blue-600 hover:text-blue-800 font-bold text-sm">수정</a>
                </div>
            </td>
            <td class="p-4 text-center">
                <button type="button" onclick="open_preview_modal(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                    class="bg-white border hover:bg-gray-50 text-gray-700 px-3 py-1 rounded text-xs shadow-sm flex items-center gap-1 mx-auto transition">
                    <span>👁️</span> 보기
                </button>
            </td>
        </tr>
    <?php }
    
    if ($list_count == 0)
        echo '<tr><td colspan="8" class="p-8 text-center text-gray-500">데이터가 없습니다.</td></tr>';
        
    $html_content = ob_get_clean();
    
    // Pagination HTML
    $paging_html = '';
    if ($total_page > 1) {
        $paging_html .= '<div class="flex justify-center gap-1">';
        
        $start_page = ( ( (int)( ($page - 1 ) / 10 ) ) * 10 ) + 1;
        $end_page = $start_page + 10 - 1;
        if ($end_page >= $total_page) $end_page = $total_page;
        
        if ($start_page > 1) {
            $prev_page = $start_page - 1;
            $paging_html .= '<button type="button" onclick="load_list(\''.$f_month.'\', '.$prev_page.')" class="px-3 py-1 border rounded hover:bg-gray-100 text-sm">이전</button>';
        }
        
        for ($k=$start_page; $k<=$end_page; $k++) {
            $active_cls = ($page == $k) ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
            $paging_html .= '<button type="button" onclick="load_list(\''.$f_month.'\', '.$k.')" class="px-3 py-1 border rounded text-sm font-bold '.$active_cls.'">'.$k.'</button>';
        }
        
        if ($total_page > $end_page) {
            $next_page = $end_page + 1;
            $paging_html .= '<button type="button" onclick="load_list(\''.$f_month.'\', '.$next_page.')" class="px-3 py-1 border rounded hover:bg-gray-100 text-sm">다음</button>';
        }
        
        $paging_html .= '</div>';
    }

    // JSON Response
    header('Content-Type: application/json');
    echo json_encode([
        'html' => $html_content,
        'paging' => $paging_html,
        'page' => $page,
        'total' => $total_count
    ]);
    
    exit;
}

// -----------------------------------------------------------------------------
// Default List View Logic (Initialize Variables)
// -----------------------------------------------------------------------------
if ($w == '') {
    $f_year = isset($_GET['f_year']) ? $_GET['f_year'] : date('Y');
    $f_month = isset($_GET['f_month']) ? $_GET['f_month'] : '';
    $q = isset($_GET['q']) ? $_GET['q'] : '';

    $sql_search = " where 1 ";
    if ($q)
        $sql_search .= " and (qa_client_name like '%$q%' or qa_code like '%$q%') ";
    if ($f_year)
        $sql_search .= " and year(qa_datetime) = '$f_year' ";
    if ($f_month)
        $sql_search .= " and month(qa_datetime) = '$f_month' ";

    $sql = " select * from g5_quote $sql_search order by qa_id desc ";
    $result = sql_query($sql);
}

$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;

// Update Business Info (Config)
if ($w == 'config') {
    check_quote_token();
    // Just saving to g5_config or separate table? 
    // For simplicity, we'll store in g5_config['cf_1'] ~ ['cf_10'] if available, or just file.
    // Actually, a separate settings file or table is cleaner. 
    // Let's use a simple JSON file for Business Config to avoid altering g5_config schema heavily.
    $biz_config = [
        'biz_no' => $_POST['biz_no'],
        'biz_name' => $_POST['biz_name'],
        'biz_ceo' => $_POST['biz_ceo'],
        'biz_addr' => $_POST['biz_addr'],
        'biz_type' => $_POST['biz_type'],
        'biz_class' => $_POST['biz_class'],
        'biz_tel' => $_POST['biz_tel'],
        'biz_email' => $_POST['biz_email'],
    ];
    file_put_contents(G5_DATA_PATH . '/quote_config.json', json_encode($biz_config));
    alert('사업자 정보가 저장되었습니다.', './admin_quote.php');
}

// Load Business Info
$biz_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];


// Save Quote
if ($w == 'u' || $w == 'c') {
    check_quote_token(); // CSRF Check if available, or custom check

    $qa_subject = $_POST['qa_subject'];
    $qa_client_name = $_POST['qa_client_name'];
    $qa_client_email = $_POST['qa_client_email'];
    $qa_deposit = (int) $_POST['qa_deposit'];
    $qa_client_hp = $_POST['qa_client_hp'];
    $qa_client_contact = $_POST['qa_client_contact'];
    $qa_client_addr = $_POST['qa_client_addr'];
    $qa_client_addr2 = $_POST['qa_client_addr2'];
    $qa_memo = $_POST['qa_memo']; // Internal Memo
    $qa_memo_user = $_POST['qa_memo_user']; // Customer Memo
    $qa_code = $_POST['qa_code'];

    // Items
    $qi_item = $_POST['qi_item'];
    $qi_desc = $_POST['qi_desc'];
    $qi_spec = $_POST['qi_spec'];
    $qi_qty = $_POST['qi_qty'];
    $qi_price = $_POST['qi_price'];
    $qi_note = $_POST['qi_note'];

    $total_supply = 0;
    $items_data = [];

    // Image Upload Dir
    $upload_dir = G5_DATA_PATH . '/quote';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, G5_DIR_PERMISSION);
        @chmod($upload_dir, G5_DIR_PERMISSION);
    }

    for ($i = 0; $i < count($qi_item); $i++) {
        if (!trim($qi_item[$i]))
            continue;
        $qty = (int) $qi_qty[$i];
        $price = (int) $qi_price[$i];
        $amount = $qty * $price;
        $total_supply += $amount;

        // Image Handling
        $img_files = [];
        for ($m = 1; $m <= 3; $m++) {
            $f_name = "qi_img$m";
            $del_name = "qi_img{$m}_del";
            $prev_name = "qi_img{$m}_prev";

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
                    $new_name = date('YmdHis') . "_" . $i . "_" . $m . "_" . rand(1000, 9999) . "." . $ext;
                    if (move_uploaded_file($_FILES[$f_name]['tmp_name'][$i], $upload_dir . '/' . $new_name)) {
                        // Compress Image (Quality 50, Max Width 800)
                        compress_quote_image($upload_dir . '/' . $new_name, $upload_dir . '/' . $new_name, 50, 800);

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

        $items_data[] = [
            'item' => $qi_item[$i],
            'desc' => $qi_desc[$i],
            'spec' => $qi_spec[$i],
            'qty' => $qty,
            'price' => $price,
            'amount' => $amount,
            'note' => $qi_note[$i],
            'img1' => $img_files[1],
            'img2' => $img_files[2],
            'img3' => $img_files[3]
        ];
    }

    $qa_price_vat = floor($total_supply * 0.1);
    $qa_price_total = $total_supply + $qa_price_vat;

    $sql_common = " qa_subject = '{$qa_subject}',
                    qa_client_name = '{$qa_client_name}',
                    qa_client_contact = '{$qa_client_contact}',
                    qa_client_hp = '{$qa_client_hp}',
                    qa_client_email = '{$qa_client_email}',
                    qa_client_addr = '{$qa_client_addr}',
                    qa_client_addr2 = '{$qa_client_addr2}',
                    qa_memo = '{$qa_memo}',
                    qa_memo_user = '{$qa_memo_user}',
                    qa_price_supply = '{$total_supply}',
                    qa_price_vat = '{$qa_price_vat}',
                    qa_price_total = '{$qa_price_total}',
                    qa_deposit = '{$qa_deposit}' ";

    if ($w == 'c' || !$qa_id) {
        $today_prefix = 'Q-' . date('Ymd') . '-';
        $row = sql_fetch(" select count(*) as cnt from g5_quote where qa_code like '{$today_prefix}%' ");
        $seq = $row['cnt'] + 1;
        $new_code = $today_prefix . sprintf('%03d', $seq);

        $sql = " insert into g5_quote set qa_code = '{$new_code}', qa_datetime = '" . G5_TIME_YMDHIS . "', {$sql_common} ";
        sql_query($sql);
        $qa_id = sql_insert_id();
    } else {
        $sql = " update g5_quote set {$sql_common} where qa_id = '{$qa_id}' ";
        sql_query($sql);
        // Important: When deleting items, we should also delete files if we are cleaning up deeply. 
        // But 'delete from g5_quote_item' doesn't auto-delete files. 
        // For now, we assume standard flow replaces them or they are orphaned (need cron cleanup).
        // Since we re-insert immediately, we kept files in $items_data.
        sql_query(" delete from g5_quote_item where qa_id = '{$qa_id}' ");
    }

    foreach ($items_data as $k => $v) {
        $sql = " insert into g5_quote_item
                    set qa_id = '{$qa_id}',
                        qi_index = '{$k}',
                        qi_item = '{$v['item']}',
                        qi_spec = '{$v['spec']}',
                        qi_desc = '{$v['desc']}',
                        qi_qty = '{$v['qty']}',
                        qi_price = '{$v['price']}',
                        qi_amount = '{$v['amount']}',
                        qi_note = '{$v['note']}',
                        qi_img1 = '{$v['img1']}',
                        qi_img2 = '{$v['img2']}',
                        qi_img3 = '{$v['img3']}' ";
        sql_query($sql);
    }

    goto_url("./admin_quote.php?w=form&qa_id=".$qa_id."&saved=1");
}

// Delete Quote
if ($w == 'd') {
    check_quote_token();
    if ($qa_id) {
        sql_query(" delete from g5_quote where qa_id = '$qa_id' ");
        sql_query(" delete from g5_quote_item where qa_id = '$qa_id' ");
        sql_query(" delete from g5_quote_log where qa_id = '$qa_id' ");
    }
    goto_url("./admin_quote.php");
}

// Bulk Delete
if ($w == 'multi_d') {
    check_quote_token();
    $chk_qa_ids = $_POST['chk_qa_id'];
    if (is_array($chk_qa_ids)) {
        foreach ($chk_qa_ids as $del_id) {
            $del_id = (int) $del_id;
            if ($del_id) {
                sql_query(" delete from g5_quote where qa_id = '$del_id' ");
                sql_query(" delete from g5_quote_item where qa_id = '$del_id' ");
                sql_query(" delete from g5_quote_log where qa_id = '$del_id' ");
            }
        }
    }
    goto_url("./admin_quote.php");
}

// Send Mail
if ($w == 'send_mail' && $qa_id) {
    check_quote_token();

    $quote = sql_fetch(" select * from g5_quote where qa_id = '$qa_id' ");
    if (!$quote)
        alert('견적서가 존재하지 않습니다.');

    $res_items = sql_query(" select * from g5_quote_item where qa_id = '$qa_id' order by qi_index asc, qi_id asc ");
    $items = [];
    while ($row = sql_fetch_array($res_items))
        $items[] = $row;

    $subject = "[간판대학] {$quote['qa_client_name']}님 견적서입니다.";

    // Config
    $biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];

    ob_start();
    ?>
    <div style="background:#f3f4f6; padding:40px 0;">
        <div
            style="max-width:800px; margin:0 auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <!-- Header -->
            <div style="background:#ea580c; padding:30px; color:#fff;">
                <h1 style="margin:0; font-size:24px; font-weight:bold;">견적서 Quote</h1>
                <p style="margin:5px 0 0; opacity:0.9;">No. <?php echo $quote['qa_code']; ?></p>
            </div>

            <div style="padding:40px;">
                <!-- Info Grid -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:40px;">
                    <tr>
                        <td style="width:50%; vertical-align:top; padding-right:20px;">
                            <h3
                                style="color:#4b5563; font-size:14px; border-bottom:2px solid #ea580c; padding-bottom:10px; margin-bottom:15px;">
                                수신 (Customer)</h3>
                            <p style="margin:5px 0;"><strong><?php echo $quote['qa_client_name']; ?> 귀하</strong></p>
                            <?php if ($quote['qa_client_email'])
                                echo "<p style='margin:5px 0; color:#666;'>{$quote['qa_client_email']}</p>"; ?>
                            <?php if ($quote['qa_client_contact'])
                                echo "<p style='margin:5px 0; color:#666;'>{$quote['qa_client_contact']}</p>"; ?>
                            <?php if ($quote['qa_client_addr'])
                                echo "<p style='margin:5px 0; color:#666;'>{$quote['qa_client_addr']}</p>"; ?>
                        </td>
                        <td style="width:50%; vertical-align:top;">
                            <h3
                                style="color:#4b5563; font-size:14px; border-bottom:2px solid #374151; padding-bottom:10px; margin-bottom:15px;">
                                발신 (Supplier)</h3>
                            <p style="margin:5px 0;"><strong><?php echo $biz_info['biz_name'] ?? '간판대학'; ?></strong></p>
                            <p style="margin:5px 0; color:#666;'>대표: <?php echo $biz_info['biz_ceo'] ?? ''; ?></p>
                            <p style=" margin:5px 0; color:#666;'>Tel: <?php echo $biz_info['biz_tel'] ?? ''; ?></p>
                            <p style="margin:5px 0; color:#666;'>Email: <?php echo $biz_info['biz_email'] ?? ''; ?></p>
                            <p style=" margin:5px 0; color:#666; font-size:12px;">
                                <?php echo $biz_info['biz_addr'] ?? ''; ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:30px;">
                    <thead style="background:#f9fafb;">
                        <tr>
                            <th style="padding:12px; text-align:left; border-bottom:1px solid #e5e7eb; color:#374151;">
                                품목
                            </th>
                            <th style="padding:12px; text-align:center; border-bottom:1px solid #e5e7eb; color:#374151;">
                                규격
                            </th>
                            <th style="padding:12px; text-align:right; border-bottom:1px solid #e5e7eb; color:#374151;">
                                수량
                            </th>
                            <th style="padding:12px; text-align:right; border-bottom:1px solid #e5e7eb; color:#374151;">
                                단가
                            </th>
                            <th style="padding:12px; text-align:right; border-bottom:1px solid #e5e7eb; color:#374151;">
                                금액
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="padding:12px; border-bottom:1px solid #f3f4f6; color:#4b5563;">
                                    <?php echo $item['qi_item']; ?><br><span
                                        style="font-size:11px; color:#9ca3af;"><?php echo $item['qi_note']; ?></span>
                                </td>
                                <td
                                    style="padding:12px; text-align:center; border-bottom:1px solid #f3f4f6; color:#6b7280; font-size:13px;">
                                    <?php echo $item['qi_spec']; ?>
                                </td>
                                <td style="padding:12px; text-align:right; border-bottom:1px solid #f3f4f6; color:#111827;">
                                    <?php echo number_format($item['qi_qty']); ?>
                                </td>
                                <td
                                    style="padding:12px; text-align:right; border-bottom:1px solid #f3f4f6; color:#6b7280; font-size:13px;">
                                    <?php echo number_format($item['qi_price']); ?>
                                </td>
                                <td
                                    style="padding:12px; text-align:right; border-bottom:1px solid #f3f4f6; color:#111827; font-weight:bold;">
                                    <?php echo number_format($item['qi_amount']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Totals -->
                <div style="text-align:right;">
                    <p style="margin:5px 0; color:#6b7280;">공급가액 <span
                            style="display:inline-block; width:120px; color:#111827; font-weight:bold;"><?php echo number_format($quote['qa_price_supply']); ?>원</span>
                    </p>
                    <p style="margin:5px 0; color:#6b7280;">부가세(10%) <span
                            style="display:inline-block; width:120px; color:#111827; font-weight:bold;"><?php echo number_format($quote['qa_price_vat']); ?>원</span>
                    </p>
                    <div
                        style="margin-top:15px; padding-top:15px; border-top:2px solid #e5e7eb; font-size:20px; font-weight:bold; color:#ea580c;">
                        최종 합계 <span
                            style="display:inline-block; width:150px;"><?php echo number_format($quote['qa_price_total']); ?>원</span>
                    </div>
                </div>

                <div
                    style="margin-top:50px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb; padding-top:20px;">
                    본 견적서는 <?php echo date('Y년 m월 d일'); ?>에 발행되었습니다.<br>
                    문의사항은 <?php echo $biz_info['biz_email'] ?? 'master@ganpandaehak.com'; ?>으로 연락주세요.
                </div>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();

    include_once(G5_LIB_PATH . '/mailer.lib.php');
    $result = mailer($biz_info['biz_name'] ?? '간판대학', $biz_info['biz_email'] ?? 'master@ganpandaehak.com', $quote['qa_client_email'], $subject, $content, 1);

    // Log & Update Status
    $qa_status = $result ? '발송완료' : '작성중'; // Only update status on success? Or keep as is? User wants "Sent" status.
    if ($result) {
        sql_query(" update g5_quote set qa_status = '발송완료', qa_send_datetime = '" . G5_TIME_YMDHIS . "' where qa_id = '$qa_id' ");
    }

    // Log
    $ql_result = $result ? '성공' : '실패';
    sql_query(" insert into g5_quote_log set qa_id = '$qa_id', ql_email = '{$quote['qa_client_email']}', ql_datetime = '" . G5_TIME_YMDHIS . "', ql_result = '$ql_result' ");

    alert('메일이 발송되었습니다. (결과: ' . $ql_result . ')', "./admin_quote.php?w=form&qa_id=$qa_id");
}

// Generate Token for CSRF (Common for List/Form/Modal)
$token = get_quote_token();

include_once(G5_THEME_PATH . '/head.php');
?>
<div class="w-full px-6 py-8 min-h-screen bg-gray-50/50">
    <div class="max-w-[1600px] mx-auto">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6 border-b pb-4 border-gray-200">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">견적 관리 <span
                        class="text-orange-600 text-sm font-medium ml-2">Admin Quote System</span></h1>
                <p class="text-gray-500 text-xs mt-1">간판대학 통합 견적 관리</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('biz_config_modal').showModal()"
                    class="bg-white border text-gray-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 transition">
                    ⚙️ 설정
                </button>
                <a href="?w=form"
                    class="bg-gray-900 hover:bg-black text-white px-5 py-2 rounded-lg text-sm font-bold shadow transition flex items-center gap-2">
                    <span class="text-lg leading-none">+</span> 새 견적
                </a>
            </div>
        </div>

        <?php if ($w == ''): // -------------------- LIST VIEW -------------------- ?>
            <!-- Search & Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                <!-- Top Control Bar -->
                <form name="fsearch" id="fsearch" method="get"
                    class="p-4 border-b border-gray-100 flex flex-wrap gap-4 items-center justify-between"
                    onsubmit="return load_list();">
                    <input type="hidden" name="f_month" id="f_month" value="<?php echo $f_month; ?>">

                    <div class="flex gap-2 items-center">
                        <div class="relative">
                            <input type="text" name="q" id="q" value="<?php echo $q; ?>" placeholder="고객명, 견적번호 검색"
                                class="border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm w-64 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 block">
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        </div>
                        <button type="submit"
                            class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700">검색</button>
                    </div>

                    <div class="flex gap-2 px-2">
                        <button type="button" onclick="location.href='./admin_quote.php'"
                            class="text-xs text-gray-400 underline hover:text-red-500 self-center">필터 초기화</button>
                    </div>
                </form>

                <!-- Month Tabs with Year Select -->
                <div class="px-4 pt-4 bg-gray-50/50 flex flex-wrap gap-1 border-b border-gray-200 items-end"
                    id="month_tabs">
                    <div class="mr-4 mb-[2px]">
                        <select name="f_year" id="f_year"
                            class="border-none bg-transparent text-lg font-bold text-gray-700 focus:ring-0 cursor-pointer pr-8"
                            onchange="load_list();">
                            <?php
                            $curr_year = date('Y');
                            for ($y = $curr_year; $y >= $curr_year - 2; $y--) {
                                $selected = ($y == $f_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>{$y}년</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="button" onclick="load_list('')"
                        class="month-tab px-4 py-2 rounded-t-lg text-sm font-bold border border-b-0 border-gray-200 transition <?php echo $f_month == '' ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                        전체
                    </button>
                    <?php 
                    for ($i = 1; $i <= 12; $i++) {
                        $m_val = sprintf('%02d', $i);
                        $active = ($f_month == $m_val) ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50';
                        echo "<button type='button' onclick=\"load_list('$m_val')\" class='month-tab px-4 py-2 rounded-t-lg text-sm font-bold border border-b-0 border-gray-200 transition {$active}'>{$i}월</button>";
                    }
                    ?>
                </div>
            </div>

                <!-- List Table -->
                <form name="fquotelist" id="fquotelist" action="./admin_quote.php"
                    onsubmit="return fquotelist_submit(this);" method="post">
                    <input type="hidden" name="w" value="d_multi">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                                <tr>
                                    <th class="p-4 text-center"><input type="checkbox" onclick="all_checked(this.checked)"
                                            class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"></th>
                                    <th class="p-4">날짜 (견적번호)</th>
                                    <th class="p-4 text-center">상태</th>
                                    <th class="p-4">고객명 / 업체명</th>
                                    <th class="p-4">견적명</th>
                                    <th class="p-4 text-right">총 금액</th>
                                    <th class="p-4 text-center">관리 (Manage)</th>
                                    <th class="p-4 text-center">미리보기</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white" id="list_tbody">
                                <?php if (sql_num_rows($result) > 0) {
                                    while ($row = sql_fetch_array($result)) {
                                        ?>
                                        <tr class="hover:bg-gray-50 transition cursor-pointer"
                                            onclick="if(!event.target.closest('input') && !event.target.closest('a') && !event.target.closest('button')) location.href='?w=form&qa_id=<?php echo $row['qa_id']; ?>'">
                                            <td class="p-4 text-center"><input type="checkbox" name="chk_qa_id[]"
                                                    value="<?php echo $row['qa_id']; ?>"
                                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"></td>
                                            <td class="p-4">
                                                <div class="font-bold text-gray-800">
                                                    <?php echo date('m.d', strtotime($row['qa_datetime'])); ?>
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono mt-0.5"><?php echo $row['qa_code']; ?>
                                                </div>
                                            </td>
                                            <td class="p-4 text-center">
                                                <span
                                                    class="inline-block px-2 py-1 rounded text-xs font-bold bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700">
                                                    <?php echo $row['qa_status']; ?>
                                                </span>
                                            </td>
                                            <td class="p-4">
                                                <div class="font-bold text-gray-900"><?php echo $row['qa_client_name']; ?></div>
                                                <div class="text-xs text-gray-500"><?php echo $row['qa_client_hp']; ?></div>
                                            </td>
                                            <td class="p-4 max-w-xs truncate text-gray-600"
                                                title="<?php echo $row['qa_subject']; ?>">
                                                <?php echo $row['qa_subject'] ?: '<span class="text-gray-300">(제목없음)</span>'; ?>
                                            </td>
                                            <td class="p-4 text-right font-bold text-gray-800">
                                                <?php echo number_format($row['qa_price_total']); ?>원
                                                <?php if ($row['qa_deposit'] > 0)
                                                    echo '<div class="text-xs text-blue-500 mt-1">계약금: ' . number_format($row['qa_deposit']) . '</div>'; ?>
                                            </td>
                                            <td class="p-4 text-center">
                                                <div class="flex gap-2 justify-center">
                                                    <a href="?w=form&qa_id=<?php echo $row['qa_id']; ?>"
                                                        class="text-blue-600 hover:text-blue-800 font-bold text-sm bg-blue-50 px-2 py-1 rounded">수정</a>
                                                    <button type="button"
                                                        onclick="open_preview_modal(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                                                        class="bg-white border hover:bg-gray-50 text-gray-700 px-2 py-1 rounded text-sm shadow-sm flex items-center gap-1 transition">
                                                        <span>👁️</span> 보기
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="p-10 text-center text-gray-400">등록된 견적서가 없습니다.</td></tr>';
                                } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t bg-gray-50 flex justify-between items-center">
                        <div><!-- Pagination etc --></div>
                        <button type="submit"
                            class="bg-white border border-red-200 text-red-600 hover:bg-red-50 px-3 py-1.5 rounded text-xs font-bold shadow-sm transition">
                            선택 삭제
                        </button>
                    </div>
                </form>
            </div>


        <?php elseif ($w == 'form'): // -------------------- FORM VIEW -------------------- ?>
            <?php
            $quote = [
                'qa_id' => '',
                'qa_code' => '',
                'qa_subject' => '',
                'qa_client_name' => '',
                'qa_client_contact' => '',
                'qa_client_email' => '',
                'qa_client_addr' => '',
                'qa_client_addr2' => '',
                'qa_memo' => '',
                'qa_memo_user' => '',
                'qa_price_supply' => 0,
                'qa_price_vat' => 0,
                'qa_price_total' => 0,
                'qa_deposit' => 0
            ];
            $items = [];
            $form_action = 'c';

            if ($qa_id) {
                $quote = sql_fetch(" select * from g5_quote where qa_id = '$qa_id' ");
                $res_items = sql_query(" select * from g5_quote_item where qa_id = '$qa_id' order by qi_index asc, qi_id asc ");
                while ($row = sql_fetch_array($res_items))
                    $items[] = $row;
                $form_action = 'u';
            }

            // Ensure at least one item row
            if (empty($items))
                $items[] = ['qi_item' => '', 'qi_spec' => '', 'qi_qty' => 1, 'qi_price' => 0, 'qi_amount' => 0, 'qi_note' => '', 'qi_desc' => ''];
            ?>

                <form name="fquote" id="fquote" action="./admin_quote.php" method="post"
                    onsubmit="return fquote_submit(this);" enctype="multipart/form-data" class="w-full">
                    <input type="hidden" name="w" value="<?php echo $form_action; ?>">
                    <input type="hidden" name="qa_id" value="<?php echo $qa_id; ?>">
                    <input type="hidden" name="qa_code" value="<?php echo $quote['qa_code']; ?>">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    
            <div class="grid grid-cols-1 lg:grid-cols-10 gap-8 items-start">
                <!-- Left: Form Area (70%) -->
                <div class="lg:col-span-7 space-y-6">

                        <!-- Section 1: Basic Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <a href="./admin_quote.php" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                        <span>◀</span> 목록으로
                                    </a>
                                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        <span class="w-1 h-4 bg-orange-600 rounded-full"></span> 기본 정보
                                    </h2>
                                </div>
                                <?php if ($quote['qa_code'])
                                    echo '<span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-500">No. ' . $quote['qa_code'] . '</span>'; ?>
                            </div>

                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">견적명 (제목)</label>
                                    <input type="text" name="qa_subject" value="<?php echo $quote['qa_subject']; ?>"
                                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm font-bold"
                                        placeholder="예: 강남점 외부 간판 제작 및 시공">
                                </div>

                                <div class="col-span-12 lg:col-span-4">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">고객/업체명 <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="qa_client_name" value="<?php echo $quote['qa_client_name']; ?>"
                                        required
                                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                                </div>
                                <div class="col-span-12 lg:col-span-4">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">이메일 <span
                                            class="text-red-500">*</span></label>
                                    <input type="email" name="qa_client_email"
                                        value="<?php echo $quote['qa_client_email']; ?>" required
                                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                                </div>
                                <div class="col-span-12 lg:col-span-4">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">연락처(HP)</label>
                                    <input type="text" name="qa_client_hp"
                                        value="<?php echo $quote['qa_client_hp'] ?? ''; ?>"
                                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                                </div>
                                <div class="col-span-12 lg:col-span-4 hidden">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">기타 연락처</label>
                                    <input type="text" name="qa_client_contact"
                                        value="<?php echo $quote['qa_client_contact']; ?>"
                                        class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm">
                                </div>

                                <div class="col-span-12">
                                    <label class="block text-xs font-bold text-gray-600 mb-1">주소</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div class="flex gap-2">
                                            <input type="text" name="qa_client_addr" id="qa_client_addr"
                                                value="<?php echo $quote['qa_client_addr']; ?>"
                                                class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white text-sm"
                                                placeholder="주소 검색">
                                            <button type="button" onclick="execDaumPostcode()"
                                                class="bg-gray-700 text-white px-4 rounded-lg text-sm font-bold hover:bg-gray-800 whitespace-nowrap shadow-sm">검색</button>
                                        </div>
                                        <input type="text" name="qa_client_addr2"
                                            value="<?php echo $quote['qa_client_addr2']; ?>"
                                            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 text-sm"
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

                        <!-- Section 2: Items (Detailed Layout) -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-1 h-4 bg-orange-600 rounded-full"></span> 견적 내역 (Items)
                                </h2>
                                <span class="text-xs text-gray-400">품목 입력 후 Enter를 누르면 행이 추가됩니다.</span>
                            </div>

                            <div class="overflow-visible">
                                <!-- JS will render rows here. We need a solid ID target. -->
                                <table class="w-full text-left border-collapse" id="tbl_items_new">
                                    <thead
                                        class="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-200">
                                        <tr>
                                            <th class="py-3 pl-2 w-8"></th>
                                            <th class="py-3 px-2">품목명 (Item)</th>
                                            <th class="py-3 px-2 w-32">규격 (Spec)</th>
                                            <th class="py-3 px-2 w-20 text-right">수량</th>
                                            <th class="py-3 px-2 w-28 text-right">단가</th>
                                            <th class="py-3 px-2 w-32 text-right">금액</th>
                                            <th class="py-3 px-2 w-32">비고</th>
                                            <th class="py-3 px-2 w-10 text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item_list_container" class="divide-y divide-gray-100">
                                        <!-- Rows injected by JS -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 pt-4 border-t border-dashed flex justify-center">
                                <button type="button" onclick="add_item_row()"
                                    class="flex items-center gap-2 px-6 py-3 bg-orange-50 text-orange-700 hover:bg-orange-100 rounded-lg text-sm font-bold transition">
                                    <span class="text-lg">+</span> 품목 행 추가하기
                                </button>
                            </div>
                        </div>

                        <!-- Section 3: Memos -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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

                        <!-- Form Actions (Small save btn for convenience, mainly rely on right panel) -->
                        <div class="text-right lg:hidden">
                            <button type="submit"
                                class="bg-orange-600 text-white px-6 py-3 rounded-lg font-bold shadow-lg w-full">저장하기</button>
                        </div>
                </div>


                <!-- Right: Sticky Sidebar (30%) -->
                <div class="lg:col-span-3 space-y-6 sticky top-6 self-start">
                    <!-- Sidebar Content -->
                <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden sticky top-5">
                    
                    <!-- Title Header -->
                    <div class="bg-gray-50 border-b border-gray-200 p-4 text-center">
                         <h3 class="text-gray-800 font-bold text-lg">견적 요약</h3>
                    </div>

                    <!-- Content (Only Summary) -->
                    <div class="p-5">
                        <div class="space-y-6">
                            <!-- Totals -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-gray-600 text-sm">
                                    <span>공급가액</span>
                                    <span class="font-medium font-mono"><span id="txt_supply">0</span></span>
                                </div>
                                <div class="flex justify-between text-gray-600 text-sm">
                                    <span>부가세 (10%)</span>
                                    <span class="font-medium font-mono"><span id="txt_vat">0</span></span>
                                </div>
                                <div class="flex justify-between text-gray-900 text-xl font-extrabold border-t pt-3 items-end">
                                    <span>총 합계</span>
                                    <span class="text-orange-600 font-mono text-2xl"><span id="txt_total">0</span></span>
                                </div>
                            </div>

                            <!-- Deposit -->
                            <div class="bg-blue-50/50 rounded-lg p-4 border border-blue-100 space-y-3">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold text-blue-600">계약금 (선금)</label>
                                    <div class="relative w-32">
                                        <input type="text" name="qa_deposit_dummy" id="qa_deposit_dummy"
                                            class="w-full text-right p-1.5 text-sm border border-blue-200 rounded font-bold text-blue-700 focus:ring-1 focus:ring-blue-500"
                                            value="<?php echo number_format($quote['qa_deposit']); ?>"
                                            oninput="sync_deposit(this)" placeholder="0">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-red-600 font-bold border-t border-dashed border-blue-200 pt-2">
                                    <span class="text-sm">잔금 (후불)</span>
                                    <span class="text-base font-mono"><span id="txt_balance">0</span></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2 pt-2">
                                <button type="submit"
                                    class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3.5 px-4 rounded-lg shadow-md transition transform hover:-translate-y-0.5 mb-2 text-base flex justify-center items-center gap-2">
                                    <span>💾</span> 견적서 저장
                                </button>
                                
                                <button type="button" onclick="open_preview_modal_safe(<?php echo $qa_id ?: 0; ?>)"
                                    class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform hover:-translate-y-0.5 mb-2 text-sm flex justify-center items-center gap-2">
                                    <span>📷</span> 이미지 저장
                                </button>
                                
                                <button type="button" onclick="go_list_safe()"
                                    class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-4 rounded-lg shadow-sm transition mb-3 text-sm flex justify-center items-center gap-2">
                                    <span>◀</span> 목록으로
                                </button>
                                
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button"
                                        onclick="copy_share_link('<?php echo G5_THEME_URL . '/quote_view.php?qa_id=' . $qa_id; ?>')"
                                        class="col-span-1 bg-white border border-gray-200 hover:bg-green-50 text-gray-700 hover:text-green-700 font-bold py-2 rounded-lg shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                                        <span class="text-lg">🔗</span>
                                        <span>링크복사</span>
                                    </button>
                                    <button type="button"
                                        onclick="window.open('./quote_view.php?qa_id=<?php echo $qa_id; ?>', '_blank')"
                                        class="col-span-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold py-2 rounded-lg shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                                        <span class="text-lg">🖨️</span>
                                        <span>PDF/인쇄</span>
                                    </button>
                                    <button type="button" onclick="send_mail_confirm()"
                                        class="col-span-1 bg-white border border-gray-200 hover:bg-blue-50 text-gray-700 hover:text-blue-700 font-bold py-2 rounded-lg shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                                        <span class="text-lg">📧</span>
                                        <span>메일발송</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>

            </div>

            <script>
                // ---------------------------------------------------------
                // 1. Data Initialization
                // ---------------------------------------------------------
                var initial_items = <?php echo isset($items) ? json_encode($items) : '[]'; ?>;

                // ---------------------------------------------------------
                // 2. Item Table Logic (Expandable Rows)
                // ---------------------------------------------------------
                // ---------------------------------------------------------
                // Sidebar Tabs
                // ---------------------------------------------------------
                function switch_sidebar_tab(tab) {
                    if (tab === 'summary') {
                        document.getElementById('side_tab_summary').classList.remove('hidden');
                        document.getElementById('side_tab_preview').classList.add('hidden');
                        
                        document.getElementById('tab_btn_summary').className = "flex-1 py-3 text-sm font-bold text-orange-600 border-b-2 border-orange-600 bg-white transition";
                        document.getElementById('tab_btn_preview').className = "flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition";
                    } else {
                        document.getElementById('side_tab_summary').classList.add('hidden');
                        document.getElementById('side_tab_preview').classList.remove('hidden');
                        
                        document.getElementById('tab_btn_summary').className = "flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition";
                        document.getElementById('tab_btn_preview').className = "flex-1 py-3 text-sm font-bold text-orange-600 border-b-2 border-orange-600 bg-white transition";
                    }
                }

                // ---------------------------------------------------------
                // 2. Item Table Logic (Enhanced)
                // ---------------------------------------------------------
                function add_item_row(data = null, markDirty = true) {
                    if (markDirty) isDirty = true; // Mark as dirty only if requested
                    var container = document.getElementById('item_list_container');
                    var idx = container.children.length / 2; // Each item has 2 trs (main + detail)
                    
                    var item = data ? data.qi_item : '';
                    var spec = data ? data.qi_spec : '';
                    var qty = data ? data.qi_qty : 1;
                    var price = data ? data.qi_price : 0;
                    var amount = data ? data.qi_amount : 0;
                    var note = data ? data.qi_note : '';
                    var desc = data ? data.qi_desc : '';

                    // Image Handling
                    var img_html = '';
                    for (var i = 1; i <= 3; i++) {
                        var img_key = 'qi_img' + i;
                        var img_val = data ? (data[img_key] || '') : '';
                        var has_img = img_val !== '';
                        var img_prev = has_img ? `<input type="hidden" name="${img_key}_prev[]" value="${img_val}">` : '';
                        var img_preview = has_img ? `<img src="<?php echo G5_DATA_URL . '/quote/'; ?>${img_val}" class="w-full h-full object-cover rounded">` : `<span class="text-[10px] text-gray-300">IMG${i}</span>`;
                        var del_btn = has_img ? `<div class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-[10px] cursor-pointer shadow z-10 hover:bg-red-600" onclick="delete_curr_image(this, '${img_key}_del[]')">&times;</div>` : '';
                        var del_input = has_img ? `<input type="hidden" name="${img_key}_del[]" value="0" disabled>` : '';

                        img_html += `
                    <div class="relative group">
                        <label class="cursor-pointer block w-14 h-14 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center overflow-hidden transition relative focus:ring-2 focus:ring-orange-500 outline-none" 
                               onpaste="paste_image(event, this)" tabindex="0" title="클릭 후 Ctrl+V로 이미지 붙여넣기 가능">
                            ${img_preview}
                            <input type="file" name="${img_key}[]" class="hidden" onchange="preview_image(this)">
                            ${img_prev}
                        </label>
                        ${del_btn}
                        ${del_input}
                    </div>`;
                    }

                    // Auto-expand if details exist
                    var has_data = (desc !== '' || data && (data.qi_img1 || data.qi_img2 || data.qi_img3));
                    var detail_cls = has_data ? 'detail-row bg-gray-50 border-b border-gray-200 shadow-inner' : 'detail-row hidden bg-gray-50 border-b border-gray-200 shadow-inner';
                    var rotate_cls = has_data ? 'rotate-180' : '';

                    var html = `
                <!-- Main Row -->
                <tr class="main-row group bg-white hover:bg-gray-50 transition border-b border-gray-100 text-sm">
                    <td class="py-2 pl-2 text-center align-middle">
                        <button type="button" onclick="toggle_detail(this)" class="text-gray-400 hover:text-orange-500 transition transform hover:scale-110 p-1 rounded-full hover:bg-orange-50" title="이미지/상세설명 추가">
                            <svg class="w-4 h-4 transform transition-transform duration-200 ${rotate_cls}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </td>
                    <td class="py-2 px-2 align-top">
                        <input type="text" name="qi_item[]" value="${item}" class="w-full p-2 border border-gray-300 rounded font-bold text-gray-800 in-item focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="품목명" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-2 align-top">
                        <input type="text" name="qi_spec[]" value="${spec}" class="w-full p-2 border border-gray-300 rounded text-center focus:ring-2 focus:ring-orange-500" placeholder="예: 450×450(mm)" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-2 align-top">
                        <input type="number" name="qi_qty[]" value="${qty}" class="w-full p-2 border border-gray-300 rounded text-right font-bold text-gray-800 in-qty focus:ring-2 focus:ring-orange-500" oninput="calc_row(this)" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-2 align-top">
                         <input type="text" name="qi_price[]" value="${price}" class="w-full p-2 border border-gray-300 rounded text-right font-bold text-gray-800 in-price focus:ring-2 focus:ring-orange-500" oninput="calc_row(this)" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-2 align-top text-right">
                        <input type="text" class="w-full p-2 border-none bg-transparent text-right font-extrabold text-orange-600 in-amount" value="${parseInt(amount).toLocaleString()}" readonly tabindex="-1">
                        <input type="hidden" name="qi_amount[]" value="${amount}">
                    </td>
                    <td class="py-2 px-2 align-top">
                        <input type="text" name="qi_note[]" value="${note}" class="w-full p-2 border border-gray-300 rounded text-gray-600 in-note focus:ring-2 focus:ring-orange-500" placeholder="비고" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-2 text-center align-middle">
                        <button type="button" onclick="del_row(this)" class="text-gray-300 hover:text-red-500 transition px-2 font-bold text-lg">
                             &times;
                        </button>
                    </td>
                </tr>
                <!-- Hidden Detail Row (Image + Desc) -->
                <tr class="${detail_cls}">
                    <td colspan="8" class="p-3 pl-10 border-l-4 border-orange-200">
                        <div class="flex gap-4 items-start">
                             <!-- Images -->
                             <div class="flex-shrink-0 flex gap-2">
                                 ${img_html}
                             </div>
                             <!-- Description -->
                             <div class="flex-grow">
                                 <textarea name="qi_desc[]" class="w-full h-16 p-2 text-xs border border-gray-300 rounded resize-none focus:ring-2 focus:ring-orange-500 bg-white" placeholder="이 품목에 대한 상세 설명 (고객 견적서 우측에 표시됩니다)"></textarea>
                             </div>
                        </div>
                    </td>
                </tr>
                `;

                    container.insertAdjacentHTML('beforeend', html);
                    
                    // Set textarea value safely
                    if (desc) {
                         // Find the last added textarea
                         var textareas = container.querySelectorAll('textarea[name="qi_desc[]"]');
                         textareas[textareas.length - 1].value = desc;
                    }
                    
                    if (data) calc_total(false);
                    if (!data && container.lastElementChild) {
                        var inputs = container.querySelectorAll('.in-item');
                        inputs[inputs.length - 1].focus();
                    }
                }

                function toggle_detail(btn) {
                    var tr = btn.closest('tr');
                    var nextTr = tr.nextElementSibling;
                    if (nextTr && nextTr.classList.contains('detail-row')) {
                        nextTr.classList.toggle('hidden');
                        var icon = btn.querySelector('svg');
                        if (nextTr.classList.contains('hidden')) {
                            icon.classList.remove('rotate-180');
                        } else {
                            icon.classList.add('rotate-180');
                        }
                    }
                }

                function check_enter_add(e, el) {
                    // Only Trigger on Enter
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        var tr = el.closest('tr');
                        var container = document.getElementById('item_list_container');
                        
                        // If it's the last row, add new row
                        // Check if this tr is the one before the last detail row
                        // Actually, container has pairs of rows (Main, Detail).
                        // So checking if tr is the SECOND TO LAST block (Main) or Last Block?
                        
                        var mainRows = container.querySelectorAll('.main-row');
                        var lastRow = mainRows[mainRows.length - 1];
                        
                        if (tr === lastRow) {
                            add_item_row();
                        }
                    }
                }

                function del_row(btn) {
                    isDirty = true; // Mark as dirty
                    var tr = btn.closest('tr');
                    var detailTr = tr.nextElementSibling;
                    if (document.querySelectorAll('.main-row').length <= 1) {
                         // Clear values instead of delete if only 1 exists
                         tr.querySelectorAll('input:not([type=hidden])').forEach(i => i.value = '');
                         tr.querySelectorAll('input[type=number]').forEach(i => i.value = '1');
                         calc_row(tr.querySelector('.in-qty'));
                        return;
                    }
                    tr.remove();
                    if (detailTr) detailTr.remove();
                    calc_total();
                }

                function calc_row(el) {
                    var tr = el.closest('tr');
                    var qty = parseInt(tr.querySelector('.in-qty').value) || 0;
                    var price = parseInt(tr.querySelector('.in-price').value.replace(/,/g, '')) || 0; // Handle commas just in case, though input type text usually used for formatted. Wait, I used text input for price in previous step? 
                    // Wait, previous code used type="text" for price with manual formatting or type="number"?
                    // Previous code: <input type="text" name="qi_price[]" ... class="in-price">
                    // NOTE: If using type="text", we must handle comma removal.
                    // Let's assume user inputs raw numbers or we handle simple parse.
                    
                    var amt = qty * price;
                    tr.querySelector('.in-amount').value = amt.toLocaleString();
                    var hiddenAmt = tr.querySelector('input[name="qi_amount[]"]');
                    if (hiddenAmt) hiddenAmt.value = amt;
                    calc_total();
                }

                function calc_total(update_ui = true) {
                    var supply = 0;
                    document.querySelectorAll('.main-row').forEach(tr => {
                        var qty = parseInt(tr.querySelector('.in-qty').value) || 0;
                        var price = parseInt(tr.querySelector('.in-price').value.replace(/,/g, '')) || 0;
                        supply += (qty * price);
                    });
                    var vat = Math.floor(supply * 0.1);
                    var total = supply + vat;
                    
                    // Deposit
                    var depositInput = document.getElementById('qa_deposit_dummy');
                    var deposit = parseInt(depositInput.value.replace(/,/g, '')) || 0;

                    if (update_ui) {
                        document.getElementById('txt_supply').innerText = supply.toLocaleString();
                        document.getElementById('txt_vat').innerText = vat.toLocaleString();
                        document.getElementById('txt_total').innerText = total.toLocaleString();
                        document.getElementById('txt_balance').innerText = (total - deposit).toLocaleString();
                    }
                }

                function sync_deposit(el) {
                    var val = el.value.replace(/[^0-9]/g, '');
                    el.value = parseInt(val || 0).toLocaleString();
                    calc_total();
                }

                function preview_image(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var wrapper = input.parentElement;
                            var oldImg = wrapper.querySelector('img');
                            if (oldImg) oldImg.remove();
                            var span = wrapper.querySelector('span'); // span with text
                            if (span) span.style.display = 'none';

                            var img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'w-full h-full object-cover rounded';
                            wrapper.appendChild(img);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function delete_curr_image(btn, name) {
                    if (confirm('이미지를 삭제하시겠습니까?')) {
                        var container = btn.closest('.relative');
                        var delInput = container.querySelector('input[type="hidden"][disabled]');
                        if (delInput) {
                            delInput.disabled = false;
                            delInput.value = '1';
                        }
                        
                        var label = container.querySelector('label');
                        var img = label.querySelector('img');
                        if (img) img.remove();
                        
                        var span = label.querySelector('span');
                        if (span) {
                             span.style.display = 'block';
                             span.innerHTML = 'DEL'; 
                        } else {
                             label.insertAdjacentHTML('afterbegin', '<span class="text-xs text-red-300 font-bold">DEL</span>');
                        }
                        
                        btn.remove();
                    }
                }
                
                function switch_sidebar(mode) { 
                    // Deprecated but kept for safety if called elsewhere.
                    // Redirect to new tab logic?
                    switch_sidebar_tab(mode);
                }

                window.addEventListener('DOMContentLoaded', function () {
                    if (initial_items && initial_items.length > 0) {
                        initial_items.forEach(item => add_item_row(item, false));
                    } else {
                        add_item_row(null, false);
                    }
                    calc_total();

                    document.getElementById('fquote').addEventListener('submit', function (e) {
                        var dep = document.getElementById('qa_deposit_dummy');
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'qa_deposit';
                        hidden.value = dep.value.replace(/,/g, '');
                        this.appendChild(hidden);
                    });
                    
                    // Reset dirty state after initial load
                    setTimeout(function(){ isDirty = false; }, 100);
                });

                function copy_share_link(url) {
                    if (!navigator.clipboard) {
                        prompt("아래 주소를 복사하세요:", url);
                        return;
                    }
                    navigator.clipboard.writeText(url).then(function () {
                        alert('공유 링크가 클립보드에 복사되었습니다.');
                    }, function (err) {
                        prompt("아래 주소를 복사하세요:", url);
                    });
                }

                function send_mail_confirm() {
                    if (typeof open_confirm === 'function') {
                        open_confirm('이 고객에게 견적서를 이메일로 발송하시겠습니까?', function () {
                            location.href = '?w=send_mail&qa_id=<?php echo $qa_id; ?>&token=<?php echo $token; ?>';
                        });
                    } else {
                        if (confirm('이 고객에게 견적서를 이메일로 발송하시겠습니까?')) {
                            location.href = '?w=send_mail&qa_id=<?php echo $qa_id; ?>&token=<?php echo $token; ?>';
                        }
                    }
                }
            </script>
        <?php endif; ?>

    </div>
</div>

<!-- Business Config Modal -->
<dialog id="biz_config_modal" class="p-0 rounded-xl shadow-2xl backdrop:bg-black/50">
    <div class="w-[600px] bg-white rounded-xl overflow-hidden">
        <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
            <h3 class="font-bold">사업자 정보 설정</h3>
            <button onclick="document.getElementById('biz_config_modal').close()"
                class="text-gray-400 hover:text-white">&times;</button>
        </div>
        <form action="./admin_quote.php" method="post" class="p-6 space-y-4">
            <input type="hidden" name="w" value="config">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold mb-1">상호(법인명)</label><input type="text" name="biz_name"
                        value="<?php echo $biz_info['biz_name'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div><label class="block text-xs font-bold mb-1">사업자정보(등록번호)</label><input type="text" name="biz_no"
                        value="<?php echo $biz_info['biz_no'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div><label class="block text-xs font-bold mb-1">대표자성명</label><input type="text" name="biz_ceo"
                        value="<?php echo $biz_info['biz_ceo'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div><label class="block text-xs font-bold mb-1">대표전화</label><input type="text" name="biz_tel"
                        value="<?php echo $biz_info['biz_tel'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div class="col-span-2"><label class="block text-xs font-bold mb-1">사업장 주소</label><input type="text"
                        name="biz_addr" value="<?php echo $biz_info['biz_addr'] ?? ''; ?>"
                        class="w-full border p-2 rounded"></div>
                <div><label class="block text-xs font-bold mb-1">업태</label><input type="text" name="biz_type"
                        value="<?php echo $biz_info['biz_type'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div><label class="block text-xs font-bold mb-1">종목</label><input type="text" name="biz_class"
                        value="<?php echo $biz_info['biz_class'] ?? ''; ?>" class="w-full border p-2 rounded"></div>
                <div class="col-span-2"><label class="block text-xs font-bold mb-1">이메일</label><input type="email"
                        name="biz_email" value="<?php echo $biz_info['biz_email'] ?? ''; ?>"
                        class="w-full border p-2 rounded"></div>
            </div>
            <div class="pt-4 text-right">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700">저장하기</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Custom Confirm Modal -->
<div id="custom_confirm_modal" class="hidden fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="close_confirm_modal()"></div>

        <!-- Modal panel -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                        <!-- Icon -->
                        <svg class="h-6 w-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            확인
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirm_msg">
                                작업을 진행하시겠습니까?
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn_confirm_yes"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                    확인
                </button>
                <button type="button" onclick="close_confirm_modal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Save & Preview Confirm Modal -->
<div id="save_preview_confirm_modal" class="hidden fixed inset-0 z-[10002] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="close_save_preview_confirm()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">변경사항 저장</h3>
                <p class="text-sm text-gray-500">
                    작성 중인 내용이 저장되지 않았습니다.<br>
                    저장 후 이미지를 생성하시겠습니까?
                </p>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="confirm_save_preview_yes()"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:w-auto sm:text-sm">
                    예 (저장 후 미리보기)
                </button>
                <button type="button" onclick="confirm_save_preview_no()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 sm:mt-0 sm:w-auto sm:text-sm">
                    아니요 (저장 안함)
                </button>
                <button type="button" onclick="close_save_preview_confirm()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-700 sm:mt-0 sm:w-auto sm:text-sm">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script for Custom Modal -->
<script>
    let confirmCallback = null;

    function open_confirm(msg, callback) {
        document.getElementById('confirm_msg').innerText = msg;
        document.getElementById('custom_confirm_modal').classList.remove('hidden');
        confirmCallback = callback;
    }

    function close_confirm_modal() {
        document.getElementById('custom_confirm_modal').classList.add('hidden');
        confirmCallback = null;
    }

    document.getElementById('btn_confirm_yes').addEventListener('click', function () {
        if (confirmCallback) confirmCallback();
        close_confirm_modal();
    });

    // --- Override Default Confirm Logic ---

    // 1. Bulk Delete Submit (List View)
    function fquotelist_submit(f) {
        if (!is_checked("chk_qa_id[]")) {
            alert("삭제할 항목을 하나 이상 선택하세요."); // Alert represents simple info, can keep or style too. User asked mostly for confirm.
            return false;
        }

        open_confirm("선택한 견적서를 정말 삭제하시겠습니까?", function () {
            // Create a hidden input to signify actual submission if needed, or just submit
            f.submit();
        });

        return false; // Always return false to stop sync submit
    }

    // 2. Delete Link (Form View)
    function delete_quote_link(url) {
        open_confirm("이 견적서를 삭제하시겠습니까?", function () {
            location.href = url;
        });
        return false;
    }

    // 3. Save Form (Form View)
    function fquote_submit(f) {
        open_confirm("저장하시겠습니까?", function () {
            f.submit();
        });
        return false;
    }

    // 4. Send Email (Form View)
    function send_mail_link(url) {
        open_confirm("이메일을 발송하시겠습니까?", function () {
            location.href = url;
        });
        return false;
    }
</script>

<!-- Preview Modal -->
<div id="preview_modal" class="hidden fixed inset-0 z-[10005] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-80 transition-opacity z-[10006]" aria-hidden="true"
             onclick="close_preview_modal()"></div>

        <!-- Center Modal -->
        <div class="relative z-[10010] inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 w-full max-w-5xl h-[90vh] flex flex-col pointer-events-auto">

            <!-- Close Button (Large X) -->
            <button onclick="close_preview_modal()" type="button"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-[10020] p-2 bg-white rounded-full shadow-lg transition transform hover:scale-110 cursor-pointer">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📄</span> 견적서 미리보기
                </h3>
            </div>

            <!-- Body (Iframe) -->
            <div class="flex-grow bg-gray-100 overflow-hidden relative">
                <iframe id="preview_frame" src="" class="w-full h-full border-0"></iframe>
            </div>

            <!-- Footer (Actions) -->
            <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-center gap-2">
                <button type="button" onclick="preview_action_link()"
                    class="bg-white border border-gray-300 hover:bg-green-50 text-gray-700 hover:text-green-700 font-bold py-2 px-3 rounded shadow-sm text-xs flex items-center justify-center gap-1 transition">
                    <span class="text-sm">🔗</span> 링크
                </button>
                <button type="button" onclick="preview_action_pdf()"
                    class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-3 rounded shadow-sm text-xs flex items-center justify-center gap-1 transition">
                    <span class="text-sm">🖨️</span> PDF/인쇄
                </button>
                 <button type="button" onclick="preview_action_image(this)"
                    class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-3 rounded shadow-sm text-xs flex items-center justify-center gap-1 transition">
                    <span class="text-sm">📷</span> 이미지저장
                </button>
                <button type="button" onclick="preview_action_email()"
                    class="bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 font-bold py-2 px-3 rounded shadow-sm text-xs flex items-center justify-center gap-1 transition">
                    <span class="text-sm">📧</span> 이메일
                </button>
            </div>
        </div>
    </div>
</div>

<script>
<script>
    // BUILD: 2025-12-24 22:45
    console.log("Admin Quote Script Loaded - Build 2025-12-24 22:45");

    var currentPreviewId = null;

    var isPreviewLoaded = false;

    // Defined globally to avoid ReferenceError
    window.open_preview_modal_safe = function(qa_id) {
        if (!qa_id || qa_id == 0) {
            alert('견적서를 먼저 저장해주세요.');
            return;
        }

        // Check Dirty
        if (typeof isDirty !== 'undefined' && isDirty) {
            // Open Confirm Logic
            document.getElementById('save_preview_confirm_modal').classList.remove('hidden');
        } else {
            // Just Open
            open_preview_modal(qa_id);
        }
    };

    // Defined globally to avoid ReferenceError
    window.open_preview_modal_safe = function(qa_id) {
        if (!qa_id || qa_id == 0) {
            alert('견적서를 먼저 저장해주세요.');
            return;
        }

        // Check Dirty
        if (typeof isDirty !== 'undefined' && isDirty) {
            // Open Confirm Logic
            document.getElementById('save_preview_confirm_modal').classList.remove('hidden');
        } else {
            // Just Open
            open_preview_modal(qa_id);
        }
    };
    
    // 1. Image Save Button Click Handler
    // Call downloadImage inside iframe
    window.preview_action_image = function(btn) {
        if (!isPreviewLoaded) {
            alert("미리보기가 로딩 중입니다. 잠시만 기다려주세요.");
            return;
        }

        var iframe = document.getElementById('preview_frame');
        if (iframe && iframe.contentWindow && iframe.contentWindow.downloadImage) {
            // Pass the button element to show loading state
            iframe.contentWindow.downloadImage(btn);
        } else {
            alert("이미지 저장 기능을 사용할 수 없습니다. 잠시 후 다시 시도해주세요.");
        }
    }

    // Save & Preview Confirm Logic
    function close_save_preview_confirm() {
        document.getElementById('save_preview_confirm_modal').classList.add('hidden');
    }

    function confirm_save_preview_yes() {
        // Save -> Reload -> Open Preview
        // Use localStorage to flag auto-open
        var qa_id = document.querySelector('input[name="qa_id"]').value;
        localStorage.setItem('auto_open_preview', qa_id);
        
        var form = document.querySelector('form[name="fquote"]');
        form.submit();
        
        close_save_preview_confirm();
    }

    function confirm_save_preview_no() {
        // Do nothing (User canceled save)
        // User requirements: "아니요: 저장 안 하고 페이지 유지(미리보기/이미지 저장 진행 X)"
        close_save_preview_confirm();
    }

    function open_preview_modal(qa_id) {
        currentPreviewId = qa_id;
        var ts = new Date().getTime();
        var url = './quote_view.php?qa_id=' + qa_id + '&_ts=' + ts;
        
        var frame = document.getElementById('preview_frame');
        
        // Reset Load State
        isPreviewLoaded = false;
        
        // Setup Onload Handler
        frame.onload = function() {
            isPreviewLoaded = true;
            console.log("Preview Iframe Loaded");
        };
        
        frame.src = url;
        
        document.getElementById('preview_modal').classList.remove('hidden');
    }
    
    function close_preview_modal() {
        document.getElementById('preview_modal').classList.add('hidden');
        document.getElementById('preview_frame').src = '';
        currentPreviewId = null;
    }

    // ESC Key Support
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            if (!document.getElementById('preview_modal').classList.contains('hidden')) {
                close_preview_modal();
            }
        }
    });

    // Auto-Open Logic (from localStorage)
    document.addEventListener("DOMContentLoaded", function() {
        var autoId = localStorage.getItem('auto_open_preview');
        if (autoId) {
            localStorage.removeItem('auto_open_preview');
            // Check if current page ID matches (safety)
            var currentId = document.querySelector('input[name="qa_id"]').value;
            if(currentId == autoId) {
                // Open Modal
                open_preview_modal(autoId);
            }
        }
    });

    // 2. Dirty Check & Back to List
    var isDirty = false;
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('form[name="fquote"]');
        if (form) {
            // Track changes
            form.addEventListener('change', function() { isDirty = true; });
            form.addEventListener('input', function() { isDirty = true; });
            
            // Allow submit
            form.addEventListener('submit', function() { isDirty = false; });
        }
        
        // Initial load reset (if saved=1, logic handles it)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('saved') === '1') {
            isDirty = false;
        }
        
        // Load Initial List (Ajax) if in List View (fsearch exists)
        if(document.getElementById('fsearch')) {
             load_list(); // Initial Load
        }
    });

    window.go_list_safe = function() {
        if (isDirty) {
            document.getElementById('back_confirm_modal').classList.remove('hidden');
        } else {
            location.href = './admin_quote.php';
        }
    }

    // Custom Back Confirm Modal Logic
    function close_back_confirm() {
        document.getElementById('back_confirm_modal').classList.add('hidden');
    }

    function confirm_back_save() {
        // Save then Redirect
        // Actually, we can just submit the form. 
        // But we want to redirect to list after save? 
        // Standard save stays in form. 
        // To implement "Save and Go List", we might need a hidden input or just rely on manual behavior.
        // User asked: "예: 저장 실행 후 목록으로 이동". 
        // Our current PHP redirects to FORM. 
        // We can add a hidden field 'redirect_to_list' to the form?
        
        var form = document.querySelector('form[name="fquote"]');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'redirect_to_list';
        input.value = '1';
        form.appendChild(input);
        
        form.submit();
        close_back_confirm();
    }

    function confirm_back_nosave() {
        // Just go
        location.href = './admin_quote.php';
    }

    // 3. AJAX Load List (Controller)
    var currentMonth = <?php echo json_encode($f_month ?? ''); ?>; 
    var currentPage = 1;

    function load_list(month, page) {
        if (typeof month !== 'undefined') {
            currentMonth = month;
            // Also update input for form submission compatibility if needed
            if(document.getElementById('f_month')) document.getElementById('f_month').value = month;
        }
        if (typeof page !== 'undefined') currentPage = page;
        
        var f_year_el = document.getElementById('f_year');
        var q_el = document.getElementById('q');
        
        if(!f_year_el) return; // Not in list view
        
        var f_year = f_year_el.value;
        var q = q_el ? q_el.value : '';
        
        var url = './admin_quote.php?w=ajax_list';
        url += '&f_year=' + encodeURIComponent(f_year);
        url += '&f_month=' + encodeURIComponent(currentMonth);
        url += '&q=' + encodeURIComponent(q);
        url += '&page=' + currentPage;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                var tbody = document.getElementById('list_tbody');
                if(tbody) tbody.innerHTML = data.html;
                
                var paging = document.getElementById('pagination_container');
                if(paging) paging.innerHTML = data.paging;
                
                update_tab_active(currentMonth);
            })
            .catch(error => console.error('Error:', error));
            
        return false;
    }
    
    function update_tab_active(month) {
        // month: '01', '02', or '' (all)
        // We look for elements inside #month_tabs
        var container = document.getElementById('month_tabs');
        if(!container) return;
        
        // Improve selector: find all buttons with onclick containing load_list
        var buttons = container.querySelectorAll('button');
        buttons.forEach(btn => {
            var onclick = btn.getAttribute('onclick');
            if(onclick && onclick.indexOf('load_list') !== -1) {
                // Check if this button is the target
                var isTarget = false;
                if(month === '' && onclick.indexOf("''") !== -1) isTarget = true;
                else if(month && onclick.indexOf("'" + month + "'") !== -1) isTarget = true;
                
                if(isTarget) {
                    btn.classList.remove('bg-white', 'text-gray-600', 'hover:bg-gray-50');
                    btn.classList.add('bg-orange-600', 'text-white', 'shadow-md');
                } else {
                    btn.classList.remove('bg-orange-600', 'text-white', 'shadow-md');
                    btn.classList.add('bg-white', 'text-gray-600', 'hover:bg-gray-50');
                }
            }
        });
    }

</script>

<!-- Custom Confirm Modal (3 Buttons: Save/Don't Save/Cancel) -->
<div id="back_confirm_modal" class="hidden fixed inset-0 z-[10001] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="close_back_confirm()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">변경사항 저장</h3>
                <p class="text-sm text-gray-500">
                    작성 중인 내용이 저장되지 않았습니다.<br>
                    저장 후 목록으로 이동하시겠습니까?
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
<?php
include_once(G5_THEME_PATH . '/tail.php');

?>
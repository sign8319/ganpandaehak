<?php
include_once('./_common.php');
// Updated: 2026-01-11 21:20 - SFTP sync trigger (Fix Modal Buttons)
// ============================================================================
// Load Common Libraries
// ============================================================================
include_once('./includes/quote_functions.php');
include_once('./includes/quote_db_schema.php');


// Init Variables to prevent Warnings
$f_month = isset($_REQUEST['f_month']) ? $_REQUEST['f_month'] : date('m');
$f_year = isset($_REQUEST['f_year']) ? $_REQUEST['f_year'] : '';
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';

// 1. Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// Initialize DB Tables
init_quote_tables();
cleanup_empty_drafts();


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

// Upgrade Schema: Member ID (For Intergration)
$row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'mb_id' ");
if (!$row) {
    sql_query(" ALTER TABLE g5_quote ADD `mb_id` varchar(20) NOT NULL DEFAULT '' AFTER `qa_id` ", false);
    sql_query(" ALTER TABLE g5_quote ADD INDEX idx_mb_id (mb_id) ", false);
}

// Load Business Info (for status list)
$biz_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];
$default_statuses = '작성중,견적발송,연락두절,계약완료,작업중,작업완료,취소';
$status_list = array_map('trim', explode(',', $biz_info['custom_statuses'] ?? $default_statuses));


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
        $today_prefix = 'Q-' . date('Ymd') . '-';
        $row = sql_fetch(" SELECT count(*) as cnt FROM g5_quote WHERE qa_code LIKE '{$today_prefix}%' ");
        if (!$row)
            $row = ['cnt' => 0];
        $seq = $row['cnt'] + 1;
        $new_code = $today_prefix . sprintf('%03d', $seq);

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
            goto_url("./admin_quote.php?w=form&qa_id=$qa_id&customer_id=$customer_id");
        }
    }
}

// Load Common AJAX Handlers
include_once('./includes/quote_ajax.php');

// -----------------------------------------------------------------------------
// Default List View Logic (Initialize Variables)
// -----------------------------------------------------------------------------
if ($w == '') {
    $f_year = isset($_GET['f_year']) ? $_GET['f_year'] : '';
    $f_month = isset($_GET['f_month']) ? $_GET['f_month'] : date('m');
    $q = isset($_GET['q']) ? $_GET['q'] : '';

    $sql_search = " where 1 and qa_subject not like '%퀵상담 신청%' "; // Filter out quick consults

    // [DRAFT SYSTEM] Hide draft quotes by default
    $show_drafts = isset($_GET['show_drafts']) ? $_GET['show_drafts'] : '0';
    if ($show_drafts != '1') {
        $sql_search .= " and qa_status != 'draft' ";
    }

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

    // Load existing config
    $biz_file = G5_DATA_PATH . '/quote_config.json';
    $biz_config = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];

    // Update business info
    $biz_config['biz_no'] = $_POST['biz_no'];
    $biz_config['biz_name'] = $_POST['biz_name'];
    $biz_config['biz_ceo'] = $_POST['biz_ceo'];
    $biz_config['biz_addr'] = $_POST['biz_addr'];
    $biz_config['biz_type'] = $_POST['biz_type'];
    $biz_config['biz_class'] = $_POST['biz_class'];
    $biz_config['biz_tel'] = $_POST['biz_tel'];
    $biz_config['biz_email'] = $_POST['biz_email'];
    $biz_config['quote_fixed_message'] = $_POST['quote_fixed_message'];
    $biz_config['custom_statuses'] = $_POST['custom_statuses'];

    // Handle company seal upload
    $upload_dir = G5_DATA_PATH . '/quote';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
    }

    // Delete seal if requested
    if (isset($_POST['delete_seal']) && $_POST['delete_seal'] == '1') {
        if (!empty($biz_config['company_seal'])) {
            $old_file = $upload_dir . '/' . $biz_config['company_seal'];
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
            $biz_config['company_seal'] = '';
        }
    }

    // Upload new seal
    if (isset($_FILES['company_seal']) && $_FILES['company_seal']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['company_seal'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
            // Delete old seal if exists
            if (!empty($biz_config['company_seal'])) {
                $old_file = $upload_dir . '/' . $biz_config['company_seal'];
                if (file_exists($old_file)) {
                    @unlink($old_file);
                }
            }

            // Save new seal
            $new_name = 'company_seal_' . time() . '.' . $ext;
            $target_path = $upload_dir . '/' . $new_name;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $biz_config['company_seal'] = $new_name;
            }
        }
    }

    file_put_contents($biz_file, json_encode($biz_config, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    alert('설정이 저장되었습니다.', './admin_quote.php');
}

// Load Business Info
$biz_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];


// Save Quote
if ($w == 'u' || $w == 'c') {
    check_quote_token(); // CSRF Check if available, or custom check

    $qa_subject = clean_sql($_POST['qa_subject'] ?? '');
    $qa_client_name = clean_sql($_POST['qa_client_name'] ?? '');
    $qa_client_email = clean_sql($_POST['qa_client_email'] ?? '');
    $qa_deposit = (int) ($_POST['qa_deposit'] ?? 0);
    $qa_client_hp = clean_sql($_POST['qa_client_hp'] ?? '');
    $qa_client_contact = clean_sql($_POST['qa_client_contact'] ?? '');
    $qa_client_addr = clean_sql($_POST['qa_client_addr'] ?? '');
    $qa_client_addr2 = clean_sql($_POST['qa_client_addr2'] ?? '');
    $qa_memo = clean_sql($_POST['qa_memo'] ?? ''); // Internal Memo
    $qa_memo_user = clean_sql($_POST['qa_memo_user'] ?? ''); // Customer Memo
    // [NEW] Company Name
    $qa_tax_company_name = clean_sql($_POST['qa_tax_company_name'] ?? '');
    $qa_code = clean_sql($_POST['qa_code'] ?? '');
    $qa_status = clean_sql($_POST['qa_status'] ?? '작성중'); // Status

    // [New] Extended Columns for Safety
    $qa_related_title = clean_sql($_POST['qa_related_title'] ?? '');
    $qa_related_url = clean_sql($_POST['qa_related_url'] ?? '');
    $qa_tax_biz_num = clean_sql($_POST['qa_tax_biz_num'] ?? '');
    $qa_tax_ceo_name = clean_sql($_POST['qa_tax_ceo_name'] ?? '');
    $qa_tax_addr = clean_sql($_POST['qa_tax_addr'] ?? '');
    $qa_tax_email = clean_sql($_POST['qa_tax_email'] ?? '');
    $qa_tax_item_name = clean_sql($_POST['qa_tax_item_name'] ?? '');
    $qa_tax_trade_name = clean_sql($_POST['qa_tax_trade_name'] ?? '');

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
            'item' => clean_sql($qi_item[$i] ?? ''),
            'desc' => clean_sql($qi_desc[$i] ?? ''),
            'spec' => clean_sql($qi_spec[$i] ?? ''),
            'qty' => $qty,
            'price' => $price,
            'amount' => $amount,
            'note' => clean_sql($qi_note[$i] ?? ''),
            'img1' => $img_files[1],
            'img2' => $img_files[2],
            'img3' => $img_files[3]
        ];
    }

    $qa_price_vat = floor($total_supply * 0.1);
    $qa_price_total = $total_supply + $qa_price_vat;

    // [FIX] Customer Sync Logic (Find or Create)
    $qa_customer_id = find_or_create_customer($qa_client_name, $qa_client_hp, $qa_client_email, $qa_client_addr, $qa_tax_company_name);

    $sql_common = " qa_subject = '{$qa_subject}',
                    qa_customer_id = '{$qa_customer_id}',
                    qa_client_name = '{$qa_client_name}',
                    qa_client_contact = '{$qa_client_contact}',
                    qa_client_hp = '{$qa_client_hp}',
                    qa_client_email = '{$qa_client_email}',
                    qa_client_addr = '{$qa_client_addr}',
                    qa_client_addr2 = '{$qa_client_addr2}',
                    qa_memo = '{$qa_memo}',
                    qa_memo_user = '{$qa_memo_user}',
                    qa_tax_company_name = '{$qa_tax_company_name}',
                    qa_price_supply = '{$total_supply}',
                    qa_price_vat = '{$qa_price_vat}',
                    qa_price_total = '{$qa_price_total}',
                    qa_deposit = '{$qa_deposit}',
                    qa_status = '{$qa_status}',
                    qa_related_title = '{$qa_related_title}',
                    qa_related_url = '{$qa_related_url}',
                    qa_tax_biz_num = '{$qa_tax_biz_num}',
                    qa_tax_ceo_name = '{$qa_tax_ceo_name}',
                    qa_tax_addr = '{$qa_tax_addr}',
                    qa_tax_email = '{$qa_tax_email}',
                    qa_tax_item_name = '{$qa_tax_item_name}',
                    qa_tax_trade_name = '{$qa_tax_trade_name}' ";

    if ($w == 'c' || !$qa_id) {
        $today_prefix = 'Q-' . date('Ymd') . '-';
        $row = sql_fetch(" select count(*) as cnt from g5_quote where qa_code like '{$today_prefix}%' ");
        $seq = $row['cnt'] + 1;
        $new_code = $today_prefix . sprintf('%03d', $seq);

        $sql = " insert into g5_quote set qa_code = '{$new_code}', qa_datetime = '" . G5_TIME_YMDHIS . "', {$sql_common} ";
        $result = sql_query($sql);
        if (!$result) {
            alert('정보 저장 중 오류가 발생했습니다.');
        }
        $qa_id = sql_insert_id();
    } else {
        $sql = " update g5_quote set {$sql_common} where qa_id = '{$qa_id}' ";
        $result = sql_query($sql);
        if (!$result) {
            alert('저장 중 오류가 발생했습니다. 관리자에게 문의하세요.');
        }
        // Important: When deleting items, we should also delete files if we are cleaning up deeply. 
        // But 'delete from g5_quote_item' doesn't auto-delete files. 
        // For now, we assume standard flow replaces them or they are orphaned (need cron cleanup).
        // Since we re-insert immediately, we kept files in $items_data.
        $del_result = sql_query(" delete from g5_quote_item where qa_id = '{$qa_id}' ");
        if (!$del_result) {
            alert('기본 항목 삭제 중 오류가 발생했습니다.');
        }
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
        $ins_result = sql_query($sql);
        if (!$ins_result) {
            alert('품목 저장 중 오류가 발생했습니다.');
        }
    }

    // [NEW] Sync to Work Table
    save_work_from_quote($qa_id);

    $redirect_to_list = (isset($_POST['redirect_to_list']) && $_POST['redirect_to_list'] == '1');
    $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';

    if ($redirect_to_list) {
        goto_url("./admin_quote.php?saved=1");
    } elseif ($redirect_url) {
        goto_url($redirect_url);
    } else {
        goto_url("./admin_quote.php?w=form&qa_id=" . $qa_id . "&saved=1");
    }
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

// Bulk Delete (FIX: validate + show errors)
if ($w == 'multi_d') {
    check_quote_token();

    $chk_qa_ids = isset($_POST['chk_qa_id']) ? $_POST['chk_qa_id'] : [];

    if (!is_array($chk_qa_ids) || count($chk_qa_ids) === 0) {
        alert('선택된 항목이 서버로 전달되지 않았습니다. (체크 후 다시 시도)');
    }

    $deleted = 0;

    foreach ($chk_qa_ids as $del_id) {
        $del_id = (int) $del_id;
        if (!$del_id)
            continue;

        // true: SQL 에러를 화면에 표시해서 원인 즉시 확인
        $r1 = sql_query(" delete from g5_quote where qa_id = '$del_id' ", true);
        $r2 = sql_query(" delete from g5_quote_item where qa_id = '$del_id' ", true);
        $r3 = sql_query(" delete from g5_quote_log where qa_id = '$del_id' ", true);

        if ($r1)
            $deleted++;
    }

    alert("선택 삭제 완료: {$deleted}건", "./admin_quote.php");
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
                <p style="margin:5px 0 0; opacity:0.9;">No.
                    <?php echo $quote['qa_code']; ?>
                </p>
            </div>

            <div style="padding:40px;">
                <!-- Info Grid -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:40px;">
                    <tr>
                        <td style="width:50%; vertical-align:top; padding-right:20px;">
                            <h3
                                style="color:#4b5563; font-size:14px; border-bottom:2px solid #ea580c; padding-bottom:10px; margin-bottom:15px;">
                                수신 (Customer)</h3>
                            <p style="margin:5px 0;"><strong>
                                    <?php echo $quote['qa_client_name']; ?> 귀하
                                </strong></p>
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
                            <p style="margin:5px 0;"><strong>
                                    <?php echo $biz_info['biz_name'] ?? '간판대학'; ?>
                                </strong></p>
                            <p style="margin:5px 0; color:#666;'>대표: <?php echo $biz_info['biz_ceo'] ?? ''; ?></p>
                            <p style=" margin:5px 0; color:#666;'>Tel:
                                <?php echo $biz_info['biz_tel'] ?? ''; ?>
                            </p>
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
                                    <?php echo $item['qi_item']; ?><br><span style="font-size:11px; color:#9ca3af;">
                                        <?php echo $item['qi_note']; ?>
                                    </span>
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
                            style="display:inline-block; width:120px; color:#111827; font-weight:bold;">
                            <?php echo number_format($quote['qa_price_supply']); ?>원
                        </span>
                    </p>
                    <p style="margin:5px 0; color:#6b7280;">부가세(10%) <span
                            style="display:inline-block; width:120px; color:#111827; font-weight:bold;">
                            <?php echo number_format($quote['qa_price_vat']); ?>원
                        </span>
                    </p>
                    <div
                        style="margin-top:15px; padding-top:15px; border-top:2px solid #e5e7eb; font-size:20px; font-weight:bold; color:#ea580c;">
                        최종 합계 <span style="display:inline-block; width:150px;">
                            <?php echo number_format($quote['qa_price_total']); ?>원
                        </span>
                    </div>
                </div>

                <div
                    style="margin-top:50px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #e5e7eb; padding-top:20px;">
                    본 견적서는
                    <?php echo date('Y년 m월 d일'); ?>에 발행되었습니다.<br>
                    문의사항은
                    <?php echo $biz_info['biz_email'] ?? 'master@ganpandaehak.com'; ?>으로 연락주세요.
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

// Set header visibility based on page type
$hide_header_inputs = ($w == ''); // Hide header inputs on list page

include_once(G5_THEME_PATH . '/head.php');
// Header include removed from here
?>

<!-- Sidebar Layout Wrapper -->
<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <!-- Sidebar -->
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>

    <div class="flex-1 min-w-0">
        <!-- Main Layout Container -->


        <style>
            /* Modal Display Control */
            #customer_choice_modal:not([open]) {
                display: none !important;
            }

            #customer_choice_modal[open] {
                display: flex !important;
            }
        </style>

        <?php if ($w == ''): // -------------------- LIST VIEW -------------------- ?>
            <div class="admin-container">
                <?php include(G5_THEME_PATH . '/admin_quote_header.php'); ?>

                <!-- Search & Tabs -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    <!-- Top Control Bar -->
                    <form name="fsearch" id="fsearch" method="get"
                        class="p-4 border-b border-gray-100 flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between"
                        onsubmit="return load_list();">
                        <input type="hidden" name="f_month" id="f_month" value="<?php echo $f_month; ?>">

                        <div class="flex flex-col lg:flex-row gap-2 items-stretch lg:items-center w-full lg:w-auto">
                            <!-- [NEW] Status Filter Dropdown -->
                            <div class="relative w-full lg:w-40">
                                <select name="f_status" id="f_status" onchange="load_list()"
                                    class="w-full pl-3 pr-8 py-2.5 lg:py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white">
                                    <option value="">전체 상태</option>
                                    <?php
                                    $f_status = isset($_REQUEST['f_status']) ? $_REQUEST['f_status'] : '';
                                    foreach ($status_list as $status) {
                                        if (empty($status))
                                            continue;
                                        $selected = ($status == $f_status) ? 'selected' : '';
                                        echo "<option value='{$status}' {$selected}>{$status}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="flex flex-col lg:flex-row gap-2 items-stretch lg:items-center w-full lg:w-auto">
                                <div class="relative w-full lg:w-auto">
                                    <input type="text" name="q" id="q" value="<?php echo $q; ?>" placeholder="고객명, 견적번호 검색"
                                        class="border border-gray-300 rounded-lg pl-9 pr-4 py-2.5 lg:py-2 text-sm w-full lg:w-64 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 block">
                                    <span class="absolute left-3 top-3 lg:top-2.5 text-gray-400">🔍</span>
                                </div>
                                <button type="submit" class="admin-btn admin-btn-black">검색</button>
                            </div>

                            <!-- Action Buttons -->
                            <div class="admin-action-bar" style="margin-bottom:0;">
                                <button type="button" onclick="document.getElementById('customer_choice_modal').showModal()"
                                    class="admin-btn admin-btn-white">
                                    + 고객등록
                                </button>
                                <a href="./admin_quote.php?w=form" class="admin-btn admin-btn-primary">
                                    견적 작성
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Month Tabs with Year Select -->
                    <div class="px-4 pt-4 bg-gray-50/50 flex flex-col lg:flex-row gap-2 lg:gap-4 border-b border-gray-200 items-start lg:items-end"
                        id="month_tabs">
                        <div
                            class="flex-none flex items-center gap-2 mb-2 lg:mb-0 w-full lg:w-auto border-b lg:border-none pb-2 lg:pb-0">
                            <span class="text-xs font-bold text-gray-500 lg:hidden mr-2">연도 선택</span>
                            <select name="f_year" id="f_year"
                                class="border-none bg-transparent text-lg font-bold text-gray-700 focus:ring-0 cursor-pointer pr-8 py-2"
                                onchange="load_list();">
                                <?php
                                $curr_year = date('Y');
                                // [NEW] Add Filter for All Years
                                $selected_all = ($f_year == '') ? 'selected' : '';
                                echo "<option value='' $selected_all>전체 연도</option>";

                                // 미래 2년 + 현재 + 과거 5년 = 총 8년치 표시
                                for ($y = $curr_year + 2; $y >= $curr_year - 5; $y--) {
                                    $selected = ($y == $f_year) ? 'selected' : '';
                                    echo "<option value='$y' $selected>{$y}년</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Scrollable Tabs Container for Mobile -->
                        <div
                            class="flex overflow-x-auto min-w-0 w-full lg:w-auto lg:flex-1 pb-2 lg:pb-0 no-scrollbar gap-1">
                            <button type="button" onclick="load_list('')"
                                class="flex-shrink-0 month-tab px-3 py-1.5 lg:px-4 lg:py-2 rounded text-xs lg:text-sm lg:rounded-t-lg lg:rounded-b-none font-bold border border-gray-200 lg:border-b-0 transition <?php echo $f_month == '' ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                                전체
                            </button>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $m_val = sprintf('%02d', $i);
                                $active = ($f_month == $m_val) ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50';
                                echo "<button type='button' onclick=\"load_list('$m_val')\" class='flex-shrink-0 month-tab px-3 py-1.5 lg:px-4 lg:py-2 rounded text-xs lg:text-sm lg:rounded-t-lg lg:rounded-b-none font-bold border border-gray-200 lg:border-b-0 transition {$active}'>{$i}월</button>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- List Table -->
                <form name="fquotelist" id="fquotelist" action="./admin_quote.php"
                    onsubmit="return fquotelist_submit(this);" method="post">
                    <input type="hidden" name="w" value="multi_d"> <!-- // FIX -->
                    <input type="hidden" name="token" value="<?php echo $token; ?>">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b text-xs">
                                <tr>
                                    <th class="p-3 text-center w-10">
                                        <input type="checkbox" id="chkall" onclick="event.stopPropagation();"
                                            class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    </th>
                                    <th class="p-3">날짜</th>
                                    <th class="p-3 text-center">요약(!)</th>
                                    <th class="p-3 text-center">상태</th>
                                    <th class="p-3">상호(업체명)</th>
                                    <th class="p-3">연락처</th>
                                    <th class="p-3">주소</th>
                                    <th class="p-3 text-right">총금액</th>
                                    <th class="p-3 text-center">견적미리보기</th>
                                    <th class="p-3 text-center">연결된</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white" id="list_tbody">
                                <?php if (sql_num_rows($result) > 0) {
                                    while ($row = sql_fetch_array($result)) {
                                        $color = get_status_color($row['qa_status']);
                                        ?>
                                        <tr class="hover:bg-orange-50 transition border-b border-gray-100"
                                            onclick="row_go(event, <?php echo (int) $row['qa_id']; ?>)">
                                            <td class="p-3 text-center">
                                                <input type="checkbox" name="chk_qa_id[]" value="<?php echo $row['qa_id']; ?>"
                                                    onclick="event.stopPropagation();"
                                                    class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            </td>
                                            <td class="p-3">
                                                <div class="leading-tight">
                                                    <div class="font-bold text-gray-800 text-sm">
                                                        <?php echo date('m.d', strtotime($row['qa_datetime'])); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="p-3 text-center">
                                                <button type="button"
                                                    onclick="show_quote_summary(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                                                    class="text-gray-400 hover:text-orange-500 transition" title="빠른 요약 보기">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                            <td class="p-3 text-center">
                                                <span
                                                    class="inline-block px-2 py-1 rounded-sm text-xs font-bold bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700">
                                                    <?php echo $row['qa_status']; ?>
                                                </span>
                                            </td>
                                            <td class="p-3 font-bold text-sm text-gray-800">
                                                <?php echo $row['qa_tax_company_name']; ?>
                                            </td>
                                            <td class="p-3 text-xs text-gray-600 font-mono">
                                                <?php echo $row['qa_client_hp']; ?>
                                            </td>
                                            <td class="p-3 text-xs text-gray-500 truncate max-w-[150px]"
                                                title="<?php echo $row['qa_client_addr'] . ' ' . $row['qa_client_addr2']; ?>">
                                                <?php echo $row['qa_client_addr']; ?>
                                            </td>
                                            <td class="p-3 text-right font-bold text-orange-600 text-sm">
                                                <?php echo number_format($row['qa_price_total']); ?>
                                            </td>
                                            <td class="p-3 text-center">
                                                <button type="button"
                                                    onclick="open_preview_modal(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                                                    class="bg-white border hover:bg-gray-50 text-gray-700 px-2 py-1 rounded text-xs shadow-sm inline-flex items-center gap-1 transition whitespace-nowrap">
                                                    <span>👁️</span> 견적미리보기
                                                </button>
                                            </td>
                                            <td class="p-3 text-center">
                                                <a href="?q=<?php echo urlencode($row['qa_tax_company_name']); ?>"
                                                    onclick="event.stopPropagation();"
                                                    class="inline-block px-2 py-1 border border-gray-300 rounded text-xs text-gray-600 hover:bg-gray-100 whitespace-nowrap">
                                                    연결된
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="10" class="p-10 text-center text-gray-400">등록된 견적서가 없습니다.</td></tr>';
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
            </div> <!-- End List View Container -->


        <?php elseif ($w == 'form'): // -------------------- FORM VIEW -------------------- ?>
            <style>
                #quote_sidebar_panel {
                    display: block !important;
                }
            </style>
            <div class="admin-container">
                <form name="fquote" id="fquote" action="./admin_quote.php" method="post"
                    onsubmit="return fquote_submit(this);" enctype="multipart/form-data" class="w-full">
                    <input type="hidden" name="w" value="<?php echo $form_action; ?>">
                    <input type="hidden" name="qa_id" value="<?php echo $qa_id; ?>">
                    <input type="hidden" name="qa_code" value="<?php echo $quote['qa_code']; ?>">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">

                    <!-- Include Header (Outside Grid) -->
                    <div class="grid grid-cols-12 gap-6 items-start">
                        <!-- Main Content Area (9/12) -->
                        <div class="col-span-12 lg:col-span-9">
                            <!-- Include Header (Inside Grid) -->
                            <?php include(G5_THEME_PATH . '/admin_quote_header.php'); ?>
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

                            // [Added for Fix] Load measure data for JS Manual Import
                            $step1_measures = [];
                            if ($qa_id) {
                                $m_res = sql_query(" SELECT * FROM g5_quote_measure WHERE qa_id = '$qa_id' ORDER BY qm_index ");
                                while ($m_row = sql_fetch_array($m_res)) {
                                    $step1_measures[] = $m_row;
                                }
                            }

                            // FIX: Load measure data from STEP1 if coming from step1
                            // Disconnected per user request: Step 2 should start empty even if coming from Step 1.
                            $from_step1 = isset($_GET['from_step1']) && $_GET['from_step1'] == '1';
                            /*
                            if ($from_step1 && $qa_id && empty($items)) {
                                $measure_result = sql_query(" SELECT * FROM g5_quote_measure WHERE qa_id = '$qa_id' ORDER BY qm_index ");
                                while ($m = sql_fetch_array($measure_result)) {
                                    $spec = '';
                                    if ($m['qm_width'] && $m['qm_height']) {
                                        $spec = $m['qm_width'] . '×' . $m['qm_height'];
                                    } elseif ($m['qm_width']) {
                                        $spec = $m['qm_width'];
                                    } elseif ($m['qm_height']) {
                                        $spec = $m['qm_height'];
                                    }

                                    $items[] = [
                                        'qi_item' => $m['qm_type'],
                                        'qi_spec' => $spec,
                                        'qi_qty' => $m['qm_qty'] > 0 ? $m['qm_qty'] : 1,
                                        'qi_price' => 0,
                                        'qi_amount' => 0,
                                        'qi_note' => $m['qm_memo'],
                                        'qi_desc' => '',
                                        'qi_img1' => '',
                                        'qi_img2' => '',
                                        'qi_img3' => ''
                                    ];
                                }
                            }
                            */

                            // Ensure at least one item row
                            $items[] = ['qi_item' => '', 'qi_spec' => '', 'qi_qty' => 1, 'qi_price' => 0, 'qi_amount' => 0, 'qi_note' => '', 'qi_desc' => ''];
                            ?>

                            <!-- Form Start -->
                            <div>





                                <div class="space-y-6">
                                    <!-- Left: Form Area -->
                                    <div class="space-y-6">



                                        <!-- Section 2: Items (Detailed Layout) -->
                                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                            <div class="flex justify-between items-center mb-4">
                                                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                                    <span class="w-1 h-4 bg-orange-600 rounded-full"></span> 견적 내역 (Items)
                                                </h2>
                                                <span class="text-xs text-gray-400">품목 입력 후 Enter를 누르면 행이 추가됩니다.</span>
                                            </div>

                                            <div class="overflow-x-auto -mx-6 px-6">
                                                <!-- JS will render rows here. We need a solid ID target. -->
                                                <table class="w-full text-left border-collapse min-w-[800px]"
                                                    id="tbl_items_new">
                                                    <thead
                                                        class="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b border-gray-200">
                                                        <tr>
                                                            <th class="py-3 px-1 md:px-2 w-8"></th>
                                                            <th class="py-3 px-1 md:px-2 min-w-[100px]">품목<br
                                                                    class="md:hidden" />(ITEM)
                                                            </th>
                                                            <!-- FIX: Split spec into W/H -->
                                                            <th class="py-3 px-1 md:px-2 w-16 md:w-24 text-center">가로<br
                                                                    class="md:hidden" />(W)
                                                            </th>
                                                            <th class="py-3 px-1 md:px-2 w-16 md:w-24 text-center">세로<br
                                                                    class="md:hidden" />(H)
                                                            </th>
                                                            <th class="py-3 px-1 md:px-2 w-14 md:w-20 text-center">수량</th>
                                                            <th class="py-3 px-1 md:px-2 w-20 md:w-28 text-right">단가</th>
                                                            <th class="py-3 px-1 md:px-2 w-24 md:w-32 text-right">금액</th>
                                                            <th class="py-3 px-1 md:px-2 w-20 md:w-32">비고</th>
                                                            <th class="py-3 px-1 md:px-2 w-8 text-center"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="item_list_container" class="divide-y divide-gray-100">
                                                        <!-- Rows injected by JS -->
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-4 pt-4 border-t border-dashed flex justify-center relative z-10">
                                                <button type="button" id="btn_add_item_row"
                                                    class="admin-btn bg-orange-50 text-orange-700 hover:bg-orange-100 shadow-sm hover:shadow">
                                                    <span class="text-lg">+</span> 품목 행 추가하기
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Section 3: Memos -->
                                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div>
                                                    <h3
                                                        class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                                        🔒 내부 메모 <span class="text-xs font-normal text-gray-400">(고객 노출
                                                            X)</span>
                                                    </h3>
                                                    <textarea name="qa_memo"
                                                        class="w-full h-32 p-3 border border-gray-200 rounded-lg bg-yellow-50/50 resize-none text-sm placeholder-gray-400 focus:bg-white focus:border-orange-500 transition"
                                                        placeholder="관리자 전용 메모입니다."><?php echo $quote['qa_memo']; ?></textarea>
                                                </div>
                                                <div>
                                                    <h3
                                                        class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                                        📢 고객 참고사항 <span class="text-xs font-normal text-orange-500">(견적서 하단
                                                            표시)</span>
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
                                        <!-- End Mobile Actions -->

                                        <script>
                                            // ----------------------------------------------                                                             // Utility Functi                                            ons
                                            // ---------------------------------------------------------

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
                                            function execDaumPostcode() {
                                                new daum.Postcode({
                                                    oncomplete: function (data) {
                                                        var addr = data.address; // 기본 주소
                                                        document.getElementById('qa_client_addr').value = addr;
                                                        document.getElementById('qa_client_addr2').focus();
                                                    }
                                                }).open();
                                            }

                                            // ---------------------------------------------------------
                                            // 1. Data Initialization
                                            // ---------------------------------------------------------
                                            var step1_measures = <?php echo json_encode($step1_measures ?? []); ?>;
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
                        <div class="cursor-pointer block w-14 h-14 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center overflow-hidden transition relative focus:ring-2 focus:ring-orange-500 outline-none" 
                               onclick="show_image_upload_menu(this)" tabindex="0" title="클릭하여 이미지 업로드">
                            ${img_preview}
                            <input type="file" name="${img_key}[]" class="hidden" onchange="preview_image(this)" accept="image/*">
                            ${img_prev}
                        </div>
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
                    <td class="py-2 pl-1 md:pl-2 text-center align-middle">
                        <button type="button" onclick="toggle_detail(this)" class="text-gray-400 hover:text-orange-500 transition transform hover:scale-110 p-1 rounded-full hover:bg-orange-50" title="이미지/상세설명 추가">
                            <svg class="w-4 h-4 transform transition-transform duration-200 ${rotate_cls}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top">
                        <input type="text" name="qi_item[]" value="${item}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded font-bold text-gray-800 in-item focus:ring-2 focus:ring-orange-500" placeholder="품목명" onkeydown="check_enter_add(event, this)" oninput="mark_dirty()">
                    </td>
                    <!-- FIX: Split spec into W/H -->
                    <td class="py-2 px-1 md:px-2 align-top">
                        <input type="text" name="qi_spec_w[]" value="${spec.split('×')[0] || ''}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded text-center focus:ring-2 focus:ring-orange-500" placeholder="W" onkeydown="check_enter_add(event, this)" oninput="merge_spec(this)">
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top">
                        <input type="text" name="qi_spec_h[]" value="${spec.split('×')[1] || ''}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded text-center focus:ring-2 focus:ring-orange-500" placeholder="H" onkeydown="check_enter_add(event, this)" oninput="merge_spec(this)">
                        <input type="hidden" name="qi_spec[]" value="${spec}" class="spec-hidden">
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top">
                        <input type="number" name="qi_qty[]" value="${qty}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded text-right font-bold text-gray-800 in-qty focus:ring-2 focus:ring-orange-500" oninput="calc_row(this)" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top">
                         <input type="text" name="qi_price[]" value="${price}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded text-right font-bold text-gray-800 in-price focus:ring-2 focus:ring-orange-500" oninput="calc_row(this)" onkeydown="check_enter_add(event, this)">
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top text-right">
                        <input type="text" class="w-full p-1.5 md:p-2 text-xs md:text-sm border-none bg-transparent text-right font-extrabold text-orange-600 in-amount" value="${parseInt(amount).toLocaleString()}" readonly tabindex="-1">
                        <input type="hidden" name="qi_amount[]" value="${amount}">
                    </td>
                    <td class="py-2 px-1 md:px-2 align-top">
                        <input type="text" name="qi_note[]" value="${note}" class="w-full p-1.5 md:p-2 text-xs md:text-sm border border-gray-300 rounded text-gray-600 in-note focus:ring-2 focus:ring-orange-500" placeholder="비고" onkeydown="check_enter_add(event, this)" oninput="mark_dirty()">
                    </td>
                    <td class="py-2 px-1 md:px-2 text-center align-middle">
                        <button type="button" onclick="del_row(this)" class="text-gray-300 hover:text-red-500 transition px-2 font-bold text-lg">
                             &times;
                        </button>
                    </td>
                </tr>
                <!-- Hidden Detail Row (Image + Desc) -->
                <tr class="${detail_cls}">
                    <td colspan="9" class="p-3 pl-6 md:pl-10 border-l-4 border-orange-200">
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

                                            function mark_dirty() {
                                                isDirty = true;
                                            }

                                            function calc_row(el) {
                                                isDirty = true; // Mark as dirty on any calc/change
                                                var tr = el.closest('tr');
                                                var qty = parseInt(tr.querySelector('.in-qty').value) || 0;
                                                var price = parseInt(tr.querySelector('.in-price').value.replace(/,/g, '')) || 0;

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

                                            // Show image upload menu
                                            function show_image_upload_menu(wrapper) {
                                                var input = wrapper.querySelector('input[type="file"]');
                                                if (!input) return;

                                                // Check if image already exists
                                                var hasImage = wrapper.querySelector('img') !== null;

                                                // Create modal
                                                var modal = document.createElement('div');
                                                modal.className = 'fixed inset-0 z-[10001] flex items-center justify-center bg-black bg-opacity-50';
                                                modal.innerHTML = `
                        <div class="bg-white rounded-lg shadow-xl p-6 m-4 max-w-sm">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">📷 이미지 업로드</h3>
                            <div class="space-y-3">
                                <button onclick="select_file_upload(this)" 
                                    class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded-lg font-bold transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    파일 선택
                                </button>
                                <button onclick="activate_paste_mode(this)" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-bold transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    붙여넣기 (Ctrl+V)
                                </button>
                                ${hasImage ? `<button onclick="remove_current_image(this)" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-bold transition flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    이미지 삭제
                                </button>` : ''}
                                <button onclick="close_image_menu()" 
                                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-lg font-bold transition">
                                    취소
                                </button>
                            </div>
                        </div>
                    `;

                                                // Store reference
                                                modal.dataset.wrapper = '';
                                                modal._wrapper = wrapper;
                                                modal._input = input;

                                                document.body.appendChild(modal);

                                                // Close on background click
                                                modal.addEventListener('click', function (e) {
                                                    if (e.target === modal) {
                                                        close_image_menu();
                                                    }
                                                });
                                            }

                                            function select_file_upload(btn) {
                                                var modal = btn.closest('.fixed');
                                                var input = modal._input;
                                                if (input) {
                                                    input.click();
                                                }
                                                close_image_menu();
                                            }

                                            function activate_paste_mode(btn) {
                                                var modal = btn.closest('.fixed');
                                                var wrapper = modal._wrapper;

                                                // Change button text
                                                btn.innerHTML = `
                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Ctrl+V를 눌러주세요...
                    `;
                                                btn.classList.add('animate-pulse');

                                                // Listen for paste
                                                var pasteHandler = function (e) {
                                                    handle_paste_in_modal(e, wrapper, modal._input);
                                                    document.removeEventListener('paste', pasteHandler);
                                                    close_image_menu();
                                                };

                                                document.addEventListener('paste', pasteHandler);

                                                // Auto close after 10 seconds
                                                setTimeout(function () {
                                                    document.removeEventListener('paste', pasteHandler);
                                                }, 10000);
                                            }

                                            function handle_paste_in_modal(event, wrapper, input) {
                                                var items = (event.clipboardData || event.originalEvent.clipboardData).items;

                                                for (var i = 0; i < items.length; i++) {
                                                    if (items[i].type.indexOf('image') !== -1) {
                                                        var blob = items[i].getAsFile();

                                                        if (input && blob) {
                                                            var dataTransfer = new DataTransfer();
                                                            var file = new File([blob], 'pasted_image_' + Date.now() + '.png', { type: blob.type });
                                                            dataTransfer.items.add(file);
                                                            input.files = dataTransfer.files;

                                                            preview_image(input);

                                                            // Visual feedback
                                                            wrapper.style.borderColor = '#ea580c';
                                                            wrapper.style.borderWidth = '2px';
                                                            setTimeout(function () {
                                                                wrapper.style.borderColor = '';
                                                                wrapper.style.borderWidth = '';
                                                            }, 500);
                                                        }
                                                        break;
                                                    }
                                                }
                                            }

                                            function remove_current_image(btn) {
                                                var modal = btn.closest('.fixed');
                                                var wrapper = modal._wrapper;
                                                var container = wrapper.closest('.relative');

                                                // Clear file input
                                                var input = wrapper.querySelector('input[type="file"]');
                                                if (input) {
                                                    input.value = '';
                                                }

                                                // Remove image preview
                                                var img = wrapper.querySelector('img');
                                                if (img) {
                                                    img.remove();
                                                }

                                                // Show placeholder
                                                var span = wrapper.querySelector('span');
                                                if (!span) {
                                                    span = document.createElement('span');
                                                    span.className = 'text-[10px] text-gray-300';
                                                    span.textContent = 'IMG';
                                                    wrapper.appendChild(span);
                                                }
                                                span.style.display = 'block';

                                                // Mark for deletion if it was a saved image
                                                var delInput = container.querySelector('input[name*="_del"]');
                                                if (delInput) {
                                                    delInput.disabled = false;
                                                    delInput.value = '1';
                                                }

                                                // Remove delete button
                                                var delBtn = container.querySelector('.absolute.bg-red-500');
                                                if (delBtn) {
                                                    delBtn.remove();
                                                }

                                                close_image_menu();
                                            }

                                            function close_image_menu() {
                                                var modal = document.querySelector('.fixed.z-\\[10001\\]');
                                                if (modal) {
                                                    modal.remove();
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

                                                    var wrapper = container.querySelector('div[onclick^="show_image_upload_menu"]');
                                                    if (!wrapper) wrapper = container.querySelector('.cursor-pointer');

                                                    if (wrapper) {
                                                        var img = wrapper.querySelector('img');
                                                        if (img) img.remove();

                                                        var span = wrapper.querySelector('span');
                                                        if (span) {
                                                            span.style.display = 'block';
                                                            span.innerHTML = 'DEL';
                                                        } else {
                                                            wrapper.insertAdjacentHTML('afterbegin', '<span class="text-xs text-red-300 font-bold">DEL</span>');
                                                        }
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
                                                    this.appendChild(hidden);
                                                });                                                                                                              // Add Item Button Listener
                                                var btnAdd = document.getElementById('btn_add_item_row');
                                                if (btnAdd) {
                                                    btnAdd.addEventListener('click', function () {
                                                        add_item_row();
                                                    });
                                                }

                                                // Reset dirty state after initial load
                                                setTimeout(function () { isDirty = false; }, 100);
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


                                    </div> <!-- End Left Form Area (space-y-6) -->
                                </div> <!-- End space-y-6 wrapper -->
                            </div> <!-- End empty wrapper -->
                        </div> <!-- End Main Content (col-9) -->

                        <!--  Side Dashboard Area (3/12) -->
                        <div class="col-span-12 lg:col-span-3">
                            <?php include_once(G5_THEME_PATH . '/admin_quote_sidebar.php'); ?>
                        </div>
                    </div><!-- /.grid -->
                </form>
            </div> <!-- End Admin Container -->
        <?php endif; // End of Form View ?>
    </div><!-- /.container -->

    <script>
        function fquote_submit(target = null) {
            const f = document.getElementById('fquote') || document.forms['fquotelist'];
            if (!f) return false;

            if (target === 'save') {
                // Handle sidebar save
                if (typeof f.onsubmit === 'function' && f.onsubmit() === false) return false;
                f.submit();
            } else {
                // Standard form submit
                open_confirm("저장하시겠습니까?", function () {
                    f.submit();
                });
            }
            return false;
        }
    </script>

    <!-- Business Config Modal -->
    <dialog id="biz_config_modal" class="p-0 rounded-xl shadow-2xl backdrop:bg-black/50"
        style="max-width: 700px; width: 90vw;">
        <div class="bg-white rounded-xl overflow-hidden">
            <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                <h3 class="font-bold text-lg">⚙️ 사업자 정보 및 견적서 설정</h3>
                <button onclick="document.getElementById('biz_config_modal').close()"
                    class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            <form action="./admin_quote.php" method="post" enctype="multipart/form-data" class="p-6 space-y-6">
                <input type="hidden" name="w" value="config">
                <input type="hidden" name="token" value="<?php echo $token; ?>">

                <!-- 사업자 정보 섹션 -->
                <div class="border-b pb-4">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="text-orange-600">📋</span> 사업자 정보
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">상호(법인명)</label><input type="text"
                                name="biz_name" value="<?php echo $biz_info['biz_name'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">사업자등록번호</label><input type="text"
                                name="biz_no" value="<?php echo $biz_info['biz_no'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">대표자성명</label><input type="text"
                                name="biz_ceo" value="<?php echo $biz_info['biz_ceo'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">대표전화</label><input type="text"
                                name="biz_tel" value="<?php echo $biz_info['biz_tel'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2"><label class="block text-xs font-bold mb-1 text-gray-600">사업장
                                주소</label><input type="text" name="biz_addr"
                                value="<?php echo $biz_info['biz_addr'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">업태</label><input type="text"
                                name="biz_type" value="<?php echo $biz_info['biz_type'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div><label class="block text-xs font-bold mb-1 text-gray-600">종목</label><input type="text"
                                name="biz_class" value="<?php echo $biz_info['biz_class'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2"><label
                                class="block text-xs font-bold mb-1 text-gray-600">이메일</label><input type="email"
                                name="biz_email" value="<?php echo $biz_info['biz_email'] ?? ''; ?>"
                                class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                        </div>
                    </div>
                </div>

                <!-- 견적서 설정 섹션 -->
                <div class="border-b pb-4">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="text-blue-600">📝</span> 견적서 고정 메시지
                    </h4>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-gray-600">
                            모든 견적서에 자동으로 표시될 메시지 (선택사항)
                        </label>
                        <textarea name="quote_fixed_message" rows="3"
                            class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-sm"
                            placeholder="예: 견적 유효기간은 7일입니다.&#10;작업 기간은 계약금 입금 후 3-5일 소요됩니다."><?php echo $biz_info['quote_fixed_message'] ?? ''; ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">💡 이 메시지는 모든 견적서 하단에 자동으로 표시됩니다.</p>
                    </div>
                </div>

                <!-- 직인 이미지 섹션 -->
                <div class="border-b pb-4">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="text-red-600">🔖</span> 직인 이미지
                    </h4>
                    <div>
                        <label class="block text-xs font-bold mb-1 text-gray-600">
                            견적서에 표시될 직인 이미지 업로드 (선택사항)
                        </label>
                        <input type="file" name="company_seal" accept="image/*"
                            class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-sm">
                        <?php if (!empty($biz_info['company_seal'])): ?>
                            <div class="mt-3 flex items-center gap-3">
                                <img src="<?php echo G5_DATA_URL . '/quote/' . $biz_info['company_seal']; ?>" alt="현재 직인"
                                    class="h-20 w-20 object-contain border rounded bg-gray-50 p-2">
                                <div>
                                    <p class="text-xs text-gray-600">현재 등록된 직인</p>
                                    <label class="inline-flex items-center mt-1">
                                        <input type="checkbox" name="delete_seal" value="1"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-xs text-red-600">직인 삭제</span>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 mt-2">💡 권장 크기: 200x200px, PNG 파일 권장 (투명 배경)</p>
                    </div>
                </div>

                <!-- 상태 관리 섹션 -->
                <div class="border-b pb-4">
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="text-purple-600">🏷️</span> 견적서 상태 관리
                    </h4>
                    <div>
                        <label class="block text-xs font-bold mb-2 text-gray-600">
                            사용할 상태 목록 (쉼표로 구분)
                        </label>
                        <input type="text" name="custom_statuses"
                            value="<?php echo $biz_info['custom_statuses'] ?? '작성중,견적발송,연락두절,계약완료,작업중,작업완료,취소'; ?>"
                            class="w-full border border-gray-300 p-2.5 rounded focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-sm"
                            placeholder="작성중,견적발송,연락두절,계약완료">
                        <p class="text-xs text-gray-500 mt-2">💡 예: 작성중,견적발송,연락두절,계약완료,작업중,작업완료,취소</p>
                        <p class="text-xs text-orange-600 mt-1">⚠️ 기존 견적서의 상태는 자동으로 변경되지 않습니다.</p>
                    </div>
                </div>

                <div class="pt-4 text-right flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('biz_config_modal').close()"
                        class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-bold hover:bg-gray-300 transition">취소</button>
                    <button type="submit"
                        class="bg-orange-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-orange-700 transition">💾
                        저장하기</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Custom Confirm Modal -->
    <div id="custom_confirm_modal" class="hidden fixed inset-0 z-[9999]" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <!-- Flex Container for Centering -->
        <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">

            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                style="z-index: 99998 !important; pointer-events: auto;" onclick="close_confirm_modal()">
            </div>

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
                        변경사항 저장
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed" id="confirm_msg">
                        작성 중인 내용이 저장되지 않았습니다.<br>
                        저장 후 이동하시겠습니까?
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

    <!-- Custom Save & Preview Confirm Modal -->
    <div id="save_preview_confirm_modal" class="hidden fixed inset-0 z-[10002]" role="dialog" aria-modal="true">
        <!-- Flex Container for Centering (모바일에서도 중앙 정렬) -->
        <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4 text-center"
            style="pointer-events: none;">

            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                style="z-index: 99998 !important; pointer-events: auto;" onclick="close_save_preview_confirm()">
            </div>

            <!-- Modal Panel -->
            <div class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg relative"
                style="z-index: 100000 !important; pointer-events: auto;">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">변경사항 저장</h3>
                    <p class="text-sm text-gray-500">
                        작성 중인 내용이 저장되지 않았습니다.<br>
                        저장 후 이미지를 생성하시겠습니까?
                    </p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="button" onclick="confirm_save_preview_yes()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 sm:w-auto sm:text-sm whitespace-nowrap">
                        예 (저장 후 미리보기)
                    </button>
                    <button type="button" onclick="confirm_save_preview_no()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-red-600 hover:bg-red-50 sm:w-auto sm:text-sm whitespace-nowrap">
                        아니요 (저장 안함)
                    </button>
                    <button type="button" onclick="close_save_preview_confirm()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-700 sm:w-auto sm:text-sm whitespace-nowrap">
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

        // [Row Click Navigate]
        // 테이블 행 전체를 클릭하면 상세로 이동하되,
        // 체크박스/버튼/링크 클릭은 네비게이션을 막습니다.
        function row_go(e, qaId) {
            if (!e) return;
            if (e.target && e.target.closest && e.target.closest('input, a, button')) return;
            location.href = '?w=form&qa_id=' + qaId;
        }

        // [Select All Checkbox Logic]
        document.addEventListener('click', function (e) {
            if (e.target.id == 'chkall') {
                var chk = e.target.checked;
                document.querySelectorAll('input[name="chk_qa_id[]"]').forEach(function (el) {
                    el.checked = chk;
                });
            }
            if (e.target.name == 'chk_qa_id[]') {
                var all = document.querySelectorAll('input[name="chk_qa_id[]"]');
                var checked = document.querySelectorAll('input[name="chk_qa_id[]"]:checked');
                var chkall = document.getElementById('chkall');
                if (chkall) {
                    chkall.checked = (all.length > 0 && all.length === checked.length);
                }
            }
        });

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
        // FIX: Merge W/H spec into hidden field
        function merge_spec(input) {
            const row = input.closest('tr');
            const w = row.querySelector('input[name="qi_spec_w[]"]').value.trim();
            const h = row.querySelector('input[name="qi_spec_h[]"]').value.trim();
            const hidden = row.querySelector('input[name="qi_spec[]"]');

            if (w && h) {
                hidden.value = w + '×' + h;
            } else if (w) {
                hidden.value = w;
            } else if (h) {
                hidden.value = h;
            } else {
                hidden.value = '';
            }
        }
    </script>

    <!-- Preview Modal -->
    <div id="preview_modal" class="hidden fixed inset-0 z-[10005]" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-2 sm:p-4">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-80 transition-opacity z-[10006]" aria-hidden="true"
                onclick="close_preview_modal()"></div>

            <!-- Center Modal -->
            <div
                class="relative z-[10010] inline-block bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-md sm:max-w-5xl h-[95vh] sm:h-[90vh] flex flex-col pointer-events-auto">

                <!-- Close Button (Large X) -->
                <button onclick="close_preview_modal()" type="button"
                    class="absolute top-2 right-2 sm:top-4 sm:right-4 text-gray-400 hover:text-gray-600 z-[10020] p-1 sm:p-2 bg-white rounded-full shadow-lg transition transform hover:scale-110 cursor-pointer">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Header -->
                <div
                    class="bg-gray-50 px-3 py-2 sm:px-6 sm:py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-base sm:text-xl font-bold text-gray-800 flex items-center gap-2">
                        <span class="hidden sm:inline">📄</span> 견적서 미리보기
                    </h3>
                </div>

                <!-- Body (Iframe) -->
                <div class="flex-grow bg-gray-100 overflow-hidden relative">
                    <iframe id="preview_frame" src="" class="w-full h-full border-0"></iframe>
                </div>

                <!-- Footer (Actions) - 모바일 반응형 -->
                <div class="bg-white px-2 py-2 sm:px-6 sm:py-4 border-t border-gray-200">
                    <!-- 모바일: 2x2 그리드 -->
                    <div class="grid grid-cols-2 gap-2 sm:hidden">
                        <button type="button" onclick="preview_action_link()"
                            class="bg-white border border-gray-300 hover:bg-green-50 text-gray-700 hover:text-green-700 font-bold py-3 px-3 rounded shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-xl">🔗</span>
                            <span class="text-xs">링크복사</span>
                        </button>
                        <button type="button" onclick="preview_action_pdf()"
                            class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-3 rounded shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-xl">🖨️</span>
                            <span class="text-xs">PDF/인쇄</span>
                        </button>
                        <button type="button" onclick="preview_action_image(this)"
                            class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-3 rounded shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-xl">📷</span>
                            <span class="text-xs">이미지저장</span>
                        </button>
                        <button type="button" onclick="preview_action_email()"
                            class="bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 font-bold py-3 px-3 rounded shadow-sm text-xs flex flex-col items-center justify-center gap-1 transition">
                            <span class="text-xl">📧</span>
                            <span class="text-xs">이메일발송</span>
                        </button>
                    </div>

                    <!-- 데스크톱: 가로 배치 -->
                    <div class="hidden sm:flex justify-center gap-2">
                        <button type="button" onclick="preview_action_link()"
                            class="bg-white border border-gray-300 hover:bg-green-50 text-gray-700 hover:text-green-700 font-bold py-2 px-3 rounded shadow-sm text-xs flex items-center justify-center gap-1 transition">
                            <span class="text-sm">🔗</span> 링크복사
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
                            <span class="text-sm">📧</span> 이메일발송
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // BUILD: 2025-12-24 23:30
        console.log("Admin Quote Script Loaded - Build 2025-12-24 23:30");

        var currentPreviewId = null;
        var isPreviewLoaded = false;

        // Defined globally to avoid ReferenceError
        window.open_preview_modal_safe = function (qa_id) {
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

        // Preview Action Handlers
        window.preview_action_link = function () {
            if (!currentPreviewId) {
                alert('견적서 ID를 찾을 수 없습니다.');
                return;
            }
            var link = window.location.origin + window.location.pathname.replace('admin_quote.php', '') + 'quote_view.php?qa_id=' + currentPreviewId;
            navigator.clipboard.writeText(link).then(function () {
                alert('링크가 클립보드에 복사되었습니다:\n' + link);
            }).catch(function () {
                prompt('아래 링크를 복사하세요:', link);
            });
        }

        window.preview_action_pdf = function () {
            if (!isPreviewLoaded) {
                alert("미리보기가 로딩 중입니다. 잠시만 기다려주세요.");
                return;
            }
            var iframe = document.getElementById('preview_frame');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.print();
            } else {
                alert("인쇄 기능을 사용할 수 없습니다.");
            }
        }

        window.preview_action_image = function (btn) {
            if (!isPreviewLoaded) {
                alert("미리보기가 로딩 중입니다. 잠시만 기다려주세요.");
                return;
            }

            var iframe = document.getElementById('preview_frame');
            if (iframe && iframe.contentWindow && iframe.contentWindow.downloadImage) {
                iframe.contentWindow.downloadImage(btn);
            } else {
                alert("이미지 저장 기능을 사용할 수 없습니다. 잠시 후 다시 시도해주세요.");
            }
        }

        window.preview_action_email = function () {
            if (!currentPreviewId) {
                alert('견적서 ID를 찾을 수 없습니다.');
                return;
            }
            if (confirm('이메일을 발송하시겠습니까?')) {
                location.href = './admin_quote.php?w=send_mail&qa_id=' + currentPreviewId + '&token=<?php echo $token; ?>'; // FIX
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
            close_save_preview_confirm();
        }

        function open_preview_modal(qa_id) {
            currentPreviewId = qa_id;
            var ts = new Date().getTime();
            var url = './quote_view.php?qa_id=' + qa_id + '&preview=1&_ts=' + ts;

            var frame = document.getElementById('preview_frame');

            // Reset Load State
            isPreviewLoaded = false;

            // Setup Onload Handler
            frame.onload = function () {
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
        document.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                if (!document.getElementById('preview_modal').classList.contains('hidden')) {
                    close_preview_modal();
                }
            }
        });

        // Auto-Open Logic (from localStorage)
        document.addEventListener("DOMContentLoaded", function () {
            var autoId = localStorage.getItem('auto_open_preview');
            if (autoId) {
                localStorage.removeItem('auto_open_preview');
                // Check if current page ID matches (safety)
                var currentId = document.querySelector('input[name="qa_id"]');
                if (currentId && currentId.value == autoId) {
                    // Open Modal
                    open_preview_modal(autoId);
                }
            }
        });

        // 2. Dirty Check & Back to List
        var isDirty = false;
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('form[name="fquote"]');
            if (form) {
                // Track changes
                form.addEventListener('change', function () { isDirty = true; });
                form.addEventListener('input', function () { isDirty = true; });

                // Allow submit
                form.addEventListener('submit', function () { isDirty = false; });
            }

            // Initial load reset (if saved=1, logic handles it)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('saved') === '1') {
                isDirty = false;
            }

            // Load Initial List (Ajax) if in List View (fsearch exists)
            if (document.getElementById('fsearch')) {
                load_list(); // Initial Load
            }
        });

        // Generic Navigation with Safe Check
        var pendingNavUrl = null;

        window.navigateToPage = function (url) {
            if (isDirty) {
                pendingNavUrl = url;
                document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
            } else {
                location.href = url;
            }
        }

        window.go_list_safe = function () {
            navigateToPage('./admin_quote.php');
        }

        // Custom Back Confirm Modal Logic
        function close_back_confirm() {
            document.getElementById('back_confirm_modal_safe').classList.add('hidden');
            pendingNavUrl = null;
        }

        function confirm_back_save() {
            var form = document.querySelector('form[name="fquote"]');

            // Remove existing redirects if any
            var oldInput = form.querySelector('input[name="redirect_to_list"]');
            if (oldInput) oldInput.remove();

            var oldUrlInput = form.querySelector('input[name="redirect_url"]');
            if (oldUrlInput) oldUrlInput.remove();

            if (pendingNavUrl) {
                if (pendingNavUrl.includes('admin_quote.php') && !pendingNavUrl.includes('w=form')) {
                    // List View
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'redirect_to_list';
                    input.value = '1';
                    form.appendChild(input);
                } else {
                    // Generic URL
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'redirect_url';
                    input.value = pendingNavUrl;
                    form.appendChild(input);
                }
            }

            form.submit();
            close_back_confirm();
        }

        function confirm_back_nosave() {
            document.getElementById('back_confirm_modal_safe').classList.add('hidden');
            if (pendingNavUrl) {
                location.href = pendingNavUrl;
            } else {
                location.href = './admin_quote.php'; // Fallback
            }
        }

        // 3. AJAX Load List (Controller)
        var currentMonth = <?php echo json_encode($f_month ?? ''); ?>;
        var currentPage = 1;

        window.load_list = function (month, page) {
            if (typeof month !== 'undefined') {
                currentMonth = month;
                // Also update input for form submission compatibility if needed
                if (document.getElementById('f_month')) document.getElementById('f_month').value = month;
            }
            if (typeof page !== 'undefined') currentPage = page;

            var f_year_el = document.getElementById('f_year');
            var q_el = document.getElementById('q');

            if (!f_year_el) return; // Not in list view

            var f_year = f_year_el.value;
            var q = q_el ? q_el.value : '';

            // [NEW] Status Filter
            var f_status_el = document.getElementById('f_status');
            var f_status = f_status_el ? f_status_el.value : '';

            var url = './admin_quote.php?w=ajax_list';
            url += '&f_year=' + encodeURIComponent(f_year);
            url += '&f_month=' + encodeURIComponent(currentMonth);
            url += '&f_status=' + encodeURIComponent(f_status);
            url += '&q=' + encodeURIComponent(q);
            url += '&page=' + currentPage;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    var tbody = document.getElementById('list_tbody');
                    if (tbody) tbody.innerHTML = data.html;

                    var paging = document.getElementById('pagination_container');
                    if (paging) paging.innerHTML = data.paging;

                    update_tab_active(currentMonth);
                })
                .catch(error => console.error('Error:', error));

            return false;
        }

        function update_tab_active(month) {
            // month: '01', '02', or '' (all)
            // We look for elements inside #month_tabs
            var container = document.getElementById('month_tabs');
            if (!container) return;

            // Improve selector: find all buttons with onclick containing load_list
            var buttons = container.querySelectorAll('button');
            buttons.forEach(btn => {
                var onclick = btn.getAttribute('onclick');
                if (onclick && onclick.indexOf('load_list') !== -1) {
                    // Check if this button is the target
                    var isTarget = false;
                    if (month === '' && onclick.indexOf("''") !== -1) isTarget = true;
                    else if (month && onclick.indexOf("'" + month + "'") !== -1) isTarget = true;

                    if (isTarget) {
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
    <!-- Custom Confirm Modal (3 Buttons: Save/Don't Save/Cancel) -->
    <div id="back_confirm_modal_safe" class="hidden fixed inset-0 z-[99999]" role="dialog" aria-modal="true">

        <!-- Flex Container for Centering -->
        <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">

            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                style="z-index: 99998 !important; pointer-events: auto;" onclick="close_back_confirm()">
            </div>

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
                <!-- Script moved inside div in previous step, keeping it here -->
                <script>
                    // Move modal to body to ensure it's on top of everything
                    document.addEventListener('DOMContentLoaded', function () {
                        var modal = document.getElementById('back_confirm_modal_safe');
                        if (modal) {
                            document.body.appendChild(modal);
                        }
                    });
                </script>
            </div>
        </div>
    </div>

    <!-- Quote Summary Modal -->
    <div id="summary_modal" class="hidden fixed inset-0 z-[10000] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="close_summary_modal()">
            </div>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        견적서 요약
                    </h3>
                    <button onclick="close_summary_modal()"
                        class="text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
                </div>

                <div class="bg-white px-6 py-5" id="summary_content">
                    <div class="text-center text-gray-500 py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto">
                        </div>
                        <p class="mt-4">로딩 중...</p>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2">
                    <button type="button" onclick="close_summary_modal()"
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-300">닫기</button>
                    <button type="button" onclick="open_full_quote()"
                        class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-orange-700">전체
                        보기</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var currentSummaryId = null;

        function show_quote_summary(qa_id) {
            currentSummaryId = qa_id;
            document.getElementById('summary_modal').classList.remove('hidden');

            // Load summary via AJAX
            fetch('./admin_quote.php?w=ajax_summary&qa_id=' + qa_id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var html = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">견적번호</div>
                                <div class="font-bold text-gray-900">${data.quote.qa_code}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">작성일</div>
                                <div class="font-bold text-gray-900">${data.quote.qa_datetime}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">고객명</div>
                                <div class="font-bold text-gray-900">${data.quote.qa_client_name}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">연락처</div>
                                <div class="font-bold text-gray-900">${data.quote.qa_client_hp || '-'}</div>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="text-xs text-gray-500 mb-2">견적명</div>
                            <div class="font-bold text-gray-900">${data.quote.qa_subject || '(제목없음)'}</div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="text-xs text-gray-500 mb-2">품목 목록 (${data.items.length}개)</div>
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                ${data.items.map((item, idx) => `
                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded">
                                        <div>
                                            <span class="text-xs text-gray-400">${idx + 1}.</span>
                                            <span class="font-medium text-gray-900 ml-2">${item.qi_item}</span>
                                            ${item.qi_spec ? `<span class="text-xs text-gray-500 ml-2">(${item.qi_spec})</span>` : ''}
                                        </div>
                                        <div class="text-sm font-bold text-gray-700">${item.qi_amount_fmt}원</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        
                        <div class="border-t pt-4 bg-orange-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">공급가액</span>
                                <span class="font-bold">${data.quote.qa_price_supply_fmt}원</span>
                            </div>
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm text-gray-600">부가세(10%)</span>
                                <span class="font-bold">${data.quote.qa_price_vat_fmt}원</span>
                            </div>
                            <div class="flex justify-between items-center text-lg border-t pt-3">
                                <span class="font-bold text-gray-900">합계</span>
                                <span class="font-extrabold text-orange-600">${data.quote.qa_price_total_fmt}원</span>
                            </div>
                        </div>
                    </div>
                `;
                        document.getElementById('summary_content').innerHTML = html;
                    } else {
                        document.getElementById('summary_content').innerHTML = '<div class="text-center text-red-500 py-8">정보를 불러올 수 없습니다.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('summary_content').innerHTML = '<div class="text-center text-red-500 py-8">오류가 발생했습니다.</div>';
                });
        }

        function close_summary_modal() {
            document.getElementById('summary_modal').classList.add('hidden');
            currentSummaryId = null;
        }

        function open_full_quote() {
            if (currentSummaryId) {
                location.href = '?w=form&qa_id=' + currentSummaryId;
            }
        }
    </script>

    <script>
        // Safe binding for Add Item Row button
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('btn_add_item_row');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Ensure function exists
                    if (typeof add_item_row === 'function') {
                        add_item_row();
                    } else {
                        console.error('add_item_row function not found');
                        alert('기능을 불러오는 중 오류가 발생했습니다. 페이지를 새로고침해주세요.');
                    }
                });
            }

            // Ensure initial row exists if empty
            setTimeout(function () {
                var container = document.getElementById('item_list_container');
                if (container && container.children.length === 0 && typeof add_item_row === 'function') {
                    add_item_row(null, false);
                }
            }, 500);
        });
    </script>

</div>
</div>
</div>

<!-- Customer Registration Choice Modal -->
<dialog id="customer_choice_modal"
    class="modal fixed inset-0 z-[99999] items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    onclick="if(event.target === this) this.close()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all scale-100"
        onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-orange-500 p-6 text-white text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-20 transform translate-x-1/3 -translate-y-1/3">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold relative z-10">신규 등록 작업 선택</h3>
            <p class="text-orange-100 text-sm mt-1 relative z-10">진행할 작업을 선택해주세요</p>
        </div>

        <!-- Options -->
        <div class="p-6 space-y-3">
            <button type="button" onclick="start_new_work(1)"
                class="w-full group flex items-center p-4 rounded-xl border-2 border-slate-100 hover:border-orange-500 hover:bg-orange-50 transition-all duration-200">
                <div
                    class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <span class="font-bold">1</span>
                </div>
                <div class="ml-4 text-left">
                    <div class="font-bold text-slate-700 group-hover:text-orange-700">현장 실측 등록</div>
                    <div class="text-xs text-slate-500">현장 사진 및 실측 데이터 입력</div>
                </div>
            </button>

            <button type="button" onclick="start_new_work(2)"
                class="w-full group flex items-center p-4 rounded-xl border-2 border-slate-100 hover:border-orange-500 hover:bg-orange-50 transition-all duration-200">
                <div
                    class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <span class="font-bold">2</span>
                </div>
                <div class="ml-4 text-left">
                    <div class="font-bold text-slate-700 group-hover:text-orange-700">견적서 작성</div>
                    <div class="text-xs text-slate-500">견적 내역 및 금액 산출</div>
                </div>
            </button>

            <!-- TODO: admin_customer.php 경로 확인 필요 -->
            <button type="button" onclick="start_new_work(3)"
                class="w-full group flex items-center p-4 rounded-xl border-2 border-slate-100 hover:border-orange-500 hover:bg-orange-50 transition-all duration-200">
                <div
                    class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <span class="font-bold">3</span>
                </div>
                <div class="ml-4 text-left">
                    <div class="font-bold text-slate-700 group-hover:text-orange-700">고객 정보 등록</div>
                    <div class="text-xs text-slate-500">기초 고객 데이터 등록</div>
                </div>
            </button>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-slate-50 text-center border-t border-slate-100">
            <form method="dialog">
                <button
                    class="text-slate-500 hover:text-slate-800 font-medium text-sm px-6 py-2 rounded-lg hover:bg-slate-200 transition">
                    취소
                </button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function start_new_work(step) {
        // Create new ID first
        const createData = new FormData();
        createData.append('w', 'ajax_create_quote');

        fetch('./admin_quote.php', { method: 'POST', body: createData })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.qa_id) {
                    let url = '';
                    switch (step) {
                        case 1: url = './admin_quote_step1.php?qa_id=' + data.qa_id; break;
                        case 2: url = './admin_quote.php?w=form&qa_id=' + data.qa_id; break;
                        case 3: url = './admin_customer.php?w=form&qa_id=' + data.qa_id; break;
                    }
                    if (url) location.href = url;
                } else {
                    alert('새 작업 생성에 실패했습니다: ' + (data.message || 'Unknown Error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('통신 오류가 발생했습니다.');
            });
    }
</script>

<script>
    // [FIX] Global Preview Modal Functions - Available in both list and form views
    var currentPreviewId = null;
    var isPreviewLoaded = false;

    window.open_preview_modal = function (qa_id) {
        if (!qa_id || qa_id == 0) {
            alert('견적서를 먼저 저장해주세요.');
            return;
        }

        currentPreviewId = qa_id;
        var ts = new Date().getTime();
        var url = './quote_view.php?qa_id=' + qa_id + '&preview=1&_ts=' + ts;

        // Check if modal exists (form view)
        var modal = document.getElementById('preview_modal');
        if (modal) {
            var frame = document.getElementById('preview_frame');

            // Reset Load State
            isPreviewLoaded = false;

            // Setup Onload Handler
            frame.onload = function () {
                isPreviewLoaded = true;
                console.log("Preview Iframe Loaded");
            };

            frame.src = url;
            modal.classList.remove('hidden');
        } else {
            // Fallback: Open in new window (list view)
            window.open(url, '_blank', 'width=1200,height=900,scrollbars=yes');
        }
    };

    window.close_preview_modal = function () {
        var modal = document.getElementById('preview_modal');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('preview_frame').src = '';
            currentPreviewId = null;
        }
    };
</script>

<script>
    // [Nuclear Option] Force remove sidebar by text content
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const w = urlParams.get('w');

        if (!w) {
            // 1. ID/Class Removal
            const sidebar = document.getElementById('quote_sidebar_panel');
            if (sidebar) sidebar.remove();

            document.querySelectorAll('.lg\\:col-span-3.sticky').forEach(el => el.remove());

            // 2. Text Content Removal (최후의 수단)
            // '공급가액', '부가세', '총 금액' 텍스트를 포함하는 div를 찾아서 숨김
            const allDivs = document.getElementsByTagName('div');
            for (let i = 0; i < allDivs.length; i++) {
                const div = allDivs[i];
                if (div.offsetParent !== null) { // visible elements only
                    const text = div.innerText || div.textContent;
                    if (text.includes('공급가액') && text.includes('부가세') && text.includes('총 금액')) {
                        // 너무 상위 요소(body 등)는 지우면 안됨. 적당한 크기의 컨테이너인지 확인
                        if (div.classList.contains('bg-white') || div.classList.contains('shadow-lg')) {
                            div.style.display = 'none';
                            div.setAttribute('data-force-hidden', 'true');
                            console.log('Sidebar found by text and hidden:', div);
                        }
                    }
                }
            }

            // 3. Modal Force Close
            document.querySelectorAll('dialog[open]').forEach(d => d.close());
        }
    });
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
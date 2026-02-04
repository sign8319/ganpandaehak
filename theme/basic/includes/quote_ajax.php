<?php
if (!defined('_GNUBOARD_'))
    exit;

/**
 * [COMMON AJAX HANDLER]
 * 견적 시스템 1, 2, 3단계 공통 AJAX 처리
 *
 * [보안] 모든 핸들러는 관리자 전용 (Admin-only)
 * 비관리자 접근 시 통일된 JSON 응답: {"success":false, "error":true, "message":"권한 없음"}
 */

/**
 * [Helper] 관리자 권한 거부 응답 (통일된 형식)
 * - Content-Type 헤더 포함
 * - 프론트 호환을 위해 success:false + error:true 동시 포함
 */
function deny_ajax_no_admin()
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => true, 'message' => '권한 없음'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. AJAX Summary (견적 상세 정보 조회) - Admin Only
if ($w == 'ajax_summary') {
    if (!$is_admin) deny_ajax_no_admin();
    $qa_id = isset($_GET['qa_id']) ? (int) $_GET['qa_id'] : 0;
    if ($qa_id) {
        $quote = sql_fetch("select * from g5_quote where qa_id = '$qa_id'");
        if ($quote) {
            $items = [];
            $result = sql_query("select * from g5_quote_item where qa_id = '$qa_id' order by qi_index, qi_id");
            while ($row = sql_fetch_array($result)) {
                $row['qi_amount_fmt'] = number_format($row['qi_amount']);
                $items[] = $row;
            }
            $quote['qa_price_supply_fmt'] = number_format($quote['qa_price_supply']);
            $quote['qa_price_vat_fmt'] = number_format($quote['qa_price_vat']);
            $quote['qa_price_total_fmt'] = number_format($quote['qa_price_total']);
            $quote['qa_datetime'] = date('Y-m-d H:i', strtotime($quote['qa_datetime']));
            echo json_encode(['success' => true, 'quote' => $quote, 'items' => $items], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// 2. AJAX Create Quote (첫 입력 시 ID 생성) - Admin Only
if ($w == 'ajax_create_quote') {
    if (!$is_admin) deny_ajax_no_admin();
    $qa_subject = '';
    // [보안] 중복 방지 헬퍼 함수 사용 (Race Condition 방지)
    $qa_code = generate_unique_quote_code_short();
    $sql = " INSERT INTO g5_quote SET
                qa_status = 'draft',
                qa_code = '$qa_code',
                qa_subject = '$qa_subject',
                qa_tax_company_name = '" . clean_sql($_POST['qa_tax_company_name'] ?? '') . "',
                qa_client_name = '" . clean_sql($_POST['qa_client_name'] ?? '') . "',
                qa_client_hp = '" . clean_sql($_POST['qa_client_hp'] ?? '') . "',
                qa_client_email = '" . clean_sql($_POST['qa_client_email'] ?? '') . "',
                qa_customer_id = '" . (int) ($_POST['qa_customer_id'] ?? 0) . "',
                qa_memo_user = '',
                mb_id = '{$member['mb_id']}',
                qa_datetime = '" . G5_TIME_YMDHIS . "' ";
    $result = sql_query($sql, false);
    if (!$result) {
        $error_msg = function_exists('sql_error_info') ? sql_error_info() : 'Unknown SQL Error';
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $error_msg]);
    } else {
        $new_id = sql_insert_id();
        echo json_encode(['success' => true, 'qa_id' => $new_id]);
    }
    exit;
}

// 3. AJAX Save Header (실시간 저장) - Admin Only
if ($w == 'ajax_save_header') {
    if (!$is_admin) deny_ajax_no_admin();
    $qa_id = (int) $_POST['qa_id'];
    if (!$qa_id) {
        echo json_encode(['success' => false]);
        exit;
    }

    $quote = sql_fetch(" SELECT * FROM g5_quote WHERE qa_id = '$qa_id' ");
    if (!$quote) {
        echo json_encode(['success' => false, 'message' => 'Quote not found']);
        exit;
    }

    $upd = [
        'qa_subject' => trim($_POST['qa_subject'] ?? $quote['qa_subject']),
        'qa_tax_company_name' => trim($_POST['qa_tax_company_name'] ?? $quote['qa_tax_company_name']),
        'qa_client_name' => trim($_POST['qa_client_name'] ?? $quote['qa_client_name']),
        'qa_client_hp' => trim($_POST['qa_client_hp'] ?? $quote['qa_client_hp']),
        'qa_client_email' => trim($_POST['qa_client_email'] ?? $quote['qa_client_email']),
        'qa_client_addr' => trim($_POST['qa_client_addr'] ?? $quote['qa_client_addr']),
        'qa_client_addr2' => trim($_POST['qa_client_addr2'] ?? $quote['qa_client_addr2']),
    ];

    // Status logic (from admin_quote.php)
    if ($quote['qa_status'] == 'draft') {
        $has_data = false;
        foreach ($upd as $key => $value) {
            if ($key != 'qa_deposit' && !empty($value)) {
                $has_data = true;
                break;
            }
        }
        if ($has_data)
            $upd['qa_status'] = 'active';
    } else {
        if (isset($_POST['qa_status']))
            $upd['qa_status'] = trim($_POST['qa_status']);
    }

    // Sync Customer Tax Info
    if (isset($_POST['qa_customer_id']) && $_POST['qa_customer_id']) {
        $cust_id = (int) $_POST['qa_customer_id'];
        $upd['qa_customer_id'] = $cust_id;
        $cust = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$cust_id' ");
        if ($cust && (($quote['qa_customer_id'] ?? 0) != $cust_id || empty($quote['qa_tax_company_name']))) {
            $tax_fields = [
                'qa_tax_company_name' => 'customer_company',
                'qa_tax_biz_num' => 'tax_biz_num',
                'qa_tax_ceo_name' => 'tax_ceo_name',
                'qa_tax_addr' => 'tax_addr',
                'qa_tax_email' => 'tax_email',
                'qa_tax_condition' => 'tax_condition',
                'qa_tax_sector' => 'tax_sector',
                'qa_tax_type' => 'tax_type',
                'qa_tax_item_name' => 'tax_item_name',
                'qa_payment_method' => 'payment_method'
            ];
            foreach ($tax_fields as $q_col => $c_col) {
                if (!empty($cust[$c_col]))
                    $upd[$q_col] = $cust[$c_col];
            }
        }

        // [NEW] Sync Company Name to Customer (Reverse Sync)
        $new_company = $upd['qa_tax_company_name'] ?? $quote['qa_tax_company_name'];
        if ($cust && $new_company && $cust['customer_company'] != $new_company) {
            // Log history
            $old_val = $cust['customer_company'] ? $cust['customer_company'] : '(없음)';
            $memo_append = "\\n[" . date('Y-m-d') . "] 상호변경(견적수정): $old_val -> $new_company";
            $safe_memo = sql_real_escape_string($memo_append);
            $safe_comp = sql_real_escape_string($new_company);

            // Update Customer
            sql_query(" UPDATE g5_customer SET 
                         customer_company = '$safe_comp', 
                         customer_memo = CONCAT(customer_memo, '$safe_memo'),
                         updated_at = '" . G5_TIME_YMDHIS . "' 
                         WHERE customer_id = '$cust_id' ");
        }
    }

    $sql_set = "";
    foreach ($upd as $k => $v) {
        $val = addslashes($v);
        $sql_set .= " , $k = '$val' ";
    }
    sql_query(" UPDATE g5_quote SET " . trim($sql_set, ',') . " WHERE qa_id = '$qa_id' ");
    echo json_encode(['success' => true]);
    exit;
}

// 4. AJAX Search Customer - Admin Only
if ($w == 'ajax_search') {
    if (!$is_admin) deny_ajax_no_admin();
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $customers = [];
    if (mb_strlen($keyword) >= 2) { // 2자 이상일 때만 검색
        $keyword_safe = addslashes($keyword);
        $sql = " SELECT c.*, s.status_step FROM g5_customer c 
                 LEFT JOIN g5_customer_status s ON c.customer_id = s.customer_id 
                 WHERE c.customer_name LIKE '%$keyword_safe%' 
                    OR c.customer_manager LIKE '%$keyword_safe%' 
                    OR c.customer_company LIKE '%$keyword_safe%'
                    OR c.customer_hp LIKE '%$keyword_safe%'
                 ORDER BY c.customer_name LIMIT 20 ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $customers[] = $row;
        }
    }
    echo json_encode(['customers' => $customers], JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. AJAX Delete Quote (작성 취소/삭제) - Admin Only
if ($w == 'ajax_delete_quote') {
    // Clear any previous output buffer to ensure clean JSON
    if (ob_get_length())
        ob_clean();

    // [보안] 관리자 권한 체크 (최우선)
    if (!$is_admin) deny_ajax_no_admin();

    $qa_id = isset($_POST['qa_id']) ? (int) $_POST['qa_id'] : 0;
    $delete_customer = isset($_POST['delete_customer']) ? (int) $_POST['delete_customer'] : 0;

    // [Skeleton Data Support]
    // If we have a qa_id, even if the quote record is missing, we must clean up g5_work and measures.
    if (!$qa_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    // [NEW] Get Customer ID before deletion if requested
    $customer_id_to_delete = 0;
    if ($delete_customer) {
        $q_info = sql_fetch(" SELECT qa_customer_id FROM g5_quote WHERE qa_id = '$qa_id' ");
        if ($q_info && $q_info['qa_customer_id']) {
            $customer_id_to_delete = $q_info['qa_customer_id'];
        }
    }

    // 1. Delete Quote Record
    sql_query(" DELETE FROM g5_quote WHERE qa_id = '$qa_id' ");

    // 2. Delete Items & Measures
    sql_query(" DELETE FROM g5_quote_item WHERE qa_id = '$qa_id' ");
    sql_query(" DELETE FROM g5_quote_measure WHERE qa_id = '$qa_id' ");

    // 3. Delete Work (Skeleton / Linked Data)
    // This cleans up the "Only Work Exists" case if proper foreign key or qa_id link exists
    sql_query(" DELETE FROM g5_work WHERE qa_id = '$qa_id' ");

    // [NEW] Delete Customer Data if requested
    if ($customer_id_to_delete) {
        sql_query(" DELETE FROM g5_customer WHERE customer_id = '$customer_id_to_delete' ");
        sql_query(" DELETE FROM g5_customer_status WHERE customer_id = '$customer_id_to_delete' ");
        sql_query(" DELETE FROM g5_customer_status_log WHERE customer_id = '$customer_id_to_delete' ");
        sql_query(" DELETE FROM g5_customer_share_link WHERE customer_id = '$customer_id_to_delete' ");

        // Also cleanup other works associated with this customer? 
        // User didn't specify, but for safety usually keeping other works is safer unless they asked "Delete customer".
        // "연관된 고객 정보도 함께 삭제" -> implies the customer entity.
        // We will leave other works linked to this customer ID (now orphaned) or set them to 0?
        // Let's set other works' customer_id to 0 to be safe.
        sql_query(" UPDATE g5_work SET customer_id = 0 WHERE customer_id = '$customer_id_to_delete' AND qa_id != '$qa_id' ");
        sql_query(" UPDATE g5_quote SET qa_customer_id = 0 WHERE qa_customer_id = '$customer_id_to_delete' AND qa_id != '$qa_id' ");
    }

    // Log if needed (Optional)
    // sql_query(" INSERT INTO g5_quote_log ... ");

    echo json_encode(['success' => true]);
    exit;
}

// 6. AJAX List Loading (Month/Year Filter) - Admin Only
if ($w == 'ajax_list') {
    if (!$is_admin) deny_ajax_no_admin();
    header('Content-Type: application/json; charset=utf-8');

    $sql_search = " where 1 ";

    // [DRAFT SYSTEM] Hide draft quotes by default
    $show_drafts = isset($_GET['show_drafts']) ? $_GET['show_drafts'] : '0';
    if ($show_drafts != '1') {
        $sql_search .= " and w.work_status != 'draft' ";
    }

    // 1. Search Query
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if ($q) {
        $q_safe = addslashes($q);
        $sql_search .= " and (w.work_subject like '%$q_safe%' or q.qa_client_name like '%$q_safe%' or q.qa_tax_company_name like '%$q_safe%' or c.customer_name like '%$q_safe%' or c.customer_company like '%$q_safe%') ";
    }

    // 2. Year Filter
    $f_year = isset($_GET['f_year']) ? trim($_GET['f_year']) : '';
    if ($f_year) {
        $f_year_safe = (int) $f_year;
        $sql_search .= " and year(w.created_at) = '$f_year_safe' ";
    }

    // 3. Month Filter (01, 02...)
    $f_month = isset($_GET['f_month']) ? trim($_GET['f_month']) : '';
    // Fix: Handle 'all' strictly
    if ($f_month && $f_month !== 'all') {
        $f_month_safe = str_pad((int) $f_month, 2, '0', STR_PAD_LEFT);
        $sql_search .= " and month(w.created_at) = '$f_month_safe' ";
    }

    // 4. Status Filter
    $f_status = isset($_GET['f_status']) ? trim($_GET['f_status']) : '';
    if ($f_status) {
        $f_status_safe = addslashes($f_status);
        $sql_search .= " and w.work_status = '$f_status_safe' ";
    }

    // 5. Pagination
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1)
        $page = 1;
    $page_rows = 30;

    // Count Total
    $sql_count = " SELECT count(*) as cnt 
                   FROM g5_work w 
                   LEFT JOIN g5_quote q ON w.qa_id = q.qa_id 
                   LEFT JOIN g5_customer c ON w.customer_id = c.customer_id 
                   $sql_search ";
    $row_count = sql_fetch($sql_count);
    $total_count = $row_count['cnt'];
    $total_page = ceil($total_count / $page_rows);

    $from_record = ($page - 1) * $page_rows;

    // Fetch Data
    $sql = " SELECT w.*, 
                    q.qa_id, q.qa_status, q.qa_tax_company_name, q.qa_client_hp, q.qa_client_addr, q.qa_client_addr2, q.qa_price_total,
                    c.customer_company, c.customer_name, c.customer_hp, c.customer_addr
             FROM g5_work w 
             LEFT JOIN g5_quote q ON w.qa_id = q.qa_id 
             LEFT JOIN g5_customer c ON w.customer_id = c.customer_id
             $sql_search 
             ORDER BY w.work_id DESC 
             LIMIT $from_record, $page_rows ";
    $result = sql_query($sql);

    ob_start();
    $list_count = 0;
    while ($row = sql_fetch_array($result)) {
        $list_count++;
        // Data Normalization (Prefer Quote data, fallback to Customer data, then work_subject)
        $disp_status = $row['work_status'];
        // [MOD] Fallback Display Logic: customer_company > customer_name > '상호 미지정'
        $disp_company = $row['customer_company'];
        if (!$disp_company || $disp_company == '')
            $disp_company = $row['customer_name'];
        if (!$disp_company || $disp_company == '')
            $disp_company = '<span class="text-gray-400 font-normal">상호 미지정</span>';
        $disp_hp = $row['qa_client_hp'] ? $row['qa_client_hp'] : $row['customer_hp'];
        $disp_addr = $row['qa_client_addr'] ? $row['qa_client_addr'] : $row['customer_addr'];

        // Status Color
        $color = get_status_color($disp_status);

        $status_bg = "bg-{$color}-100 text-{$color}-700";
        if ($color == 'gray')
            $status_bg = "bg-gray-100 text-gray-600";

        // Link Logic
        $link_url = 'javascript:void(0);';
        if ($row['qa_id']) {
            $link_url = "?w=form&qa_id={$row['qa_id']}";
        } elseif ($row['customer_id']) {
            $link_url = "./admin_customer.php?w=u&customer_id={$row['customer_id']}";
        }

        // Checkbox value: Use work_id as the primary identifier for the list
        $chk_val = $row['work_id'];

        // Date Display
        $date_str = date('m.d', strtotime($row['created_at']));
        ?>
        <tr class="hover:bg-orange-50 transition border-b border-gray-100"
            onclick="if(!event.target.closest('input') && !event.target.closest('a') && !event.target.closest('button')) location.href='<?php echo $link_url; ?>'">
            <td class="p-3 text-center">
                <input type="checkbox" name="chk_qa_id[]" value="<?php echo $chk_val; ?>"
                    class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
            </td>
            <td class="p-3">
                <div class="leading-tight">
                    <div class="font-bold text-gray-800 text-sm">
                        <?php echo $date_str; ?>
                    </div>
                </div>
            </td>
            <td class="p-3 text-center">
                <?php if ($row['qa_id']) { ?>
                    <button type="button" onclick="show_quote_summary(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                        class="text-gray-400 hover:text-orange-500 transition" title="빠른 요약 보기">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                <?php } ?>
            </td>
            <td class="p-3 text-center">
                <span
                    class="inline-block px-2 py-1 rounded-sm text-xs font-bold bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-700">
                    <?php echo $disp_status; ?>
                </span>
            </td>
            <td class="p-3 font-bold text-sm text-gray-800">
                <?php echo $disp_company; ?>
            </td>
            <td class="p-3 text-xs text-gray-600 font-mono">
                <?php echo $disp_hp; ?>
            </td>
            <td class="p-3 text-xs text-gray-500 truncate max-w-[150px]" title="<?php echo $disp_addr; ?>">
                <?php echo $disp_addr; ?>
            </td>
            <td class="p-3 text-right font-bold text-orange-600 text-sm">
                <?php echo $row['qa_price_total'] ? number_format($row['qa_price_total']) : '-'; ?>
            </td>
            <td class="p-3 text-center">
                <?php if ($row['qa_id']) { ?>
                    <button type="button" onclick="open_preview_modal(<?php echo $row['qa_id']; ?>); event.stopPropagation();"
                        class="bg-white border hover:bg-gray-50 text-gray-700 px-2 py-1 rounded text-xs shadow-sm inline-flex items-center gap-1 transition whitespace-nowrap">
                        <span>👁️</span> 견적미리보기
                    </button>
                <?php } ?>
            </td>
            <td class="p-3 text-center">
                <a href="?q=<?php echo urlencode($disp_company); ?>" onclick="event.stopPropagation();"
                    class="inline-block px-2 py-1 border border-gray-300 rounded text-xs text-gray-600 hover:bg-gray-100 whitespace-nowrap">
                    연결된
                </a>
            </td>
        </tr>
    <?php }

    if ($list_count == 0)
        echo '<div class="col-span-full py-20 text-center bg-white rounded-xl shadow-soft border border-gray-100">
                <div class="text-4xl mb-4 text-gray-200"><i class="fas fa-folder-open"></i></div>
                <p class="text-gray-400 font-medium">표시할 견적 내역이 없습니다.</p>
              </div>';

    $html_content = ob_get_clean();

    // Pagination HTML
    $paging_html = '';
    if ($total_page > 1) {
        $paging_html .= '<div class="flex justify-center gap-1">';

        $start_page = (((int) (($page - 1) / 10)) * 10) + 1;
        $end_page = $start_page + 10 - 1;
        if ($end_page >= $total_page)
            $end_page = $total_page;

        if ($start_page > 1) {
            $prev_page = $start_page - 1;
            $paging_html .= '<button type="button" onclick="load_list(\'' . $f_month . '\', ' . $prev_page . ')" class="px-3 py-1 border rounded hover:bg-gray-100 text-sm">이전</button>';
        }

        for ($k = $start_page; $k <= $end_page; $k++) {
            $active_cls = ($page == $k) ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50';
            $paging_html .= '<button type="button" onclick="load_list(\'' . $f_month . '\', ' . $k . ')" class="px-3 py-1 border rounded text-sm font-bold ' . $active_cls . '">' . $k . '</button>';
        }

        if ($total_page > $end_page) {
            $next_page = $end_page + 1;
            $paging_html .= '<button type="button" onclick="load_list(\'' . $f_month . '\', ' . $next_page . ')" class="px-3 py-1 border rounded hover:bg-gray-100 text-sm">다음</button>';
        }

        $paging_html .= '</div>';
    }

    // JSON Response
    echo json_encode([
        'html' => $html_content,
        'paging' => $paging_html,
        'page' => $page,
        'total' => $total_count
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

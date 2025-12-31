<?php
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// -----------------------------------------------------------------------------
// 0. DB Initialization (Auto-create tables if not exists)
// -----------------------------------------------------------------------------
if(!sql_query(" DESCRIBE g5_estimate ", false)) {
    $sql_header = "
        CREATE TABLE IF NOT EXISTS `g5_estimate` (
          `es_id` int(11) NOT NULL AUTO_INCREMENT,
          `es_code` varchar(20) NOT NULL DEFAULT '',
          `es_company` varchar(50) NOT NULL DEFAULT '',
          `es_email` varchar(100) NOT NULL DEFAULT '',
          `es_price` int(11) NOT NULL DEFAULT 0,
          `es_vat` int(11) NOT NULL DEFAULT 0,
          `es_total` int(11) NOT NULL DEFAULT 0,
          `es_status` varchar(20) NOT NULL DEFAULT '대기',
          `es_datetime` datetime NOT NULL,
          PRIMARY KEY (`es_id`),
          UNIQUE KEY `es_code` (`es_code`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_header, true);

    $sql_items = "
        CREATE TABLE IF NOT EXISTS `g5_estimate_items` (
          `ei_id` int(11) NOT NULL AUTO_INCREMENT,
          `es_id` int(11) NOT NULL DEFAULT 0,
          `ei_item` varchar(255) NOT NULL DEFAULT '',
          `ei_spec` varchar(255) NOT NULL DEFAULT '',
          `ei_qty` int(11) NOT NULL DEFAULT 0,
          `ei_price` int(11) NOT NULL DEFAULT 0,
          `ei_amount` int(11) NOT NULL DEFAULT 0,
          PRIMARY KEY (`ei_id`),
          KEY `es_id` (`es_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";
    sql_query($sql_items, true);
}


// -----------------------------------------------------------------------------
// 1. Backend Logic
// -----------------------------------------------------------------------------
$w = isset($_REQUEST['w']) ? trim($_REQUEST['w']) : '';
$es_id = isset($_REQUEST['es_id']) ? (int) $_REQUEST['es_id'] : 0;

// Status Toggle (AJAX-like redirect)
if ($w == 'status' && $es_id) {
    $st = isset($_REQUEST['st']) ? trim($_REQUEST['st']) : '대기';
    sql_query(" update g5_estimate set es_status = '{$st}' where es_id = '{$es_id}' ");
    goto_url(G5_THEME_URL . '/admin_estimate.php');
}

// Save Estimate
if ($w == 'submit') {
    $es_company = isset($_POST['es_company']) ? trim($_POST['es_company']) : '';
    $es_email = isset($_POST['es_email']) ? trim($_POST['es_email']) : '';
    $es_code = isset($_POST['es_code']) ? trim($_POST['es_code']) : ''; // For update

    // Arrays
    $ei_item = $_POST['ei_item'];
    $ei_spec = $_POST['ei_spec'];
    $ei_qty = $_POST['ei_qty'];
    $ei_price = $_POST['ei_price'];

    // Calculate Totals
    $total_supply = 0;
    $items_data = [];
    $count = count($ei_item);

    for ($i = 0; $i < $count; $i++) {
        if (!trim($ei_item[$i]))
            continue;
        $qty = (int) $ei_qty[$i];
        $price = (int) $ei_price[$i];
        $amount = $qty * $price;
        $total_supply += $amount;

        $items_data[] = [
            'item' => $ei_item[$i],
            'spec' => $ei_spec[$i],
            'qty' => $qty,
            'price' => $price,
            'amount' => $amount
        ];
    }

    $es_vat = floor($total_supply * 0.1);
    $es_total = $total_supply + $es_vat;

    // INSERT or UPDATE Header
    if ($es_id > 0) {
        // Update
        $sql = " update g5_estimate
                    set es_company = '{$es_company}',
                        es_email = '{$es_email}',
                        es_price = '{$total_supply}',
                        es_vat = '{$es_vat}',
                        es_total = '{$es_total}'
                  where es_id = '{$es_id}' ";
        sql_query($sql);

        // Delete old items
        sql_query(" delete from g5_estimate_items where es_id = '{$es_id}' ");
        $target_id = $es_id;
        $target_code = $es_code;
    } else {
        // Insert
        // Generate Code: Q-YYYYMMDD-XXX
        $today_prefix = 'Q-' . date('Ymd') . '-';
        $row = sql_fetch(" select count(*) as cnt from g5_estimate where es_code like '{$today_prefix}%' ");
        $seq = $row['cnt'] + 1;
        $target_code = $today_prefix . sprintf('%03d', $seq);

        $sql = " insert into g5_estimate
                    set es_code = '{$target_code}',
                        es_company = '{$es_company}',
                        es_email = '{$es_email}',
                        es_price = '{$total_supply}',
                        es_vat = '{$es_vat}',
                        es_total = '{$es_total}',
                        es_status = '대기',
                        es_datetime = '" . G5_TIME_YMDHIS . "' ";
        sql_query($sql);
        $target_id = sql_insert_id();
    }

    // Insert Items
    foreach ($items_data as $item) {
        $sql = " insert into g5_estimate_items
                    set es_id = '{$target_id}',
                        ei_item = '{$item['item']}',
                        ei_spec = '{$item['spec']}',
                        ei_qty = '{$item['qty']}',
                        ei_price = '{$item['price']}',
                        ei_amount = '{$item['amount']}' ";
        sql_query($sql);
    }

    // Mail Sending
    if (isset($_POST['send_mail']) && $_POST['send_mail'] == '1' && $es_email) {
        $subject = "[간판대학] {$es_company}님 견적서입니다.";

        ob_start();
        ?>
        <div style="padding:20px; background:#f5f5f5;">
            <div style="background:#fff; padding:30px; border:1px solid #ddd; max-width:800px; margin:0 auto;">
                <h1 style="color:#f97316; border-bottom:2px solid #f97316; padding-bottom:10px; margin-bottom:20px;">견적서</h1>
                <table style="width:100%; margin-bottom:20px;">
                    <tr>
                        <td style="font-weight:bold;">견적번호: <?php echo $target_code; ?></td>
                        <td style="text-align:right;">일자: <?php echo date('Y-m-d'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">수신: <?php echo $es_company; ?> 귀하</td>
                        <td style="text-align:right;">발신: 간판대학</td>
                    </tr>
                </table>

                <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                    <thead>
                        <tr style="background:#fb923c; color:#fff;">
                            <th style="padding:10px; border:1px solid #ddd;">품목</th>
                            <th style="padding:10px; border:1px solid #ddd;">규격</th>
                            <th style="padding:10px; border:1px solid #ddd;">수량</th>
                            <th style="padding:10px; border:1px solid #ddd;">단가</th>
                            <th style="padding:10px; border:1px solid #ddd;">금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items_data as $item): ?>
                            <tr>
                                <td style="padding:8px; border:1px solid #ddd; text-align:center;"><?php echo $item['item']; ?></td>
                                <td style="padding:8px; border:1px solid #ddd; text-align:center;"><?php echo $item['spec']; ?></td>
                                <td style="padding:8px; border:1px solid #ddd; text-align:center;">
                                    <?php echo number_format($item['qty']); ?></td>
                                <td style="padding:8px; border:1px solid #ddd; text-align:right;">
                                    <?php echo number_format($item['price']); ?></td>
                                <td style="padding:8px; border:1px solid #ddd; text-align:right;">
                                    <?php echo number_format($item['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; background:#fff7ed;">
                                공급가액</td>
                            <td
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; background:#fff7ed;">
                                <?php echo number_format($total_supply); ?>원</td>
                        </tr>
                        <tr>
                            <td colspan="4"
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; background:#fff7ed;">
                                부가세(10%)</td>
                            <td
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; background:#fff7ed;">
                                <?php echo number_format($es_vat); ?>원</td>
                        </tr>
                        <tr>
                            <td colspan="4"
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; color:#d97706; font-size:1.2em;">
                                합계</td>
                            <td
                                style="padding:10px; border:1px solid #ddd; text-align:right; font-weight:bold; color:#d97706; font-size:1.2em;">
                                <?php echo number_format($es_total); ?>원</td>
                        </tr>
                    </tfoot>
                </table>
                <p style="text-align:center; color:#888; margin-top:30px;">본 견적서는 간판대학에서 발행되었습니다.</p>
            </div>
        </div>
        <?php
        $content = ob_get_clean();

        include_once(G5_LIB_PATH . '/mailer.lib.php');
        mailer('간판대학', 'master@ganpandaehak.com', $es_email, $subject, $content, 1);

        // Update Status
        sql_query(" update g5_estimate set es_status = '발송완료' where es_id = '{$target_id}' ");
    }

    alert('저장되었습니다.', G5_THEME_URL . '/admin_estimate.php');
}

include_once(G5_THEME_PATH . '/head.php');
?>
<?php if ($w == '' || $w == 'status'): // ------------------------ LIST VIEW ------------------------ ?>

    <?php
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sql_search = "";
    if ($q)
        $sql_search = " and es_company like '%{$q}%' ";

    $sql = " select * from g5_estimate where 1 {$sql_search} order by es_id desc ";
    $result = sql_query($sql);
    ?>
    <div class="container mx-auto px-4 py-8 min-h-screen">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900 border-l-4 border-orange-500 pl-4">견적 관리</h2>
            <a href="?w=write"
                class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300">
                + 견적작성
            </a>
        </div>

        <!-- Search Form -->
        <form class="mb-6 flex gap-2" method="get">
            <input type="text" name="q" value="<?php echo $q ?>" placeholder="업체명 검색"
                class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
            <button type="submit"
                class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-6 rounded-lg transition duration-300">검색</button>
        </form>

        <!-- List Table -->
        <div class="bg-white rounded-xl shadow-lg run-off overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="p-4 font-bold text-gray-600">견적번호</th>
                        <th class="p-4 font-bold text-gray-600">업체명</th>
                        <th class="p-4 font-bold text-gray-600 text-right">총주문액</th>
                        <th class="p-4 font-bold text-gray-600 text-center">등록일</th>
                        <th class="p-4 font-bold text-gray-600 text-center">상태</th>
                        <th class="p-4 font-bold text-gray-600 text-center">관리</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    if (sql_num_rows($result) == 0)
                        echo '<tr><td colspan="6" class="p-10 text-center text-gray-500">등록된 견적이 없습니다.</td></tr>';
                    while ($row = sql_fetch_array($result)) {
                        $status_cls = ($row['es_status'] == '발송완료') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                        ?>
                        <tr class="hover:bg-orange-50 transition duration-150">
                            <td class="p-4 font-mono text-sm"><?php echo $row['es_code']; ?></td>
                            <td class="p-4 font-bold text-gray-800"><?php echo $row['es_company']; ?></td>
                            <td class="p-4 text-right font-bold text-orange-600"><?php echo number_format($row['es_total']); ?>원
                            </td>
                            <td class="p-4 text-center text-gray-500 text-sm"><?php echo substr($row['es_datetime'], 0, 10); ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $status_cls; ?>">
                                    <?php echo $row['es_status']; ?>
                                </span>
                            </td>
                            <td class="p-4 text-center gap-2">
                                <a href="?w=write&es_id=<?php echo $row['es_id']; ?>"
                                    class="text-blue-600 hover:underline font-medium text-sm mr-2">수정</a>
                                <?php if ($row['es_status'] == '대기') { ?>
                                    <a href="?w=status&st=발송완료&es_id=<?php echo $row['es_id']; ?>"
                                        onclick="return confirm('발송완료 상태로 변경하시겠습니까?');"
                                        class="text-green-600 hover:underline font-medium text-sm">발송처리</a>
                                <?php } else { ?>
                                    <a href="?w=status&st=대기&es_id=<?php echo $row['es_id']; ?>"
                                        onclick="return confirm('대기 상태로 변경하시겠습니까?');"
                                        class="text-gray-500 hover:underline font-medium text-sm">대기처리</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($w == 'write' || $w == 'update'): // ------------------------ WRITE VIEW ------------------------ ?>

    <?php
    $form_title = "견적 작성";
    $estimate = [
        'es_id' => '',
        'es_code' => '',
        'es_company' => '',
        'es_email' => '',
        'es_price' => 0,
        'es_vat' => 0,
        'es_total' => 0
    ];
    $items = [];

    if ($es_id > 0) {
        $form_title = "견적 수정";
        $estimate = sql_fetch(" select * from g5_estimate where es_id = '{$es_id}' ");
        $res_items = sql_query(" select * from g5_estimate_items where es_id = '{$es_id}' order by ei_id asc ");
        while ($row = sql_fetch_array($res_items)) {
            $items[] = $row;
        }
    }

    // Default 1 row if empty
    if (empty($items))
        $items[] = ['ei_item' => '', 'ei_spec' => '', 'ei_qty' => '1', 'ei_price' => '0', 'ei_amount' => 0];
    ?>

    <div class="container mx-auto px-4 py-8 min-h-screen">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6">
                <h2 class="text-2xl font-bold text-white"><?php echo $form_title; ?></h2>
                <?php if ($estimate['es_code'])
                    echo '<p class="text-orange-100 text-sm mt-1">견적번호: ' . $estimate['es_code'] . '</p>'; ?>
            </div>

            <form name="festimate" action="?w=submit" method="post" onsubmit="return fsubmit(this);" class="p-6">
                <input type="hidden" name="es_id" value="<?php echo $estimate['es_id']; ?>">
                <input type="hidden" name="es_code" value="<?php echo $estimate['es_code']; ?>">

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">업체명 (수신처)</label>
                        <input type="text" name="es_company" value="<?php echo $estimate['es_company']; ?>" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">이메일 (견적서 발송용)</label>
                        <input type="email" name="es_email" value="<?php echo $estimate['es_email']; ?>" required
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <!-- Item List -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-gray-700 font-bold">견적 품목</label>
                        <button type="button" onclick="add_row()"
                            class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-sm font-semibold transition">+
                            행 추가</button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full text-left" id="tbl_items">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="p-3 w-12 text-center text-sm font-bold text-gray-600">No</th>
                                    <th class="p-3 text-sm font-bold text-gray-600">항목명</th>
                                    <th class="p-3 w-40 text-sm font-bold text-gray-600">규격</th>
                                    <th class="p-3 w-24 text-sm font-bold text-gray-600">수량</th>
                                    <th class="p-3 w-32 text-sm font-bold text-gray-600 text-right">단가</th>
                                    <th class="p-3 w-32 text-sm font-bold text-gray-600 text-right">합계</th>
                                    <th class="p-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="item_list" class="divide-y divide-gray-100">
                                <?php foreach ($items as $k => $item) { ?>
                                    <tr class="item-row">
                                        <td class="p-2 text-center text-gray-500 text-sm idx"><?php echo $k + 1; ?></td>
                                        <td class="p-2"><input type="text" name="ei_item[]"
                                                value="<?php echo $item['ei_item']; ?>"
                                                class="w-full p-2 border rounded focus:ring-1 focus:ring-orange-500 outline-none"
                                                placeholder="품목명 입력"></td>
                                        <td class="p-2"><input type="text" name="ei_spec[]"
                                                value="<?php echo $item['ei_spec']; ?>"
                                                class="w-full p-2 border rounded focus:ring-1 focus:ring-orange-500 outline-none"
                                                placeholder="규격"></td>
                                        <td class="p-2"><input type="number" name="ei_qty[]"
                                                value="<?php echo $item['ei_qty']; ?>"
                                                class="w-full p-2 border rounded text-right focus:ring-1 focus:ring-orange-500 outline-none in-qty"
                                                oninput="calc_row(this)"></td>
                                        <td class="p-2"><input type="number" name="ei_price[]"
                                                value="<?php echo $item['ei_price']; ?>"
                                                class="w-full p-2 border rounded text-right focus:ring-1 focus:ring-orange-500 outline-none in-price"
                                                oninput="calc_row(this)"></td>
                                        <td class="p-2 text-right font-semibold text-gray-700 in-total">
                                            <?php echo number_format($item['ei_amount']); ?></td>
                                        <td class="p-2 text-center"><button type="button" onclick="del_row(this)"
                                                class="text-red-400 hover:text-red-600 text-xl font-bold">&times;</button></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 p-6 rounded-lg mb-8 text-right">
                    <div class="flex justify-end items-center gap-10 mb-2">
                        <span class="text-gray-500">공급가액</span>
                        <span class="text-xl font-bold text-gray-800"><span
                                id="txt_price"><?php echo number_format($estimate['es_price']); ?></span>원</span>
                    </div>
                    <div class="flex justify-end items-center gap-10 mb-4">
                        <span class="text-gray-500">부가세 (10%)</span>
                        <span class="text-xl font-bold text-gray-800"><span
                                id="txt_vat"><?php echo number_format($estimate['es_vat']); ?></span>원</span>
                    </div>
                    <div class="flex justify-end items-center gap-10 pt-4 border-t border-gray-200">
                        <span class="text-lg font-bold text-gray-900">최종 합계</span>
                        <span class="text-3xl font-extrabold text-orange-600"><span
                                id="txt_total"><?php echo number_format($estimate['es_total']); ?></span>원</span>
                    </div>
                </div>

                <!-- Send Mail Option -->
                <div class="flex items-center mb-8 bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <input type="checkbox" id="send_mail" name="send_mail" value="1"
                        class="w-5 h-5 text-orange-600 rounded focus:ring-orange-500 cursor-pointer">
                    <label for="send_mail" class="ml-3 text-gray-800 font-medium cursor-pointer select-none">저장 후 이메일로 견적서
                        발송하기</label>
                </div>

                <!-- Buttons -->
                <div class="flex justify-center gap-4">
                    <a href="?w="
                        class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-lg transition">취소</a>
                    <button type="submit"
                        class="px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg shadow-lg transform hover:-translate-y-1 transition text-lg">
                        저장하기
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function add_row() {
            var tr = document.querySelector('.item-row').cloneNode(true);
            var inputs = tr.querySelectorAll('input');
            inputs.forEach(function (input) {
                if (input.classList.contains('in-qty')) input.value = 1;
                else if (input.classList.contains('in-price')) input.value = 0;
                else input.value = '';
            });
            tr.querySelector('.in-total').innerText = '0';
            document.getElementById('item_list').appendChild(tr);
            reindex();
        }

        function del_row(btn) {
            var rows = document.querySelectorAll('.item-row');
            if (rows.length <= 1) {
                alert('최소 1개 행은 있어야 합니다.');
                return;
            }
            btn.closest('tr').remove();
            reindex();
            calc_total();
        }

        function reindex() {
            var rows = document.querySelectorAll('.item-row');
            rows.forEach(function (row, index) {
                row.querySelector('.idx').innerText = index + 1;
            });
        }

        function calc_row(input) {
            var tr = input.closest('tr');
            var qty = parseInt(tr.querySelector('.in-qty').value) || 0;
            var price = parseInt(tr.querySelector('.in-price').value) || 0;
            var total = qty * price;

            tr.querySelector('.in-total').innerText = total.toLocaleString();
            calc_total();
        }

        function calc_total() {
            var supply = 0;
            var rows = document.querySelectorAll('.item-row');
            rows.forEach(function (row) {
                var qty = parseInt(row.querySelector('.in-qty').value) || 0;
                var price = parseInt(row.querySelector('.in-price').value) || 0;
                supply += (qty * price);
            });

            var vat = Math.floor(supply * 0.1);
            var total = supply + vat;

            document.getElementById('txt_price').innerText = supply.toLocaleString();
            document.getElementById('txt_vat').innerText = vat.toLocaleString();
            document.getElementById('txt_total').innerText = total.toLocaleString();
        }

        function fsubmit(f) {
            if (!f.es_company.value) {
                alert('업체명을 입력하세요.');
                f.es_company.focus();
                return false;
            }
            return confirm('저장하시겠습니까?');
        }
    </script>

<?php endif; ?>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
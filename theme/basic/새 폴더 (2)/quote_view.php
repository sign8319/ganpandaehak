<?php
include_once('./_common.php');

$qa_id = isset($_GET['qa_id']) ? (int) $_GET['qa_id'] : 0;

if (!$qa_id)
    alert('잘못된 접근입니다.');

$quote = sql_fetch(" select * from g5_quote where qa_id = '$qa_id' ");
if (!$quote)
    alert('견적서가 존재하지 않습니다.');

$sql = " select * from g5_quote_item where qa_id = '$qa_id' order by qi_index asc, qi_id asc ";
$result = sql_query($sql);
$items = [];
while ($row = sql_fetch_array($result))
    $items[] = $row;

// Business Info
$biz_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = file_exists($biz_file) ? json_decode(file_get_contents($biz_file), true) : [];

include_once(G5_THEME_PATH . '/head.sub.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    @import url("https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css");

    body {
        font-family: 'Pretendard', sans-serif;
        background-color: #525659;
        /* PDF viewer background feel */
        margin: 0;
        padding: 0;
    }

    #quote_wrap {
        width: 210mm;
        min-height: 297mm;
        margin: 40px auto;
        background: white;
        padding: 15mm;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        box-sizing: border-box;
        position: relative;
    }

    /* Print Settings */
    @media print {
        body {
            background: none;
            margin: 0;
        }

        #quote_wrap {
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border: none;
        }

        @page {
            size: A4;
            margin: 0;
        }

        .no-print {
            display: none !important;
        }
    }

    .tbl-header th {
        background-color: #f9fafb;
        border-top: 2px solid #ea580c; /* Orange-600 */
        border-bottom: 1px solid #e5e7eb;
        padding: 10px 4px;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        text-align: center;
    }

    .tbl-body td {
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 4px;
        font-size: 13px;
        color: #1f2937;
        vertical-align: top;
    }

    .tbl-summary td {
        padding: 6px 0;
    }
</style>

<div id="quote_wrap">

    <!-- Header: Logo & Title -->
    <div class="flex justify-between items-end mb-10 pb-4 border-b-2 border-gray-800 relative">
        <div class="flex items-end gap-3">
            <!-- Logo -->
            <img src="<?php echo G5_THEME_IMG_URL; ?>/logo.png" alt="간판대학" class="h-14 object-contain"
                onerror="this.style.display='none';">
            <div class="mb-1">
                <h1 class="text-2xl font-extrabold text-orange-600 tracking-tight leading-none">간판대학</h1>
                <p class="text-[11px] text-gray-500 font-bold tracking-widest mt-0.5">SIGN UNIVERSITY</p>
            </div>
        </div>
        <div class="text-right">
            <h2 class="text-4xl font-extrabold text-gray-900 tracking-widest">견 적 서</h2>
            <div class="mt-2 text-sm text-gray-600 font-bold flex justify-end items-center gap-2">
                <span class="text-gray-400 font-normal">Date.</span>
                <?php echo date('Y. m. d', strtotime($quote['qa_datetime'])); ?>
            </div>
        </div>
        
        <!-- Web-only Action Bar (Hidden in Print) -->
        <div class="no-print absolute top-0 right-0 transform -translate-y-full pb-2 flex gap-2">
            <button onclick="downloadImage(this)" class="bg-gray-800 text-white text-xs px-3 py-1.5 rounded flex items-center gap-1 hover:bg-gray-700 transition">
                <span>📷</span> 이미지 저장
            </button>
        </div>
    </div>

    <!-- Info Section: Customer & Supplier -->
    <div class="flex gap-10 mb-10">
        <!-- Customer (Left) -->
        <div class="w-1/2 flex flex-col justify-center">
            <div class="mb-2">
                <span class="text-xs text-orange-600 font-bold tracking-wider mb-1 block">CUSTOMER</span>
                <div class="text-3xl font-bold text-gray-800 border-b-2 border-gray-200 pb-2 inline-block min-w-[200px]">
                    <?php echo $quote['qa_client_name']; ?> <span class="text-lg font-medium text-gray-500">귀하</span>
                </div>
            </div>
            <div class="space-y-1.5 text-sm text-gray-600 mt-4 pl-1">
                <?php if ($quote['qa_client_hp']) { ?>
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-bold text-xs uppercase pt-0.5">Tel</span>
                        <span class="font-medium text-gray-800"><?php echo $quote['qa_client_hp']; ?></span>
                    </div>
                <?php } ?>
                <?php if ($quote['qa_client_email']) { ?>
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-bold text-xs uppercase pt-0.5">Email</span>
                        <span class="font-medium text-gray-800"><?php echo $quote['qa_client_email']; ?></span>
                    </div>
                <?php } ?>
                <?php if ($quote['qa_client_addr'] || $quote['qa_client_addr2']) { ?>
                    <div class="flex">
                        <span class="w-16 text-gray-400 font-bold text-xs uppercase pt-0.5">Address</span>
                        <span class="font-medium text-gray-800"><?php echo $quote['qa_client_addr'] . ' ' . $quote['qa_client_addr2']; ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Supplier (Right) Box Style -->
        <div class="w-1/2">
            <div class="border-2 border-gray-200 rounded-lg p-5 bg-gray-50/50 relative">
                <div class="absolute -top-3 left-4 bg-white px-2 text-xs font-bold text-gray-400 tracking-wider">SUPPLIER</div>
                
                <div class="grid grid-cols-[80px_1fr] gap-y-2 text-sm">
                    <div class="text-gray-500 font-medium">상 호</div>
                    <div class="font-bold text-gray-900"><?php echo $biz_info['biz_name']; ?></div>
                    
                    <div class="text-gray-500 font-medium">대 표 자</div>
                    <div class="text-gray-800"><?php echo $biz_info['biz_ceo']; ?></div>
                    
                    <div class="text-gray-500 font-medium">등록번호</div>
                    <div class="text-gray-800 font-mono tracking-tight"><?php echo $biz_info['biz_no']; ?></div>
                    
                    <div class="text-gray-500 font-medium">주 소</div>
                    <div class="text-gray-800 break-keep leading-tight"><?php echo $biz_info['biz_addr']; ?></div>
                    
                    <div class="text-gray-500 font-medium">업태/종목</div>
                    <div class="text-gray-800"><?php echo $biz_info['biz_type']; ?> / <?php echo $biz_info['biz_class']; ?></div>
                    
                    <div class="text-gray-500 font-medium">연락처</div>
                    <div class="text-gray-800 font-bold text-orange-700"><?php echo $biz_info['biz_tel']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-8">
        <table class="w-full border-collapse">
            <thead>
                <tr class="tbl-header">
                    <th class="w-12">NO</th>
                    <th>품목 (Item)</th>
                    <th class="w-24">규격</th>
                    <th class="w-16">수량</th>
                    <th class="w-24">단가</th>
                    <th class="w-28">금액</th>
                    <th class="w-24">비고</th>
                </tr>
            </thead>
            <tbody class="tbl-body">
                <?php
                $item_cnt = count($items);
                if ($item_cnt > 0) {
                    foreach ($items as $k => $item) {
                        $has_images = ($item['qi_img1'] || $item['qi_img2'] || $item['qi_img3']);
                        ?>
                        <tr>
                            <td class="text-center text-gray-400 font-medium"><?php echo $k + 1; ?></td>
                            <td>
                                <div class="font-bold text-[15px] text-gray-900 mb-1"><?php echo $item['qi_item']; ?></div>

                                <!-- Image & Description Layout -->
                                <?php if ($has_images || $item['qi_desc']) { ?>
                                    <div class="mt-3 flex flex-row gap-4 items-start">
                                        <!-- Left: Images (Horizontal Grid) -->
                                        <?php if ($has_images) { ?>
                                            <div class="flex gap-2 flex-shrink-0">
                                                <?php for ($m = 1; $m <= 3; $m++) {
                                                    $img = $item['qi_img' . $m];
                                                    if ($img) {
                                                        $img_url = G5_DATA_URL . '/quote/' . $img;
                                                        echo "<div class='w-24 h-24 rounded overflow-hidden border border-gray-200 bg-white'>";
                                                        echo "<img src='$img_url' class='w-full h-full object-cover'>";
                                                        echo "</div>";
                                                    }
                                                } ?>
                                            </div>
                                        <?php } ?>

                                        <!-- Right: Description (Yellow Box) -->
                                        <?php if ($item['qi_desc']) { ?>
                                            <div class="flex-1 bg-yellow-50 p-4 rounded border border-yellow-100 text-sm text-gray-800 leading-relaxed text-left min-w-0" style="min-width: 260px; word-break: keep-all; white-space: normal;">
                                                <?php echo $item['qi_desc']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </td>
                            <td class="text-center text-gray-600 text-sm"><?php echo $item['qi_spec']; ?></td>
                            <td class="text-center font-bold text-gray-800"><?php echo number_format($item['qi_qty']); ?></td>
                            <td class="text-right text-gray-600 font-mono text-sm"><?php echo number_format($item['qi_price']); ?></td>
                            <td class="text-right font-bold text-gray-900"><?php echo number_format($item['qi_amount']); ?></td>
                            <td class="text-center text-xs text-gray-500"><?php echo $item['qi_note']; ?></td>
                        </tr>
                    <?php }
                } ?>
                
                <!-- Spacer Rows for clean A4 look -->
                <?php 
                $min_rows = 5;
                if ($item_cnt < $min_rows) {
                    for ($j = 0; $j < ($min_rows - $item_cnt); $j++) { ?>
                        <tr class="h-12 border-b border-gray-50">
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                <?php }
                } ?>

            </tbody>
        </table>
    </div>

    <!-- Totals Section -->
    <div class="flex justify-end mb-12">
        <div class="w-full max-w-[450px] bg-slate-50 p-6 rounded-lg border border-slate-200 shadow-sm">
            <table class="w-full text-sm font-medium tbl-summary">
                <tr>
                    <td class="text-gray-500">공급가액</td>
                    <td class="text-right text-gray-800 font-mono"><?php echo number_format($quote['qa_price_supply']); ?> 원</td>
                </tr>
                <tr>
                    <td class="text-gray-500">부가세(VAT)</td>
                    <td class="text-right text-gray-800 font-mono"><?php echo number_format($quote['qa_price_vat']); ?> 원</td>
                </tr>
                <tr class="border-t border-slate-300 mt-2">
                    <td class="pt-3 text-base text-gray-900 font-bold">합계금액</td>
                    <td class="pt-3 text-right text-lg text-orange-600 font-extrabold font-mono">
                        <?php echo number_format($quote['qa_price_total']); ?> 원
                    </td>
                </tr>

                <?php if ($quote['qa_deposit'] > 0) {
                    $balance = $quote['qa_price_total'] - $quote['qa_deposit'];
                    ?>
                    <tr><td colspan="2" class="h-3"></td></tr>
                    <tr class="text-blue-600">
                        <td class="text-xs font-bold">계약금 (선금)</td>
                        <td class="text-right font-bold font-mono">- <?php echo number_format($quote['qa_deposit']); ?> 원</td>
                    </tr>
                    <tr class="text-red-500 text-base font-bold bg-white rounded shadow-sm">
                        <td class="pl-2 py-1">잔금 (후불)</td>
                        <td class="text-right pr-2 py-1 font-mono"><?php echo number_format($balance); ?> 원</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <!-- Customer Memo -->
    <?php if ($quote['qa_memo_user']) { ?>
        <div class="mb-10 border-l-4 border-orange-500 bg-orange-50/30 p-5 pl-6 text-sm">
            <h4 class="font-bold text-gray-800 mb-2 flex items-center gap-2 text-base">
                NOTICE
            </h4>
            <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                <?php echo $quote['qa_memo_user']; ?>
            </div>
        </div>
    <?php } ?>

    <!-- Footer -->
    <div class="mt-auto border-t-2 border-gray-800 pt-6 text-center">
        <p class="text-xl font-bold text-gray-900 tracking-tight mb-1"><?php echo $biz_info['biz_name']; ?></p>
        <p class="text-xs text-gray-500 font-medium"><?php echo $biz_info['biz_addr']; ?></p>
        <p class="text-[10px] text-gray-400 mt-3 font-mono">Thank you for your business. We appreciate it.</p>
    </div>

</div>

<!-- Action Buttons Bar (Web Only) -->
<div class="no-print w-full max-w-[210mm] mx-auto mt-6 mb-10">
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-wrap gap-3 justify-center items-center">
        <button onclick="downloadImage(this)" 
            class="bg-gray-800 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-700 transition flex items-center gap-2">
            <span>📷</span> 이미지 저장
        </button>
        
        <button onclick="window.print()" 
            class="bg-orange-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-orange-700 transition flex items-center gap-2">
            <span>📄</span> PDF/인쇄
        </button>
        
        <button onclick="history.back()" 
            class="bg-white border-2 border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-50 transition flex items-center gap-2">
            <span>◀</span> 목록으로
        </button>
        
        <button onclick="alert('배달완료 기능은 관리자 페이지에서 사용 가능합니다.')" 
            class="bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition flex items-center gap-2">
            <span>✓</span> 배달완료
        </button>
    </div>
</div>

<script>
    function downloadImage(btn) {
        // Handle external call (btn might be null)
        var originalText = "";
        
        if (btn) {
            originalText = btn.innerText;
            btn.innerText = "저장 중...";
        }
        
        html2canvas(document.querySelector("#quote_wrap"), { 
            scale: 2, 
            backgroundColor: "#ffffff",
            useCORS: true 
        }).then(canvas => {
            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert("이미지 캡처에 실패했습니다 (Empty Blob).");
                    if (btn) btn.innerText = originalText;
                    return;
                }
                var link = document.createElement('a');
                link.download = '견적서_<?php echo $quote['qa_client_name']; ?>_<?php echo date("YmdHis"); ?>.png';
                link.href = URL.createObjectURL(blob);
                link.click();
                
                if (btn) btn.innerText = originalText;
            }, "image/png");
        }).catch(err => {
            alert("이미지 저장 중 오류가 발생했습니다.");
            console.error(err);
            if (btn) btn.innerText = originalText;
        });
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.sub.php');
?>
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

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
        background: #f5f5f5;
        padding: 20px;
    }

    #quote_wrap {
        max-width: 210mm;
        margin: 0 auto;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Print Settings */
    @media print {
        body {
            background: white;
            padding: 0;
        }

        #quote_wrap {
            max-width: 100%;
            box-shadow: none;
        }

        .no-print {
            display: none !important;
        }

        @page {
            size: A4;
            margin: 0;
        }
    }

    /* =========================
       HEADER - 미니멀 스타일
    ========================= */
    .quote-header {
        background: #FF6B35;
        padding: 25px 30px;
        border-bottom: 4px solid #e85a28;
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .logo-area {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logo-area img {
        height: 50px;
        width: auto;
        object-fit: contain;
    }

    .company-info h1 {
        font-size: 28px;
        font-weight: 900;
        color: white;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .title-area h2 {
        font-size: 32px;
        font-weight: 900;
        color: white;
        margin: 0;
        letter-spacing: 2px;
    }

    .title-area .subtitle {
        font-size: 10px;
        color: rgba(255, 255, 255, 0.9);
        margin-top: 3px;
        letter-spacing: 2px;
    }

    .header-meta {
        display: flex;
        gap: 25px;
        padding: 12px 0;
        border-top: 1px solid rgba(255, 255, 255, 0.3);
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
    }

    .meta-item .label {
        font-size: 10px;
        opacity: 0.8;
        font-weight: 600;
    }

    .meta-item .value {
        font-size: 13px;
        font-weight: 700;
    }

    /* =========================
       CONTENT AREA
    ========================= */
    .quote-content {
        padding: 40px;
    }

    /* 고객/공급자 정보 - 깔끔한 2단 레이아웃 */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 2px solid #f0f0f0;
    }

    .info-box {
        background: #fafafa;
        padding: 18px;
        border-left: 4px solid #FF6B35;
    }

    .info-box.supplier {
        background: #2c3e50;
        color: white;
        border-left-color: #FF6B35;
    }

    .info-title {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 12px;
    }

    .info-box.supplier .info-title {
        color: rgba(255, 255, 255, 0.7);
    }

    .info-name {
        font-size: 16px;
        font-weight: 900;
        color: #1a1a1a;
        margin-bottom: 12px;
    }

    .info-box.supplier .info-name {
        color: white;
    }

    .info-details {
        display: grid;
        gap: 6px;
    }

    .info-line {
        display: grid;
        grid-template-columns: 70px 1fr;
        gap: 10px;
        font-size: 12px;
        line-height: 1.5;
    }

    .info-line .key {
        color: #666;
        font-weight: 600;
    }

    .info-box.supplier .info-line .key {
        color: rgba(255, 255, 255, 0.7);
    }

    .info-line .val {
        color: #1a1a1a;
        font-weight: 500;
    }

    .info-box.supplier .info-line .val {
        color: white;
    }

    /* =========================
       ITEMS TABLE - 전문적 스타일
    ========================= */
    .section-title {
        font-size: 16px;
        font-weight: 900;
        color: #1a1a1a;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #FF6B35;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 20px;
        background: #FF6B35;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
    }

    .items-table thead {
        background: #2c3e50;
    }

    .items-table th {
        padding: 14px 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }

    .items-table th:last-child {
        border-right: none;
    }

    .items-table th:first-child {
        text-align: center;
        width: 50px;
    }

    .items-table th:nth-child(3) {
        width: 120px;
    }

    .items-table th:nth-child(4) {
        width: 80px;
        text-align: center;
    }

    .items-table th:nth-child(5),
    .items-table th:nth-child(6) {
        width: 130px;
        text-align: right;
    }

    .items-table tbody tr {
        border-bottom: 1px solid #e0e0e0;
        transition: background 0.2s;
    }

    .items-table tbody tr:hover {
        background: #f9f9f9;
    }

    .items-table td {
        padding: 16px 12px;
        font-size: 13px;
        color: #333;
        vertical-align: top;
    }

    .items-table td:first-child {
        text-align: center;
        font-weight: 700;
        color: #999;
    }

    .items-table td:nth-child(4) {
        text-align: center;
        font-weight: 600;
    }

    .items-table td:nth-child(5),
    .items-table td:nth-child(6) {
        text-align: right;
        font-weight: 600;
    }

    .item-name {
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
        font-size: 14px;
    }

    .item-desc {
        font-size: 12px;
        color: #666;
        line-height: 1.5;
        margin-top: 4px;
    }

    /* 아이템 이미지 - 더 크게 */
    .item-images {
        display: flex;
        gap: 10px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .item-image {
        width: 180px;
        height: 180px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fafafa;
        padding: 8px;
    }

    /* =========================
       PRICE SUMMARY - 깔끔한 스타일
    ========================= */
    .price-summary {
        max-width: 400px;
        margin-left: auto;
        margin-bottom: 40px;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .price-row:last-child {
        border-bottom: none;
        border-top: 2px solid #333;
        padding-top: 16px;
        margin-top: 8px;
    }

    .price-label {
        font-size: 14px;
        color: #666;
        font-weight: 600;
    }

    .price-value {
        font-size: 15px;
        color: #333;
        font-weight: 700;
    }

    .price-row:last-child .price-label {
        font-size: 16px;
        color: #1a1a1a;
        font-weight: 900;
    }

    .price-row:last-child .price-value {
        font-size: 24px;
        color: #FF6B35;
        font-weight: 900;
    }

    /* =========================
       NOTICE BOX
    ========================= */
    .notice-box {
        background: #f0f7ff;
        border-left: 4px solid #4285f4;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 0 4px 4px 0;
    }

    .notice-title {
        font-size: 13px;
        font-weight: 700;
        color: #1a73e8;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .notice-content {
        font-size: 13px;
        color: #333;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* =========================
       FOOTER
    ========================= */
    .quote-footer {
        text-align: center;
        padding: 30px 40px;
        background: #fafafa;
        border-top: 1px solid #e0e0e0;
    }

    .footer-company {
        font-size: 18px;
        font-weight: 900;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .footer-address {
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
    }

    .footer-message {
        font-size: 11px;
        color: #999;
        font-style: italic;
    }

    /* =========================
       ACTION BUTTONS
    ========================= */
    .action-buttons {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .action-btn {
        background: white;
        color: #333;
        border: 1px solid #ddd;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }

    .action-btn:hover {
        background: #FF6B35;
        color: white;
        border-color: #FF6B35;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
    }
</style>

<div class="action-buttons no-print">
    <button onclick="downloadImage()" class="action-btn">
        <span>📷</span> 이미지 저장
    </button>
</div>

<div id="quote_wrap">
    <!-- HEADER -->
    <div class="quote-header">
        <div class="header-top">
            <div class="logo-area">
                <img src="/ganpandaehak/data/assets/asset_20251228193653_7935.png" alt="간판대학 로고">
                <div class="company-info">
                    <h1>간판대학</h1>
                </div>
            </div>
            <div class="title-area">
                <h2>견적서</h2>
                <div class="subtitle">QUOTATION</div>
            </div>
        </div>
        
        <div class="header-meta">
            <div class="meta-item">
                <span class="label">견적번호</span>
                <span class="value"><?php echo $quote['qa_code']; ?></span>
            </div>
            <div class="meta-item">
                <span class="label">발행일</span>
                <span class="value"><?php echo date('Y. m. d', strtotime($quote['qa_datetime'])); ?></span>
            </div>
            <?php if ($quote['qa_subject']): ?>
            <div class="meta-item">
                <span class="label">건명</span>
                <span class="value"><?php echo $quote['qa_subject']; ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="quote-content">
        <!-- 고객/공급자 정보 -->
        <div class="info-grid">
            <!-- 고객 -->
            <div class="info-box">
                <div class="info-title">Customer</div>
                <div class="info-name"><?php echo $quote['qa_client_name']; ?> 귀하</div>
                <div class="info-details">
                    <?php if ($quote['qa_client_hp']): ?>
                    <div class="info-line">
                        <span class="key">연락처</span>
                        <span class="val"><?php echo $quote['qa_client_hp']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($quote['qa_client_email']): ?>
                    <div class="info-line">
                        <span class="key">이메일</span>
                        <span class="val"><?php echo $quote['qa_client_email']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($quote['qa_client_addr'] || $quote['qa_client_addr2']): ?>
                    <div class="info-line">
                        <span class="key">주소</span>
                        <span class="val"><?php echo trim($quote['qa_client_addr'] . ' ' . $quote['qa_client_addr2']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 공급자 -->
            <div class="info-box supplier">
                <div class="info-title">Supplier</div>
                <div class="info-name"><?php echo !empty($biz_info['biz_name']) ? $biz_info['biz_name'] : '간판대학'; ?></div>
                <div class="info-details">
                    <?php if (!empty($biz_info['biz_ceo'])): ?>
                    <div class="info-line">
                        <span class="key">대표자</span>
                        <span class="val"><?php echo $biz_info['biz_ceo']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($biz_info['biz_no'])): ?>
                    <div class="info-line">
                        <span class="key">사업자번호</span>
                        <span class="val"><?php echo $biz_info['biz_no']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($biz_info['biz_type']) && !empty($biz_info['biz_class'])): ?>
                    <div class="info-line">
                        <span class="key">업태/종목</span>
                        <span class="val"><?php echo $biz_info['biz_type'] . ' / ' . $biz_info['biz_class']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($biz_info['biz_tel'])): ?>
                    <div class="info-line">
                        <span class="key">연락처</span>
                        <span class="val"><?php echo $biz_info['biz_tel']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($biz_info['biz_email'])): ?>
                    <div class="info-line">
                        <span class="key">이메일</span>
                        <span class="val"><?php echo $biz_info['biz_email']; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($biz_info['biz_addr'])): ?>
                    <div class="info-line">
                        <span class="key">주소</span>
                        <span class="val"><?php echo $biz_info['biz_addr']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 견적 내역 -->
        <div class="section-title">견적 내역 (Items)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>품목 (Item)</th>
                    <th>규격</th>
                    <th>수량</th>
                    <th>단가</th>
                    <th>금액</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($items as $item) { 
                    // Check for images
                    $has_images = false;
                    $images = [];
                    for ($i = 1; $i <= 3; $i++) {
                        if (!empty($item['qi_img' . $i])) {
                            $has_images = true;
                            $images[] = G5_DATA_URL . '/quote/' . $item['qi_img' . $i];
                        }
                    }
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>
                        <div class="item-name"><?php echo $item['qi_item']; ?></div>
                        <?php if ($item['qi_desc']): ?>
                            <div class="item-desc"><?php echo nl2br($item['qi_desc']); ?></div>
                        <?php endif; ?>
                        <?php if ($has_images): ?>
                            <div class="item-images">
                                <?php foreach ($images as $img_url): ?>
                                    <img src="<?php echo $img_url; ?>" class="item-image" alt="품목 이미지">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $item['qi_spec']; ?></td>
                    <td><?php echo number_format($item['qi_qty']); ?></td>
                    <td><?php echo number_format($item['qi_price']); ?>원</td>
                    <td><?php echo number_format($item['qi_amount']); ?>원</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 금액 합계 -->
        <div class="price-summary">
            <div class="price-row">
                <div class="price-label">공급가액</div>
                <div class="price-value"><?php echo number_format($quote['qa_price_supply']); ?> 원</div>
            </div>
            <div class="price-row">
                <div class="price-label">부가세 (VAT 10%)</div>
                <div class="price-value"><?php echo number_format($quote['qa_price_vat']); ?> 원</div>
            </div>
            <div class="price-row">
                <div class="price-label">합계금액</div>
                <div class="price-value"><?php echo number_format($quote['qa_price_total']); ?> 원</div>
            </div>
        </div>

        <!-- 안내사항 -->
        <?php if (!empty($biz_info['quote_fixed_message'])): ?>
        <div class="notice-box">
            <div class="notice-title">
                <span>📌</span> 안내사항
            </div>
            <div class="notice-content"><?php echo nl2br($biz_info['quote_fixed_message']); ?></div>
        </div>
        <?php endif; ?>

        <!-- 특이사항 -->
        <?php if (!empty($quote['qa_memo_user'])): ?>
        <div class="notice-box" style="background: #fff8e1; border-left-color: #FFA726;">
            <div class="notice-title" style="color: #F57C00;">
                <span>📝</span> 특이사항
            </div>
            <div class="notice-content"><?php echo nl2br($quote['qa_memo_user']); ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <div class="quote-footer">
        <div class="footer-company"><?php echo !empty($biz_info['biz_name']) ? $biz_info['biz_name'] : '간판대학'; ?></div>
        <div class="footer-address"><?php echo !empty($biz_info['biz_addr']) ? $biz_info['biz_addr'] : '서울특별시 성북구 정릉로 23길 29-2, 1층'; ?></div>
        <div class="footer-message">Thank you for your business. We appreciate it.</div>
    </div>
</div>

<script>
function downloadImage() {
    var element = document.getElementById('quote_wrap');
    
    html2canvas(element, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff',
        logging: false,
        width: element.scrollWidth,
        height: element.scrollHeight
    }).then(function(canvas) {
        var link = document.createElement('a');
        link.download = '견적서_<?php echo $quote['qa_client_name']; ?>_<?php echo date('Ymd'); ?>.png';
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
        alert('이미지가 다운로드되었습니다.');
    }).catch(function(error) {
        console.error('Error:', error);
        alert('이미지 생성 중 오류가 발생했습니다.');
    });
}
</script>

<?php include_once(G5_THEME_PATH . '/tail.sub.php'); ?>

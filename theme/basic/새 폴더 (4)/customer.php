<?php
include_once('./_common.php');

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (!$token) {
    alert('잘못된 접근입니다.');
}

// Load customer by token
$share_link = sql_fetch(" SELECT * FROM g5_customer_share_link WHERE share_token = '$token' ");

if (!$share_link) {
    alert('유효하지 않은 링크입니다.');
}

if (!$share_link['is_active']) {
    alert('비활성화된 링크입니다.');
}

$customer_id = $share_link['customer_id'];

// Load customer info
$customer = sql_fetch(" SELECT * FROM g5_customer WHERE customer_id = '$customer_id' ");
if (!$customer) {
    alert('고객 정보를 찾을 수 없습니다.');
}

// Load status
$status = sql_fetch(" SELECT * FROM g5_customer_status WHERE customer_id = '$customer_id' ");
$current_status = $status['status_step'] ?? '견적서발송';

// Load latest quote (if exists)
$quote = null;
$items = [];

// FIX: Try to find quote by customer name match (since qa_customer_id might not be set yet)
$customer_name_safe = addslashes($customer['customer_name']);
$quote = sql_fetch(" SELECT * FROM g5_quote 
                     WHERE qa_client_name = '$customer_name_safe' 
                     ORDER BY qa_datetime DESC 
                     LIMIT 1 ");

if ($quote) {
    $result = sql_query(" SELECT * FROM g5_quote_item WHERE qa_id = '{$quote['qa_id']}' ORDER BY qi_index ");
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }
}

include_once(G5_THEME_PATH . '/head.sub.php');
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    @import url("https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css");

    body {
        font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0;
        padding: 20px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .header {
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        padding: 40px;
        color: white;
        text-align: center;
    }

    .header h1 {
        font-size: 32px;
        font-weight: 900;
        margin: 0 0 10px 0;
    }

    .header p {
        font-size: 14px;
        opacity: 0.9;
        margin: 0;
    }

    .content {
        padding: 40px;
    }

    .section {
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        border-radius: 2px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 15px;
        background: #f9fafb;
        padding: 20px;
        border-radius: 12px;
    }

    .info-label {
        font-size: 14px;
        font-weight: 600;
        color: #666;
    }

    .info-value {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 500;
    }

    /* Status Steps */
    .status-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 30px 0;
        position: relative;
    }

    .status-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e5e7eb;
        z-index: 0;
    }

    .status-step {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
    }

    .status-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        border: 3px solid white;
        margin: 0 auto 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #9ca3af;
    }

    .status-step.active .status-circle {
        background: linear-gradient(135deg, #FF6B35 0%, #F7931E 100%);
        color: white;
    }

    .status-step.completed .status-circle {
        background: #10b981;
        color: white;
    }

    .status-label {
        font-size: 11px;
        font-weight: 600;
        color: #9ca3af;
        word-break: keep-all;
        white-space: nowrap;
    }

    .status-step.active .status-label,
    .status-step.completed .status-label {
        color: #1a1a1a;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .container {
            margin: 10px;
            border-radius: 12px;
        }

        .header {
            padding: 30px 20px;
        }

        .header h1 {
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .section-title {
            font-size: 16px;
        }

        .info-grid {
            grid-template-columns: 80px 1fr;
            gap: 10px;
            padding: 15px;
        }

        .info-label {
            font-size: 12px;
        }

        .info-value {
            font-size: 12px;
        }

        /* 모바일에서 상태 스텝 축소 */
        .status-steps {
            margin: 20px 0;
            gap: 5px;
        }

        .status-circle {
            width: 32px;
            height: 32px;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .status-label {
            font-size: 9px;
            max-width: 55px;
            margin: 0 auto;
            line-height: 1.2;
            white-space: normal;
        }

        .status-step {
            min-width: 0;
            flex: 1;
        }

        .items-table {
            font-size: 12px;
        }

        .items-table thead th {
            padding: 12px 8px;
            font-size: 11px;
        }

        .items-table tbody td {
            padding: 12px 8px;
            font-size: 12px;
        }

        .notice-box {
            padding: 15px;
        }

        .notice-title {
            font-size: 12px;
        }

        .notice-content {
            font-size: 11px;
        }
    }

    /* Items Table */
    .items-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .items-table thead th {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 18px 15px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .items-table thead th:first-child {
        text-align: center;
        width: 60px;
    }

    .items-table thead th:nth-child(3),
    .items-table thead th:nth-child(4) {
        text-align: center;
    }

    .items-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .items-table tbody tr:last-child {
        border-bottom: none;
    }

    .items-table tbody td {
        padding: 20px 15px;
        font-size: 14px;
        color: #333;
    }

    .items-table tbody td:first-child {
        text-align: center;
        font-weight: 700;
        color: #FF6B35;
    }

    .item-name {
        font-weight: 700;
        color: #1a1a1a;
    }

    .item-spec,
    .item-qty {
        text-align: center;
        font-weight: 600;
        color: #666;
    }

    .notice-box {
        background: #fff7ed;
        border-left: 5px solid #FF6B35;
        padding: 20px;
        border-radius: 8px;
        margin-top: 30px;
    }

    .notice-title {
        font-size: 14px;
        font-weight: 800;
        color: #ea580c;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notice-content {
        font-size: 13px;
        color: #555;
        line-height: 1.6;
    }
</style>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>진행 현황</h1>
        <p><?php echo $customer['customer_name']; ?> 고객님</p>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Customer Info -->
        <div class="section">
            <div class="section-title">고객 정보</div>
            <div class="info-grid">
                <div class="info-label">업체명</div>
                <div class="info-value"><?php echo $customer['customer_name']; ?></div>

                <?php if ($customer['customer_manager']): ?>
                    <div class="info-label">담당자</div>
                    <div class="info-value"><?php echo $customer['customer_manager']; ?></div>
                <?php endif; ?>

                <div class="info-label">연락처</div>
                <div class="info-value"><?php echo $customer['customer_hp']; ?></div>

                <?php if ($customer['customer_addr']): ?>
                    <div class="info-label">주소</div>
                    <div class="info-value"><?php echo $customer['customer_addr']; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Progress -->
        <div class="section">
            <div class="section-title">진행 상태</div>

            <?php
            $all_statuses = ['견적발송', '계약입금', '디자인중', '제작중', '시공중', '시공완료'];
            $status_mapping = [
                '견적서발송' => '견적발송',
                '계약금입금' => '계약입금',
                '디자인작업중' => '디자인중',
                '제작중' => '제작중',
                '시공중' => '시공중',
                '시공완료' => '시공완료'
            ];
            $current_status_short = isset($status_mapping[$current_status]) ? $status_mapping[$current_status] : $current_status;
            $current_index = array_search($current_status_short, $all_statuses);
            if ($current_index === false)
                $current_index = 0;
            ?>

            <div class="status-steps">
                <?php foreach ($all_statuses as $idx => $step): ?>
                    <div
                        class="status-step <?php echo $idx == $current_index ? 'active' : ($idx < $current_index ? 'completed' : ''); ?>">
                        <div class="status-circle">
                            <?php if ($idx < $current_index): ?>
                                <i class="fas fa-check"></i>
                            <?php elseif ($idx == $current_index): ?>
                                <i class="fas fa-circle"></i>
                            <?php else: ?>
                                <?php echo $idx + 1; ?>
                            <?php endif; ?>
                        </div>
                        <div class="status-label"><?php echo $step; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quote Items -->
        <?php if ($quote && count($items) > 0): ?>
            <div class="section">
                <div class="section-title">견적 구성</div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>품목</th>
                            <th>규격</th>
                            <th>수량</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($items as $item):
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <div class="item-name"><?php echo $item['qi_item']; ?></div>
                                    <?php if ($item['qi_note']): ?>
                                        <div style="font-size: 12px; color: #999; margin-top: 5px;"><?php echo $item['qi_note']; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="item-spec"><?php echo $item['qi_spec']; ?></td>
                                <td class="item-qty"><?php echo number_format($item['qi_qty']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Notice -->
        <div class="notice-box">
            <div class="notice-title">
                <i class="fas fa-info-circle"></i>
                안내사항
            </div>
            <div class="notice-content">
                • 진행 상태는 실시간으로 업데이트됩니다.<br>
                • 문의사항이 있으시면 담당자에게 연락 주시기 바랍니다.<br>
                • 이 페이지는 고객님만 접근 가능한 전용 페이지입니다.
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.sub.php');
?>
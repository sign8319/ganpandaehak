<?php
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$tab = isset($_GET['tab']) ? trim($_GET['tab']) : '';
$qa_id = isset($_GET['qa_id']) ? (int)$_GET['qa_id'] : 0;

if (!$qa_id) {
    echo json_encode(['success' => false, 'message' => 'ID가 필요합니다.']);
    exit;
}

// 견적서 기본 정보 조회
$quote = sql_fetch("SELECT * FROM g5_quote WHERE qa_id = '$qa_id'");

if (!$quote) {
    echo json_encode(['success' => false, 'message' => '견적서를 찾을 수 없습니다.']);
    exit;
}

$html = '';

// 탭별 데이터 생성
switch ($tab) {
    case 'estimate':
        $html = getEstimateTab($qa_id, $quote);
        break;
    case 'quote':
        $html = getQuoteTab($qa_id, $quote);
        break;
    case 'customer':
        $html = getCustomerTab($qa_id, $quote);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '잘못된 탭입니다.']);
        exit;
}

echo json_encode(['success' => true, 'html' => $html]);
exit;

// ============================================================================
// 현장실측 탭
// ============================================================================
function getEstimateTab($qa_id, $quote) {
    // 현장실측 데이터 조회
    $sql = "SELECT * FROM g5_quote_measure WHERE qa_id = '$qa_id' ORDER BY qm_index";
    $result = sql_query($sql);
    
    $measures = [];
    while ($row = sql_fetch_array($result)) {
        $measures[] = $row;
    }
    
    ob_start();
    ?>
    <div class="detail-section">
        <h3><i class="fas fa-ruler-combined"></i> 기본 정보</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <label>견적번호</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_code']); ?></div>
            </div>
            <div class="detail-item">
                <label>작성일</label>
                <div class="value"><?php echo $quote['qa_datetime']; ?></div>
            </div>
            <div class="detail-item">
                <label>업체명</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_name']); ?></div>
            </div>
            <div class="detail-item">
                <label>연락처</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_hp'] ?? $quote['qa_client_contact'] ?? '-'); ?></div>
            </div>
        </div>
    </div>

    <?php if (count($measures) > 0): ?>
    <div class="detail-section">
        <h3><i class="fas fa-tape"></i> 현장실측 목록 (<?php echo count($measures); ?>개)</h3>
        <table class="measure-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">NO</th>
                    <th>간판 종류</th>
                    <th style="width: 100px; text-align: center;">가로(W)</th>
                    <th style="width: 100px; text-align: center;">세로(H)</th>
                    <th style="width: 80px; text-align: center;">수량</th>
                    <th>메모</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach($measures as $m): 
                ?>
                <tr>
                    <td style="text-align: center; font-weight: bold; color: #667eea;"><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($m['qm_type']); ?></strong></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($m['qm_width']); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($m['qm_height']); ?></td>
                    <td style="text-align: center;"><?php echo number_format($m['qm_qty']); ?></td>
                    <td><?php echo $m['qm_memo'] ? nl2br(htmlspecialchars($m['qm_memo'])) : '-'; ?></td>
                </tr>
                <?php if ($m['qm_img1'] || $m['qm_img2']): ?>
                <tr>
                    <td colspan="6" style="background: #f9fafb; padding: 15px;">
                        <strong style="display: block; margin-bottom: 10px; color: #666;">
                            <i class="fas fa-images"></i> 첨부 이미지:
                        </strong>
                        <div class="measure-images">
                            <?php if ($m['qm_img1']): ?>
                            <img src="<?php echo htmlspecialchars($m['qm_img1']); ?>" 
                                 onclick="window.open(this.src, '_blank')"
                                 alt="측정 이미지 1">
                            <?php endif; ?>
                            <?php if ($m['qm_img2']): ?>
                            <img src="<?php echo htmlspecialchars($m['qm_img2']); ?>" 
                                 onclick="window.open(this.src, '_blank')"
                                 alt="측정 이미지 2">
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>등록된 현장실측 데이터가 없습니다.</p>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// ============================================================================
// 견적작성 탭
// ============================================================================
function getQuoteTab($qa_id, $quote) {
    // 견적 품목 조회
    $sql = "SELECT * FROM g5_quote_item WHERE qa_id = '$qa_id' ORDER BY qi_index, qi_id";
    $result = sql_query($sql);
    
    $items = [];
    $total_supply = 0;
    $total_vat = 0;
    
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
        $total_supply += $row['qi_supply'];
        $total_vat += $row['qi_vat'];
    }
    
    $total_amount = $total_supply + $total_vat;
    
    ob_start();
    ?>
    <div class="detail-section">
        <h3><i class="fas fa-file-alt"></i> 견적서 정보</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <label>견적번호</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_code']); ?></div>
            </div>
            <div class="detail-item">
                <label>작성일</label>
                <div class="value"><?php echo $quote['qa_datetime']; ?></div>
            </div>
            <div class="detail-item">
                <label>업체명</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_name']); ?></div>
            </div>
            <div class="detail-item">
                <label>연락처</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_hp'] ?? $quote['qa_client_contact'] ?? '-'); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>주소</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_addr'] ?: '-'); ?></div>
            </div>
        </div>
    </div>

    <?php if (count($items) > 0): ?>
    <div class="detail-section">
        <h3><i class="fas fa-list-ul"></i> 견적 품목 (<?php echo count($items); ?>개)</h3>
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">NO</th>
                    <th>품목명</th>
                    <th style="width: 120px;">규격</th>
                    <th style="width: 80px; text-align: center;">수량</th>
                    <th style="width: 120px; text-align: right;">단가</th>
                    <th style="width: 120px; text-align: right;">공급가액</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach($items as $item): 
                ?>
                <tr>
                    <td style="text-align: center; font-weight: bold; color: #667eea;"><?php echo $no++; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($item['qi_item']); ?></strong>
                        <?php if ($item['qi_note']): ?>
                        <div style="font-size: 12px; color: #999; margin-top: 5px;">
                            <?php echo nl2br(htmlspecialchars($item['qi_note'])); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['qi_spec']); ?></td>
                    <td style="text-align: center;"><?php echo number_format($item['qi_qty']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($item['qi_price']); ?>원</td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($item['qi_supply']); ?>원
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="detail-section">
        <h3><i class="fas fa-calculator"></i> 금액 계산</h3>
        <div class="price-section">
            <div class="price-row">
                <span>공급가액</span>
                <span><?php echo number_format($total_supply); ?>원</span>
            </div>
            <div class="price-row">
                <span>부가세(10%)</span>
                <span><?php echo number_format($total_vat); ?>원</span>
            </div>
            <div class="price-row">
                <span>합계</span>
                <span><?php echo number_format($total_amount); ?>원</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>등록된 견적 품목이 없습니다.</p>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// ============================================================================
// 고객등록 탭
// ============================================================================
function getCustomerTab($qa_id, $quote) {
    // 고객 정보 조회 (qa_customer_id가 있으면)
    $customer = null;
    $customer_status = null;
    $share_link = null;
    
    if ($quote['qa_customer_id']) {
        $customer_id = $quote['qa_customer_id'];
        $customer = sql_fetch("SELECT * FROM g5_customer WHERE customer_id = '$customer_id'");
        $customer_status = sql_fetch("SELECT * FROM g5_customer_status WHERE customer_id = '$customer_id'");
        $share_link = sql_fetch("SELECT * FROM g5_customer_share_link WHERE customer_id = '$customer_id'");
    }
    
    ob_start();
    ?>
    <div class="detail-section">
        <h3><i class="fas fa-building"></i> 고객 기본 정보</h3>
        
        <?php if ($customer): ?>
        <div class="detail-grid">
            <div class="detail-item">
                <label>업체명</label>
                <div class="value"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
            </div>
            <div class="detail-item">
                <label>담당자</label>
                <div class="value"><?php echo htmlspecialchars($customer['customer_manager'] ?: '-'); ?></div>
            </div>
            <div class="detail-item">
                <label>연락처</label>
                <div class="value"><?php echo htmlspecialchars($customer['customer_hp']); ?></div>
            </div>
            <div class="detail-item">
                <label>이메일</label>
                <div class="value"><?php echo htmlspecialchars($customer['customer_email'] ?: '-'); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>주소</label>
                <div class="value"><?php echo htmlspecialchars($customer['customer_addr'] ?: '-'); ?></div>
            </div>
            <?php if ($customer['customer_tags']): ?>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>태그</label>
                <div class="value">
                    <?php 
                    $tags = explode(',', $customer['customer_tags']);
                    foreach($tags as $tag): 
                        $tag = trim($tag);
                        if ($tag):
                    ?>
                    <span style="display: inline-block; background: #e0e7ff; color: #4f46e5; padding: 4px 12px; border-radius: 12px; margin-right: 5px; font-size: 12px;">
                        <?php echo htmlspecialchars($tag); ?>
                    </span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($customer['customer_memo']): ?>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>메모</label>
                <div class="value" style="white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($customer['customer_memo'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
            <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                <i class="fas fa-exclamation-triangle" style="font-size: 20px;"></i>
                <div>
                    <strong>고객 정보가 등록되지 않았습니다.</strong><br>
                    <span style="font-size: 13px;">견적서에서 고객을 등록하면 상세 정보를 볼 수 있습니다.</span>
                </div>
            </div>
        </div>
        
        <!-- 견적서 정보로 대체 표시 -->
        <div class="detail-grid" style="margin-top: 20px;">
            <div class="detail-item">
                <label>업체명 (견적서 기준)</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_name']); ?></div>
            </div>
            <div class="detail-item">
                <label>연락처 (견적서 기준)</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_hp'] ?? $quote['qa_client_contact'] ?? '-'); ?></div>
            </div>
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>주소 (견적서 기준)</label>
                <div class="value"><?php echo htmlspecialchars($quote['qa_client_addr'] ?: '-'); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($customer_status): ?>
    <div class="detail-section">
        <h3><i class="fas fa-tasks"></i> 진행 상태</h3>
        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 13px; color: #666; margin-bottom: 5px;">현재 상태</div>
                    <div style="font-size: 24px; font-weight: bold; color: #0369a1;">
                        <?php echo htmlspecialchars($customer_status['status_step']); ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 13px; color: #666; margin-bottom: 5px;">최종 업데이트</div>
                    <div style="font-size: 14px; color: #333;">
                        <?php echo $customer_status['updated_at']; ?>
                    </div>
                    <?php if ($customer_status['updated_by']): ?>
                    <div style="font-size: 12px; color: #999; margin-top: 3px;">
                        담당: <?php echo htmlspecialchars($customer_status['updated_by']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($share_link): ?>
    <div class="detail-section">
        <h3><i class="fas fa-link"></i> 공유 링크</h3>
        <div style="background: <?php echo $share_link['is_active'] ? '#f0fdf4' : '#fef2f2'; ?>; padding: 20px; border-radius: 8px; border-left: 4px solid <?php echo $share_link['is_active'] ? '#10b981' : '#ef4444'; ?>;">
            <div style="margin-bottom: 15px;">
                <div style="font-size: 13px; color: #666; margin-bottom: 5px;">상태</div>
                <div style="font-size: 16px; font-weight: bold; color: <?php echo $share_link['is_active'] ? '#059669' : '#dc2626'; ?>;">
                    <?php echo $share_link['is_active'] ? '✓ 활성화' : '✗ 비활성화'; ?>
                </div>
            </div>
            <?php if ($share_link['is_active']): ?>
            <div>
                <div style="font-size: 13px; color: #666; margin-bottom: 5px;">공유 URL</div>
                <div style="background: white; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 13px; word-break: break-all; border: 1px solid #d1d5db;">
                    <?php 
                    $share_url = G5_URL . '/theme/basic/customer.php?token=' . $share_link['share_token'];
                    echo htmlspecialchars($share_url); 
                    ?>
                </div>
                <button onclick="navigator.clipboard.writeText('<?php echo $share_url; ?>').then(() => alert('클립보드에 복사되었습니다!'))" 
                        style="margin-top: 10px; padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                    <i class="fas fa-copy"></i> 링크 복사
                </button>
            </div>
            <?php endif; ?>
            <div style="margin-top: 15px; font-size: 12px; color: #999;">
                생성일: <?php echo $share_link['created_at']; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($customer): ?>
    <div class="detail-section">
        <h3><i class="fas fa-clock"></i> 등록 정보</h3>
        <div class="detail-grid">
            <div class="detail-item">
                <label>등록일</label>
                <div class="value"><?php echo $customer['created_at']; ?></div>
            </div>
            <div class="detail-item">
                <label>수정일</label>
                <div class="value"><?php echo $customer['updated_at']; ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
?>

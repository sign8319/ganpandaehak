<?php
// 디버깅을 위해 에러 출력 활성화 (문제 해결 후 주석 처리 필요)
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

include_once('./_common.php'); // 현재 폴더의 _common.php 참조

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

$page_title = "내 견적 내역";
include_once(G5_THEME_PATH . '/head.php');

$mb_id = $member['mb_id'];

// 견적 내역 조회
// g5_quote 테이블 사용 (admin_quote.php 와 통합)
// [DRAFT SYSTEM] Exclude draft and cancelled quotes
$sql = " SELECT q.*, 
         (SELECT COUNT(*) FROM g5_quote_item qi WHERE qi.qa_id = q.qa_id) as item_count 
         FROM g5_quote q 
         WHERE q.mb_id = '{$mb_id}' AND q.qa_status != '취소' AND q.qa_status != 'draft' 
         ORDER BY q.qa_datetime DESC ";
$result = sql_query($sql);
?>

<style>
    .quote-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .quote-h1 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 30px;
        color: #333;
    }

    .quote-item {
        border: 1px solid #eee;
        padding: 25px;
        margin-bottom: 20px;
        border-radius: 15px;
        background: white;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .quote-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }

    .quote-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #888;
        font-size: 13px;
        margin-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .quote-date {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .quote-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 12px;
        color: #222;
    }

    .quote-info {
        color: #555;
        margin-bottom: 15px;
        font-size: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .quote-info span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }

    /* 작성중 */
    .status-badge.sent {
        background: #dbeafe;
        color: #1e40af;
    }

    /* 견적발송, 작업중 */
    .status-badge.replied {
        background: #d4edda;
        color: #155724;
    }

    /* 계약완료, 작업완료 */
    .status-badge.cancel {
        background: #fee2e2;
        color: #991b1b;
    }

    /* 취소, 연락두절 */

    .quote-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .btn-detail {
        padding: 8px 16px;
        background: #333;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-size: 13px;
        transition: background 0.2s;
    }

    .btn-detail:hover {
        background: #000;
    }

    .btn-download {
        padding: 8px 16px;
        border: 1px solid #ddd;
        background: white;
        color: #333;
        text-decoration: none;
        border-radius: 5px;
        font-size: 13px;
        transition: background 0.2s;
    }

    .btn-download:hover {
        background: #f9f9f9;
        border-color: #ccc;
    }

    .btn-delete {
        padding: 6px 12px;
        border: 1px solid #ffcccc;
        background: #fff5f5;
        color: #ff4444;
        text-decoration: none;
        border-radius: 5px;
        font-size: 12px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #ffe6e6;
        border-color: #ff9999;
        color: #cc0000;
    }

    .quote-header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .empty-state {
        text-align: center;
        padding: 80px 0;
        color: #888;
    }
</style>

<div class="quote-container">
    <h1 class="quote-h1">내 견적 내역</h1>

    <div class="quote-list">
        <?php
        $count = 0;
        // g5_quote 테이블 데이터 순회
        while ($quote = sql_fetch_array($result)) {
            $count++;

            // 상태값에 따른 스타일 및 텍스트 매핑
            $status_class = 'pending';
            $status_text = $quote['qa_status'];

            // 상태별 CSS 클래스 지정
            if (in_array($quote['qa_status'], ['견적발송', '작업중'])) {
                $status_class = 'sent';
            } else if (in_array($quote['qa_status'], ['계약완료', '작업완료'])) {
                $status_class = 'replied';
            } else if (in_array($quote['qa_status'], ['취소', '연락두절'])) {
                $status_class = 'cancel';
            }

            // 주소 없음 처리
            $location_text = $quote['qa_client_addr'] ? $quote['qa_client_addr'] : '미입력';

            // 제목 (없으면 자동 생성된 QA_SUBJECT 사용)
            $subject = $quote['qa_subject'] ? $quote['qa_subject'] : '상세 견적 요청';
            ?>
            <div class="quote-item">
                <div class="quote-header">
                    <div class="quote-id">견적번호 : <?php echo $quote['qa_code']; ?></div>
                    <div class="quote-header-right">
                        <div class="quote-date">
                            <i class="fa fa-calendar-o"></i>
                            <?php echo date('Y.m.d', strtotime($quote['qa_datetime'])); ?>
                        </div>
                        <button type="button" class="btn-delete" onclick="deleteQuote(<?php echo $quote['qa_id']; ?>)">
                            삭제
                        </button>
                    </div>
                </div>

                <div class="quote-title">
                    <?php echo $subject; ?>
                </div>

                <div class="quote-info">
                    <span><i class="fa fa-map-marker"></i> <?php echo $location_text; ?></span>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_text; ?>
                    </span>
                </div>

                <div class="quote-actions">
                    <a href="<?php echo G5_URL ?>/mypage/quote_detail.php?id=<?php echo $quote['qa_id']; ?>"
                        class="btn-detail">상세보기</a>

                    <?php if ($quote['qa_status'] == '견적발송' || $quote['qa_status'] == '계약완료' || $quote['qa_status'] == '작업완료'): ?>
                        <a href="#" class="btn-download" onclick="alert('준비중입니다.'); return false;">📝 견적서 다운로드</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php
        }

        if ($count == 0) {
            ?>
            <div class="empty-state">
                <i class="fa fa-file-text-o" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
                <p>신청한 견적 내역이 없습니다.</p>
                <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=consult"
                    style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #ff6b00; color: white; border-radius: 5px; text-decoration: none;">
                    견적 요청하기
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    function deleteQuote(id) {
        if (confirm('정말 삭제하시겠습니까?\n목록에서 사라지며, 관리자에게 취소 상태로 전달됩니다.')) {
            location.href = '<?php echo G5_URL ?>/mypage/delete_quote.php?id=' + id;
        }
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
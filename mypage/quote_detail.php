<?php
// 디버깅 설정 (문제 해결 후 주석 처리)
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$mb_id = $member['mb_id'];

if (!$id) {
    alert('잘못된 접근입니다.');
}

// 본인 견적 확인
$sql = " SELECT * FROM g5_quote WHERE qa_id = '{$id}' AND mb_id = '{$mb_id}' ";
$quote = sql_fetch($sql);

if (!$quote) {
    alert('존재하지 않거나 열람 권한이 없는 견적입니다.');
}

$page_title = "견적 내역 상세";
include_once(G5_THEME_PATH . '/head.php');

// 답변 조회
$replies = sql_query(" SELECT * FROM quote_replies WHERE quote_id = '{$id}' ORDER BY created_at ASC ");

// 데이터 파싱 (qa_memo_user 에서 예산, 일정 추출)
$content = $quote['qa_memo_user'];
$budget = '';
$schedule = '';

// 예산 추출
if (preg_match('/\[예산\] (.*?)(?:\n|$)/u', $content, $matches)) {
    $budget = trim($matches[1]);
    $content = str_replace($matches[0], '', $content);
}

// 일정 추출
if (preg_match('/\[오픈예정일\] (.*?)(?:\n|$)/u', $content, $matches)) {
    $schedule = trim($matches[1]);
    $content = str_replace($matches[0], '', $content);
}

$content = trim($content);
?>
<div class="w-full md:max-w-3xl mx-auto py-8 px-4">

    <!-- 상단 상태 배지 및 제목 -->
    <div class="flex flex-col items-center mb-8">
        <?php
        $status_bg = 'bg-gray-100 text-gray-800';
        if (in_array($quote['qa_status'], ['견적발송', '작업중']))
            $status_bg = 'bg-blue-100 text-blue-800';
        if (in_array($quote['qa_status'], ['계약완료', '작업완료']))
            $status_bg = 'bg-green-100 text-green-800';
        if (in_array($quote['qa_status'], ['취소', '연락두절']))
            $status_bg = 'bg-red-100 text-red-800';
        ?>
        <span class="px-3 py-1 rounded-full text-sm font-bold mb-3 <?php echo $status_bg; ?>">
            <?php echo $quote['qa_status']; ?>
        </span>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">견적 요청 상세</h1>
        <p class="text-gray-500 mt-2">견적번호: <?php echo $quote['qa_code']; ?> | 신청일: <?php echo $quote['qa_datetime']; ?>
        </p>
    </div>

    <div class="bg-white overflow-hidden md:rounded-2xl md:shadow-2xl md:border md:border-gray-100">

        <!-- 로고 이미지 (폼과 동일한 느낌) -->
        <div class="flex justify-center pt-8 pb-4">
            <img src="/data/assets/asset_20251228193653_7935.png" alt="간판대학" class="w-36 md:w-48 object-contain">
        </div>

        <div class="p-4 md:p-10 space-y-6">

            <!-- 이름 / 연락처 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">이름 또는 업체명</label>
                    <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                        <?php echo htmlspecialchars($quote['qa_client_name']); ?>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">연락처</label>
                    <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                        <?php echo htmlspecialchars($quote['qa_client_hp']); ?>
                    </div>
                </div>
            </div>

            <!-- 주소 -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">설치 주소</label>
                <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3 mb-2">
                    <?php echo $quote['qa_client_addr'] ? htmlspecialchars($quote['qa_client_addr']) : '주소 미입력'; ?>
                </div>
                <?php if ($quote['qa_client_addr2']): ?>
                    <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                        <?php echo htmlspecialchars($quote['qa_client_addr2']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 간판 종류 -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">어떤 간판이 필요하세요?</label>
                <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                    <?php echo htmlspecialchars($quote['qa_subject']); ?>
                </div>
            </div>

            <!-- 예산 / 일정 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">예상 예산대</label>
                    <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                        <?php echo $budget ? htmlspecialchars($budget) : '미선택'; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">오픈 예정일</label>
                    <div class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3">
                        <?php echo $schedule ? htmlspecialchars($schedule) : '미입력'; ?>
                    </div>
                </div>
            </div>

            <!-- 내용 -->
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">전달하고 싶은 내용</label>
                <div
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-3 min-h-[100px] whitespace-pre-line">
                    <?php echo htmlspecialchars($content); ?>
                </div>
            </div>

        </div>
    </div>

    <!-- 답변 섹션 -->
    <?php
    $reply_count = sql_num_rows($replies);
    if ($reply_count > 0):
        ?>
        <div class="mt-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 px-2">📬 도착한 답변</h3>
            <?php while ($reply = sql_fetch_array($replies)): ?>
                <div class="bg-orange-50 border border-orange-100 rounded-2xl p-6 shadow-sm mb-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="flex items-center text-orange-600 font-bold">
                            <span class="bg-orange-100 p-1 rounded-full mr-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </span>
                            간판대학 담당자
                        </span>
                        <span class="text-gray-400 text-sm">
                            <?php echo date('Y.m.d H:i', strtotime($reply['created_at'])); ?>
                        </span>
                    </div>
                    <div class="text-gray-800 leading-relaxed whitespace-pre-line">
                        <?php echo htmlspecialchars($reply['content']); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <div class="mt-8 flex justify-center gap-2">
        <a href="./quote_history.php"
            class="px-6 py-2.5 bg-gray-800 text-white rounded-xl hover:bg-gray-700 transition font-medium">
            목록으로
        </a>
        <?php if (in_array($quote['qa_status'], ['작성중', '견적발송'])): ?>
            <a href="./quote_edit.php?id=<?php echo $id; ?>"
                class="px-6 py-2.5 bg-orange-500 text-white rounded-xl hover:bg-orange-600 transition font-medium">
                수정하기
            </a>
        <?php endif; ?>
    </div>

</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
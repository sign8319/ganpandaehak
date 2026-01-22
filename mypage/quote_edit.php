<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$mb_id = $member['mb_id'];

if (!$id) {
    alert('잘못된 접근입니다.');
}

// 견적 조회
$sql = " SELECT * FROM g5_quote WHERE qa_id = '{$id}' AND mb_id = '{$mb_id}' ";
$quote = sql_fetch($sql);

if (!$quote) {
    alert('존재하지 않거나 수정 권한이 없는 견적입니다.');
}

// 수정 가능 상태 확인
if (!in_array($quote['qa_status'], ['작성중', '견적발송'])) {
    alert('진행 중이거나 완료된 견적은 수정할 수 없습니다.');
}

// 품목 조회 (간판 종류)
$item_sql = " SELECT * FROM g5_quote_item WHERE qa_id = '{$id}' ORDER BY qi_index ASC LIMIT 1 ";
$item = sql_fetch($item_sql);
$sign_type = $item ? $item['qi_item'] : '';

// 데이터 파싱
$content = $quote['qa_memo_user'];
$budget = '';
$schedule = '';

if (preg_match('/\[예산\] (.*?)(?:\n|$)/u', $content, $matches)) {
    $budget = trim($matches[1]);
    $content = str_replace($matches[0], '', $content);
}

if (preg_match('/\[오픈예정일\] (.*?)(?:\n|$)/u', $content, $matches)) {
    $schedule = trim($matches[1]);
    $content = str_replace($matches[0], '', $content);
}
$content = trim($content);

$page_title = "견적 내역 수정";
include_once(G5_THEME_PATH . '/head.php');
?>

<!-- 다음 우편번호 -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<div class="w-full md:max-w-3xl mx-auto py-8 px-4">
    <div class="flex flex-col items-center mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">견적 요청 수정</h1>
        <p class="text-gray-500 mt-2">견적번호:
            <?php echo $quote['qa_code']; ?>
        </p>
    </div>

    <div class="bg-white overflow-hidden md:rounded-2xl md:shadow-2xl md:border md:border-gray-100">
        <form name="fquoteedit" action="./quote_edit_update.php" onsubmit="return fquoteedit_submit(this);"
            method="post" enctype="multipart/form-data" autocomplete="off" class="p-4 md:p-10">
            <input type="hidden" name="qa_id" value="<?php echo $id; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label for="qa_client_name" class="block text-gray-700 text-sm font-bold mb-1">이름 또는 업체명 <span
                            class="text-orange-500">*</span></label>
                    <input type="text" name="qa_client_name"
                        value="<?php echo htmlspecialchars($quote['qa_client_name']); ?>" id="qa_client_name" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                </div>
                <div>
                    <label for="qa_client_hp" class="block text-gray-700 text-sm font-bold mb-1">연락처 <span
                            class="text-orange-500">*</span></label>
                    <input type="tel" name="qa_client_hp"
                        value="<?php echo htmlspecialchars($quote['qa_client_hp']); ?>" id="qa_client_hp" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-1">설치 주소 (선택)</label>
                <div class="flex gap-2 mb-2">
                    <input type="text" name="qa_client_addr" id="qa_client_addr"
                        value="<?php echo htmlspecialchars($quote['qa_client_addr']); ?>" placeholder="주소 검색을 클릭하세요"
                        readonly
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-2.5 cursor-pointer"
                        onclick="openDaumPostcode()">
                    <button type="button" onclick="openDaumPostcode()"
                        class="whitespace-nowrap bg-gray-800 text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-700 transition">주소
                        검색</button>
                </div>
                <input type="text" name="qa_client_addr2" id="qa_client_addr2"
                    value="<?php echo htmlspecialchars($quote['qa_client_addr2']); ?>" placeholder="상세 주소를 입력해주세요"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl p-2.5">
            </div>

            <div class="mb-4">
                <label for="sign_type" class="block text-gray-700 text-sm font-bold mb-1">어떤 간판이 필요하세요?</label>
                <select name="sign_type" id="sign_type"
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                    <option value="">간판 종류를 선택해주세요</option>
                    <?php
                    $types = ['채널간판', '스카시(입체문자)', '플렉스간판', '돌출간판/입간판', '네온사인/아트 neon', '어닝/천막', '현수막/실사출력', '기타/모름'];
                    foreach ($types as $t) {
                        $selected = ($sign_type == $t) ? 'selected' : '';
                        echo "<option value='{$t}' {$selected}>{$t}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label for="budget_range" class="block text-gray-700 text-sm font-bold mb-1">예상 예산대</label>
                    <select name="budget_range" id="budget_range"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                        <option value="">선택하세요</option>
                        <?php
                        $budgets = ['50만원 이하', '50~100만원', '100~200만원', '200~300만원', '300~500만원', '500만원 이상', '미정'];
                        foreach ($budgets as $b) {
                            $selected = ($budget == $b) ? 'selected' : '';
                            echo "<option value='{$b}' {$selected}>{$b}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="expected_date" class="block text-gray-700 text-sm font-bold mb-1">오픈 예정일</label>
                    <input type="text" name="expected_date" id="expected_date"
                        value="<?php echo htmlspecialchars($schedule); ?>"
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                </div>
            </div>

            <div class="mb-6">
                <label for="qa_content" class="block text-gray-700 text-sm font-bold mb-1">전달하고 싶은 내용 <span
                        class="text-orange-500">*</span></label>
                <textarea name="qa_content" id="qa_content" rows="6" required
                    class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5"
                    placeholder="문의하실 내용을 자유롭게 적어주세요."><?php echo htmlspecialchars($content); ?></textarea>
            </div>

            <div class="flex justify-center gap-2">
                <a href="./quote_detail.php?id=<?php echo $id; ?>"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">취소</a>
                <button type="submit"
                    class="px-6 py-3 bg-orange-500 text-white rounded-xl font-bold hover:bg-orange-600 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">수정
                    완료</button>
            </div>

        </form>
    </div>
</div>

<script>
    function openDaumPostcode() {
        new daum.Postcode({
            oncomplete: function (data) {
                var addr = '';
                if (data.userSelectedType === 'R') {
                    addr = data.roadAddress;
                } else {
                    addr = data.jibunAddress;
                }
                document.getElementById('qa_client_addr').value = addr;
                document.getElementById('qa_client_addr2').focus();
            }
        }).open();
    }

    function fquoteedit_submit(f) {
        if (!f.qa_client_name.value) {
            alert("이름 또는 업체명을 입력해주세요.");
            return false;
        }
        return true;
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
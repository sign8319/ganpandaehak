<?php
if (!defined('_GNUBOARD_'))
    exit;
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');

    body {
        font-family: 'Pretendard', sans-serif;
    }
</style>

<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900">결제창 생성</h2>
        <p class="text-gray-500 mt-2">복잡한 설정 없이 금액만 입력하세요.</p>
    </div>

    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);"
        method="post" enctype="multipart/form-data" autocomplete="off" class="space-y-6">

        <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="secret" value="secret">
        <input type="hidden" name="html" value="html1">

        <div>
            <label class="block text-lg font-bold text-gray-800 mb-2">고객명 (결제 건명)</label>
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" required
                class="w-full border-gray-300 rounded-lg p-4 text-lg bg-white" placeholder="예: 홍길동 고객님 잔금">
        </div>

        <div>
            <label class="block text-lg font-bold text-gray-800 mb-2">비밀번호 설정</label>
            <input type="text" name="custom_password" required
                class="w-full border-gray-300 rounded-lg p-4 text-lg bg-white" placeholder="고객 접속용 비밀번호를 입력해주세요">
        </div>

        <div>
            <label class="block text-lg font-bold text-gray-800 mb-2">이미지 첨부 (송장/영수증)</label>
            <input type="file" name="bf_file[]" class="w-full border-gray-300 rounded-lg p-4 text-lg bg-white border">
        </div>

        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
            <label class="block text-lg font-bold text-gray-800 mb-2">최종 결제 금액</label>
            <div class="relative">
                <input type="text" name="wr_1" id="wr_1" value="<?php echo $wr_1 ?>" onkeyup="inputNumberFormat(this)"
                    class="w-full border-gray-300 rounded-lg p-4 text-right pr-12 text-2xl font-black text-brand-orange"
                    placeholder="0" required>
                <span class="absolute right-4 top-5 text-gray-500 font-bold text-lg">원</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">* 콤마(,)는 자동으로 찍힙니다.</p>
        </div>

        <div>
            <label class="block text-lg font-bold text-gray-800 mb-2">메모 / 상세 내용</label>
            <?php echo $editor_html; ?>
        </div>

        <div class="flex justify-center gap-4 pt-6 border-t">
            <a href="<?php echo get_pretty_url($bo_table); ?>"
                class="px-8 py-4 bg-gray-200 text-gray-700 rounded-xl font-bold">취소</a>
            <button type="submit" id="btn_submit" accesskey="s"
                class="px-12 py-4 bg-gray-900 text-white rounded-xl font-bold hover:bg-black">생성 완료</button>
        </div>
    </form>
</div>

<script>
    // 콤마 찍기 함수
    function inputNumberFormat(obj) {
        obj.value = comma(uncomma(obj.value));
    }
    function comma(str) {
        str = String(str);
        return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
    }
    function uncomma(str) {
        str = String(str);
        return str.replace(/[^\d]+/g, '');
    }

    function fwrite_submit(f) {
        <?php echo $editor_js; ?>
        document.getElementById("btn_submit").disabled = true;
        return true;
    }
</script>
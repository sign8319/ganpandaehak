<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        orange: '#F97316',
                        dark: '#1F2937',
                    }
                }
            }
        }
    }
</script>
<style>
    @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');

    body {
        font-family: 'Pretendard', sans-serif;
    }
</style>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-bold text-gray-900">상품 등록</h2>
        <p class="text-gray-500 mt-2">나만의 특별한 디자인 간판을 등록해주세요.</p>
    </div>

    <!-- 게시물 작성/수정 시작 { -->
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

        <?php
        $option = '';
        $option_hidden = '';
        if ($is_notice || $is_html || $is_secret || $is_mail) {
            $option = '';
            if ($is_notice) {
                $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="notice" name="notice" class="form-checkbox text-brand-orange rounded" value="1" ' . $notice_checked . '> <span class="ml-2">공지</span></label>';
            }
            if ($is_html) {
                if ($is_dhtml_editor) {
                    $option_hidden .= '<input type="hidden" value="html1" name="html">';
                } else {
                    $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="form-checkbox text-brand-orange rounded" value="' . $html_value . '" ' . $html_checked . '> <span class="ml-2">HTML</span></label>';
                }
            }
            if ($is_secret) {
                if ($is_admin || $is_secret == 1) {
                    $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="secret" name="secret" class="form-checkbox text-brand-orange rounded" value="secret" ' . $secret_checked . '> <span class="ml-2">비밀글</span></label>';
                } else {
                    $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                }
            }
        }
        echo $option_hidden;
        ?>

        <!-- 옵션 체크박스 -->
        <?php if ($option) { ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <?php echo $option ?>
            </div>
        <?php } ?>

        <!-- 카테고리 -->
        <?php if ($is_category) { ?>
            <div>
                <label for="ca_name" class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                <select name="ca_name" id="ca_name" required
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3">
                    <option value="">분류를 선택하세요</option>
                    <?php echo $category_option ?>
                </select>
            </div>
        <?php } ?>

        <!-- 제목 -->
        <div>
            <label for="wr_subject" class="block text-sm font-medium text-gray-700 mb-1">상품명</label>
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3"
                placeholder="상품명을 입력하세요">
        </div>

        <!-- Price Removed -->

        <!-- 내용 -->
        <div>
            <label for="wr_content" class="block text-sm font-medium text-gray-700 mb-1">상품 설명</label>
            <div class="wr_content">
                <?php if ($write_min || $write_max) { ?>
                    <p id="char_count_desc" class="text-xs text-gray-500 mb-1">최소
                        <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하
                    </p>
                <?php } ?>
                <?php echo $editor_html; ?>
                <p class='text-sm text-red-500 mt-2'>※ 본문에 삽입된 이미지는 에디터상에서 크게 보여도, 실제 등록 후에는 화면 크기에 맞춰 자동으로 조절됩니다.</p>
            </div>
        </div>

        <!-- 링크 -->
        <?php for ($i = 1; $is_link && $i <= G5_LINK_COUNT; $i++) { ?>
            <div>
                <label for="wr_link<?php echo $i ?>" class="block text-sm font-medium text-gray-700 mb-1">링크
                    #<?php echo $i ?></label>
                <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w == "u") {
                       echo $write['wr_link' . $i];
                   } ?>" id="wr_link<?php echo $i ?>"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3">
            </div>
        <?php } ?>

        <!-- 파일 첨부 -->
        <?php for ($i = 0; $is_file && $i < $file_count; $i++) { ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <label for="bf_file_<?php echo $i + 1 ?>"
                    class="block text-sm font-medium mb-2 <?php echo $i == 0 ? 'text-red-500 font-bold' : 'text-gray-700'; ?>">
                    <?php if ($i == 0) { ?>
                        대표 이미지 (썸네일 - 목록에 보여질 사진)
                    <?php } else { ?>
                        상세 페이지 추가 이미지 #<?php echo $i; ?>
                    <?php } ?>
                </label>
                <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1 ?>"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-orange file:text-white hover:file:bg-orange-600">

                <?php if ($is_file_content) { ?>
                    <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>"
                        class="mt-2 w-full border-gray-300 rounded shadow-sm text-sm p-2" placeholder="파일 설명">
                <?php } ?>

                <?php if ($w == 'u' && $file[$i]['file']) { ?>
                    <div class="mt-2 flex items-center text-sm">
                        <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]" value="1"
                            class="mr-2 rounded text-red-500">
                        <label for="bf_file_del<?php echo $i ?>"
                            class="text-gray-600"><?php echo $file[$i]['source'] . '(' . $file[$i]['size'] . ')'; ?> 삭제</label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- 캡챠 -->
        <?php if ($is_use_captcha) { ?>
            <div>
                <?php echo $captcha_html ?>
            </div>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex justify-center gap-4 mt-8">
            <a href="<?php echo get_pretty_url($bo_table); ?>"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg text-lg font-bold hover:bg-gray-300 transition-colors">취소</a>
            <button type="submit" id="btn_submit" accesskey="s"
                class="px-8 py-3 bg-brand-orange text-white rounded-lg text-lg font-bold hover:bg-orange-600 shadow-lg transition-colors">작성완료</button>
        </div>

    </form>
</div>

<script>
    <?php if ($write_min || $write_max) { ?>
        // 글자수 제한
        var char_min = parseInt(<?php echo $write_min; ?>); // 최소
        var char_max = parseInt(<?php echo $write_max; ?>); // 최대
        check_byte("wr_content", "char_count");

        $(function () {
            $("#wr_content").on("keyup", function () {
                check_byte("wr_content", "char_count");
            });
        });
    <?php } ?>

    function html_auto_br(obj) {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f) {
        <?php echo $editor_js; ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url + "/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function (data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('" + subject + "')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('" + content + "')가 포함되어있습니다");
            if (typeof (ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte("wr_content", "char_count"));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 " + char_min + "글자 이상 쓰셔야 합니다.");
                    return false;
                }
                else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 " + char_max + "글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        <?php echo $captcha_js; ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
</script>
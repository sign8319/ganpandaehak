<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>; background: transparent;" class="container py-4">
    <!-- 돌아가기 버튼 -->
    <div class="product-view__back pb-8 flex items-center">
        <a href="<?php echo $list_href ?>"
            class="back-button flex items-center text-gray-600 hover:text-gray-800 transition-colors">
            <div class="back-button__icon pr-2 flex items-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </div>
            <span class="back-button__text text-base font-normal">돌아가기</span>
        </a>
    </div>
    <!-- 상단 정보 카드 -->
    <div class="portfolio-view__header mb-8">
        <div
            class="w-full max-w-[1248px] bg-white rounded-2xl p-8 shadow-lg border border-gray-100 flex flex-col md:flex-row gap-6 justify-between items-start md:items-center">

            <div class="flex-1">
                <!-- [NEW] 뱃지 & 지역 -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-3 py-1 bg-[#0a192f] text-white text-xs font-bold rounded-full">
                        ✨ REAL 후기
                    </span>
                    <?php if ($view['wr_2']) { ?>
                        <span
                            class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full border border-gray-200">
                            📍 <?php echo $view['wr_2']; ?>
                        </span>
                    <?php } ?>
                </div>

                <!-- [NEW] 별점 & 제목 -->
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="flex text-amber-400 text-2xl">
                            <?php
                            $star_score = (int) ($view['wr_1'] ? $view['wr_1'] : 5);
                            for ($k = 1; $k <= 5; $k++) {
                                echo ($k <= $star_score) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <span class="text-xl font-bold text-gray-900 mt-1"><?php echo $star_score ?>.0</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight">
                        <?php echo get_text($view['wr_subject']); ?>
                    </h1>
                </div>

                <!-- 작성자 & 날짜 -->
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-gray-700"><?php echo $view['wr_name']; ?> 고객님</span>
                    </div>
                    <span class="w-px h-3 bg-gray-300"></span>
                    <span><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></span>
                    <span class="w-px h-3 bg-gray-300"></span>
                    <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
                </div>
            </div>

            <!-- 관리자 버튼 (Desktop) -->
            <?php if ($update_href || $delete_href) { ?>
                <div class="flex gap-2 shrink-0">
                    <?php if ($update_href) { ?>
                        <a href="<?php echo $update_href; ?>"
                            class="inline-flex items-center px-4 py-2 bg-white text-gray-700 border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            수정
                        </a>
                    <?php } ?>
                    <?php if ($delete_href) { ?>
                        <a href="<?php echo $delete_href; ?>"
                            class="inline-flex items-center px-4 py-2 bg-white text-red-600 border border-red-200 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors"
                            onclick="return confirm('정말 삭제하시겠습니까?');">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            삭제
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- 본문/이미지/특징 카드 -->
    <div class="portfolio-view__body w-full max-w-[1248px] bg-white rounded-2xl p-8 flex flex-col gap-8">

        <!-- 이미지 리스트 -->
        <?php
        $img_files = array();
        if (!empty($view['file']) && is_array($view['file'])) {
            foreach ($view['file'] as $view_file) {
                // 이미지 파일만 필터링 (이미지 확장자 체크)
                if (
                    isset($view_file['view']) && $view_file['view'] &&
                    isset($view_file['file']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $view_file['file'])
                ) {
                    $img_files[] = $view_file;
                }
            }
        }
        ?>
        <?php if (count($img_files)) { ?>
            <div class="flex flex-wrap gap-4 sm:gap-6">
                <?php foreach ($img_files as $img) { ?>
                    <div class="w-full sm:w-auto">
                        <img class="w-full h-auto max-w-[500px] max-h-[500px] object-cover rounded-2xl aspect-square"
                            src="<?php echo G5_DATA_URL . '/file/' . $bo_table . '/' . $img['file']; ?>"
                            alt="<?php echo htmlspecialchars($img['source']); ?>" />
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- 본문 설명 -->
        <div class="text-gray-700 text-base font-normal leading-relaxed mt-2">
            <?php echo get_view_thumbnail($view['content']); ?>
        </div>


    </div>
</article>
<!-- } 게시판 읽기 끝 -->

<script>
    <?php if ($board['bo_download_point'] < 0) { ?>
        $(function () {
            $("a.view_file_download").click(function () {
                if (!g5_is_member) {
                    alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
                    return false;
                }

                var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

                if (confirm(msg)) {
                    var href = $(this).attr("href") + "&js=on";
                    $(this).attr("href", href);

                    return true;
                } else {
                    return false;
                }
            });
        });
    <?php } ?>

    function board_move(href) {
        window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
    }
</script>

<script>
    $(function () {
        $("a.view_image").click(function () {
            window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
            return false;
        });

        // 추천, 비추천
        $("#good_button, #nogood_button").click(function () {
            var $tx;
            if (this.id == "good_button")
                $tx = $("#bo_v_act_good");
            else
                $tx = $("#bo_v_act_nogood");

            excute_good(this.href, $(this), $tx);
            return false;
        });

        // 이미지 리사이즈
        $("#bo_v_atc").viewimageresize();
    });

    function excute_good(href, $el, $tx) {
        $.post(
            href,
            { js: "on" },
            function (data) {
                if (data.error) {
                    alert(data.error);
                    return false;
                }

                if (data.count) {
                    $el.find("strong").text(number_format(String(data.count)));
                    if ($tx.attr("id").search("nogood") > -1) {
                        $tx.text("이 글을 비추천하셨습니다.");
                        $tx.fadeIn(200).delay(2500).fadeOut(200);
                    } else {
                        $tx.text("이 글을 추천하셨습니다.");
                        $tx.fadeIn(200).delay(2500).fadeOut(200);
                    }
                }
            }, "json"
        );
    }
</script>
<!-- } 게시글 읽기 끝 -->
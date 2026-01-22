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
    <!-- 상단 정보 카드 -->
    <div class="portfolio-view__header mb-8">
        <div class="premium-view-header">
            <div style="flex:1;">
                <!-- [NEW] 뱃지 & 지역 -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <span
                        style="padding:0.25rem 0.75rem; background-color:#0a192f; color:white; font-size:0.75rem; font-weight:bold; border-radius:9999px;">
                        ✨ REAL 후기
                    </span>
                    <?php if ($view['wr_2']) { ?>
                        <span
                            style="padding:0.25rem 0.75rem; background-color:#f3f4f6; color:#4b5563; font-size:0.75rem; font-weight:bold; border-radius:9999px; border:1px solid #e5e7eb;">
                            📍 <?php echo $view['wr_2']; ?>
                        </span>
                    <?php } ?>
                </div>

                <!-- [NEW] 별점 & 제목 -->
                <div style="margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                        <div style="display:flex; color:#fbbf24; font-size:1.5rem;">
                            <?php
                            $star_score = (int) ($view['wr_1'] ? $view['wr_1'] : 5);
                            for ($k = 1; $k <= 5; $k++) {
                                echo ($k <= $star_score) ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <span
                            style="font-size:1.25rem; font-weight:bold; color:#111827; margin-top:0.25rem;"><?php echo $star_score ?>.0</span>
                    </div>
                    <h1 style="font-size:2.25rem; font-weight:bold; color:#111827; line-height:1.2;">
                        <?php echo get_text($view['wr_subject']); ?>
                    </h1>
                </div>

                <!-- 작성자 & 날짜 -->
                <div style="display:flex; align-items:center; gap:1rem; font-size:0.875rem; color:#6b7280;">
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <div
                            style="width:2rem; height:2rem; border-radius:9999px; background-color:#e5e7eb; display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                            <svg style="width:1rem; height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span style="font-weight:500; color:#374151;"><?php echo $view['wr_name']; ?> 고객님</span>
                    </div>
                    <span style="width:1px; height:0.75rem; background-color:#d1d5db;"></span>
                    <span><?php echo date('Y.m.d H:i', strtotime($view['wr_datetime'])); ?></span>
                    <span style="width:1px; height:0.75rem; background-color:#d1d5db;"></span>
                    <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
                </div>
            </div>

            <!-- 관리자 버튼 (Desktop) -->
            <?php if ($update_href || $delete_href) { ?>
                <div style="display:flex; gap:0.5rem; flex-shrink:0;">
                    <?php if ($update_href) { ?>
                        <a href="<?php echo $update_href; ?>"
                            style="display:inline-flex; align-items:center; padding:0.5rem 1rem; background:white; color:#374151; border:1px solid #d1d5db; font-size:0.875rem; font-weight:500; border-radius:0.5rem; text-decoration:none;">
                            수정
                        </a>
                    <?php } ?>
                    <?php if ($delete_href) { ?>
                        <a href="<?php echo $delete_href; ?>"
                            style="display:inline-flex; align-items:center; padding:0.5rem 1rem; background:white; color:#dc2626; border:1px solid #fecaca; font-size:0.875rem; font-weight:500; border-radius:0.5rem; text-decoration:none;"
                            onclick="return confirm('정말 삭제하시겠습니까?');">
                            삭제
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- 본문/이미지/특징 카드 -->
    <!-- 본문/이미지/특징 카드 -->
    <div class="premium-view-body">

        <!-- 이미지 리스트 -->
        <?php
        $img_files = array();
        if (!empty($view['file']) && is_array($view['file'])) {
            foreach ($view['file'] as $i => $view_file) { // Use index $i
                // [NEW] Check logic: if wr_10 is 'fixed', skip the first image (index 0) as it is the cropped thumbnail
                if ($view['wr_10'] === 'fixed' && $i == 0)
                    continue;

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
            <div class="premium-view-images">
                <?php foreach ($img_files as $img) { ?>
                    <div class="premium-view-image-item">
                        <img src="<?php echo G5_DATA_URL . '/file/' . $bo_table . '/' . $img['file']; ?>"
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
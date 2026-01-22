<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>; background: transparent;" class="container py-4">
    <!-- 돌아가기 버튼 -->
    <div class="product-view__back pb-8 flex items-center">
            <a href="<?php echo $list_href ?>" class="back-button flex items-center text-gray-600 hover:text-gray-800 transition-colors">
                <div class="back-button__icon pr-2 flex items-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </div>
                <span class="back-button__text text-base font-normal">돌아가기</span>
            </a>
        </div>
    <!-- 상단 정보 카드 -->
    <div class="portfolio-view__header">
        <div class="w-full max-w-[1248px] bg-white rounded-2xl p-6 flex flex-col gap-2">
            <div class="flex-col md:flex-row justify-between items-start">
                <div class="flex-1 pb-4">                    
                    <div class="text-black text-3xl font-bold leading-9">
                        <?php echo get_text($view['wr_subject']); ?>
                    </div>                    
                    <!-- 작성자와 작성시간 -->
                    <div class="flex flex-col md:flex-row items-start gap-2 mt-4">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-600 text-sm font-medium">작성자</span>
                            <span class="text-black text-sm"><?php echo $view['wr_name']; ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-600 text-sm font-medium">작성일</span>
                            <span class="text-black text-sm"><?php echo date('Y-m-d H:i', strtotime($view['wr_datetime'])); ?></span>
                        </div>
                    </div>
                </div>
                <!-- 관리자/작성자용 수정/삭제 버튼 -->
                <?php if ($update_href || $delete_href) { ?>
                <div class="flex justify-end gap-2 ml-4">
                    <?php if ($update_href) { ?>
                    <a href="<?php echo $update_href; ?>" class="inline-flex items-center px-4 py-2 bg-white text-black border border-gray-200 text-sm  font-medium rounded-lg">
                        <img src="<?php echo G5_IMG_URL; ?>/edit_icon.png" alt="수정" class="w-4 h-4 mr-2" onerror="this.style.display='none';">
                        수정
                    </a>
                    <?php } ?>
                    <?php if ($delete_href) { ?>
                    <a href="<?php echo $delete_href; ?>" class="inline-flex items-center px-4 py-2 bg-white text-black border border-gray-200 text-sm font-medium rounded-lg" onclick="return confirm('정말 삭제하시겠습니까?');">
                        <img src="<?php echo G5_IMG_URL; ?>/delete_icon.png" alt="삭제" class="w-4 h-4 mr-2" onerror="this.style.display='none';">
                        삭제
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- 본문/이미지/특징 카드 -->
    <div class="portfolio-view__body w-full max-w-[1248px] bg-white rounded-2xl p-8 flex flex-col gap-8">
        
    <!-- 이미지 리스트 -->
    <?php
        $img_files = array();
        if (!empty($view['file']) && is_array($view['file'])) {
            foreach($view['file'] as $view_file) {
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
            <?php foreach($img_files as $img) { ?>
            <div class="w-full sm:w-auto">
                <img class="w-full h-auto max-w-[500px] max-h-[500px] object-cover rounded-2xl aspect-square" 
                     src="<?php echo G5_DATA_URL.'/file/'.$bo_table.'/'.$img['file']; ?>" 
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
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 추천, 비추천
    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if(this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");

        excute_good(this.href, $(this), $tx);
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if(data.error) {
                alert(data.error);
                return false;
            }

            if(data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if($tx.attr("id").search("nogood") > -1) {
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
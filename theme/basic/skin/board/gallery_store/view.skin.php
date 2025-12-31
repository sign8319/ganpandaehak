<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>; background: transparent;" class="container py-4">
    <div class="product-view">
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

        <!-- 상품 메인 정보 -->
        <div class="product-view__main pb-12">
            <div class="product-main flex flex-col lg:flex-row gap-8">
                <!-- 상품 이미지 -->
                <div class="product-main__image w-full lg:w-1/2">
                    <div class="product-image rounded-2xl shadow-lg overflow-hidden">
                        <?php
                        // 첫 번째 이미지 파일 출력
                        $temp_image_url = G5_THEME_IMG_URL.'/thumb_temp.jpg';
                        $display_image = false;
                        
                        // 파일이 있고 첫 번째 파일이 이미지인 경우
                        if (isset($view['file']['count']) && $view['file']['count'] > 0) {
                            for ($i=0; $i<count($view['file']); $i++) {
                                if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && $view['file'][$i]['view']) {
                                    echo '<img class="w-full h-auto lg:h-[500px] object-cover" src="'.$view['file'][$i]['href'].'" alt="'.get_text($view['wr_subject']).'" />';
                                    $display_image = true;
                                    break;
                                }
                            }
                        }
                        
                        // 이미지가 표시되지 않은 경우 임시 이미지 표시
                        if (!$display_image) {
                            echo '<img class="w-full h-auto lg:h-[500px] object-cover" src="'.G5_THEME_IMG_URL.'/thumb_temp.jpg" alt="상품 이미지" />';
                        }
                        ?>
                    </div>
                </div>

                <!-- 상품 정보 -->
                <div class="product-main__info w-full lg:w-1/2">
                    <div class="product-info bg-white rounded-2xl p-6 flex flex-col">
                        <!-- 상품 제목 -->
                        <div class="product-info__title pb-6">
                            <h1 class="text-black text-3xl font-bold leading-9">
                                <?php echo get_text($view['wr_subject']); ?>
                            </h1>
                        </div>                                           

                        <!-- 구매 버튼 -->
                        <div class="product-info__action">            
                            <a href="<?php 
                                $link = trim($view['wr_link1']);
                                if (!empty($link)) {
                                    // link.php를 통해 카운트를 올리고 링크로 이동
                                    echo G5_BBS_URL.'/link.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'&no=1';
                                } else {
                                    echo '#';
                                }
                            ?>" target="_blank" class="purchase-button w-full px-8 py-4 bg-accent rounded-lg flex justify-center items-center transition-colors cursor-pointer">
                                <div class="purchase-button__icon pr-2">
                                    <img src="<?php echo G5_THEME_IMG_URL; ?>/icon_shop.svg" alt="구매하기" class="w-5 h-5">
                                </div>
                                <span class="purchase-button__text text-gray-900 text-lg font-bold">구매하기</span>
                                <span class="bo_v_link_cnt flex items-center ml-2 text-sm text-black">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <?php echo isset($view['wr_link1_hit']) ? $view['wr_link1_hit'] : 0; ?>
                                </span>
                            </a>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>

        <!-- 본문 내용 시작 { -->
        <div id="bo_v_con" class="border-t border-gray-200 py-12"><?php echo get_view_thumbnail($view['content']); ?></div>
        <?php //echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
        <!-- } 본문 내용 끝 -->

        <!-- 관리자/작성자 버튼
        <?php if($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
        <div class="product-view__admin-actions mt-8 flex justify-end space-x-2">
            <?php if ($update_href) { ?>
            <a href="<?php echo $update_href ?>" class="admin-button admin-button--edit px-4 py-2 bg-white text-black border border-gray-200 rounded-lg transition-colors">
                <i class="fa fa-pencil-square-o mr-2"></i>수정
            </a>
            <?php } ?>
            <?php if ($delete_href) { ?>
            <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;" class="admin-button admin-button--delete px-4 py-2 bg-white text-black border border-gray-200 rounded-lg transition-colors">
                <i class="fa fa-trash-o mr-2"></i>삭제
            </a>
            <?php } ?>
            <?php if ($copy_href) { ?>
            <a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;" class="admin-button admin-button--copy px-4 py-2 bg-white text-black border border-gray-200 rounded-lg transition-colors">
                <i class="fa fa-files-o mr-2"></i>복사
            </a>
            <?php } ?>
            <?php if ($move_href) { ?>
            <a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;" class="admin-button admin-button--move px-4 py-2 bg-white text-black border border-gray-200 rounded-lg transition-colors">
                <i class="fa fa-arrows mr-2"></i>이동
            </a>
            <?php } ?>
        </div>
        <?php } ?> -->

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

        <!-- 이전글/다음글 -->
        <!-- <?php if ($prev_href || $next_href) { ?>
        <div class="product-view__navigation mt-8">
            <ul class="navigation-list space-y-2">
                <?php if ($prev_href) { ?>
                <li class="navigation-item navigation-item--prev border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <a href="<?php echo $prev_href ?>" class="flex items-center">
                        <i class="fa fa-chevron-up mr-2 text-gray-500"></i>
                        <span class="navigation-item__label text-gray-500 mr-4">이전글</span>
                        <span class="navigation-item__title text-gray-900"><?php echo $prev_wr_subject;?></span>
                        <span class="navigation-item__date ml-auto text-gray-500"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></span>
                    </a>
                </li>
                <?php } ?>
                <?php if ($next_href) { ?>
                <li class="navigation-item navigation-item--next border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <a href="<?php echo $next_href ?>" class="flex items-center">
                        <i class="fa fa-chevron-down mr-2 text-gray-500"></i>
                        <span class="navigation-item__label text-gray-500 mr-4">다음글</span>
                        <span class="navigation-item__title text-gray-900"><?php echo $next_wr_subject;?></span>
                        <span class="navigation-item__date ml-auto text-gray-500"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?> -->
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
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>


<div class="w-full min-h-48 flex flex-col justify-center items-center py-12 bg-black/30 bg-center bg-cover bg-no-repeat section-bg-image bg-[url('<?php echo G5_THEME_IMG_URL ?>/board_store_header.jpg')]">
        <h1 class="text-center text-white text-5xl font-bold leading-[48px]"><?php echo $board['bo_subject']?></h1>
</div>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="width:<?php echo $width; ?>" class="container py-12">
    
    

    <?php if ($is_category) { ?>
    <nav id="bo_cate" class="w-full flex justify-center items-center py-4 mb-6">
        <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    <?php } ?>

    <form name="fboardlist"  id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <?php if ($is_admin == 'super' || $is_auth) { ?>
    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div id="bo_btn_top">
        
        <ul class="btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <li>
                <button type="button" class="btn_bo_sch btn_b01 btn" title="게시판 검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">게시판 검색</span></button> 
            </li>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
            <?php if ($is_admin == 'super' || $is_auth) { ?>
            <li>
                <button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
                <?php if ($is_checkbox) { ?>
                <ul class="more_opt is_list_btn">
                    <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
                    <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
                    <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
                </ul>
                <?php } ?>
            </li>
            <?php }  ?>
        </ul>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->
    <?php } ?>

    <?php if ($is_admin) { ?>
    <div class="w-full flex justify-end items-center py-4 px-4 sm:px-0 mx-auto">
        <div class="flex items-center space-x-2">
            <input type="checkbox" id="chkall" onclick="all_checked(this.checked)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
            <label for="chkall" class="text-sm font-medium text-gray-700">전체선택</label>
        </div>
    </div>
    <?php } ?>

    <div class="portfolio-list__grid w-full grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-12 mx-auto">
        <?php if (count($list)) { foreach ($list as $i => $row) { ?>
        <?php
            $thumb = get_list_thumbnail($board['bo_table'], $row['wr_id'], 384, 192, false, true);
            $img_src = $thumb['src'] ? $thumb['src'] : G5_THEME_IMG_URL.'/thumb_temp.jpg';
            $img_alt = $thumb['alt'] ? $thumb['alt'] : htmlspecialchars($row['subject']);
        ?>
        <div class="portfolio-list__card bg-white rounded-2xl shadow-lg drop-shadow-md flex flex-col justify-start items-start overflow-hidden hover:shadow-xl transition-shadow relative">
            <?php if ($is_admin) { ?>
            <div class="absolute top-2 left-2 z-10">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="chk_wr_id_<?php echo $i ?>" class="sr-only"><?php echo $row['subject'] ?> 선택</label>
            </div>
            <?php } ?>
            <a href="<?php echo $row['href']; ?>" class="w-full h-full">
            <div class="portfolio-list__thumb w-full h-48 flex flex-col justify-start items-start overflow-hidden">
                <img class="w-full h-48 object-cover" src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" />
            </div>
            <div class="portfolio-list__info w-full h-auto p-4 flex flex-col justify-start items-start">                
                <div class="pb-2 flex justify-start items-start w-full">
                    <div class="text-black text-lg font-bold leading-7 w-full truncate portfolio-list__title">
                        <?php echo $row['subject']; ?>
                    </div>
                </div>
                <div class="w-full h-10 flex justify-start items-start flex-wrap content-start overflow-hidden">
                    <div class="text-gray-700 text-sm leading-tight w-full line-clamp-2">
                        <?php echo $row['wr_1'] ?? ''; ?>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php }} else { ?>
        <div class="col-span-1 sm:col-span-1 md:col-span-2 lg:col-span-3 flex justify-center items-center w-full text-center text-gray-400 py-12 text-lg font-semibold min-h-[200px]">
            상품이 없습니다.
        </div>
        <?php } ?>
    </div>

    <!-- 페이지 -->
    <div class="w-full flex justify-center items-center py-8">
        <div class="pg_wrap">
            <?php             
            if (isset($write_pages) && !empty($write_pages)) {
                echo $write_pages;
            }             
            elseif (isset($total_page) && $total_page > 1) {
                echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, get_pretty_url($bo_table, '', $qstr.'&amp;page='));
            }            
            ?>
        </div>
    </div>
    <!-- 페이지 끝 -->
      

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
        </ul>   
        <?php } ?>
    </div>
    <?php } ?> 
    </form>  

    
    <!-- 게시판 검색 시작 { -->
    <div class="bo_sch_wrap">   
        <fieldset class="bo_sch">
            <h3>검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <?php echo get_board_sfl_select_options($sfl); ?>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <div class="sch_bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
                <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </div>
            <button type="button" class="bo_sch_cls"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg"></div>
    </div>
    <script>
        // 게시판 검색
        $(".btn_bo_sch").on("click", function() {
            $(".bo_sch_wrap").toggle();
        })
        $('.bo_sch_bg, .bo_sch_cls').click(function(){
            $('.bo_sch_wrap').hide();
        });
    </script>
    <!-- } 게시판 검색 끝 -->
</div>



<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}

// 게시판 리스트 관리자 옵션
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) {
        e.stopPropagation();
        $(".more_opt.is_list_btn").toggle();
    });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_list_btn').length) {
            $(".more_opt.is_list_btn").hide();
        }
    });
});
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->

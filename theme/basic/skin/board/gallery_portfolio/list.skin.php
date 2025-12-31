<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 관리자 권한 체크
$is_checkbox = ($is_admin == 'super' || $is_auth);

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<div class="w-full min-h-48 flex flex-col justify-center items-center py-6 bg-black/30 bg-center sm:bg-center md:bg-center lg:bg-center xl:bg-center bg-cover bg-no-repeat section-bg-image bg-[url('<?php echo G5_THEME_IMG_URL ?>/board_portfolio_header.jpg')] sm:min-h-64 md:min-h-80 lg:min-h-96 xl:min-h-[28rem]" style="background-position: 10% center;">
</div>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="width:<?php echo $width; ?>" class="pb-12 container">    

    <?php if ($is_category) { ?>
    <nav id="bo_cate" class="w-full flex justify-center items-center py-4">
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
        	<?php if ($is_admin == 'super' || $is_auth) {  ?>
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
    <div class="w-full flex justify-end mb-4">
        <div class="flex items-center space-x-2">
            <input type="checkbox" id="chkall" onclick="all_checked(this.checked)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
            <label for="chkall" class="text-sm font-medium text-gray-700">전체선택</label>
        </div>
    </div>
    <?php } ?>
    
    <div class="portfolio-list__grid w-full grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 py-12">
        <?php if (count($list)) { foreach ($list as $i => $row) { ?>
        <?php
            // 첫 번째 첨부 이미지 가져오기
            $sql = "SELECT bf_file, bf_content FROM {$g5['board_file_table']} 
                    WHERE bo_table = '{$board['bo_table']}' AND wr_id = '{$row['wr_id']}' 
                    AND (bf_type = '1' OR bf_type = '2' OR bf_type = '3')
                    AND (bf_file LIKE '%.jpg' OR bf_file LIKE '%.jpeg' OR bf_file LIKE '%.png' OR bf_file LIKE '%.gif' OR bf_file LIKE '%.webp')
                    ORDER BY bf_no ASC LIMIT 1";
            $file_result = sql_query($sql);
            $file_row = sql_fetch_array($file_result);
            
            // 이미지 파일 경로 설정
            $img_src = '';
            $img_alt = htmlspecialchars($row['subject']);
            
            if ($file_row && $file_row['bf_file']) {
                $img_src = G5_DATA_URL.'/file/'.$board['bo_table'].'/'.$file_row['bf_file'];
                $img_alt = $file_row['bf_content'] ? htmlspecialchars($file_row['bf_content']) : $img_alt;
            }
        ?>
        <div class="portfolio-list__card bg-white rounded-2xl shadow-lg drop-shadow-md flex flex-col justify-start items-start overflow-hidden hover:shadow-xl transition-shadow relative">
            <?php if ($is_admin) { ?>
            <div class="absolute top-2 right-2 z-10">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="chk_wr_id_<?php echo $i ?>" class="sr-only"><?php echo $row['subject'] ?> 선택</label>
            </div>
            <?php } ?>
            <a href="<?php echo $row['href']; ?>" class="w-full h-full">
            <div class="portfolio-list__thumb w-full h-48 flex flex-col justify-start items-start overflow-hidden">
                <?php if ($img_src) { ?>
                <img class="w-full h-48 object-cover" src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" />
                <?php } else { ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <img class="w-24 h-auto object-contain" src="<?php echo G5_THEME_IMG_URL; ?>/logo.png" alt="로고" />
                </div>
                <?php } ?>
            </div>
            <div class="portfolio-list__info w-full h-32 p-4 flex flex-col justify-start items-start">
                <div class="pb-1 flex justify-start items-start w-full">
                    <div class="text-gray-500 text-sm leading-tight w-full truncate"> <?php echo $row['wr_1'] ?? '-'; ?> </div>
                </div>
                <div class="pb-2 flex justify-start items-start w-full">
                    <div class="text-black text-lg font-bold leading-7 w-full truncate portfolio-list__title">
                        <?php echo $row['subject']; ?>
                    </div>
                </div>
                <div class="w-full h-10 flex justify-start items-start flex-wrap content-start overflow-hidden">
                    <div class="text-gray-700 text-sm leading-tight w-full line-clamp-2 break-keep">
                        <?php 
                        $content = $row['wr_2'] ?? '';                      
                        // 줄 수 제한 (2줄, 한 줄당 약 25자로 계산)
                        $maxChars = 50; // 2줄 × 25자
                        if (mb_strlen($content, 'UTF-8') > $maxChars) {
                            $content = mb_substr($content, 0, $maxChars, 'UTF-8') . '...';
                        }                        
                        echo htmlspecialchars($content);
                        ?>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php }} else { ?>
        <div class="w-full text-center text-gray-400 py-12">포트폴리오가 없습니다.</div>
        <?php } ?>
    </div>

    <!-- 퀵상담 CTA 배너 -->
    <section x-data="{ modalOpen: false }" class="container">
        <div class="w-full max-w-4xl mx-auto p-4 sm:p-6 bg-accent rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4 sm:gap-6">
        <div class="flex-1 flex flex-col justify-center items-start pr-0 md:pr-8">
            <div class="pb-2 text-black text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold leading-7 break-keep">
                전문가가 상담 후 딱 맞는 간판을 추천해드립니다.
            </div>
            <div class="text-gray-700 text-sm sm:text-base md:text-lg lg:text-xl font-normal leading-normal">
                퀵 상담신청으로 간편하게 시작하세요.
            </div>
        </div>
        <div>
            <a href="<?php echo G5_BBS_URL . '/write.php?bo_table=consult'; ?>" class="w-full md:w-auto px-6 py-3 bg-white rounded-full shadow flex justify-center items-center font-bold text-gray-900 text-base text-center transition hover:bg-gray-100">
                퀵 상담 신청하기
            </a>
        </div>    
    </section>

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

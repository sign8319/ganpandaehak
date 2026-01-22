<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox)
    $colspan++;
if ($is_good)
    $colspan++;
if ($is_nogood)
    $colspan++;

// signexp 게시판에서 '전체' 카테고리 숨김 및 첫 번째 카테고리로 리다이렉트
if ($bo_table == 'signexp' && $board['bo_use_category']) {
    $categories = explode('|', $board['bo_category_list']);
    if ($sca == '') {
        goto_url(get_pretty_url($bo_table, '', 'sca=' . urlencode($categories[0])));
    }

    // '전체' 항목 제거
    $category_option = preg_replace('/<li><a[^>]*>전체<\/a><\/li>/i', '', $category_option);
}


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<!-- 게시판 목록 시작 { -->
<div id="bo_list" style="width:<?php echo $width; ?>">
    <div
        class="w-full min-h-48 flex flex-col justify-center items-center py-12 bg-black/30 bg-center bg-cover bg-no-repeat section-bg-image bg-[url('<?php echo G5_THEME_IMG_URL ?>/board_guide_header.jpg')]">
        <h1 class="text-center text-white text-5xl font-bold leading-[48px]"><?php echo $board['bo_subject'] ?></h1>
    </div>

    <!-- 게시판 카테고리 시작 { -->
    <?php if ($is_category) { ?>
        <nav id="bo_cate" class="w-full flex justify-center items-center py-4 mb-6">
            <h2 class="sr-only"><?php echo $board['bo_subject'] ?> 카테고리</h2>
            <ul id="bo_cate_ul" class="flex flex-wrap gap-2">
                <?php echo $category_option ?>
            </ul>
        </nav>
    <?php } ?>
    <!-- } 게시판 카테고리 끝 -->

    <div class="container py-12">
        <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php"
            onsubmit="return fboardlist_submit(this);" method="post">

            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
            <input type="hidden" name="stx" value="<?php echo $stx ?>">
            <input type="hidden" name="spt" value="<?php echo $spt ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sst" value="<?php echo $sst ?>">
            <input type="hidden" name="sod" value="<?php echo $sod ?>">
            <input type="hidden" name="page" value="<?php echo $page ?>">
            <input type="hidden" name="sw" value="">


            <?php
            // 첫 번째 게시글의 이미지 파일 경로 생성
            $image_src = "https://placehold.co/1248x600"; // 기본 이미지
            if (count($list) > 0) {
                $first_post = $list[0];
                $sql = "SELECT * FROM {$g5['board_file_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$first_post['wr_id']}' AND bf_no = 0 ORDER BY bf_no ASC LIMIT 1";
                $file_result = sql_query($sql);
                $file = sql_fetch_array($file_result);

                if ($file && $file['bf_file']) {
                    $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $file['bf_file'];
                    if (file_exists($file_path)) {
                        $image_src = G5_DATA_URL . '/file/' . $bo_table . '/' . $file['bf_file'];
                    }
                }
            }
            ?>
            <div class="flex justify-center items-start px-4 sm:px-8 md:px-12 lg:px-20 container">
                <?php if (count($list) == 0) { ?>
                    <div class="w-full text-center text-gray-500 py-12 text-lg font-semibold">게시글이 없습니다</div>
                <?php } else { ?>
                    <img class="h-auto lg:w-[1248px] object-cover rounded-2xl shadow-md" src="<?php echo $image_src; ?>"
                        alt="첫 번째 게시글 이미지" />
                <?php } ?>
            </div>

            <?php if ($is_admin == 'super' || $is_auth) { ?>
                <!-- 게시판 목록 시작 { -->
                <div id="bo_list" style="width:<?php echo $width; ?>">

                    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
                    <div id="bo_btn_top">
                        <!-- <div id="bo_list_total">
                    <span>Total <?php echo number_format($total_count) ?>건</span>
                    <?php echo $page ?> 페이지
                </div> -->

                        <ul class="btn_bo_user">
                            <?php if ($admin_href) { ?>
                                <li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i
                                            class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li>
                            <?php } ?>
                            <?php if ($rss_href) { ?>
                                <li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss"
                                            aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
                            <li>
                                <button type="button" class="btn_bo_sch btn_b01 btn" title="게시판 검색"><i class="fa fa-search"
                                        aria-hidden="true"></i><span class="sound_only">게시판 검색</span></button>
                            </li>
                            <?php if ($write_href) { ?>
                                <li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil"
                                            aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                            <?php if ($is_admin == 'super' || $is_auth) { ?>
                                <li>
                                    <button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="게시판 리스트 옵션"><i
                                            class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트
                                            옵션</span></button>
                                    <?php if ($is_checkbox) { ?>
                                        <ul class="more_opt is_list_btn">
                                            <li><button type="submit" name="btn_submit" value="선택삭제"
                                                    onclick="document.pressed=this.value"><i class="fa fa-trash-o"
                                                        aria-hidden="true"></i> 선택삭제</button></li>
                                            <li><button type="submit" name="btn_submit" value="선택복사"
                                                    onclick="document.pressed=this.value"><i class="fa fa-files-o"
                                                        aria-hidden="true"></i> 선택복사</button></li>
                                            <li><button type="submit" name="btn_submit" value="선택이동"
                                                    onclick="document.pressed=this.value"><i class="fa fa-arrows"
                                                        aria-hidden="true"></i> 선택이동</button></li>
                                        </ul>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

                    <div class="flex justify-center items-start container">
                        <div class="w-full max-w-6xl py-8 flex flex-col items-start">
                            <!-- 제목 영역 -->
                            <div class="pb-6 w-full">
                                <div class="w-full pb-4 border-b border-gray-200 flex items-center">
                                    <div class="text-zinc-800 text-3xl font-bold leading-loose">
                                        <?php echo $board['bo_subject']; ?>
                                    </div>
                                </div>
                            </div>
                            <!-- 테이블 헤더 -->
                            <div class="w-full flex flex-col overflow-x-auto">

                                <div
                                    class="min-w-[250px] sm:min-w-[320px] md:min-w-[400px] lg:min-w-[500px] w-full flex flex-col">
                                    <div class="flex bg-stone-50 border-t border-b border-zinc-100">
                                        <?php if ($is_checkbox) { ?>
                                            <div
                                                class="w-8 sm:w-10 md:w-12 px-1 sm:px-2 py-3 sm:py-4 flex items-center justify-center">
                                                <input type="checkbox" id="chkall"
                                                    onclick="if (this.checked) all_checked(true); else all_checked(false);"
                                                    class="w-4 h-4 text-accent bg-gray-100 border-gray-300 rounded focus:accent focus:ring-2">
                                                <label for="chkall" class="sr-only">
                                                    <span></span>
                                                    <b class="sound_only">현재 페이지 게시물 전체선택</b>
                                                </label>
                                            </div>
                                        <?php } ?>
                                        <div
                                            class="w-8 sm:w-12 md:w-16 px-1 sm:px-2 py-3 sm:py-4 flex items-center justify-center">
                                            <div
                                                class="text-neutral-600 text-xs sm:text-sm md:text-base font-semibold leading-normal text-center">
                                                번호</div>
                                        </div>
                                        <div class="flex-1 px-1 sm:px-3 py-3 sm:py-4 flex items-center">
                                            <div
                                                class="text-neutral-600 text-xs sm:text-sm md:text-base font-semibold  leading-normal">
                                                제목</div>
                                        </div>
                                        <div
                                            class="hidden sm:hidden md:block lg:block sm:flex w-16 sm:w-24 md:w-32 px-2 sm:px-3 py-3 sm:py-4 items-center justify-end">
                                            <div
                                                class="text-neutral-600 text-xs sm:text-sm md:text-base font-semibold  leading-normal">
                                                등록일</div>
                                        </div>
                                    </div>
                                    <!-- 게시글 목록 -->
                                    <div>
                                        <?php if (count($list)) {
                                            foreach ($list as $i => $row) { ?>
                                                <div class="flex border-b border-zinc-100 hover:bg-gray-50 transition">
                                                    <?php if ($is_checkbox) { ?>
                                                        <div
                                                            class="w-8 sm:w-10 md:w-12 px-1 sm:px-2 py-3 sm:py-4 flex items-center justify-center">
                                                            <input type="checkbox" name="chk_wr_id[]"
                                                                value="<?php echo $row['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>"
                                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                                            <label for="chk_wr_id_<?php echo $i ?>" class="sr-only">
                                                                <span></span>
                                                                <b class="sound_only"><?php echo $row['subject'] ?></b>
                                                            </label>
                                                        </div>
                                                    <?php } ?>
                                                    <div
                                                        class="w-8 sm:w-12 md:w-16 px-1 sm:px-2 py-3 sm:py-4 flex items-center justify-center">
                                                        <div
                                                            class="text-zinc-800 text-xs sm:text-sm md:text-base font-normal leading-normal text-center">
                                                            <?php
                                                            if ($row['is_notice']) {
                                                                echo '<span class="text-black font-semibold">공지</span>';
                                                            } else {
                                                                echo $row['num'];
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 px-1 sm:px-3 py-3 sm:py-4 flex items-center">
                                                        <a href="<?php echo $row['href']; ?>"
                                                            class="text-zinc-800 text-xs sm:text-sm md:text-base font-normal  leading-normal truncate">
                                                            <?php echo $row['subject']; ?>
                                                        </a>
                                                    </div>
                                                    <div
                                                        class="hidden sm:hidden md:block lg:block sm:flex w-16 sm:w-24 md:w-32 px-2 sm:px-3 py-3 sm:py-4 items-center justify-center">
                                                        <div
                                                            class="text-zinc-800 text-xs sm:text-sm md:text-base font-normal leading-normal">
                                                            <?php echo $row['datetime2']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php }
                                        } else { ?>
                                            <div class="flex border-b border-zinc-100">
                                                <div class="w-full px-3 py-8 text-center text-gray-400">게시글이 없습니다.</div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <!-- 하단 버튼 영역 -->
                            <div class="pt-8 w-full flex justify-end">
                                <?php if ($write_href) { ?>
                                    <a href="<?php echo $write_href; ?>"
                                        class="inline-flex items-center px-6 py-2 bg-accent rounded-lg hover:bg-lime-500 transition text-black text-base font-normal font-['Pretendard']">
                                        <i class="fa fa-pencil mr-2"></i>글쓰기
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <!-- 페이지 -->
                    <?php echo $write_pages; ?>
                    <!-- 페이지 -->


                    <?php if ($list_href || $is_checkbox || $write_href) { ?>
                        <div class="bo_fx">
                            <?php if ($list_href || $write_href) { ?>
                                <ul class="btn_bo_user">
                                    <?php if ($admin_href) { ?>
                                        <li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i
                                                    class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li>
                                    <?php } ?>
                                    <?php if ($rss_href) { ?>
                                        <li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss"
                                                    aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
                                    <?php if ($write_href) { ?>
                                        <li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
                                </ul>
                            <?php } ?>
                        </div>
                    <?php } ?>
            </form>
        <?php } ?>
    </div>

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
                    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx"
                        class="sch_input" size="25" maxlength="20" placeholder=" 검색어를 입력해주세요">
                    <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search"
                            aria-hidden="true"></i><span class="sound_only">검색</span></button>
                </div>
                <button type="button" class="bo_sch_cls" title="닫기"><i class="fa fa-times" aria-hidden="true"></i><span
                        class="sound_only">닫기</span></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg"></div>
    </div>
    <script>
        jQuery(function ($) {
            // 게시판 검색
            $(".btn_bo_sch").on("click", function () {
                $(".bo_sch_wrap").toggle();
            })
            $('.bo_sch_bg, .bo_sch_cls').click(function () {
                $('.bo_sch_wrap').hide();
            });
        });
    </script>
    <!-- } 게시판 검색 끝 -->
</div>

<?php if ($is_checkbox) { ?>
    <noscript>
        <p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
    </noscript>
<?php } ?>

<?php if ($is_checkbox) { ?>
    <script>
        function all_checked(sw) {
            var f = document.fboardlist;

            for (var i = 0; i < f.length; i++) {
                if (f.elements[i].name == "chk_wr_id[]")
                    f.elements[i].checked = sw;
            }
        }

        function fboardlist_submit(f) {
            var chk_count = 0;

            for (var i = 0; i < f.length; i++) {
                if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
                    chk_count++;
            }

            if (!chk_count) {
                alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
                return false;
            }

            if (document.pressed == "선택복사") {
                select_copy("copy");
                return;
            }

            if (document.pressed == "선택이동") {
                select_copy("move");
                return;
            }

            if (document.pressed == "선택삭제") {
                if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
                    return false;

                f.removeAttribute("target");
                f.action = g5_bbs_url + "/board_list_update.php";
            }

            return true;
        }

        // 선택한 게시물 복사 및 이동
        function select_copy(sw) {
            var f = document.fboardlist;

            if (sw == "copy")
                str = "복사";
            else
                str = "이동";

            var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

            f.sw.value = sw;
            f.target = "move";
            f.action = g5_bbs_url + "/move.php";
            f.submit();
        }

        // 게시판 리스트 관리자 옵션
        jQuery(function ($) {
            $(".btn_more_opt.is_list_btn").on("click", function (e) {
                e.stopPropagation();
                $(".more_opt.is_list_btn").toggle();
            });
            $(document).on("click", function (e) {
                if (!$(e.target).closest('.is_list_btn').length) {
                    $(".more_opt.is_list_btn").hide();
                }
            });
        });


    </script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
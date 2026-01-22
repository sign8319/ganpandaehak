<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css?ver=' . time() . '">', 0);
?>

<style>
    /* -----------------------------------------------------------------
   NEW HORIZONTAL LIST LAYOUT (Inline Fix)
   ----------------------------------------------------------------- */
    .premium-list-layout {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 30px 0;
    }

    .premium-list-item {
        display: flex;
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #f3f4f6;
    }

    .premium-list-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .premium-list-item a {
        display: flex;
        width: 100%;
        text-decoration: none;
        color: inherit;
    }

    @media (max-width: 768px) {
        .premium-list-item a {
            flex-direction: column;
        }
    }

    .premium-list-thumb {
        width: 280px;
        height: 200px;
        /* Fixed height for consistency */
        flex-shrink: 0;
        position: relative;
        background-color: #f3f4f6;
    }

    @media (max-width: 768px) {
        .premium-list-thumb {
            width: 100%;
            height: 200px;
        }
    }

    .premium-list-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .premium-list-content {
        flex: 1;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .premium-list-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }

    .premium-list-title {
        font-size: 1.25rem;
        font-weight: bold;
        color: #111827;
        margin-right: 1rem;
        line-height: 1.4;
    }

    .premium-list-title:hover {
        color: #ea580c;
    }

    .premium-list-desc {
        font-size: 0.925rem;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .premium-list-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .premium-badge-keyword {
        display: inline-block;
        padding: 2px 8px;
        background-color: #f3f4f6;
        color: #4b5563;
        font-size: 0.75rem;
        border-radius: 4px;
    }

    .premium-rating-block {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #fbbf24;
        font-weight: bold;
        font-size: 0.9rem;
    }
</style>

<div class="premium-header">
    <div class="pattern-bg"></div>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <!-- Left: Title Section -->
        <div class="premium-title-section">
            <div class="premium-badge">
                ✨ 간판제작 고객 만족도 TOP!
            </div>
            <h1 class="premium-main-title">
                <span style="color:white;">간판의품격</span>
                <span style="color:#fbbf24; position:relative; display:inline-block;">
                    고객 이용 후기
                    <!-- SVG Underline mimicking Tailwind decoration -->
                    <svg style="position:absolute; bottom:-8px; left:0; width:100%; height:8px; opacity:0.5; color:#f59e0b;"
                        viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="3" fill="none" />
                    </svg>
                </span>
            </h1>
            <p class="premium-subtitle">
                실제 고객님들이 남겨주신 100% 리얼 후기입니다.<br>
                정직하고 투명한 시공으로 보답하겠습니다.
            </p>
        </div>

        <!-- Right: Ratings Badge -->
        <div class="review-stats-card">
            <div class="review-score-area">
                <div style="color:#d1d5db; font-size:0.875rem; font-weight:500; margin-bottom:0.25rem;">숨고 / 당근마켓 리뷰 평점
                </div>
                <div class="review-big-score"
                    style="display:flex; justify-content:flex-end; align-items:center; gap:0.5rem;">
                    5.0
                    <span style="font-size:1.5rem; color:#fbbf24;">점</span>
                </div>
                <div class="review-stars" style="display:flex; justify-content:flex-end;">
                    ⭐⭐⭐⭐⭐
                </div>
            </div>
            <div style="height:4rem; width:1px; background:rgba(255,255,255,0.2);"></div>
            <div style="text-align:center;">
                <div style="color:#d1d5db; font-size:0.75rem; margin-bottom:0.25rem;">총 누적 후기</div>
                <div style="font-size:1.5rem; font-weight:bold; color:white;"><?php echo number_format($total_count) ?>+
                </div>
                <div style="font-size:0.75rem; color:#fbbf24; margin-top:0.25rem;">실시간 집계중</div>
            </div>
        </div>
    </div>
</div>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="width:<?php echo $width; ?>" class="container py-12">



    <?php if ($is_category) { ?>
        <nav id="bo_cate">
            <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
            <ul id="bo_cate_ul">
                <?php echo $category_option ?>
            </ul>
        </nav>
    <?php } ?>

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php"
        onsubmit="return fboardlist_submit(this);" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="spt" value="<?php echo $spt ?>">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="sw" value="">

        <?php if ($is_admin) { ?>
            <!-- 게시판 페이지 정보 및 버튼 시작 { -->
            <div id="bo_btn_top">

                <ul class="btn_bo_user">
                    <?php if ($admin_href) { ?>
                        <li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i
                                    class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
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
                                            onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i>
                                            선택삭제</button></li>
                                    <li><button type="submit" name="btn_submit" value="선택복사"
                                            onclick="document.pressed=this.value"><i class="fa fa-files-o" aria-hidden="true"></i>
                                            선택복사</button></li>
                                    <li><button type="submit" name="btn_submit" value="선택이동"
                                            onclick="document.pressed=this.value"><i class="fa fa-arrows" aria-hidden="true"></i>
                                            선택이동</button></li>
                                </ul>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <!-- } 게시판 페이지 정보 및 버튼 끝 -->
        <?php } ?>

        <?php if ($is_admin) { ?>
            <div class="w-full flex justify-end items-center py-4 px-4 sm:px-0 container mx-auto">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="chkall" onclick="all_checked(this.checked)"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    <label for="chkall" class="text-sm font-medium text-gray-700">전체선택</label>
                </div>
            </div>
        <?php } ?>

        <!-- [NEW] Horizontal List Layout -->
        <div class="premium-list-layout">
            <?php if (count($list)) {
                foreach ($list as $i => $row) {
                    // 썸네일
                    $thumb = get_list_thumbnail($board['bo_table'], $row['wr_id'], 400, 300, false, true);
                    $default_img = G5_THEME_IMG_URL . '/thumb_temp.jpg';
                    $img_src = $thumb['src'] ? $thumb['src'] : $default_img;
                    $img_alt = $thumb['alt'] ? $thumb['alt'] : htmlspecialchars($row['subject']);

                    // 키워드 파싱 (wr_3)
                    $keywords = array();
                    if ($row['wr_3']) {
                        $keywords = explode(',', $row['wr_3']);
                    }
                    ?>
                    <div class="premium-list-item">
                        <?php if ($is_admin) { ?>
                            <div style="position:absolute; top:10px; left:10px; z-index:10;">
                                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id'] ?>"
                                    id="chk_wr_id_<?php echo $i ?>" class="w-4 h-4">
                            </div>
                        <?php } ?>

                        <a href="<?php echo $row['href']; ?>">
                            <!-- Left: Image -->
                            <div class="premium-list-thumb">
                                <?php if ($img_src) { ?>
                                    <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" />
                                <?php } else { ?>
                                    <div
                                        style="width:100%; height:100%; background:#e5e7eb; display:flex; align-items:center; justify-content:center;">
                                        <img style="width:60px;" src="<?php echo G5_THEME_IMG_URL; ?>/logo.png" alt="로고" />
                                    </div>
                                <?php } ?>
                                <!-- Region Badge overlay -->
                                <?php if ($row['wr_2']) { ?>
                                    <span
                                        style="position:absolute; top:10px; left:10px; background:rgba(0,0,0,0.6); color:white; padding:2px 8px; border-radius:4px; font-size:0.75rem;">
                                        📍 <?php echo $row['wr_2'] ?>
                                    </span>
                                <?php } ?>
                            </div>

                            <!-- Right: Content -->
                            <div class="premium-list-content">
                                <div>
                                    <div class="premium-list-header">
                                        <div class="premium-list-title">
                                            <?php echo $row['subject']; ?>
                                        </div>
                                        <div class="premium-rating-block">
                                            <?php
                                            $star_score = (int) ($row['wr_1'] ? $row['wr_1'] : 5);
                                            echo '★ ' . $star_score . '.0';
                                            ?>
                                        </div>
                                    </div>

                                    <div style="font-size:0.875rem; color:#6b7280; margin-bottom:0.5rem;">
                                        <?php echo $row['wr_name']; ?> 고객님
                                    </div>

                                    <div class="premium-list-desc">
                                        <?php
                                        // [NEW] wr_4 (리스트 노출용 후기)가 있으면 그것을 우선 출력
                                        if (!empty($row['wr_4'])) {
                                            echo htmlspecialchars($row['wr_4']);
                                        } else {
                                            // 없으면 기존 본문 요약 (하위 호환성)
                                            $content = strip_tags($row['wr_content'] ?? '');
                                            $content = str_replace('&nbsp;', ' ', $content);
                                            $content = preg_replace('/\s+/', ' ', $content);
                                            $content = trim($content);
                                            echo htmlspecialchars($content);
                                        }
                                        ?>
                                    </div>
                                </div>

                                <!-- Keywords -->
                                <?php if (!empty($keywords)) { ?>
                                    <div class="premium-list-meta">
                                        <?php
                                        // 최대 3개까지만 노출
                                        $k_count = 0;
                                        foreach ($keywords as $k) {
                                            if ($k_count >= 3)
                                                break;
                                            if (trim($k) == '')
                                                continue;
                                            ?>
                                            <span class="premium-badge-keyword"><?php echo trim($k) ?></span>
                                            <?php
                                            $k_count++;
                                        }
                                        if (count($keywords) > 3) {
                                            echo '<span style="font-size:0.75rem; color:#9ca3af;">+' . (count($keywords) - 3) . '</span>';
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </a>
                    </div>
                <?php }
            } else { ?>
                <div
                    style="width:100%; text-align:center; padding:50px 0; color:#9ca3af; font-size:1.125rem; font-weight:600;">
                    게시글이 없습니다.
                </div>
            <?php } ?>
        </div>

        <!-- 페이지 -->
        <div class="w-full flex justify-center items-center py-8">
            <div class="pg_wrap">
                <?php
                // 페이지네이션 항상 출력: $write_pages가 있으면 출력, 없으면 $total_page > 1일 때 get_paging() 출력
                if (isset($write_pages) && !empty($write_pages)) {
                    echo $write_pages;
                } else if (isset($total_page) && $total_page > 1) {
                    echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, get_pretty_url($bo_table, '', $qstr . '&amp;page='));
                }
                ?>
            </div>
        </div>
        <!-- 페이지 끝 -->

        <!-- 글쓰기 버튼 -->
        <?php if ($write_href) { ?>
            <div class="flex justify-end">
                <a href="<?php echo $write_href; ?>"
                    class="w-24 h-10 px-4 py-2 bg-accent rounded-lg inline-flex justify-start items-center cursor-pointer">
                    <div class="pr-1 flex justify-start items-start">
                        <i class="fa fa-pencil text-black text-sm"></i>
                    </div>
                    <div class="text-center justify-center text-black text-base font-normal leading-normal">글쓰기</div>
                </a>
            </div>
        <?php } ?>



        <?php if ($is_admin && ($list_href || $is_checkbox || $write_href)) { ?>
            <div class="bo_fx">
                <?php if ($list_href || $write_href) { ?>
                    <ul class="btn_bo_user">
                        <?php if ($admin_href) { ?>
                            <li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i
                                        class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
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
                        class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
                    <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search"
                            aria-hidden="true"></i><span class="sound_only">검색</span></button>
                </div>
                <button type="button" class="bo_sch_cls"><i class="fa fa-times" aria-hidden="true"></i><span
                        class="sound_only">닫기</span></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg"></div>
    </div>
    <script>
        // 게시판 검색
        $(".btn_bo_sch").on("click", function () {
            $(".bo_sch_wrap").toggle();
        })
        $('.bo_sch_bg, .bo_sch_cls').click(function () {
            $('.bo_sch_wrap').hide();
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

            if (sw == 'copy')
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
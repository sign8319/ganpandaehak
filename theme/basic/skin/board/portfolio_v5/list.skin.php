<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Pretendard', 'sans-serif'] },
                colors: {
                    brand: { orange: '#F97316', dark: '#1F2937', gray: '#F3F4F6' }
                }
            }
        }
    }
</script>
<style>
    @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');

    .img-zoom {
        transition: transform 0.5s ease;
    }

    .product-card:hover .img-zoom {
        transform: scale(1.05);
    }

    /* 사이드바 메뉴 활성화 스타일 */
    .cate-link.active {
        background-color: #F97316;
        color: white;
        font-weight: bold;
        border-color: #F97316;
    }

    .cate-link:hover:not(.active) {
        background-color: #FFF7ED;
        color: #F97316;
    }
</style>

<div class="font-sans w-full max-w-[1800px] mx-auto px-4 py-12">

    <!-- 게시판 목록 시작 { -->
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

        <div class="flex flex-col lg:flex-row gap-10">

            <!-- 사이드바 (스토어 스타일) -->
            <aside class="w-full lg:w-64 flex-shrink-0">
                <div class="lg:sticky lg:top-24">
                    <div class="mb-6 pb-4 border-b-2 border-gray-900 hidden lg:block">
                        <h2 class="text-2xl font-extrabold text-gray-900">CATEGORY</h2>
                    </div>

                    <nav class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-4 lg:pb-0 scrollbar-hide">
                        <a href="<?php echo get_pretty_url($bo_table); ?>"
                            class="cate-link flex-shrink-0 w-auto lg:w-full px-5 py-3.5 rounded-lg border border-gray-200 text-gray-600 transition-all text-sm md:text-base <?php echo $sca == '' ? 'active shadow-md' : 'bg-white'; ?>">
                            <div class="flex justify-between items-center w-full">
                                <span>전체 보기</span>
                                <i class="fa fa-chevron-right text-xs opacity-50 hidden lg:block"></i>
                            </div>
                        </a>

                        <?php
                        $categories = explode('|', $board['bo_category_list']);
                        foreach ($categories as $cat) {
                            $cat = trim($cat);
                            if (!$cat)
                                continue;
                            $isActive = ($sca == $cat) ? 'active shadow-md' : 'bg-white';
                            $url = get_pretty_url($bo_table, '', 'sca=' . urlencode($cat));

                            echo '<a href="' . $url . '" class="cate-link flex-shrink-0 w-auto lg:w-full px-5 py-3.5 rounded-lg border border-gray-200 text-gray-600 transition-all text-sm md:text-base ' . $isActive . '">';
                            echo '<div class="flex justify-between items-center w-full">';
                            echo '<span>' . $cat . '</span>';
                            echo '<i class="fa fa-chevron-right text-xs opacity-50 hidden lg:block"></i>';
                            echo '</div>';
                            echo '</a>';
                        }
                        ?>
                    </nav>

                    <div class="mt-10 hidden lg:block p-6 bg-gray-900 rounded-2xl text-white text-center">
                        <p class="text-sm text-gray-400 mb-2">도움이 필요하신가요?</p>
                        <h3 class="text-xl font-bold mb-4">1:1 맞춤 상담</h3>
                        <a href="/write.php?bo_table=consult"
                            class="inline-block w-full py-3 bg-brand-orange rounded-lg font-bold hover:bg-orange-600 transition-colors">
                            견적 문의하기
                        </a>
                    </div>
                </div>
            </aside>


            <main class="flex-1 min-w-0">

                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">
                            <?php echo $sca ? $sca : $board['bo_subject']; ?>
                        </h1>
                        <p class="text-gray-500 mt-2">
                            총 <strong class="text-brand-orange"><?php echo number_format($total_count) ?></strong>개의
                            포트폴리오가 있습니다.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <?php if ($admin_href) { ?>
                            <a href="<?php echo $admin_href ?>"
                                class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors">
                                <i class="fa fa-cog"></i>
                            </a>
                        <?php } ?>
                        <?php if ($write_href) { ?>
                            <a href="<?php echo $write_href ?>"
                                class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-black transition-colors flex items-center gap-2">
                                <i class="fa fa-pencil"></i> 글쓰기
                            </a>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($is_checkbox) { ?>
                    <div class="mb-4 text-right">
                        <input type="checkbox" id="chkall"
                            onclick="if (this.checked) all_checked(true); else all_checked(false);" class="mr-1">
                        <label for="chkall" class="text-sm text-gray-500">전체 선택</label>
                        <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"
                            class="ml-2 text-xs bg-red-50 text-red-500 px-2 py-1 rounded border border-red-100 hover:bg-red-100">선택삭제</button>
                        <button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"
                            class="ml-1 text-xs bg-gray-50 text-gray-500 px-2 py-1 rounded border border-gray-100 hover:bg-gray-100">선택복사</button>
                    </div>
                <?php } ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
                    <?php
                    for ($i = 0; $i < count($list); $i++) {
                        // 데이터 정제 로직 (SIGN.ZIP 메인화면과 동일)
                        $subject = strip_tags($list[$i]['wr_subject']); // 제목 태그 제거
                    
                        // 본문 정제
                        $content_raw = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $list[$i]['wr_content']); // 스타일 태그 제거
                        $content = strip_tags($content_raw); // 태그 제거
                        $content = str_replace('&nbsp;', '', $content); // &nbsp; 제거
                        $content = html_entity_decode($content); // 엔티티 변환
                        $content = preg_replace('/#[^ ]+/', '', $content); // 해시태그 제거
                        $content = str_replace('&nbsp;', ' ', $content); // 남은 &nbsp; 공백처리
                        $content_clean = trim($content); // 공백 제거
                    
                        // 위치 정제
                        $location_clean = preg_replace('/#[^ ]+/', '', $list[$i]['wr_1']);
                        $location_clean = trim($location_clean);

                        // 썸네일
                        $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], 500, 500, false, true);
                        $img_src = $thumb['src'] ? $thumb['src'] : 'https://placehold.co/500x500/f3f4f6/9ca3af?text=No+Image';

                        // 가격 정보
                        $price_value = $list[$i]['wr_2'];
                        ?>
                        <div class="group block product-card relative">
                            <a href="<?php echo $list[$i]['href'] ?>">
                                <div
                                    class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                                    <!-- 썸네일 영역 -->
                                    <div class="relative aspect-square overflow-hidden bg-gray-50 shrink-0">
                                        <img src="<?php echo $img_src ?>" class="w-full h-full object-cover img-zoom">

                                        <!-- 카테고리 배지 -->
                                        <?php if ($list[$i]['ca_name']) { ?>
                                            <div class="absolute top-3 left-3">
                                                <span
                                                    class="px-2.5 py-1 bg-brand-orange text-white text-[10px] font-bold rounded shadow-sm">
                                                    <?php echo $list[$i]['ca_name'] ?>
                                                </span>
                                            </div>
                                        <?php } ?>

                                        <!-- 조회수 -->
                                        <div
                                            class="absolute top-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1 backdrop-blur-sm">
                                            <i class="fa fa-eye"></i> <?php echo $list[$i]['wr_hit'] ?>
                                        </div>
                                    </div>

                                    <!-- 텍스트 영역 -->
                                    <div class="p-6 h-full flex flex-col">

                                        <!-- 체크박스 (관리자용) -->
                                        <?php if ($is_checkbox) { ?>
                                            <div
                                                class="absolute top-2 left-1/2 -translate-x-1/2 bg-white/90 p-1 rounded z-10 shadow-sm">
                                                <input type="checkbox" name="chk_wr_id[]"
                                                    value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
                                            </div>
                                        <?php } ?>

                                        <!-- 3-1. 분류/지역 (제목 위) -->
                                        <?php if ($location_clean) { ?>
                                            <div class="flex items-center gap-1 text-gray-400 text-xs mb-1">
                                                <i class="fa fa-map-marker-alt text-brand-orange"></i>
                                                <span><?php echo htmlspecialchars($location_clean); ?></span>
                                            </div>
                                        <?php } ?>

                                        <!-- 3-2. 제목 (높이 고정: h-14) -->
                                        <h3 class="font-bold text-gray-900 text-lg mb-1 line-clamp-2 h-14">
                                            <?php echo htmlspecialchars($subject); ?>
                                        </h3>

                                        <!-- 3-3. 본문 (정제된 텍스트) -->
                                        <p class="text-gray-500 text-sm mt-2 mb-4 line-clamp-3">
                                            <?php echo htmlspecialchars($content_clean); ?>
                                        </p>

                                        <!-- 3-4. 가격/견적 (최하단 좌측 정렬) -->
                                        <?php if ($price_value) { ?>
                                            <div class="mt-auto text-left">
                                                <div
                                                    class="inline-flex items-end gap-1 rounded bg-gray-50 border border-gray-200 px-3 py-1.5">
                                                    <span class="text-xs text-gray-400 font-medium mb-0.5">예상 견적</span>
                                                    <span class="text-base font-bold text-gray-700 leading-none">
                                                        <?php echo htmlspecialchars($price_value); ?>만원대
                                                    </span>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>

                    <?php if (count($list) == 0) { ?>
                        <div
                            class="col-span-full py-32 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                            <div class="text-4xl mb-4">📦</div>
                            <p class="text-gray-500 text-lg">등록된 게시물이 없습니다.</p>
                        </div>
                    <?php } ?>
                </div>

                <div class="mt-16 flex justify-center">
                    <?php echo $write_pages; ?>
                </div>

            </main>
        </div>
    </form>
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
    </script>
<?php } ?>
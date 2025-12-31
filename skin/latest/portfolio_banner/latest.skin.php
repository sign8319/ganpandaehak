<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

// 회원 여부 재확인 (가장 확실한 방법)
global $member;
$is_member_check = (isset($member['mb_id']) && $member['mb_id']) ? true : false;

// width, height 설정
$thumb_w = 400;
$thumb_h = 500;
?>

<style>
    @keyframes neon-pulse {

        0%,
        100% {
            text-shadow: 0 0 5px rgba(249, 115, 22, 0.5), 0 0 10px rgba(249, 115, 22, 0.3);
            opacity: 1;
        }

        50% {
            text-shadow: 0 0 10px rgba(249, 115, 22, 0.8), 0 0 20px rgba(249, 115, 22, 0.5);
            opacity: 0.8;
        }
    }

    .neon-text {
        font-family: 'Courier New', Courier, monospace;
        color: #F97316;
        animation: neon-pulse 1.5s infinite alternate;
        letter-spacing: 2px;
        font-weight: bold;
    }
</style>

<div class="relative w-full max-w-7xl mx-auto px-4 py-8">
    <!-- Grid Layout: 3 items x 2 rows (Total 6) -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $img_content = '<img src="https://placehold.co/400x500/f3f4f6/9ca3af?text=No+Image" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="' . $list[$i]['subject'] . '">';

            // 직접 파일 정보 가져오기
            global $g5;
            $bo_table = isset($board['bo_table']) ? $board['bo_table'] : 'ca_portfolio';
            $sql = "SELECT bf_file FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '{$list[$i]['wr_id']}' AND bf_type BETWEEN '1' AND '3' ORDER BY bf_no LIMIT 1";
            $row = sql_fetch($sql);

            if ($row && $row['bf_file']) {
                $img_src = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['bf_file'];
                $img_content = '<img src="' . $img_src . '" class="card-image w-full h-full object-cover" alt="' . $list[$i]['subject'] . '">';
            }

            // 데이터 정제
            $location_clean = preg_replace('/#[^ ]+/', '', $list[$i]['wr_1']);
            $location_clean = trim($location_clean);

            $content_clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $list[$i]['wr_content']);
            $content_clean = strip_tags($content_clean);
            $content_clean = str_replace('&nbsp;', ' ', $content_clean);
            $content_clean = trim($content_clean);

            $category = $list[$i]['ca_name'] ? $list[$i]['ca_name'] : '일반';
            ?>
            <div class="h-auto">
                <a href="<?php echo $list[$i]['href'] ?>"
                    class="card-hover group bg-white rounded-2xl overflow-hidden shadow-lg block h-full flex flex-col transform transition-all duration-300 border border-gray-100 relative">

                    <!-- 썸네일 이미지 -->
                    <div class="card-image-container relative aspect-square shrink-0 overflow-hidden">
                        <?php echo $img_content; ?>
                        <div class="gradient-overlay"></div>

                        <!-- Hover Overlay Text -->
                        <div
                            class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                            <span class="text-white font-bold border border-white px-4 py-2 rounded-full backdrop-blur-sm">
                                자세히 보기
                            </span>
                        </div>

                        <!-- 왼쪽 상단 카테고리 배지 -->
                        <div class="absolute top-4 left-4 z-10">
                            <span class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                                <?php echo htmlspecialchars($category); ?>
                            </span>
                        </div>

                        <!-- 오른쪽 상단 조회수 -->
                        <div
                            class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 px-2 py-1 rounded-full text-white text-xs flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            </svg>
                            <?php echo number_format($list[$i]['wr_hit']); ?>
                        </div>
                    </div>

                    <!-- 텍스트 정보 영역 -->
                    <div class="p-6 flex flex-col flex-1 text-left">
                        <!-- 위치 정보 (제목 위, 주황색 아이콘 + 회색 텍스트) -->
                        <?php if ($location_clean) { ?>
                            <div class="flex items-center gap-1 text-xs text-gray-500 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-3 h-3 text-orange-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                <span><?php echo htmlspecialchars($location_clean); ?></span>
                            </div>
                        <?php } ?>

                        <!-- 제목 -->
                        <h3
                            class="font-bold text-gray-900 text-sm md:text-lg mb-2 md:line-clamp-2 h-auto md:h-14 group-hover:text-brand-orange transition-colors leading-tight">
                            <?php echo $list[$i]['subject'] ?>
                        </h3>

                        <!-- 본문 -->
                        <p class="text-gray-500 text-xs mb-1 line-clamp-3 leading-relaxed">
                            <?php echo cut_str($content_clean, 160); ?>
                        </p>
                        <div class="text-xs text-gray-400 mb-4 cursor-pointer">더보기</div>

                        <!-- 예상 견적/가격 박스 (사이즈 축소 요청 반영) -->
                        <div class="mt-auto">
                            <div
                                class="inline-flex items-center gap-1 bg-gray-50 border border-gray-100 rounded px-2 py-1.5">
                                <?php
                                // 가격 데이터 유효성 검사 (길이가 20자 이상이면 내용이 들어간 오류로 판단하여 숨김)
                                $wr_2_clean = strip_tags($list[$i]['wr_2']);
                                if ($list[$i]['wr_2'] && mb_strlen($wr_2_clean, 'utf-8') < 20) {
                                    ?>
                                    <span class="text-xs text-gray-500">예상 견적</span>
                                    <span class="text-base font-black text-gray-900 ml-1">
                                        <?php if ($is_member_check) { ?>
                                            <?php echo $list[$i]['wr_2']; ?><span class="text-xs text-gray-900 font-bold">만원대</span>
                                        <?php } else { ?>
                                            <span class="neon-text text-lg">???</span> <span
                                                class="text-xs text-gray-400 font-bold">만원대</span>
                                        <?php } ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="text-xs text-gray-400 font-bold">별도문의</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>

        <?php if (count($list) == 0) { ?>
            <div class="col-span-full w-full">
                <div class="py-20 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <p class="text-gray-500">등록된 게시물이 없습니다.</p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
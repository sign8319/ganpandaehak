<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
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
        font-family: 'Courier New', Courier, monospace !important;
        color: #F97316 !important;
        animation: neon-pulse 1.5s infinite alternate;
        letter-spacing: 2px;
        font-weight: bold;
    }
</style>




<style>
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

    /* Card Hover Effects from Index */
    .card-hover {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .card-hover:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .card-image-container {
        overflow: hidden;
        position: relative;
    }

    .card-image {
        transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .card-hover:hover .card-image {
        transform: scale(1.15);
    }

    .gradient-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .card-hover:hover .gradient-overlay {
        opacity: 1;
    }
</style>

<div class="font-sans w-full max-w-[1800px] mx-auto px-4 py-12">

    <div class="flex flex-col lg:flex-row gap-10">

        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="lg:sticky lg:top-24">
                <div class="mb-6 pb-4 border-b-2 border-gray-900 hidden lg:block">
                    <h2 class="text-2xl font-extrabold text-gray-900">CATEGORY</h2>
                </div>

                <nav class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-4 lg:pb-0 scrollbar-hide">
                    <a href="<?php echo $category_href ?>"
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
                    <a href="#" onclick="openConsultModal(); return false;"
                        class="inline-block w-full py-3 bg-orange-500 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                        견적 문의하기
                    </a>
                </div>
            </div>
        </aside>

        <main class="flex-1 min-w-0">

            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">
                        <?php echo $sca ? $sca : '전체 포트폴리오'; ?>
                    </h1>
                    <p class="text-gray-500 mt-2">
                        총 <strong class="text-brand-orange"><?php echo number_format($total_count) ?></strong>개의 작품이
                        있습니다.
                    </p>
                </div>
                <?php if ($write_href) { ?>
                    <a href="<?php echo $write_href ?>"
                        class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-black transition-colors flex items-center gap-2">
                        <i class="fa fa-pencil"></i> 등록하기
                    </a>
                <?php } ?>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                for ($i = 0; $i < count($list); $i++) {
                    $row = $list[$i];
                    // 썸네일
                    $thumb = get_list_thumbnail($board['bo_table'], $row['wr_id'], 500, 500, true, true);
                    $img_src = $thumb['src'] ? $thumb['src'] : 'https://placehold.co/500x500/f3f4f6/9ca3af?text=No+Image';

                    // 데이터 정제
                    $location_clean = preg_replace('/#[^ ]+/', '', $row['wr_1']);
                    $location_clean = trim($location_clean);

                    $content_clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $row['wr_content']);
                    $content_clean = strip_tags($content_clean);
                    $content_clean = str_replace('&nbsp;', ' ', $content_clean);
                    $content_clean = trim($content_clean);

                    $category = $row['ca_name'] ? $row['ca_name'] : '일반';
                    ?>
                    <a href="<?php echo $row['href'] ?>"
                        class="card-hover group bg-white rounded-2xl overflow-hidden shadow-lg block flex flex-col h-full transform transition-all duration-300">

                        <!-- 썸네일 이미지 -->
                        <div class="card-image-container relative aspect-square shrink-0 overflow-hidden">
                            <img src="<?php echo $img_src ?>" alt="<?php echo htmlspecialchars($row['subject']) ?>"
                                class="card-image w-full h-full object-cover">
                            <div class="gradient-overlay"></div>

                            <!-- Hover Overlay Text -->
                            <div
                                class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                                <span
                                    class="text-white font-bold border border-white px-4 py-2 rounded-full backdrop-blur-sm">
                                    자세히 보기
                                </span>
                            </div>

                            <div class="absolute top-4 left-4 z-10">
                                <span class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                                    <?php echo htmlspecialchars($category); ?>
                                </span>
                            </div>

                            <div
                                class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 px-2 py-1 rounded-full text-white text-xs flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-3 h-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                </svg>
                                <?php echo number_format($row['wr_hit']); ?>
                            </div>
                        </div>

                        <!-- 텍스트 정보 영역 -->
                        <div class="p-6 flex flex-col flex-1">
                            <!-- 위치 정보 -->
                            <?php if ($location_clean) { ?>
                                <div class="flex items-center gap-1 text-xs text-gray-400 mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" class="w-3 h-3 text-orange-400">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <?php echo htmlspecialchars($location_clean); ?>
                                </div>
                            <?php } ?>

                            <!-- 제목 -->
                            <h3
                                class="font-bold text-gray-900 text-sm md:text-lg mb-1 md:line-clamp-2 h-auto md:h-14 group-hover:text-brand-orange transition-colors">
                                <?php echo $row['subject'] ?>
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
                                    // 가격 데이터 유효성 검사
                                    $wr_2_clean = strip_tags($row['wr_2']);
                                    if ($row['wr_2'] && mb_strlen($wr_2_clean, 'utf-8') < 20) {
                                        ?>
                                        <span class="text-xs text-gray-500">예상 견적</span>
                                        <span class="text-base font-black text-gray-900 ml-1">
                                            <?php if ($is_member) { ?>
                                                <?php echo $row['wr_2']; ?><span class="text-xs text-gray-900 font-bold">만원대</span>
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
                <?php } ?>

                <?php if (count($list) == 0) { ?>
                    <div
                        class="col-span-full py-32 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="text-4xl mb-4">📂</div>
                        <p class="text-gray-500 text-lg">등록된 포트폴리오가 없습니다.</p>
                    </div>
                <?php } ?>
            </div>

            <div class="mt-16 flex justify-center">
                <?php echo $write_pages; ?>
            </div>

        </main>
    </div>
</div>
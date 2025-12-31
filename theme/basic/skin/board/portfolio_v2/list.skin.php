<?php
if (!defined('_GNUBOARD_'))
    exit;
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<style>
    /* ============================================
       네온 효과 (가격 ???)
       ============================================ */
    @keyframes neon-pulse {
        0%, 100% {
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

    /* ============================================
       카드 호버 효과
       ============================================ */
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

    /* ============================================
       Before & After 슬라이더 (카드용)
       ============================================ */
    .ba-slider-container {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .ba-before-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    .ba-after-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 50%;
        height: 100%;
        overflow: hidden;
        z-index: 2;
    }

    .ba-after-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 200%;
        height: 100%;
        object-fit: cover;
    }

    .ba-slider-handle {
        position: absolute;
        top: 0;
        left: 50%;
        width: 3px;
        height: 100%;
        background: white;
        z-index: 3;
        transform: translateX(-50%);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .ba-slider-handle::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        border: 2px solid #f97316;
    }

    .ba-slider-handle::after {
        content: '⟷';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        color: #f97316;
        font-weight: bold;
    }

    .ba-label {
        position: absolute;
        top: 12px;
        padding: 6px 12px;
        color: white;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        z-index: 4;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .ba-label-before {
        left: 12px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .ba-label-after {
        right: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    /* ============================================
       카테고리 링크
       ============================================ */
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

    /* ============================================
       검색창
       ============================================ */
    .search-container {
        position: relative;
        margin-bottom: 32px;
    }

    .search-input {
        width: 100%;
        padding: 16px 56px 16px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }

    .search-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }

    /* ============================================
       필터 버튼
       ============================================ */
    .filter-container {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        color: #6b7280;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .filter-btn:hover {
        border-color: #f97316;
        color: #f97316;
        background: #fff7ed;
    }

    .filter-btn.active {
        border-color: #f97316;
        background: #f97316;
        color: white;
    }

    /* ============================================
       스크롤 애니메이션
       ============================================ */
    .fade-in-up {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .fade-in-up.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ============================================
       로딩 스켈레톤
       ============================================ */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    /* ============================================
       반응형
       ============================================ */
    @media (max-width: 768px) {
        .search-input {
            padding: 14px 48px 14px 16px;
            font-size: 14px;
        }

        .search-btn {
            padding: 8px 16px;
            font-size: 13px;
        }

        .filter-btn {
            padding: 8px 16px;
            font-size: 13px;
        }
    }
</style>

<div class="font-sans w-full max-w-[1800px] mx-auto px-4 py-12">
    <div class="flex flex-col lg:flex-row gap-10">

        <!-- 사이드바 -->
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
                    <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=consult"
                        class="inline-block w-full py-3 bg-orange-500 rounded-lg font-bold hover:bg-orange-600 transition-colors">
                        견적 문의하기
                    </a>
                </div>
            </div>
        </aside>

        <!-- 메인 콘텐츠 -->
        <main class="flex-1 min-w-0">

            <!-- 검색창 -->
            <div class="search-container">
                <form name="fsearch" method="get" action="<?php echo get_pretty_url($bo_table); ?>">
                    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
                    <input type="hidden" name="sca" value="<?php echo $sca ?>">
                    <input type="hidden" name="sfl" value="wr_subject||wr_content">
                    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" 
                           class="search-input" placeholder="🔍 제목, 내용으로 검색하세요...">
                    <button type="submit" class="search-btn">검색</button>
                </form>
            </div>

            <!-- 필터 + 헤더 -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">
                        <?php echo $sca ? $sca : '전체 포트폴리오'; ?>
                    </h1>
                    <p class="text-gray-500 mt-2">
                        총 <strong class="text-brand-orange"><?php echo number_format($total_count) ?></strong>개의 작품이 있습니다.
                    </p>
                </div>
                <?php if ($write_href) { ?>
                    <a href="<?php echo $write_href ?>"
                        class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-black transition-colors flex items-center gap-2">
                        <i class="fa fa-pencil"></i> 등록하기
                    </a>
                <?php } ?>
            </div>

            <!-- 필터 버튼 -->
            <div class="filter-container">
                <button class="filter-btn active" onclick="filterAll()">
                    🎨 전체
                </button>
                <button class="filter-btn" onclick="filterType('fixed')">
                    📷 고정형
                </button>
                <button class="filter-btn" onclick="filterType('beforeafter')">
                    🔄 Before & After
                </button>
                <button class="filter-btn" onclick="sortBy('latest')">
                    🆕 최신순
                </button>
                <button class="filter-btn" onclick="sortBy('popular')">
                    🔥 인기순
                </button>
            </div>

            <!-- 카드 그리드 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="portfolio-grid">
                <?php
                for ($i = 0; $i < count($list); $i++) {
                    $row = $list[$i];
                    
                    // 파일 정보 가져오기
                    global $g5;
                    $sql_file = "SELECT bf_no, bf_file FROM {$g5['board_file_table']} 
                                WHERE bo_table = '{$bo_table}' AND wr_id = '{$row['wr_id']}' 
                                ORDER BY bf_no ASC LIMIT 2";
                    $file_result = sql_query($sql_file);
                    
                    $file_0 = null;
                    $file_1 = null;
                    while ($file_row = sql_fetch_array($file_result)) {
                        if ($file_row['bf_no'] == 0) $file_0 = $file_row['bf_file'];
                        if ($file_row['bf_no'] == 1) $file_1 = $file_row['bf_file'];
                    }
                    
                    // Before/After 여부 판단
                    $is_ba = ($file_0 && $file_1);
                    
                    if ($is_ba) {
                        $after_img = G5_DATA_URL . '/file/' . $bo_table . '/' . $file_0;
                        $before_img = G5_DATA_URL . '/file/' . $bo_table . '/' . $file_1;
                    } else {
                        $thumb = get_list_thumbnail($board['bo_table'], $row['wr_id'], 500, 500, true, true);
                        $img_src = $thumb['src'] ? $thumb['src'] : 'https://placehold.co/500x500/f3f4f6/9ca3af?text=No+Image';
                    }

                    // 데이터 정제
                    $location_clean = preg_replace('/#[^ ]+/', '', $row['wr_1']);
                    $location_clean = trim($location_clean);

                    $content_clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', "", $row['wr_content']);
                    $content_clean = strip_tags($content_clean);
                    $content_clean = str_replace('&nbsp;', ' ', $content_clean);
                    $content_clean = trim($content_clean);

                    $category = $row['ca_name'] ? $row['ca_name'] : '일반';
                    ?>
                    <div class="fade-in-up" data-type="<?php echo $is_ba ? 'beforeafter' : 'fixed'; ?>">
                        <a href="<?php echo $row['href'] ?>"
                            class="card-hover group bg-white rounded-2xl overflow-hidden shadow-lg block flex flex-col h-full transform transition-all duration-300">

                            <!-- 이미지 영역 -->
                            <div class="card-image-container relative aspect-square shrink-0">
                                <?php if ($is_ba) { ?>
                                    <!-- Before & After 슬라이더 -->
                                    <div class="ba-slider-container">
                                        <img src="<?php echo $before_img ?>" alt="Before" class="ba-before-img">
                                        
                                        <div class="ba-after-container" data-container="<?php echo $i; ?>">
                                            <img src="<?php echo $after_img ?>" alt="After" class="ba-after-img">
                                        </div>
                                        
                                        <div class="ba-slider-handle" data-handle="<?php echo $i; ?>"></div>
                                        
                                        <span class="ba-label ba-label-before">Before</span>
                                        <span class="ba-label ba-label-after">After</span>
                                    </div>
                                <?php } else { ?>
                                    <!-- 일반 이미지 -->
                                    <img src="<?php echo $img_src ?>" alt="<?php echo htmlspecialchars($row['subject']) ?>"
                                        class="card-image w-full h-full object-cover">
                                    <div class="gradient-overlay"></div>
                                <?php } ?>

                                <!-- Hover Overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
                                    <span class="text-white font-bold border border-white px-4 py-2 rounded-full backdrop-blur-sm">
                                        자세히 보기
                                    </span>
                                </div>

                                <!-- 카테고리 -->
                                <div class="absolute top-4 left-4 z-10">
                                    <span class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full shadow-lg">
                                        <?php echo htmlspecialchars($category); ?>
                                    </span>
                                </div>

                                <!-- 조회수 -->
                                <div class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 px-2 py-1 rounded-full text-white text-xs flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    </svg>
                                    <?php echo number_format($row['wr_hit']); ?>
                                </div>
                            </div>

                            <!-- 텍스트 정보 -->
                            <div class="p-6 flex flex-col flex-1">
                                <!-- 위치 -->
                                <?php if ($location_clean) { ?>
                                    <div class="flex items-center gap-1 text-xs text-gray-400 mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 text-orange-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <?php echo htmlspecialchars($location_clean); ?>
                                    </div>
                                <?php } ?>

                                <!-- 제목 -->
                                <h3 class="font-bold text-gray-900 text-sm md:text-lg mb-1 line-clamp-2 h-auto md:h-14 group-hover:text-brand-orange transition-colors">
                                    <?php echo $row['subject'] ?>
                                </h3>

                                <!-- 본문 -->
                                <p class="text-gray-500 text-xs mb-1 line-clamp-3 leading-relaxed">
                                    <?php echo cut_str($content_clean, 160); ?>
                                </p>
                                <div class="text-xs text-gray-400 mb-4 cursor-pointer">더보기</div>

                                <!-- 가격 -->
                                <div class="mt-auto">
                                    <div class="inline-flex items-center gap-1 bg-gray-50 border border-gray-100 rounded px-2 py-1.5">
                                        <?php
                                        $wr_2_clean = strip_tags($row['wr_2']);
                                        if ($row['wr_2'] && mb_strlen($wr_2_clean, 'utf-8') < 20) {
                                            ?>
                                            <span class="text-xs text-gray-500">예상 견적</span>
                                            <span class="text-base font-black text-gray-900 ml-1">
                                                <?php if ($is_member) { ?>
                                                    <?php echo $row['wr_2']; ?><span class="text-xs text-gray-900 font-bold">만원대</span>
                                                <?php } else { ?>
                                                    <span class="neon-text text-lg">???</span> <span class="text-xs text-gray-400 font-bold">만원대</span>
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
                    <div class="col-span-full py-32 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="text-4xl mb-4">📂</div>
                        <p class="text-gray-500 text-lg">등록된 포트폴리오가 없습니다.</p>
                    </div>
                <?php } ?>
            </div>

            <!-- 페이지네이션 -->
            <div class="mt-16 flex justify-center">
                <?php echo $write_pages; ?>
            </div>

        </main>
    </div>
</div>

<script>
// ============================================
// Before & After 슬라이더
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const baCards = document.querySelectorAll('.ba-slider-container');
    
    baCards.forEach((card, index) => {
        const container = card.querySelector(`[data-container="${index}"]`);
        const handle = card.querySelector(`[data-handle="${index}"]`);
        let isActive = false;

        // 호버 시 활성화
        card.addEventListener('mouseenter', () => isActive = true);
        card.addEventListener('mouseleave', () => isActive = false);

        // 마우스 이동
        card.addEventListener('mousemove', (e) => {
            if (!isActive) return;
            
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            let percentage = (x / rect.width) * 100;
            percentage = Math.max(0, Math.min(100, percentage));

            container.style.width = percentage + '%';
            handle.style.left = percentage + '%';
        });

        // 터치 이벤트 (모바일)
        card.addEventListener('touchmove', (e) => {
            const rect = card.getBoundingClientRect();
            const touch = e.touches[0];
            const x = touch.clientX - rect.left;
            let percentage = (x / rect.width) * 100;
            percentage = Math.max(0, Math.min(100, percentage));

            container.style.width = percentage + '%';
            handle.style.left = percentage + '%';
        });
    });
});

// ============================================
// 필터 기능
// ============================================
function filterAll() {
    const items = document.querySelectorAll('[data-type]');
    items.forEach(item => {
        item.style.display = 'block';
    });
    updateActiveFilter(0);
}

function filterType(type) {
    const items = document.querySelectorAll('[data-type]');
    items.forEach(item => {
        if (item.dataset.type === type) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
    updateActiveFilter(type === 'fixed' ? 1 : 2);
}

function sortBy(type) {
    // 정렬 기능은 서버 사이드에서 처리
    alert(type === 'latest' ? '최신순으로 정렬됩니다' : '인기순으로 정렬됩니다');
}

function updateActiveFilter(index) {
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach((btn, i) => {
        if (i === index) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

// ============================================
// 스크롤 애니메이션
// ============================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in-up').forEach(el => {
    observer.observe(el);
});
</script>

<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// 스타일시트 연결
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
add_javascript('<script src="' . G5_JS_URL . '/viewimageresize.js"></script>', 0);

// [NEW] Get Thumbnail for Widget
$thumb = get_list_thumbnail($board['bo_table'], $view['wr_id'], 400, 400);
$thumb_src = $thumb['src'] ? $thumb['src'] : G5_THEME_URL . '/img/no_img.png';
// If no thumb from standard lib, try file array or content
if (!$thumb['src']) {
    if (isset($view['file'][0]['path']) && $view['file'][0]['file']) {
        $thumb_src = $view['file'][0]['path'] . '/' . $view['file'][0]['file'];
    }
}
?>

<style>
    body {
        font-family: 'Pretendard', sans-serif;
    }

    /* 본문 이미지 자동 리사이징 */
    #bo_v_con img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }

    /* 썸네일/대표이미지 영역 강제 100% (오른쪽 여백 제거) */
    .view-thumbnail-container img {
        width: 100% !important;
        height: auto !important;
        object-fit: cover;
        display: block;
        /* 하단 여백 제거 */
    }

    @keyframes neon-border {

        0%,
        100% {
            box-shadow: 0 0 5px rgba(249, 115, 22, 0.5), inset 0 0 5px rgba(249, 115, 22, 0.2);
            border-color: #F97316;
        }

        50% {
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.8), inset 0 0 10px rgba(249, 115, 22, 0.4);
            border-color: #fed7aa;
        }
    }

    .btn-emphasis {
        animation: neon-border 2s infinite alternate;
    }

    /* Watermark Styles (Global) */
    .watermark-overlay {
        position: absolute !important;
        z-index: 50;
        pointer-events: none;
        max-width: 25% !important;
        width: 25% !important;
        height: auto !important;
        opacity: 0.9;
        display: block !important;
        margin: 0 !important;
        /* Prevent editor margins from affecting it */
        box-shadow: none !important;
        /* Reset any shadows */
        border: none !important;
    }

    .watermark-center {
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .watermark-br {
        bottom: 20px;
        right: 20px;
    }

    .watermark-tl {
        top: 20px;
        left: 20px;
    }
</style>

<div class="w-full max-w-[1600px] mx-auto px-4 py-12">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

        <div class="w-full">
            <div
                class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 view-thumbnail-container relative">
                <?php
                // [MOVED] Watermark Logic (Available for both Single and Dual Image modes)
                $watermark_html = '';
                // DEBUG: echo "<!-- WR_7: {$view['wr_7']}, WR_8: {$view['wr_8']} -->"; 
                if (isset($view['wr_7']) && $view['wr_7'] == '1' && !empty($view['wr_8'])) {
                    $wm_pos = isset($view['wr_6']) ? $view['wr_6'] : 'center';
                    $wm_class = 'watermark-center';
                    if ($wm_pos == 'bottom-right')
                        $wm_class = 'watermark-br';
                    if ($wm_pos == 'top-left')
                        $wm_class = 'watermark-tl';
                    $watermark_html = '<img src="' . $view['wr_8'] . '" class="watermark-overlay ' . $wm_class . '" alt="Watermark">';
                }

                // Before & After 슬라이더 로직 (첨부파일 2개 이상일 때 자동 적용)
                // 규칙: 파일 #1(bf_no=0)=After(완성), 파일 #2(bf_no=1)=Before(작업전)
                if (isset($view['file'][0]) && isset($view['file'][1]) && $view['file'][0]['view'] && $view['file'][1]['view']) {
                    $after_img_src = $view['file'][0]['path'] . '/' . $view['file'][0]['file'];
                    $before_img_src = $view['file'][1]['path'] . '/' . $view['file'][1]['file'];

                    // Default to 'after' if empty (matches write skin default), or strict check
                    // But if user specifically selected 'auto', we need to respect it.
                    // If wr_9 is empty, it means legacy data or default -> let's default to 'after' based on write skin advice? 
                    // Or slider? User complained about slider.
                    // Let's use strict check.
                    $display_mode = !empty($view['wr_9']) ? $view['wr_9'] : '';

                    // [MOVED UP] Watermark Logic was here
                
                    if ($display_mode == 'before') {
                        echo '<img src="' . $before_img_src . '" class="w-full h-auto object-cover rounded-2xl" alt="Before">';
                        echo '<span class="absolute top-5 left-5 bg-red-500 text-white px-3 py-1 rounded-lg font-bold">Before</span>';
                        echo $watermark_html;
                    } elseif ($display_mode == 'after') {
                        echo '<img src="' . $after_img_src . '" class="w-full h-auto object-cover rounded-2xl" alt="After">';
                        echo '<span class="absolute top-5 right-5 bg-green-500 text-white px-3 py-1 rounded-lg font-bold">After</span>';
                        echo $watermark_html;
                    } else {
                        // Slider Mode (auto) OR Default
                        ?>
                        <!-- Before/After Slider HTML (Synced with Main Page) -->
                        <style>
                            /* 메인 페이지와 동일한 스타일 적용 */
                            .view-comparison-wrapper {
                                position: relative;
                                width: 100%;
                                aspect-ratio: 1 / 1;
                                height: auto;
                                overflow: hidden;
                                cursor: col-resize;
                                background: #f0f0f0;
                                border-radius: 16px;
                            }

                            .view-comparison-wrapper img {
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                                display: block;
                                user-select: none;
                                pointer-events: none;
                            }

                            .view-before-image {
                                z-index: 1;
                            }

                            .view-after-container {
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 50%;
                                height: 100%;
                                overflow: hidden;
                                z-index: 2;
                            }

                            .view-after-image {
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 200%;
                                height: 100%;
                                object-fit: cover;
                            }

                            /* 핸들 스타일 메인과 통일 (주황색 원형 버튼) */
                            .view-slider-handle {
                                position: absolute;
                                top: 0;
                                left: 50%;
                                width: 6px;
                                height: 100%;
                                background: white;
                                z-index: 3;
                                transform: translateX(-50%);
                                pointer-events: none;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
                            }

                            .view-slider-handle::before {
                                content: '';
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                width: 56px;
                                height: 56px;
                                background: white;
                                border-radius: 50%;
                                transform: translate(-50%, -50%);
                                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
                                border: 3px solid #f97316;
                                transition: all 0.3s ease;
                            }

                            .view-comparison-wrapper:hover .view-slider-handle::before {
                                background: #f97316;
                                transform: translate(-50%, -50%) scale(1.1);
                                box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
                            }

                            .view-slider-handle::after {
                                content: '';
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: 24px;
                                height: 24px;
                                background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8L22 12L18 16"/><path d="M6 8L2 12L6 16"/><line x1="2" y1="12" x2="22" y2="12"/></svg>');
                                background-size: contain;
                                background-repeat: no-repeat;
                                background-position: center;
                                z-index: 1;
                                transition: all 0.3s ease;
                            }

                            .view-comparison-wrapper:hover .view-slider-handle::after {
                                background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8L22 12L18 16"/><path d="M6 8L2 12L6 16"/><line x1="2" y1="12" x2="22" y2="12"/></svg>');
                            }

                            /* 라벨 스타일 메인과 통일 (빨강/초록 그라데이션) */
                            .view-label {
                                position: absolute;
                                top: 20px;
                                padding: 6px 14px;
                                background: rgba(0, 0, 0, 0.6);
                                /* Semi-transparent black */
                                color: white;
                                border-radius: 20px;
                                font-size: 14px;
                                font-weight: bold;
                                z-index: 10;
                                pointer-events: none;
                                transition: opacity 0.3s ease;
                                /* Smooth fade for labels */
                            }

                            .view-comparison-wrapper:hover .view-label {
                                transform: scale(1.05);
                                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
                            }

                            .label-b {
                                left: 20px;
                                background-color: #ef4444;
                            }

                            /* Red for Before */
                            .label-a {
                                right: 20px;
                                background-color: #10b981;
                            }

                            /* Green for After */

                            /* 힌트 텍스트 */
                            .view-slider-hint {
                                position: absolute;
                                bottom: 20px;
                                left: 50%;
                                transform: translateX(-50%);
                                padding: 8px 16px;
                                background: rgba(0, 0, 0, 0.7);
                                color: white;
                                font-size: 13px;
                                border-radius: 20px;
                                z-index: 4;
                                opacity: 0;
                                animation: fadeInOut 3s ease-in-out infinite;
                                pointer-events: none;
                            }

                            @keyframes fadeInOut {

                                0%,
                                100% {
                                    opacity: 0;
                                }

                                10%,
                                90% {
                                    opacity: 1;
                                }
                            }

                            .view-comparison-wrapper:hover .view-slider-hint {
                                animation: none;
                                opacity: 0;
                            }

                            /* Auto Mode Styles */
                            .view-comparison-wrapper.auto-mode {
                                cursor: default !important;
                            }

                            .view-comparison-wrapper.auto-mode .view-slider-handle,
                            .view-comparison-wrapper.auto-mode .view-slider-hint {
                                display: none !important;
                            }

                            .view-comparison-wrapper.auto-mode .view-after-container {
                                width: 100% !important;
                                border-right: none;
                                transition: opacity 1s ease-in-out;
                            }

                            /* [NEW] Label Toggling in Auto Mode */
                            /* When showing After (opacity 1), hide Before label */
                            .view-comparison-wrapper.auto-mode.state-after .label-b {
                                opacity: 0;
                            }

                            .view-comparison-wrapper.auto-mode.state-after .label-a {
                                opacity: 1;
                            }

                            /* When showing Before (opacity 0), hide After label */
                            .view-comparison-wrapper.auto-mode.state-before .label-a {
                                opacity: 0;
                            }

                            .view-comparison-wrapper.auto-mode.state-before .label-b {
                                opacity: 1;
                            }
                        </style>

                        <!-- Initial State: state-after (since we start with After image visible) -->
                        <div class="view-comparison-wrapper <?php echo $wrapper_class; ?> state-after" id="viewBeforeAfter">
                            <!-- Background: Before Image (bf_no=1) -->
                            <img src="<?php echo $before_img_src; ?>" class="view-before-image" alt="Before">
                            <span class="view-label label-b">Before</span>

                            <!-- Foreground: After Image (bf_no=0) -->
                            <div class="view-after-container" style="opacity: 1;">
                                <img src="<?php echo $after_img_src; ?>" class="view-after-image" alt="After">
                            </div>
                            <span class="view-label label-a">After</span>

                            <!-- Handle -->
                            <div class="view-slider-handle"></div>

                            <!-- Hint Text -->
                            <div class="view-slider-hint">마우스를 좌우로 움직여보세요</div>

                            <!-- Watermark Injection for Auto/Slider Mode -->
                            <?php echo $watermark_html; ?>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const wrapper = document.getElementById('viewBeforeAfter');
                                if (!wrapper) return;

                                const afterContainer = wrapper.querySelector('.view-after-container');
                                const handle = wrapper.querySelector('.view-slider-handle');
                                let isActive = false;

                                // [NEW] Auto Slide State
                                const displayMode = "<?php echo $display_mode; ?>";
                                console.log('JS Display Mode:', displayMode);

                                // Force class if JS detects auto (double safety)
                                if (displayMode === 'auto') {
                                    wrapper.classList.add('auto-mode');
                                }

                                let autoInterval = null;
                                let isAutoPlaying = (displayMode === 'auto');

                                function updateSlider(clientX) {
                                    if (isAutoPlaying) return;

                                    const rect = wrapper.getBoundingClientRect();
                                    const x = clientX - rect.left;
                                    let percentage = (x / rect.width) * 100;
                                    percentage = Math.max(0, Math.min(100, percentage));

                                    setSliderPos(percentage);
                                }

                                function setSliderPos(percentage) {
                                    afterContainer.style.width = percentage + '%';
                                    handle.style.left = percentage + '%';
                                }

                                // Auto Play Logic (Crossfade)
                                if (isAutoPlaying) {
                                    // opacity starts at 1 (After)
                                    let showAfter = true;
                                    wrapper.classList.add('state-after'); // Initial state

                                    autoInterval = setInterval(() => {
                                        showAfter = !showAfter; // Toggle
                                        afterContainer.style.opacity = showAfter ? '1' : '0';

                                        // Toggle Label States
                                        if (showAfter) {
                                            wrapper.classList.add('state-after');
                                            wrapper.classList.remove('state-before');
                                        } else {
                                            wrapper.classList.add('state-before');
                                            wrapper.classList.remove('state-after');
                                        }
                                    }, 2500);
                                } else {
                                    // Manual Slider Logic Bindings
                                    // 호버 방식: 마우스만 올리면 작동
                                    wrapper.addEventListener('mouseenter', () => isActive = true);
                                    wrapper.addEventListener('mouseleave', () => isActive = false);
                                    wrapper.addEventListener('mousemove', (e) => {
                                        if (!isActive) return;
                                        updateSlider(e.clientX);
                                    });

                                    // 모바일: 터치 방식
                                    wrapper.addEventListener('touchmove', (e) => {
                                        const touch = e.touches[0];
                                        updateSlider(touch.clientX);
                                    });
                                }
                            });
                        </script>
                        <?php
                    } // End of Slider Mode else type
                } else {
                    // 기존 로직: 파일이 없거나 1개일 때
                    // ... (이 부분은 이전과 동일하므로 생략 가능하나 전체 교체를 위해 유지)
                    $v_img_count = count($view['file']);
                    if ($v_img_count && isset($view['file'][0]) && $view['file'][0]['view']) {
                        echo '<div style="position: relative; line-height: 0; display: block;">';
                        echo get_view_thumbnail($view['file'][0]['view']);
                        echo $watermark_html; // Inject Watermark for Single Image
                        echo '</div>';
                    } else {
                        echo '<img src="https://placehold.co/800x800/f3f4f6/9ca3af?text=No+Image" class="w-full h-full object-cover">';
                    }
                }
                ?>
            </div>
        </div>

        <div class="lg:sticky lg:top-24 h-fit">
            <div class="flex justify-between items-center mb-4">
                <?php if ($view['ca_name']) { ?>
                    <span class="px-3 py-1 bg-gray-900 text-white text-xs font-bold rounded-full uppercase tracking-wider">
                        <?php echo $view['ca_name']; ?>
                    </span>
                <?php } ?>
                <div class="text-gray-400 text-sm">
                    <i class="fa fa-eye"></i> <?php echo number_format($view['wr_hit']) ?>
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 leading-tight">
                <?php echo cut_str(get_text($view['wr_subject']), 255); ?>
            </h1>

            <!-- Price Removed -->

            <div class="text-gray-600 mb-8 text-sm space-y-2">
                <p><i class="fa fa-check text-brand-orange mr-2"></i>전문 디자이너의 1:1 맞춤 디자인</p>
                <p><i class="fa fa-check text-brand-orange mr-2"></i>철저한 시공 및 A/S 보장</p>
                <p><i class="fa fa-check text-brand-orange mr-2"></i>무료 견적 상담 가능</p>
            </div>

            <div class="flex flex-col gap-3">
                <!-- Member Only Price Section -->
                <div class="mt-2">
                    <?php if ($is_member) { ?>
                        <?php if ($view['wr_2']) { ?>
                            <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-5 text-center">
                                <p class="text-sm text-gray-500 mb-1">예상 견적가</p>
                                <div class="text-3xl font-black text-gray-900 tracking-tight">
                                    <?php echo $view['wr_2']; ?><span class="text-lg font-bold text-gray-600 ml-1">만원대</span>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="w-full bg-gray-50 border border-gray-200 rounded-xl p-5 text-center">
                            <p class="text-gray-800 font-bold mb-3">로그인 시 예상 견적 즉시 확인</p>
                            <a href="<?php echo G5_BBS_URL ?>/login.php?url=<?php echo urlencode(get_pretty_url($bo_table, $view['wr_id'])); ?>"
                                class="btn-emphasis block w-full py-4 bg-gray-900 text-white rounded-xl font-black text-lg hover:bg-black transition-all shadow-xl flex items-center justify-center gap-2 border-2 border-orange-500">
                                <span>로그인하고 금액 확인하기</span>
                                <i class="fa fa-unlock-alt text-orange-400"></i>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <!-- [Updated] Hover Interaction Selection UI -->
                <div class="portfolio-action-area" id="portfolioActionArea">
                    <div class="btn-wrapper action-wrapper-target" id="actionWrapper">
                        <!-- Initial State (Rendered by JS based on storage) -->
                    </div>
                </div>

                <?php if ($update_href || $delete_href) { ?>
                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-200">
                        <?php if ($update_href) { ?>
                            <a href="<?php echo $update_href ?>"
                                class="px-3 py-1 bg-white border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">수정</a>
                        <?php } ?>
                        <?php if ($delete_href) { ?>
                            <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;"
                                class="px-3 py-1 bg-white border border-red-200 text-red-500 rounded text-sm hover:bg-red-50">삭제</a>
                        <?php } ?>
                        <a href="<?php echo $list_href ?>"
                            class="px-3 py-1 bg-gray-100 rounded text-sm text-gray-600 hover:bg-gray-200">목록으로</a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Additional Styles for Hover/Active Interaction -->
    <style>
        .btn-wrapper {
            position: relative;
            min-height: 60px;
            /* Prevent layout shift */
        }

        .btn-default-custom {
            width: 100%;
            background: #FF6B2C;
            color: white;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 107, 44, 0.3);
        }

        .btn-options-custom {
            display: none;
            flex-direction: column;
            gap: 12px;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover Effect (Desktop) */
        @media (min-width: 769px) {
            .btn-wrapper:hover .btn-default-custom {
                display: none;
            }

            .btn-wrapper:hover .btn-options-custom {
                display: flex;
            }
        }

        /* Active Effect (Mobile via JS) */
        .btn-wrapper.active .btn-default-custom {
            display: none;
        }

        .btn-wrapper.active .btn-options-custom {
            display: flex;
        }

        /* Option Buttons */
        .btn-opt-primary {
            width: 100%;
            background: #FF6B2C;
            color: white;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-opt-primary:hover {
            background: #E55A1F;
        }

        .btn-opt-secondary {
            width: 100%;
            background: white;
            color: #555;
            padding: 16px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-opt-secondary:hover {
            background: #f8f8f8;
            border-color: #bbb;
        }

        /* Saved State */
        .saved-state {
            display: flex;
            flex-direction: column;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }

        .saved-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: #FFF5F0;
            border: 1px solid #FFD4B8;
            border-radius: 12px;
            color: #FF6B2C;
            font-weight: 700;
            font-size: 16px;
        }

        .btn-remove-small {
            padding: 4px 12px;
            font-size: 13px;
            color: #999;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            font-weight: 500;
        }

        .btn-remove-small:hover {
            color: #ff4444;
            border-color: #ffcccc;
            background: #fff0f0;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>

    <div class="border-t-2 border-gray-900 pt-16 mt-8">
        <h3 class="text-2xl font-extrabold text-gray-900 mb-10 text-center">상품 상세 정보</h3>

        <div id="bo_v_con" class="prose max-w-none text-gray-800 leading-loose mx-auto">
            <?php echo get_view_thumbnail($view['content']); ?>
        </div>

        <!-- [NEW] Duplicate Action Area at Bottom -->
        <div class="mt-16 max-w-md mx-auto">
            <div class="portfolio-action-area">
                <div class="btn-wrapper action-wrapper-target">
                    <!-- Content rendered by JS -->
                </div>
            </div>
        </div>
    </div>

    <?php include_once(G5_BBS_PATH . '/view_comment.php'); ?>

</div>

<script>
    $(function () {
        // 이미지 리사이즈
        $("#bo_v_con").viewimageresize();

        <?php if (isset($view['wr_7']) && $view['wr_7'] == '1' && !empty($view['wr_8'])) { ?>
            // [NEW] 에디터 본문 이미지 워터마크 적용
            var wmUrl = "<?php echo $view['wr_8']; ?>";
            var wmPos = "<?php echo isset($view['wr_6']) ? $view['wr_6'] : 'center'; ?>";
            var wmClass = 'watermark-center';
            if (wmPos === 'bottom-right') wmClass = 'watermark-br';
            if (wmPos === 'top-left') wmClass = 'watermark-tl';

            var wmHtml = '<img src="' + wmUrl + '" class="watermark-overlay ' + wmClass + '" alt="Watermark">';

            var selector = "<?php echo ($view['wr_3'] == '1') ? '#bo_v_con img' : '#bo_v_con img.watermark-target'; ?>";

            $(selector).each(function () {
                var $img = $(this);
                if (!$img.parent().hasClass('watermark-wrapper')) {
                    $img.wrap('<div class="watermark-wrapper" style="display:inline-block; position:relative; max-width:100%; line-height: 0;"></div>');
                    $img.after(wmHtml);
                }
            });
        <?php } ?>
    });

    // [New] Hover Interaction Logic
    const currentItem = {
        id: "<?php echo $view['wr_id']; ?>",
        subject: "<?php echo addslashes($view['wr_subject']); ?>",
        img: "<?php echo isset($thumb_src) ? $thumb_src : '' ?>",
        url: window.location.href // [NEW] URL for Widget
    };

    // Initial Load
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(checkPortfolioState, 100);

        // [NEW] Set Current Page Portfolio for Widget
        if (typeof setCurrentPagePortfolio === 'function') {
            setCurrentPagePortfolio(currentItem.id, currentItem.subject, currentItem.img, currentItem.url);
        }

        // Mobile Touch Events
        $(document).on('click', '.btn-default-custom', function (e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                $(this).closest('.btn-wrapper').addClass('active');
            }
        });

        // Close on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.btn-wrapper').length) {
                $('.btn-wrapper').removeClass('active');
            }
        });
    });

    // Listen for storage changes
    window.addEventListener('storage', function (e) {
        if (e.key === 'selected_portfolios') checkPortfolioState();
    });

    // Check State Function
    function checkPortfolioState() {
        let items = [];
        try {
            items = JSON.parse(localStorage.getItem('selected_portfolios') || '[]');
        } catch (e) { }

        const isSelected = items.some(i => i.id === currentItem.id);
        const count = items.length;

        // [UPDATED] Target all wrappers (Top and Bottom)
        const wrappers = document.querySelectorAll('.action-wrapper-target');

        wrappers.forEach(wrapper => {
            if (isSelected) {
                // Render Saved State
                wrapper.innerHTML = `
                    <div class="saved-state">
                        <div class="saved-badge">
                            <span><i class="fa fa-check-circle"></i> 보관됨</span>
                            <button class="btn-remove-small" onclick="removeThis()">선택 취소</button>
                        </div>
                        <button class="btn-opt-primary" onclick="goToQuotePage()">
                            담은 디자인으로 상담신청 (${count}개) <i class="fa fa-arrow-right"></i>
                        </button>
                        <button class="btn-opt-secondary" onclick="goToList()">
                            다른 디자인도 담기
                        </button>
                    </div>
                `;
            } else {
                // Render Default State with Hover Options
                wrapper.innerHTML = `
                    <button class="btn-default-custom" onclick="handleDefaultClick(event)">
                        이 컨셉으로 디자인 상담
                    </button>
                    
                    <div class="btn-options-custom">
                        <button class="btn-opt-primary" onclick="goToEstimateNow()">
                            이 디자인으로 바로 상담신청 <i class="fa fa-arrow-right"></i>
                        </button>
                        <button class="btn-opt-secondary" onclick="addToCartAndContinue()">
                            + 보관하고 다른 디자인 보기
                        </button>
                    </div>
                `;
                wrapper.classList.remove('active'); // Reset mobile active state
            }
        });
    }

    // Actions
    function handleDefaultClick(e) {
        // Desktop: button is hidden on hover, so this click only happens if css fails or very fast click?
        // Actually on desktop hover hides default btn. So click is impossible unless touch/mobile.
        // Mobile logic handled by event listener above. 
        // If desktop click happens (e.g. edge case), treat as "Go to Estimate Now" or "Expand"?
        // Let's assume this is mostly for mobile trigger, handled by listener.
    }

    function goToEstimateNow() {
        // Direct Consult
        const url = "<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&sca=견적문의&portfolio_ids=" + currentItem.id;
        location.href = url;
    }

    function addToCartAndContinue() {
        if (typeof addPortfolio === 'function') {
            addPortfolio(currentItem);

            // Manual UI feedback before storage event fires
            setTimeout(checkPortfolioState, 50);

            // [UPDATED] Redirect to List after adding
            setTimeout(() => {
                location.href = "<?php echo $list_href ?>";
            }, 500); // Short delay for toast to appear briefly or just immediate? 
            // User said: "누르면 추가되고 본문으로 이동되게해주고" (Add and go to text/list)

        } else {
            alert('오류: 위젯 로드 실패');
        }
    }

    function removeThis() {
        if (typeof removePortfolio === 'function') {
            removePortfolio(currentItem.id);
            setTimeout(checkPortfolioState, 50);
        }
    }

    function goToQuotePage() {
        if (typeof submitQuoteRequest === 'function') {
            submitQuoteRequest();
        } else {
            location.href = "<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&sca=견적문의";
        }
    }

    function goToList() {
        location.href = "<?php echo $list_href ?>";
    }
</script>
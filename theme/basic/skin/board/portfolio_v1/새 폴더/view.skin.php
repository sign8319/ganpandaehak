<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// 스타일시트 연결
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
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
</style>

<div class="w-full max-w-[1600px] mx-auto px-4 py-12">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

        <div class="w-full">
            <div
                class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 view-thumbnail-container relative">
                <?php
                // Before & After 슬라이더 로직 (첨부파일 2개 이상일 때 자동 적용)
                // 규칙: 파일 #1(bf_no=0)=After(완성), 파일 #2(bf_no=1)=Before(작업전)
                if (isset($view['file'][0]) && isset($view['file'][1]) && $view['file'][0]['view'] && $view['file'][1]['view']) {
                    $after_img_src = $view['file'][0]['path'] . '/' . $view['file'][0]['file'];
                    $before_img_src = $view['file'][1]['path'] . '/' . $view['file'][1]['file'];
                    ?>
                    <!-- Before/After Slider HTML (Synced with Main Page) -->
                    <style>
                        /* 메인 페이지와 동일한 스타일 적용 */
                        .view-comparison-wrapper {
                            position: relative;
                            width: 100%;
                            aspect-ratio: 4 / 3;
                            overflow: hidden;
                            cursor: col-resize;
                            background: #f0f0f0;
                            border-radius: 16px;
                            /* 메인과 동일한 라운드 */
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
                            border-right: 2px solid white;
                            background: #f0f0f0;
                        }

                        .view-after-image {
                            width: 100% !important;
                            max-width: none !important;
                        }

                        /* 핸들 스타일 메인과 통일 (주황색 원형 버튼) */
                        .view-slider-handle {
                            position: absolute;
                            top: 0;
                            left: 50%;
                            width: 4px;
                            height: 100%;
                            background: white;
                            /* 흰색 선 */
                            z-index: 3;
                            transform: translateX(-50%);
                            pointer-events: none;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }

                        .view-slider-handle::before {
                            content: '';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            width: 56px;
                            height: 56px;
                            background: #f97316;
                            /* 메인 테마 주황색 */
                            border-radius: 50%;
                            transform: translate(-50%, -50%);
                            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
                            border: 4px solid white;
                            /* 흰색 테두리 추가 */
                        }

                        .view-slider-handle::after {
                            content: '⟷';
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            font-size: 24px;
                            color: white;
                            font-weight: bold;
                        }

                        /* 라벨 스타일 메인과 통일 (빨강/초록) */
                        .view-label {
                            position: absolute;
                            top: 20px;
                            padding: 6px 16px;
                            color: white;
                            border-radius: 8px;
                            font-weight: 800;
                            z-index: 4;
                            font-size: 14px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
                        }

                        .label-b {
                            left: 20px;
                            background: #ef4444;
                        }

                        /* Before: Red */
                        .label-a {
                            right: 20px;
                            background: #10b981;
                        }

                        /* After: Green */

                        @media (min-width: 1024px) {
                            .view-comparison-wrapper {
                                aspect-ratio: 16 / 10;
                            }
                        }
                    </style>

                    <div class="view-comparison-wrapper" id="viewBeforeAfter">
                        <!-- Background: Before Image (bf_no=1) -->
                        <img src="<?php echo $before_img_src; ?>" class="view-before-image" alt="Before">
                        <span class="view-label label-b">Before</span>

                        <!-- Foreground: After Image (bf_no=0) - Clipped -->
                        <div class="view-after-container">
                            <img src="<?php echo $after_img_src; ?>" class="view-after-image" alt="After">
                        </div>
                        <span class="view-label label-a">After</span>

                        <!-- Handle -->
                        <div class="view-slider-handle"></div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const wrapper = document.getElementById('viewBeforeAfter');
                            if (!wrapper) return;

                            const afterContainer = wrapper.querySelector('.view-after-container');
                            const afterImg = wrapper.querySelector('.view-after-image');
                            const handle = wrapper.querySelector('.view-slider-handle');
                            let isActive = false;

                            function syncWidth() {
                                const w = wrapper.offsetWidth;
                                afterImg.style.width = w + 'px';
                            }

                            window.addEventListener('resize', syncWidth);
                            syncWidth();

                            function updateSlider(clientX) {
                                const rect = wrapper.getBoundingClientRect();
                                const x = clientX - rect.left;
                                let percentage = (x / rect.width) * 100;
                                percentage = Math.max(0, Math.min(100, percentage));

                                afterContainer.style.width = percentage + '%';
                                handle.style.left = percentage + '%';
                            }

                            wrapper.addEventListener('mouseenter', () => isActive = true);
                            wrapper.addEventListener('mouseleave', () => isActive = false);
                            wrapper.addEventListener('mousemove', (e) => {
                                if (!isActive) return;
                                updateSlider(e.clientX);
                            });

                            wrapper.addEventListener('touchmove', (e) => {
                                const touch = e.touches[0];
                                updateSlider(touch.clientX);
                            });

                            wrapper.addEventListener('click', (e) => {
                                updateSlider(e.clientX);
                            });
                        });
                    </script>

                <?php
                } else {
                    // 기존 로직: 파일이 없거나 1개일 때
                    // ... (이 부분은 이전과 동일하므로 생략 가능하나 전체 교체를 위해 유지)
                    $v_img_count = count($view['file']);
                    if ($v_img_count && isset($view['file'][0]) && $view['file'][0]['view']) {
                        echo get_view_thumbnail($view['file'][0]['view']);
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
                <div class="flex gap-3">
                    <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&sca=견적문의&subject=<?php echo urlencode('포트폴리오: ' . $view['wr_subject']); ?>"
                        class="flex-1 py-4 bg-orange-500 text-white rounded-xl font-bold text-lg hover:bg-orange-600 transition-colors shadow-lg text-center flex items-center justify-center">
                        이 컨셉으로 견적 받기
                    </a>
                </div>

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

    <div class="border-t-2 border-gray-900 pt-16 mt-8">
        <h3 class="text-2xl font-extrabold text-gray-900 mb-10 text-center">상품 상세 정보</h3>

        <div id="bo_v_con" class="prose max-w-none text-gray-800 leading-loose mx-auto">
            <?php echo get_view_thumbnail($view['content']); ?>
        </div>
    </div>

    <?php include_once(G5_BBS_PATH . '/view_comment.php'); ?>

</div>

<script>
    $(function () {
        // 이미지 리사이즈
        $("#bo_v_con").viewimageresize();
    });
</script>
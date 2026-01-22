<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// 스타일시트 연결
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        orange: '#F97316',
                        dark: '#1F2937',
                    }
                }
            }
        }
    }
</script>
<style>
    @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');

    body {
        font-family: 'Pretendard', sans-serif;
    }

    /* 상세 본문 이미지 스타일 */
    #bo_v_con img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 py-16">

    <!-- Split Layout Container -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

        <!-- Left Column: Image Area -->
        <div class="w-full">
            <?php
            // 첫 번째 첨부파일 이미지 가져오기
            $v_img_count = count($view['file']);
            $first_img_url = '';

            if (isset($view['file']) && is_array($view['file'])) {
                foreach ($view['file'] as $file) {
                    if (isset($file['view']) && $file['view']) { // 이미지만
                        $first_img_url = $file['path'] . '/' . $file['file'];
                        break; // 첫 번째만 가져오기
                    }
                }
            }

            if ($first_img_url) {
                // 이미지가 있을 때
                echo '<div class="w-full h-[500px] rounded-2xl overflow-hidden shadow-sm border border-gray-100 bg-gray-50 flex items-center justify-center">';
                echo '<img src="' . $first_img_url . '" class="w-full h-full object-cover" alt="결제 내역 이미지">';
                echo '</div>';
            } else {
                // 이미지가 없을 때 (Fallback)
                echo '<div class="w-full h-[500px] rounded-2xl bg-gray-100 flex flex-col items-center justify-center text-gray-400 border border-gray-200">';
                echo '<i class="fa fa-file-invoice mb-4 text-6xl opacity-30"></i>';
                echo '<p class="text-lg font-bold">결제 내역 이미지</p>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Right Column: Info Area -->
        <div class="flex flex-col justify-center">

            <div class="mb-4 pb-4 border-b border-gray-200">
                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold mb-3">결제
                    정보</span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight">
                    <?php echo cut_str(get_text($view['wr_subject']), 255); ?>
                </h1>
            </div>

            <!-- Price Information Table -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-100">
                <?php
                // 가격 계산 로직 (wr_1이 합계 금액이라고 가정)
                $total_price_val = isset($view['wr_1']) ? (int) preg_replace('/[^0-9]/', '', $view['wr_1']) : 0;

                // 공급가액, 부가세 역산 (합계 / 1.1)
                $supply_price = $total_price_val > 0 ? round($total_price_val / 1.1) : 0;
                $vat_price = $total_price_val - $supply_price;
                ?>

                <div class="flex justify-between items-center mb-3 text-sm text-gray-500">
                    <span>공급가액</span>
                    <span class="font-medium"><?php echo number_format($supply_price); ?> 원</span>
                </div>
                <div
                    class="flex justify-between items-center mb-4 text-sm text-gray-500 pb-4 border-b border-gray-200 border-dashed">
                    <span>부가세 (VAT)</span>
                    <span class="font-medium"><?php echo number_format($vat_price); ?> 원</span>
                </div>

                <div class="flex justify-between items-end">
                    <span class="text-gray-900 font-bold mb-1">최종 결제 금액</span>
                    <div class="text-right">
                        <span
                            class="text-4xl font-black text-blue-600 block leading-none"><?php echo number_format($total_price_val); ?></span>
                        <span class="text-sm font-bold text-gray-400">원 (VAT 포함)</span>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <button type="button" onclick="alert('PG사 결제창이 연동될 예정입니다.');"
                class="w-full py-5 bg-[#1e293b] text-white rounded-xl font-bold text-xl hover:bg-black transition-all hover:shadow-xl hover:-translate-y-1 block mb-4">
                <i class="fa fa-credit-card mr-2 text-yellow-400"></i> 신용카드 결제하기
            </button>

            <p class="text-center text-xs text-gray-400">
                <i class="fa fa-lock"></i> 안전한 결제를 위해 보안 연결(SSL)을 사용합니다.
            </p>

        </div>
    </div>


    <!-- Bottom Content Area -->
    <?php if ($view['content']) { ?>
        <div class="pt-12 border-t border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <span class="w-1 h-8 bg-black mr-3 inline-block"></span>
                상세 견적 및 안내
            </h3>
            <div id="bo_v_con" class="prose max-w-none text-gray-800 leading-relaxed bg-white">
                <?php echo get_view_thumbnail($view['content']); ?>
            </div>
        </div>
    <?php } ?>

    <!-- Control Buttons -->
    <div class="flex justify-center gap-3 mt-16 pt-8 border-t border-gray-100">
        <a href="<?php echo $list_href ?>"
            class="px-6 py-2.5 bg-gray-100 rounded-lg text-gray-700 font-bold hover:bg-gray-200 transition-colors">
            목록으로
        </a>
        <?php if ($update_href) { ?>
            <a href="<?php echo $update_href ?>"
                class="px-6 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-600 font-bold hover:bg-gray-50 transition-colors">
                수정
            </a>
        <?php } ?>
        <?php if ($delete_href) { ?>
            <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;"
                class="px-6 py-2.5 bg-white border border-red-200 rounded-lg text-red-500 font-bold hover:bg-red-50 transition-colors">
                삭제
            </a>
        <?php } ?>
    </div>

</div>

<script>
    $(function () {
        // 이미지 리사이즈
        $("#bo_v_con").viewimageresize();
    });
</script>
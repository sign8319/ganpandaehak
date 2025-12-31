<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: { orange: '#F97316', dark: '#1F2937' }
                }
            }
        }
    }
</script>

<div class="w-full px-4 py-8 pb-20 bg-white min-h-screen">

    <div class="text-center mb-8">
        <p class="text-sm text-gray-400 font-bold mb-1">결제 상세 정보</p>
        <h1 class="text-2xl font-black text-gray-900 leading-tight">
            <?php echo cut_str(get_text($view['wr_subject']), 255); ?>
        </h1>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-8">
        <!-- Amount -->
        <div class="bg-brand-dark p-8 text-center text-white">
            <p class="text-gray-400 mb-1 text-xs font-medium">최종 결제 금액</p>
            <?php
            $price_val = preg_replace('/[^0-9]/', '', $view['wr_1']);
            $price = $price_val ? number_format((int) $price_val) : '미지정';
            ?>
            <div class="flex items-center justify-center gap-1">
                <span class="text-4xl font-black text-brand-orange"><?php echo $price; ?></span>
                <span class="text-xl font-bold text-gray-300">원</span>
            </div>
        </div>

        <!-- Buttons -->
        <div class="p-6">
            <button type="button" onclick="alert('모바일 결제 연동이 필요합니다.');"
                class="w-full py-4 bg-blue-600 text-white rounded-xl font-bold text-lg hover:bg-blue-700 transition-colors shadow-md mb-6 flex items-center justify-center gap-2">
                <i class="fa fa-credit-card"></i> 카드 결제
            </button>

            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                <h3 class="font-bold text-gray-900 mb-3 text-sm flex items-center">
                    <i class="fa fa-building-columns text-gray-400 mr-2"></i> 입금 계좌 안내
                </h3>
                <div class="space-y-2 text-xs text-gray-600">
                    <div class="flex justify-between border-b border-gray-200 pb-2">
                        <span>은행명</span>
                        <strong class="text-gray-800">국민은행</strong>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-2">
                        <span>계좌번호</span>
                        <strong class="text-gray-800">123-4567-8901</strong>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span>예금주</span>
                        <strong class="text-gray-800">간판대학</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <?php if ($view['content']) { ?>
        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-900 mb-3 border-b-2 border-gray-900 pb-2">상세 내용</h3>
            <div id="bo_v_con" class="prose max-w-none text-sm text-gray-800 leading-relaxed">
                <?php echo get_view_thumbnail($view['content']); ?>
            </div>
        </div>
    <?php } ?>

    <!-- Control Buttons -->
    <div class="grid grid-cols-3 gap-2 border-t border-gray-100 pt-6">
        <a href="<?php echo $list_href ?>"
            class="py-3 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold text-center">
            목록
        </a>
        <?php if ($update_href) { ?>
            <a href="<?php echo $update_href ?>"
                class="py-3 bg-white border border-gray-300 text-gray-600 rounded-lg text-sm font-bold text-center">
                수정
            </a>
        <?php } ?>
        <?php if ($delete_href) { ?>
            <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;"
                class="py-3 bg-white border border-red-200 text-red-500 rounded-lg text-sm font-bold text-center">
                삭제
            </a>
        <?php } ?>
    </div>
</div>

<script>
    $(function () {
        $("#bo_v_con").viewimageresize();
    });
</script>
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

    /* 본문 이미지 자동 리사이징 */
    #bo_v_con img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px;
    }
</style>

<div class="w-full max-w-[1600px] mx-auto px-4 py-12">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">

        <div class="w-full">
            <div class="rounded-2xl overflow-hidden bg-gray-50 border border-gray-200">
                <?php
                // 첫 번째 첨부파일 이미지 출력
                $v_img_count = count($view['file']);
                if ($v_img_count && $view['file'][0]['view']) {
                    echo get_view_thumbnail($view['file'][0]['view']);
                } else {
                    // 이미지가 없을 경우
                    echo '<img src="https://placehold.co/800x800/f3f4f6/9ca3af?text=No+Image" class="w-full h-full object-cover">';
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
                    <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&sca=견적문의"
                        class="flex-1 py-4 bg-brand-orange text-white rounded-xl font-bold text-lg hover:bg-orange-600 transition-colors shadow-lg text-center flex items-center justify-center">
                        견적 문의
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
                                class="block w-full py-2.5 bg-gray-800 text-white rounded-lg font-bold text-sm hover:bg-gray-700 transition-colors">
                                로그인하고 금액 확인하기
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
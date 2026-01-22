<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
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
                    brand: { orange: '#F97316', dark: '#1F2937' }
                }
            }
        }
    }
</script>

<div class="w-full px-4 py-8 bg-gray-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 mb-2">결제 센터</h1>
        <p class="text-sm text-gray-500">고객전용 결제 페이지입니다.</p>
    </div>

    <div class="space-y-4">
        <?php
        for ($i = 0; $i < count($list); $i++) {
            $is_lock = $list[$i]['is_secret'];
            $lock_icon = $is_lock ? '<i class="fa fa-lock text-brand-orange ml-1"></i>' : '';
            ?>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100"
                onclick="location.href='<?php echo $list[$i]['href'] ?>'">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-xs text-gray-400"><?php echo $list[$i]['datetime2'] ?></span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded">결제대기</span>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-1">
                    <?php echo $list[$i]['subject'] ?>     <?php echo $lock_icon ?>
                </h3>
            </div>
        <?php } ?>

        <?php if (count($list) == 0) { ?>
            <div class="py-20 text-center text-gray-400">
                <i class="fa fa-folder-open text-4xl mb-3 opacity-30"></i>
                <p>등록된 결제 건이 없습니다.</p>
            </div>
        <?php } ?>
    </div>

    <div class="mt-8 flex justify-center">
        <?php echo $write_pages; ?>
    </div>

    <?php if ($is_admin) { ?>
        <div class="fixed bottom-6 right-6">
            <a href="<?php echo $write_href ?>"
                class="flex items-center justify-center w-14 h-14 bg-gray-900 text-white rounded-full shadow-xl hover:bg-black transition-colors">
                <i class="fa fa-pencil text-xl"></i>
            </a>
        </div>
    <?php } ?>
</div>
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
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
    
    .img-zoom { transition: transform 0.5s ease; }
    .product-card:hover .img-zoom { transform: scale(1.05); }
    
    /* 사이드바 메뉴 활성화 스타일 */
    .cate-link.active { background-color: #F97316; color: white; font-weight: bold; border-color: #F97316; }
    .cate-link:hover:not(.active) { background-color: #FFF7ED; color: #F97316; }
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
                            <span>전체 상품 보기</span>
                            <i class="fa fa-chevron-right text-xs opacity-50 hidden lg:block"></i>
                        </div>
                    </a>

                    <?php
                    $categories = explode('|', $board['bo_category_list']);
                    foreach ($categories as $cat) {
                        $cat = trim($cat);
                        if (!$cat) continue;
                        $isActive = ($sca == $cat) ? 'active shadow-md' : 'bg-white';
                        $url = get_pretty_url($bo_table, '', 'sca='.urlencode($cat));
                        
                        echo '<a href="'.$url.'" class="cate-link flex-shrink-0 w-auto lg:w-full px-5 py-3.5 rounded-lg border border-gray-200 text-gray-600 transition-all text-sm md:text-base '.$isActive.'">';
                        echo '<div class="flex justify-between items-center w-full">';
                        echo '<span>'.$cat.'</span>';
                        echo '<i class="fa fa-chevron-right text-xs opacity-50 hidden lg:block"></i>';
                        echo '</div>';
                        echo '</a>';
                    }
                    ?>
                </nav>

                <div class="mt-10 hidden lg:block p-6 bg-gray-900 rounded-2xl text-white text-center">
                    <p class="text-sm text-gray-400 mb-2">도움이 필요하신가요?</p>
                    <h3 class="text-xl font-bold mb-4">1:1 맞춤 상담</h3>
                    <a href="/write.php?bo_table=consult" class="inline-block w-full py-3 bg-brand-orange rounded-lg font-bold hover:bg-orange-600 transition-colors">
                        견적 문의하기
                    </a>
                </div>
            </div>
        </aside>


        <main class="flex-1 min-w-0">
            
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">
                        <?php echo $sca ? $sca : '전체 상품'; ?>
                    </h1>
                    <p class="text-gray-500 mt-2">
                        총 <strong class="text-brand-orange"><?php echo number_format($total_count) ?></strong>개의 상품이 있습니다.
                    </p>
                </div>
                <?php if ($write_href) { ?>
                <a href="<?php echo $write_href ?>" class="px-5 py-2.5 bg-gray-900 text-white rounded-lg text-sm font-bold hover:bg-black transition-colors flex items-center gap-2">
                    <i class="fa fa-pencil"></i> 상품 등록
                </a>
                <?php } ?>
            </div>

            <?php if (!$sca) { // 전체 목록일 때만 배너 노출 ?>
            <div class="relative w-full h-64 rounded-3xl overflow-hidden mb-12 bg-gray-800 flex items-center">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/80 to-transparent z-10"></div>
                <img src="https://images.unsplash.com/photo-1542206395-9feb3edaa68d?q=80&w=2000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-50">
                
                <div class="relative z-20 px-10 md:px-16">
                    <span class="text-brand-orange font-bold tracking-wider text-sm mb-2 block">NEW COLLECTION</span>
                    <h2 class="text-3xl md:text-4xl font-black text-white leading-tight mb-6">
                        공간의 가치를 높이는<br>프리미엄 사이니지
                    </h2>
                </div>
            </div>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-x-6 gap-y-10">
                <?php
                for ($i = 0; $i < count($list); $i++) {
                    $row = $list[$i];
                    // 썸네일
                    $thumb = get_list_thumbnail($board['bo_table'], $row['wr_id'], 500, 500, false, true);
                    $img_src = $thumb['src'] ? $thumb['src'] : 'https://placehold.co/500x500/f3f4f6/9ca3af?text=No+Image';
                    
                    // 가격 처리 (오류 방지)
                    $price_val = preg_replace('/[^0-9]/', '', $row['wr_1']);
                    $price = $price_val ? number_format((int)$price_val) : '상담문의';
                    $price_unit = ($price_val) ? '원' : '';
                    
                  // [수정] 내가 선택한 뱃지 보여주기
$badges = [];
$badge_val = isset($row['wr_2']) ? $row['wr_2'] : '';

if ($badge_val == 'best') {
    $badges[] = ['bg'=>'bg-red-600', 'text'=>'BEST'];
} else if ($badge_val == 'new') {
    $badges[] = ['bg'=>'bg-green-600', 'text'=>'NEW'];
} else if ($badge_val == 'sale') {
    $badges[] = ['bg'=>'bg-brand-orange', 'text'=>'SALE'];
} else if ($badge_val == 'hit') {
    $badges[] = ['bg'=>'bg-purple-600', 'text'=>'HIT'];
}
                ?>
                <a href="<?php echo $row['href'] ?>" class="group block product-card">
                    <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="relative aspect-square overflow-hidden bg-gray-50">
                            <img src="<?php echo $img_src ?>" class="w-full h-full object-cover img-zoom">
                            
                            <div class="absolute top-3 left-3 flex flex-col gap-1">
                                <?php foreach($badges as $badge) { ?>
                                <span class="px-2.5 py-1 <?php echo $badge['bg'] ?> text-white text-[10px] font-bold rounded shadow-sm">
                                    <?php echo $badge['text'] ?>
                                </span>
                                <?php } ?>
                            </div>
                            
                            <div class="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity translate-y-2 group-hover:translate-y-0 duration-300">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg text-gray-800 hover:text-red-500 transition-colors">
                                    <i class="fa fa-heart"></i>
                                </div>
                                <div class="w-10 h-10 bg-brand-orange rounded-full flex items-center justify-center shadow-lg text-white hover:bg-orange-700 transition-colors">
                                    <i class="fa fa-search"></i>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="text-xs text-gray-400 mb-1"><?php echo $row['ca_name'] ? $row['ca_name'] : 'Item' ?></div>
                            <h3 class="text-lg font-bold text-gray-900 truncate mb-2 group-hover:text-brand-orange transition-colors">
                                <?php echo $row['subject'] ?>
                            </h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-xl font-black text-gray-900"><?php echo $price ?></span>
                                <span class="text-sm text-gray-500 font-medium"><?php echo $price_unit ?></span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php } ?>

                <?php if (count($list) == 0) { ?>
                <div class="col-span-full py-32 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="text-4xl mb-4">📦</div>
                    <p class="text-gray-500 text-lg">등록된 상품이 없습니다.</p>
                </div>
                <?php } ?>
            </div>

            <div class="mt-16 flex justify-center">
                <?php echo $write_pages; ?>
            </div>
            
        </main>
    </div>
</div>
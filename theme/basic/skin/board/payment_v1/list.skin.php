<?php
if (!defined('_GNUBOARD_')) exit;
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');
    body { font-family: 'Pretendard', sans-serif; }
</style>

<div class="w-full max-w-6xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">결제 센터</h1>
        <p class="text-gray-500">고객님 전용 결제 페이지입니다.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="hidden md:table-cell py-4 px-6 text-sm font-bold text-gray-600 w-20 text-center">번호</th>
                    <th class="py-4 px-6 text-sm font-bold text-gray-600">고객명 (결제 건명)</th>
                    <th class="py-4 px-6 text-sm font-bold text-gray-600 text-right">결제 금액</th>
                    <th class="hidden md:table-cell py-4 px-6 text-sm font-bold text-gray-600 w-32 text-center">날짜</th>
                    <th class="py-4 px-6 text-sm font-bold text-gray-600 w-24 text-center">상태</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                for ($i = 0; $i < count($list); $i++) {
                    // 금액 정보 가져오기 (wr_1)
                    $price = $list[$i]['wr_1'];
                    if(!$price) $price = "0";
                ?>
                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="location.href='<?php echo $list[$i]['href'] ?>'">
                        <td class="hidden md:table-cell py-5 px-6 text-center text-gray-500 text-sm">
                            <?php echo $list[$i]['num'] ?>
                        </td>
                        <td class="py-5 px-6">
                            <span class="text-gray-900 font-bold text-lg hover:text-orange-600">
                                <?php echo $list[$i]['subject'] ?>
                            </span>
                        </td>
                        <td class="py-5 px-6 text-right">
                            <span class="text-lg font-black text-gray-900"><?php echo $price ?></span>
                            <span class="text-sm text-gray-500">원</span>
                        </td>
                        <td class="hidden md:table-cell py-5 px-6 text-center text-gray-400 text-sm">
                            <?php echo date("Y.m.d", strtotime($list[$i]['wr_datetime'])) ?>
                        </td>
                        <td class="py-5 px-6 text-center">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full">대기</span>
                        </td>
                    </tr>
                <?php } ?>

                <?php if (count($list) == 0) { ?>
                    <tr>
                        <td colspan="5" class="py-20 text-center text-gray-500">
                            현재 등록된 결제 요청 건이 없습니다.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php if ($is_admin) { ?>
        <div class="mt-8 text-right">
            <a href="<?php echo $write_href ?>" class="inline-block px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition-colors">
                + 결제창 생성
            </a>
        </div>
    <?php } ?>
</div>
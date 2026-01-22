<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인 후 이용 가능합니다.', G5_BBS_URL . '/login.php');
}

$g5['title'] = '쿠폰함';
include_once(G5_THEME_PATH . '/head.php');

// 쿠폰 테이블 생성 시도
sql_query(" CREATE TABLE IF NOT EXISTS `g5_coupon` (
    `cp_id` varchar(255) NOT NULL DEFAULT '',
    `cp_subject` varchar(255) NOT NULL DEFAULT '',
    `cp_method` tinyint(4) NOT NULL DEFAULT '0', 
    `cp_price` int(11) NOT NULL DEFAULT '0',
    `cp_start` date NOT NULL DEFAULT '0000-00-00',
    `cp_end` date NOT NULL DEFAULT '0000-00-00',
    PRIMARY KEY (`cp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

sql_query(" CREATE TABLE IF NOT EXISTS `g5_coupon_log` (
    `cl_id` int(11) NOT NULL AUTO_INCREMENT,
    `cp_id` varchar(255) NOT NULL DEFAULT '',
    `mb_id` varchar(255) NOT NULL DEFAULT '',
    `cl_use` tinyint(4) NOT NULL DEFAULT '0',
    `cl_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`cl_id`),
    KEY `mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

// 보유 쿠폰 목록
$coupon_list = array();
$sql = " select c.*, cl.cl_use, cl.cl_datetime 
           from g5_coupon_log cl
           join g5_coupon c on cl.cp_id = c.cp_id
          where cl.mb_id = '{$member['mb_id']}' 
            and cl.cl_use = 0
          order by c.cp_end asc ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $coupon_list[] = $row;
}
?>

<div class="max-w-[800px] mx-auto py-10 px-4 sm:px-6">
    <!-- Header -->
    <div class="mb-10 text-center sm:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">쿠폰함</h2>
        <p class="mt-2 text-slate-500">할인 혜택을 놓치지 마세요.</p>
    </div>

    <!-- Registration -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-10 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-bl-full -mr-10 -mt-10 opacity-50"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-slate-900 mb-4">쿠폰 코드 등록</h3>
            <form action="./coupon_update.php" method="post" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="cp_code" required
                    class="flex-1 px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900"
                    placeholder="쿠폰 코드를 입력해 주세요">
                <button type="submit"
                    class="px-8 py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-orange-600 transition-all shadow-lg hover:shadow-orange-100">등록하기</button>
            </form>
        </div>
    </div>

    <!-- Coupon List -->
    <div class="space-y-6">
        <h3 class="text-xl font-bold text-slate-900 px-2 flex items-center justify-between">
            보유 중인 쿠폰
            <span class="text-sm font-medium text-slate-400">
                <?php echo count($coupon_list); ?>장
            </span>
        </h3>

        <?php if (count($coupon_list) > 0): ?>
            <div class="grid grid-cols-1 gap-4">
                <?php foreach ($coupon_list as $cp):
                    $is_expired = $cp['cp_end'] < date('Y-m-d');
                    ?>
                    <div
                        class="bg-white rounded-3xl shadow-sm border-2 border-dashed border-slate-200 p-8 relative flex flex-col sm:flex-row items-center justify-between gap-6 group hover:border-orange-200 transition-all">
                        <!-- Left Hole Decoration -->
                        <div
                            class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-50 rounded-full border-r-2 border-dashed border-slate-200">
                        </div>
                        <!-- Right Hole Decoration -->
                        <div
                            class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-50 rounded-full border-l-2 border-dashed border-slate-200">
                        </div>

                        <div class="text-center sm:text-left">
                            <h4 class="text-xl font-black text-slate-900 mb-1 group-hover:text-orange-600 transition-colors">
                                <?php echo $cp['cp_subject']; ?>
                            </h4>
                            <p class="text-sm font-bold text-slate-400 italic">
                                <?php echo $cp['cp_start']; ?> ~
                                <?php echo $cp['cp_end']; ?>
                            </p>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-center sm:text-right">
                                <span class="text-3xl font-black text-slate-900">
                                    <?php echo $cp['cp_method'] == 0 ? '₩' . number_format($cp['cp_price']) : $cp['cp_price'] . '%'; ?>
                                </span>
                                <span class="text-sm font-bold text-slate-400 block -mt-1">할인</span>
                            </div>
                            <button
                                class="px-6 py-3 bg-slate-50 text-slate-400 font-bold rounded-xl hover:bg-slate-100 transition-all text-sm">사용처
                                확인</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div
                class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-[40px] py-16 flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-white rounded-3xl shadow-sm flex items-center justify-center mb-6 text-slate-200">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                        </path>
                    </svg>
                </div>
                <p class="text-slate-400 font-black text-lg">사용할 수 있는 쿠폰이 없습니다</p>
                <p class="text-slate-400 text-sm mt-1 font-medium italic">이벤트와 프로모션에 참여해 쿠폰을 받아보세요!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
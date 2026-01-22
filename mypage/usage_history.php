<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인 후 이용 가능합니다.', G5_BBS_URL . '/login.php');
}

$g5['title'] = '이용 내역';
include_once(G5_THEME_PATH . '/head.php');

// 이용 내역 조회 (g5_usage_history 테이블 없으면 생성 시도)
sql_query(" CREATE TABLE IF NOT EXISTS `g5_usage_history` (
    `uh_id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(255) NOT NULL DEFAULT '',
    `quote_id` int(11) NOT NULL DEFAULT '0',
    `uh_subject` varchar(255) NOT NULL DEFAULT '',
    `uh_status` varchar(20) NOT NULL DEFAULT '디자인',
    `uh_amount` int(11) NOT NULL DEFAULT '0',
    `uh_photos` text NOT NULL,
    `uh_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`uh_id`),
    KEY `mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

$history_list = array();
$sql = " select * from g5_usage_history where mb_id = '{$member['mb_id']}' order by uh_datetime desc ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $history_list[] = $row;
}
?>

<div class="max-w-[1000px] mx-auto py-10 px-4 sm:px-6">
    <!-- Header -->
    <div class="mb-12 text-center">
        <h2 class="text-4xl font-black text-slate-900 tracking-tight">이용 내역</h2>
        <p class="mt-3 text-slate-500 font-medium text-lg">간판이 완성되어가는 과정을 한눈에 확인하세요.</p>
    </div>

    <?php if (count($history_list) > 0): ?>
        <div class="grid grid-cols-1 gap-8">
            <?php foreach ($history_list as $uh):
                $status_steps = array('디자인', '제작', '시공', '완료');
                $current_idx = array_search($uh['uh_status'], $status_steps);
                ?>
                <div
                    class="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-500 group">
                    <div class="p-8 sm:p-12">
                        <!-- Info Header -->
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
                            <div>
                                <span
                                    class="text-xs font-black text-orange-600 bg-orange-50 px-3 py-1 rounded-full uppercase tracking-widest mb-2 block">PROJECT</span>
                                <h3 class="text-2xl font-black text-slate-900">
                                    <?php echo $uh['uh_subject']; ?>
                                </h3>
                                <p class="text-slate-400 text-sm mt-1 font-medium italic">
                                    <?php echo $uh['uh_datetime']; ?> 주문
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="text-xs font-bold text-slate-400 block mb-1">결제 금액</span>
                                <span class="text-2xl font-black text-slate-900 tracking-tighter">₩
                                    <?php echo number_format($uh['uh_amount']); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Process Stepper -->
                        <div class="relative mb-12 px-4 sm:px-0">
                            <!-- Progress Line -->
                            <div
                                class="absolute top-1/2 left-0 w-full h-1.5 bg-slate-100 -translate-y-1/2 rounded-full hidden sm:block">
                            </div>
                            <div class="absolute top-1/2 left-0 h-1.5 bg-orange-500 -translate-y-1/2 rounded-full transition-all duration-1000 hidden sm:block"
                                style="width: <?php echo ($current_idx / (count($status_steps) - 1)) * 100; ?>%"></div>

                            <div class="flex flex-col sm:flex-row justify-between items-center relative z-10 gap-6 sm:gap-0">
                                <?php foreach ($status_steps as $idx => $step):
                                    $is_active = $idx <= $current_idx;
                                    $is_current = $idx == $current_idx;
                                    ?>
                                    <div class="flex flex-row sm:flex-col items-center gap-4 sm:gap-4 w-full sm:w-auto">
                                        <div
                                            class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 <?php echo $is_active ? 'bg-orange-500 text-white shadow-lg shadow-orange-100 scale-110' : 'bg-white text-slate-300 border-2 border-slate-100'; ?>">
                                            <?php if ($idx == 0): ?>
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            <?php elseif ($idx == 1): ?>
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                            <?php elseif ($idx == 2): ?>
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex flex-col sm:items-center">
                                            <span
                                                class="text-sm font-black <?php echo $is_active ? 'text-slate-900' : 'text-slate-300'; ?> transition-colors duration-500">
                                                <?php echo $step; ?>
                                            </span>
                                            <?php if ($is_current): ?>
                                                <span class="text-[10px] font-black text-orange-500 animate-pulse mt-1">진행 중</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Action/Photo Area -->
                        <?php if ($uh['uh_status'] == '완료'): ?>
                            <div
                                class="mt-8 pt-8 border-t border-slate-50 flex flex-col sm:flex-row items-center justify-between gap-6">
                                <p class="text-slate-500 font-medium flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>시공이 완벽하게 마무리되었습니다.
                                </p>
                                <button onclick="viewPhotos('<?php echo $uh['uh_id']; ?>')"
                                    class="w-full sm:w-auto px-8 py-4 bg-slate-900 text-white font-black rounded-2xl hover:bg-orange-600 transition-all shadow-xl hover:shadow-orange-100 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    시공 완료 사진 확인하기
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="bg-slate-50 rounded-3xl p-6 text-center">
                                <p class="text-slate-400 font-bold text-sm italic">"꼼꼼하게 진행하고 있습니다. 조금만 기다려주세요!"</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div
            class="bg-white rounded-[50px] shadow-sm border border-slate-100 py-32 flex flex-col items-center justify-center px-10">
            <div class="w-32 h-32 bg-orange-50 rounded-[40px] flex items-center justify-center mb-8 animate-bounce">
                <svg class="w-16 h-16 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
            </div>
            <h3 class="text-3xl font-black text-slate-900 mb-2">아직 이용 내역이 없어요</h3>
            <p class="text-slate-400 font-medium text-lg mb-10 text-center">간판대학의 전문가들이 고객님의 공간을 <br
                    class="hidden sm:block">바꿀 준비가 되어있습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=consult"
                class="px-10 py-5 bg-orange-500 text-white font-black rounded-3xl hover:bg-orange-600 transition-all shadow-2xl shadow-orange-100 text-lg">
                지금 간판 견적 받기
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    function viewPhotos(id) {
        // 갤러리 로직 (추후 구현)
        alert('이미지 갤러리 기능을 준비 중입니다.');
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인 후 이용 가능합니다.', G5_BBS_URL . '/login.php');
}

$g5['title'] = '마이페이지';
include_once(G5_THEME_PATH . '/head.php');

// DB 필드 체크 (CLI에서 실패했으므로 여기서 직접 처리 시도 - 에러 무시)
sql_query(" ALTER TABLE {$g5['member_table']} ADD COLUMN mb_biz_name VARCHAR(255) DEFAULT '' AFTER mb_mailling ", false);
sql_query(" ALTER TABLE {$g5['member_table']} ADD COLUMN mb_biz_num VARCHAR(255) DEFAULT '' AFTER mb_biz_name ", false);
sql_query(" ALTER TABLE {$g5['member_table']} ADD COLUMN mb_biz_rep VARCHAR(255) DEFAULT '' AFTER mb_biz_num ", false);
sql_query(" ALTER TABLE {$g5['member_table']} ADD COLUMN mb_biz_addr VARCHAR(255) DEFAULT '' AFTER mb_biz_rep ", false);
sql_query(" ALTER TABLE {$g5['member_table']} ADD COLUMN mb_biz_type VARCHAR(255) DEFAULT '' AFTER mb_biz_addr ", false);

// 시공 주소지 테이블 생성 (없으면 생성)
sql_query(" CREATE TABLE IF NOT EXISTS `g5_member_address` (
    `ad_id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(255) NOT NULL DEFAULT '',
    `ad_subject` varchar(255) NOT NULL DEFAULT '',
    `ad_name` varchar(255) NOT NULL DEFAULT '',
    `ad_tel` varchar(255) NOT NULL DEFAULT '',
    `ad_hp` varchar(255) NOT NULL DEFAULT '',
    `ad_zip` varchar(10) NOT NULL DEFAULT '',
    `ad_addr1` varchar(255) NOT NULL DEFAULT '',
    `ad_addr2` varchar(255) NOT NULL DEFAULT '',
    `ad_addr3` varchar(255) NOT NULL DEFAULT '',
    `ad_jibeon` varchar(255) NOT NULL DEFAULT '',
    `ad_default` tinyint(4) NOT NULL DEFAULT '0',
    PRIMARY KEY (`ad_id`),
    KEY `mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ", false);

// 사용자 주소록 가져오기
$ad_list = array();
$sql = " select * from g5_member_address where mb_id = '{$member['mb_id']}' order by ad_default desc, ad_id desc ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $ad_list[] = $row;
}
?>

<div class="max-w-[800px] mx-auto py-10 px-4 sm:px-6">
    <!-- Header -->
    <div class="mb-10 text-center sm:text-left">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">마이페이지</h2>
        <p class="mt-2 text-slate-500">회원 정보와 사업자 정보를 관리하세요.</p>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="p-8">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900">
                        <?php echo $member['mb_nick']; ?>님
                    </h3>
                    <p class="text-slate-500 font-medium">
                        <?php echo $member['mb_email']; ?>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-slate-50 p-5 rounded-2xl">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">회원 등급</span>
                    <span class="text-slate-900 font-bold">일반 회원 (레벨
                        <?php echo $member['mb_level']; ?>)
                    </span>
                </div>
                <div class="bg-slate-50 p-5 rounded-2xl">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">가입일</span>
                    <span class="text-slate-900 font-bold">
                        <?php echo substr($member['mb_datetime'], 0, 10); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-4 flex justify-end">
            <?php if ($member['mb_social_id']): ?>
                <a href="<?php echo G5_BBS_URL; ?>/register_form.php?w=u&mb_id=<?php echo $member['mb_id']; ?>"
                    class="text-sm font-bold text-slate-600 hover:text-orange-600 transition-colors">회원정보 수정</a>
            <?php else: ?>
                <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=register_form.php"
                    class="text-sm font-bold text-slate-600 hover:text-orange-600 transition-colors">비밀번호 변경 / 회원정보 수정</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Business Info Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4 px-2">
            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                사업자 정보 <span class="text-xs font-medium text-slate-400 font-normal ml-2">세금계산서 발행용</span>
            </h3>
        </div>
        <form action="./my_page_update.php" method="post"
            class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
            <input type="hidden" name="w" value="biz">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">상호명 (사업자명)</label>
                    <input type="text" name="mb_biz_name" value="<?php echo $member['mb_biz_name']; ?>"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-900"
                        placeholder="상호명을 입력하세요">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">사업자 등록번호</label>
                    <input type="text" name="mb_biz_num" value="<?php echo $member['mb_biz_num']; ?>"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-900"
                        placeholder="000-00-00000">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">대표자명</label>
                    <input type="text" name="mb_biz_rep" value="<?php echo $member['mb_biz_rep']; ?>"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-900"
                        placeholder="대표자 성함">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">업태 / 종목</label>
                    <input type="text" name="mb_biz_type" value="<?php echo $member['mb_biz_type']; ?>"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-900"
                        placeholder="서비스 / 통신판매">
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">사업장 주소지</label>
                <input type="text" name="mb_biz_addr" value="<?php echo $member['mb_biz_addr']; ?>"
                    class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-orange-500 transition-all font-medium text-slate-900"
                    placeholder="사업장 소재지 전체를 입력하세요">
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="px-8 py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-orange-600 transition-all shadow-lg hover:shadow-orange-200">사업자
                    정보 저장하기</button>
            </div>
        </form>
    </div>

    <!-- Construction Address Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4 px-2">
            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                시공 주소지 관리
            </h3>
            <button onclick="openAddrModal()"
                class="text-sm font-bold text-orange-600 bg-orange-50 px-4 py-2 rounded-full hover:bg-orange-100 transition-colors">+
                추가하기</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (count($ad_list) > 0): ?>
                <?php foreach ($ad_list as $ad): ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 relative group">
                        <?php if ($ad['ad_default']): ?>
                            <span
                                class="absolute top-6 right-6 px-2 py-0.5 bg-orange-100 text-orange-600 text-[10px] font-black rounded-md">기본</span>
                        <?php endif; ?>

                        <h4 class="font-bold text-slate-900 text-lg mb-1">
                            <?php echo $ad['ad_subject']; ?>
                        </h4>
                        <div class="mb-4">
                            <p class="text-sm font-medium text-slate-700 leading-snug">
                                <?php echo $ad['ad_addr1']; ?>
                            </p>
                            <p class="text-sm text-slate-500">
                                <?php echo $ad['ad_addr2']; ?>
                                <?php echo $ad['ad_addr3']; ?>
                            </p>
                        </div>
                        <div class="text-xs text-slate-400 font-medium">
                            <span class="mr-3">
                                <?php echo $ad['ad_name']; ?>
                            </span>
                            <span>
                                <?php echo $ad['ad_hp']; ?>
                            </span>
                        </div>

                        <div
                            class="mt-4 pt-4 border-t border-slate-50 flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="editAddress(<?php echo $ad['ad_id']; ?>)"
                                class="text-xs font-bold text-slate-400 hover:text-slate-900">수정</button>
                            <button onclick="deleteAddress(<?php echo $ad['ad_id']; ?>)"
                                class="text-xs font-bold text-slate-400 hover:text-red-500">삭제</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div
                    class="col-span-1 sm:col-span-2 bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl py-12 flex flex-col items-center justify-center">
                    <div
                        class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center mb-4 text-slate-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7">
                            </path>
                        </svg>
                    </div>
                    <p class="text-slate-400 font-bold">등록된 시공 주소지가 없습니다</p>
                    <p class="text-slate-400 text-sm mt-1">간판 설치가 필요한 장소를 미리 등록해두세요</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Address Modal (Simplified logic for now) -->
<div id="addrModal"
    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-[500px] rounded-[40px] shadow-2xl overflow-hidden p-10">
        <h3 class="text-2xl font-black text-slate-900 mb-6">주소지 추가</h3>
        <form action="./my_page_update.php" method="post" id="addrForm">
            <input type="hidden" name="w" value="ad_add">
            <input type="hidden" name="ad_id" id="ad_id">

            <div class="space-y-4 mb-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">주소지
                        별칭</label>
                    <input type="text" name="ad_subject" required
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900"
                        placeholder="예: 가게본점, 사무실">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">받는 사람 /
                        연락처</label>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="ad_name" required
                            class="px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900"
                            placeholder="이름">
                        <input type="text" name="ad_hp" required
                            class="px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900"
                            placeholder="휴대폰번호">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">주소
                        검색</label>
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="ad_zip" readonly
                            class="w-24 px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900"
                            placeholder="우편번호">
                        <button type="button"
                            class="flex-1 bg-slate-900 text-white font-bold rounded-2xl hover:bg-orange-600 transition-all text-sm">주소
                            찾기</button>
                    </div>
                    <input type="text" name="ad_addr1" readonly
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900 mb-2"
                        placeholder="기본 주소">
                    <input type="text" name="ad_addr2"
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-900"
                        placeholder="상세 주소">
                </div>
                <label class="flex items-center gap-3 ml-1 cursor-pointer">
                    <input type="checkbox" name="ad_default" value="1"
                        class="w-5 h-5 rounded-lg border-2 border-slate-200 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-bold text-slate-600">기본 주소지로 설정하기</span>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button type="button" onclick="closeAddrModal()"
                    class="py-4 bg-slate-50 text-slate-400 font-bold rounded-2xl hover:bg-slate-100 transition-all">취소</button>
                <button type="submit"
                    class="py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-orange-600 transition-all shadow-xl hover:shadow-orange-100">저장하기</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddrModal() {
        document.getElementById('addrModal').classList.remove('hidden');
        document.getElementById('addrForm').reset();
        document.getElementById('ad_id').value = '';
    }
    function closeAddrModal() {
        document.getElementById('addrModal').classList.add('hidden');
    }
    function deleteAddress(id) {
        if (confirm('이 주소지를 삭제하시겠습니까?')) {
            location.href = './my_page_update.php?w=ad_del&ad_id=' + id;
        }
    }
    // Edit Address logic to be implemented later (fetch data via AJAX or embedded JSON)
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
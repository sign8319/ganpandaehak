<?php
if (!isset($mb_name))
    $mb_name = isset($member['mb_name']) ? $member['mb_name'] : '';
if (!isset($mb_hp))
    $mb_hp = isset($member['mb_hp']) ? $member['mb_hp'] : '';
if (!isset($mb_email))
    $mb_email = isset($member['mb_email']) ? $member['mb_email'] : '';

if (!isset($is_use_captcha))
    $is_use_captcha = true;
if ($is_use_captcha) {
    // 비회원일 때만 캡챠 사용
    if (!$is_member) {
        // 설정된 캡챠 타입에 따라 적절한 캡챠 로드
        if ($config['cf_captcha'] == 'recaptcha') {
            include_once(G5_PLUGIN_PATH . '/recaptcha/recaptcha.class.php');
            include_once(G5_PLUGIN_PATH . '/recaptcha/recaptcha.user.lib.php');
            $captcha_html = captcha_html();
            $captcha_js = chk_captcha_js();
        } else {
            // 기본 kcaptcha
            include_once(G5_PLUGIN_PATH . '/kcaptcha/captcha.lib.php');
            $captcha_html = captcha_html();
            $captcha_js = chk_captcha_js();
        }
    } else {
        // 회원일 때는 캡챠 비활성화
        $captcha_html = '';
        $captcha_js = '';
    }
}
?>

<!-- 모달 영역 (순수 JavaScript) -->
<div id="modalContainer" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    style="display: none;">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-2xl mx-auto relative max-h-[95vh] flex flex-col"
        style="scrollbar-width: none; -ms-overflow-style: none;" onclick="event.stopPropagation();">
        <!-- 고정 헤더 -->
        <div
            class="flex-shrink-0 p-2 md:p-3 bg-accent rounded-t-2xl border-b border-gray-200 flex justify-between items-start">
            <div class="flex flex-col">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900">1분 퀵 상담 신청</h2>
                <p>요청서 작성 후 30분 내 연락 드립니다.</p>
            </div>
            <button type="button" class="text-gray-400 hover:text-gray-700 text-xl sm:text-2xl"
                onclick="closeModal()">&times;</button>
        </div>

        <!-- 스크롤 가능한 내용 -->
        <div class="flex-1 overflow-y-auto p-2 md:p-4" style="scrollbar-width: none; -ms-overflow-style: none;">
            <form name="main_top_consult_form" id="main_top_consult_form"
                action="<?php echo G5_BBS_URL; ?>/write_update.php" onsubmit="return main_top_consult_form_check(this);"
                method="post" enctype="multipart/form-data">
                <input type="hidden" name="w" value="">
                <input type="hidden" name="wr_id" value="">
                <input type="hidden" name="sca" value="">
                <input type="hidden" name="sfl" value="">
                <input type="hidden" name="stx" value="">
                <input type="hidden" name="spt" value="">
                <input type="hidden" name="sst" value="">
                <input type="hidden" name="sod" value="">
                <input type="hidden" name="page" value="">
                <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
                <input type="hidden" name="bo_table" value="consult">
                <input type="hidden" name="wr_subject" value="<?php echo date('Y-m-d H:i:s'); ?> 퀵상담 신청">
                <input type="hidden" name="token" value="<?php echo get_write_token('consult'); ?>">
                <input type="hidden" name="wr_homepage" value="">
                <input type="hidden" name="wr_password" value="">
                <input type="hidden" name="html" value="">
                <input type="hidden" name="secret" value="">
                <input type="hidden" name="mail" value="">



                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                    <div class="mb-4">
                        <label for="wr_name" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">이름 또는 업체명
                            <span class="text-red-500">*</span></label>
                        <input type="text" class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm"
                            id="wr_name" name="wr_name" placeholder="이름을 입력해주세요" required
                            value="<?php echo $mb_name; ?>">
                    </div>
                    <div class="mb-4">
                        <label for="wr_phone" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">연락처
                            <span class="text-red-500">*</span></label>
                        <input type="tel" class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm"
                            id="wr_phone" name="wr_phone" placeholder="연락처를 입력해주세요" required
                            value="<?php echo $mb_hp; ?>" oninput="this.value = this.value.replace(/[^0-9-]/g, '')"
                            maxlength="13">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="m_wr_email" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">이메일 <span
                            class="text-red-500">*</span></label>
                    <input type="email" class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm" id="wr_email"
                        name="wr_email" placeholder="이메일을 입력해주세요" required value="<?php echo $mb_email; ?>">
                </div>
                <div class="mb-4">
                    <label for="wr_1" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">어떤 간판이
                        필요하세요?</label>
                    <div class="custom-select w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm relative">
                        <div class="selected-option flex items-center justify-between cursor-pointer"
                            onclick="toggleDropdown('wr_1')">
                            <span class="selected-text">선택하세요</span>
                            <i class="fa fa-chevron-down text-gray-500"></i>
                        </div>
                        <div
                            class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-300 max-h-60 overflow-y-auto z-50 hidden">
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value=""
                                onclick="selectOption('wr_1', '', '선택하세요')">선택하세요</div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="플렉스 간판"
                                onclick="selectOption('wr_1', '플렉스 간판', '플렉스 간판')">
                                <span class="text-red-500 mr-2">●</span>플렉스 간판
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="채널 간판"
                                onclick="selectOption('wr_1', '채널 간판', '채널 간판')">
                                <span class="text-red-500 mr-2">●</span>채널 간판
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="돌출 간판"
                                onclick="selectOption('wr_1', '돌출 간판', '돌출 간판')">
                                <span class="text-red-500 mr-2">●</span>돌출 간판
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                data-value="실사 출력(유리창선팅,현수막 등)"
                                onclick="selectOption('wr_1', '실사 출력(유리창선팅,현수막 등)', '실사 출력(유리창선팅,현수막 등)')">
                                <span class="text-red-500 mr-2">●</span>실사 출력(유리창선팅,현수막 등)
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="지주 간판"
                                onclick="selectOption('wr_1', '지주 간판', '지주 간판')">
                                <span class="text-red-500 mr-2">●</span>지주 간판
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="네온 사인"
                                onclick="selectOption('wr_1', '네온 사인', '네온 사인')">
                                <span class="text-red-500 mr-2">●</span>네온 사인
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="실내 사인물"
                                onclick="selectOption('wr_1', '실내 사인물', '실내 사인물')">
                                <span class="text-red-500 mr-2">●</span>실내 사인물
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="간판 철거"
                                onclick="selectOption('wr_1', '간판 철거', '간판 철거')">
                                <span class="text-red-500 mr-2">●</span>간판 철거
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="프랜차이즈/기업상담"
                                onclick="selectOption('wr_1', '프랜차이즈/기업상담', '프랜차이즈/기업상담')">
                                <span class="text-red-500 mr-2">●</span>프랜차이즈/기업상담
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                data-value="잘 모르겠어요(상담이 필요해요)"
                                onclick="selectOption('wr_1', '잘 모르겠어요(상담이 필요해요)', '잘 모르겠어요(상담이 필요해요)')">
                                <span class="text-red-500 mr-2">●</span>잘 모르겠어요(상담이 필요해요)
                            </div>
                        </div>
                        <input type="hidden" id="wr_1" name="wr_1" value="">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="wr_2" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">디자인 파일이
                        있으신가요?</label>
                    <div class="custom-select w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm relative">
                        <div class="selected-option flex items-center justify-between cursor-pointer"
                            onclick="toggleDropdown('wr_2')">
                            <span class="selected-text">선택하세요</span>
                            <i class="fa fa-chevron-down text-gray-500"></i>
                        </div>
                        <div
                            class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-300 max-h-60 overflow-y-auto z-50 hidden">
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value=""
                                onclick="selectOption('wr_2', '', '선택하세요')">선택하세요</div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="디자인 파일이 있어요"
                                onclick="selectOption('wr_2', '디자인 파일이 있어요', '디자인 파일이 있어요')">
                                <span class="text-red-500 mr-2">●</span>디자인 파일이 있어요
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                data-value="참고하고 싶은 이미지가 있어요"
                                onclick="selectOption('wr_2', '참고하고 싶은 이미지가 있어요', '참고하고 싶은 이미지가 있어요')">
                                <span class="text-red-500 mr-2">●</span>참고하고 싶은 이미지가 있어요
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                data-value="디자인이 전혀 없어요(처음부터 도와주세요)"
                                onclick="selectOption('wr_2', '디자인이 전혀 없어요(처음부터 도와주세요)', '디자인이 전혀 없어요(처음부터 도와주세요)')">
                                <span class="text-red-500 mr-2">●</span>디자인이 전혀 없어요(처음부터 도와주세요)
                            </div>
                        </div>
                        <input type="hidden" id="wr_2" name="wr_2" value="">
                    </div>
                </div>

                <!-- 파일 첨부 -->
                <div class="mb-4">
                    <label for="bf_file" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">디자인 파일 또는 참고
                        이미지를 업로드해주세요</label>
                    <div class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm">
                        <label for="bf_file_<?php echo $i + 1 ?>" class="lb_icon">
                            <span class="sound_only"> 파일 #<?php echo $i + 1 ?></span></label>
                        <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1 ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="wr_3" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">예상하시는 예산대가 있으시면
                        알려주세요</label>
                    <div class="custom-select w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm relative">
                        <div class="selected-option flex items-center justify-between cursor-pointer"
                            onclick="toggleDropdown('wr_3')">
                            <span class="selected-text">선택하세요</span>
                            <i class="fa fa-chevron-down text-gray-500"></i>
                        </div>
                        <div
                            class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-300 max-h-60 overflow-y-auto z-50 hidden">
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value=""
                                onclick="selectOption('wr_3', '', '선택하세요')">선택하세요</div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="0 ~ 100만원"
                                onclick="selectOption('wr_3', '0 ~ 100만원', '0 ~ 100만원')">
                                <span class="text-red-500 mr-2">●</span>0 ~ 100만원
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="100 ~ 200만원"
                                onclick="selectOption('wr_3', '100 ~ 200만원', '100 ~ 200만원')">
                                <span class="text-red-500 mr-2">●</span>100 ~ 200만원
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="200 ~ 300만원"
                                onclick="selectOption('wr_3', '200 ~ 300만원', '200 ~ 300만원')">
                                <span class="text-red-500 mr-2">●</span>200 ~ 300만원
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100" data-value="300 ~ 500만원"
                                onclick="selectOption('wr_3', '300 ~ 500만원', '300 ~ 500만원')">
                                <span class="text-red-500 mr-2">●</span>300 ~ 500만원
                            </div>
                            <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100"
                                data-value="금액은 중요하지 않아요"
                                onclick="selectOption('wr_3', '금액은 중요하지 않아요', '금액은 중요하지 않아요')">
                                <span class="text-red-500 mr-2">●</span>금액은 중요하지 않아요
                            </div>
                        </div>
                        <input type="hidden" id="wr_3" name="wr_3" value="">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="wr_4" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">오픈 예정일이
                        언제인가요?</label>
                    <input type="date" id="wr_4" name="wr_4"
                        class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm">
                </div>
                <div class="mb-4">
                    <label for="wr_5" class="block text-gray-700 text-xs sm:text-sm font-semibold mb-1">전달하고 싶은 내용을
                        적어주세요</label>
                    <textarea id="wr_5" name="wr_5" rows="1"
                        class="w-full border border-gray-300 px-2 py-1 text-xs sm:text-sm"
                        placeholder="전달하고 싶은 내용을 입력해주세요"></textarea>
                </div>



                <?php if ($is_use_captcha) { //자동등록방지  ?>
                    <div class="write_div">
                        <div class="flex justify-center">
                            <?php echo $captcha_html ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="flex justify-center mt-3 sm:mt-4">
                    <button type="submit"
                        class="px-4 sm:px-6 py-2 sm:py-2.5 bg-accent text-black text-xs sm:text-sm font-bold rounded-full shadow">상담
                        신청하기</button>
                </div>
                <div
                    class="text-center justify-center text-black text-sm md:text-mb font-semibold leading-6 md:leading-8">
                    <span class="block lg:inline">이메일 : <?php echo $default['de_admin_info_email'] ?></span>
                    <span class="hidden lg:inline"> | </span>
                    <span class="block lg:inline">대표전화 : <?php echo $default['de_admin_company_tel'] ?></span>
                </div>
                <div class="text-center text-gray-700 text-xs font-normal leading-tight">*입력하신 개인정보는 상담 및 견적 안내 이외의 목적으로
                    사용되지 않으며, 관련 법령에 따라 안전하게 관리됩니다.</div>
            </form>
        </div>
    </div>
</div>

<script>
    // 커스텀 셀렉트 박스 함수들 (모달 전용 - 사용하지 않음, 유지보수용)
    // 주의: 모달이 활성화되지 않으므로 더미 함수로 정의하여 에러 방지
    // write.skin.php에서는 toggleDropdownWrite, selectOptionWrite를 사용합니다.
    // 모달이 다시 활성화될 경우 아래 함수들을 실제 구현으로 교체해야 합니다.
    function toggleDropdown(selectId) {
        // 모달이 사용되지 않으므로 더미 함수
        console.log('모달 함수는 현재 사용되지 않습니다. write.skin.php의 toggleDropdownWrite를 사용하세요.');
        return false;
    }

    function selectOption(selectId, value, text) {
        // 모달이 사용되지 않으므로 더미 함수
        console.log('모달 함수는 현재 사용되지 않습니다. write.skin.php의 selectOptionWrite를 사용하세요.');
        return false;
    }

    // 모달 배경 클릭 시 닫기 (즉시 설정)
    const modalContainer = document.getElementById('modalContainer');
    if (modalContainer) {
        modalContainer.addEventListener('click', function (e) {
            if (e.target === modalContainer) {
                closeModal();
            }
        });
    }



    function main_top_consult_form_check(f) {
        if (document.getElementById("wr_name").value.trim() == "") {
            alert("이름을 입력해 주세요.");
            document.getElementById("wr_name").focus();
            return false;
        }
        if (document.getElementById("wr_email").value.trim() == "") {
            alert("이메일을 입력해 주세요.");
            document.getElementById("wr_email").focus();
            return false;
        }
        if (document.getElementById("wr_phone").value.trim() == "") {
            alert("연락처를 입력해 주세요.");
            document.getElementById("wr_phone").focus();
            return false;
        }

        <?php if (!$is_member && $is_use_captcha) { ?>
            <?php echo $captcha_js; // 비회원일 때만 캡챠 검증  ?>
        <?php } ?>

        // 폼 데이터에서 입력값 가져오기
        var name = document.getElementById("wr_name").value.trim();
        var phone = document.getElementById("wr_phone").value.trim();
        var email = document.getElementById("wr_email").value.trim();
        var signboardType = document.getElementById("wr_1").value.trim() || '미입력';
        var designFile = document.getElementById("wr_2").value.trim() || '미입력';
        var budget = document.getElementById("wr_3").value.trim() || '미입력';
        var openDate = document.getElementById("wr_4").value.trim() || '미입력';
        var content = document.getElementById("wr_5").value.trim() || '미입력';

        // 현재 시간
        var now = new Date();
        var dateStr = now.getFullYear() + '-' +
            String(now.getMonth() + 1).padStart(2, '0') + '-' +
            String(now.getDate()).padStart(2, '0') + ' ' +
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0') + ':' +
            String(now.getSeconds()).padStart(2, '0');

        // 동적으로 내용 생성
        var formattedContent =
            '[퀵상담 신청 정보]\n' +
            '신청일시: ' + dateStr + '\n\n' +
            '이름/업체명: ' + name + '\n' +
            '연락처: ' + phone + '\n' +
            '이메일: ' + email + '\n\n' +
            '간판 종류: ' + signboardType + '\n' +
            '디자인 파일: ' + designFile + '\n' +
            '예상 예산: ' + budget + '\n' +
            '오픈 예정일: ' + openDate + '\n\n' +
            '문의내용:\n' + content;

        // wr_content 요소가 없으면 동적으로 생성
        var wrContentElement = document.getElementById("wr_content");
        if (!wrContentElement) {
            wrContentElement = document.createElement("input");
            wrContentElement.type = "hidden";
            wrContentElement.name = "wr_content";
            wrContentElement.id = "wr_content";
            document.querySelector("form").appendChild(wrContentElement);
        }

        // 내용을 wr_content에 설정
        wrContentElement.value = formattedContent;

        let token = get_write_token(f.bo_table.value);
        if (!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }
        if (typeof f.token === "undefined") {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'token';
            input.value = token;
            f.prepend(input);
        } else {
            f.token.value = token;
        }

        return true;
    }

</script>

<style>
    /* 모달 입력 필드 focus 스타일 */
    #modalContainer input[type="text"],
    #modalContainer input[type="tel"],
    #modalContainer input[type="email"],
    #modalContainer input[type="date"],
    #modalContainer textarea {
        outline: none !important;
    }

    #modalContainer input[type="text"]:focus,
    #modalContainer input[type="tel"]:focus,
    #modalContainer input[type="email"]:focus,
    #modalContainer input[type="date"]:focus,
    #modalContainer textarea:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px #a3e635 !important;
        /* lime-400 색상 */
        border-color: #a3e635 !important;
    }

    /* 커스텀 셀렉트 박스 focus 스타일 */
    #modalContainer .custom-select:focus-within {
        outline: none !important;
        box-shadow: 0 0 0 2px #a3e635 !important;
        border-color: #a3e635 !important;
    }

    /* 파일 업로드 영역 focus 스타일 */
    #modalContainer .w-full.border:focus-within {
        outline: none !important;
        box-shadow: 0 0 0 2px #a3e635 !important;
        border-color: #a3e635 !important;
    }
</style>
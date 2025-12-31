<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<section id="bo_w" class="w-full md:max-w-3xl mx-auto py-4 px-2 md:px-0 md:py-12">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

    <div class="bg-white overflow-hidden md:rounded-2xl md:shadow-2xl md:border md:border-gray-100">
        <!-- 헤더 섹션 삭제됨 (심플화) -->

        <!-- 게시물 작성/수정 시작 { -->
        <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);"
            method="post" enctype="multipart/form-data" autocomplete="off" class="p-4 md:p-10">

            <!-- 로고 이미지 추가 -->
            <div class="flex justify-center mb-6 md:mb-8">
                <img src="/ganpandaehak/data/assets/asset_20251228193653_7935.png" alt="간판대학"
                    class="w-36 md:w-48 object-contain hover:scale-105 transition-transform duration-300">
            </div>

            <p
                class="text-gray-500 text-xs sm:text-sm mb-6 text-center font-medium bg-gray-50 py-2 rounded-lg whitespace-nowrap overflow-hidden text-ellipsis px-1">
                작성해 주시면 <span class="text-orange-500 font-bold">30분 내로</span> 전문 상담원이 연락드립니다.
            </p>
            <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
            <input type="hidden" name="w" value="<?php echo $w ?>">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
            <input type="hidden" name="stx" value="<?php echo $stx ?>">
            <input type="hidden" name="spt" value="<?php echo $spt ?>">
            <input type="hidden" name="sst" value="<?php echo $sst ?>">
            <input type="hidden" name="sod" value="<?php echo $sod ?>">
            <input type="hidden" name="page" value="<?php echo $page ?>">
            <input type="hidden" name="wr_homepage" value=" ">
            <input type="hidden" name="wr_password" value="1234">
            <input type="hidden" name="wr_subject" id="wr_subject" value="">

            <?php
            $option = '';
            $option_hidden = '';
            if ($is_notice || $is_html || $is_secret || $is_mail) {
                $option = '';
                if ($is_notice) {
                    $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="notice" name="notice"  class="selec_chk" value="1" ' . $notice_checked . '>' . PHP_EOL . '<label for="notice"><span></span>공지</label></li>';
                }
                if ($is_html) {
                    if ($is_dhtml_editor) {
                        $option_hidden .= '<input type="hidden" value="html1" name="html">';
                    } else {
                        $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="' . $html_value . '" ' . $html_checked . '>' . PHP_EOL . '<label for="html"><span></span>html</label></li>';
                    }
                }
                if ($is_secret) {
                    if ($is_admin || $is_secret == 1) {
                        $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="secret" name="secret"  class="selec_chk" value="secret" ' . $secret_checked . '>' . PHP_EOL . '<label for="secret"><span></span>비밀글</label></li>';
                    } else {
                        $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                    }
                }
                if ($is_mail) {
                    $option .= PHP_EOL . '<li class="chk_box"><input type="checkbox" id="mail" name="mail"  class="selec_chk" value="mail" ' . $recv_email_checked . '>' . PHP_EOL . '<label for="mail"><span></span>답변메일받기</label></li>';
                }
            }
            echo $option_hidden;
            ?>

            <?php if ($is_category) { ?>
                <div class="mb-6">
                    <label for="ca_name" class="sound_only">분류<strong>필수</strong></label>
                    <select name="ca_name" id="ca_name" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">분류를 선택하세요</option>
                        <?php echo $category_option ?>
                    </select>
                </div>
            <?php } ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                <div>
                    <label for="wr_name" class="block text-gray-700 text-sm font-bold mb-1">이름 또는 업체명 <span
                            class="text-orange-500">*</span></label>
                    <input type="text" name="wr_name" value="<?php echo isset($name) ? $name : ''; ?>" id="wr_name"
                        required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5 transition-colors"
                        placeholder="예: 간판대학">
                </div>

                <div>
                    <label for="wr_phone" class="block text-gray-700 text-sm font-bold mb-1">연락처 <span
                            class="text-orange-500">*</span></label>
                    <input type="tel" name="wr_phone" id="wr_phone"
                        value="<?php echo isset($write['wr_phone']) ? $write['wr_phone'] : (isset($member['mb_hp']) ? $member['mb_hp'] : ''); ?>"
                        required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5 transition-colors"
                        placeholder="예: 010-1234-5678" oninput="this.value = this.value.replace(/[^0-9-]/g, '')"
                        maxlength="13">
                </div>

                <?php if ($is_email) { ?>
                    <div class="md:col-span-2">
                        <label for="wr_email" class="block text-gray-700 text-sm font-bold mb-1">이메일 <span
                                class="text-orange-500">*</span></label>
                        <input type="email" name="wr_email" value="<?php echo $email ?>" id="wr_email" required
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5 transition-colors"
                            placeholder="example@email.com">
                    </div>
                <?php } ?>
            </div>

            <hr class="border-gray-100 mb-4">

            <div class="space-y-3 md:space-y-5">
                <!-- 상담 문의 필드들 -->
                <div>
                    <label for="wr_1" class="block text-gray-700 text-sm font-bold mb-1">어떤 간판이 필요하세요?</label>
                    <div
                        class="custom-select w-full bg-white border border-gray-200 rounded-xl relative hover:border-gray-400 transition-colors">
                        <div class="selected-option flex items-center justify-between cursor-pointer p-2.5"
                            onclick="toggleDropdownWrite('wr_1', event)">
                            <span
                                class="selected-text text-gray-600"><?php echo isset($write['wr_1']) && $write['wr_1'] ? $write['wr_1'] : '간판 종류를 선택해주세요'; ?></span>
                            <i class="fa fa-chevron-down text-gray-400"></i>
                        </div>
                        <div
                            class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-200 max-h-60 overflow-y-auto z-50 hidden rounded-xl shadow-xl mt-2 p-1">
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="" onclick="selectOptionWrite('wr_1', '', '간판 종류를 선택해주세요', event)">선택하지 않음
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="플렉스 간판" onclick="selectOptionWrite('wr_1', '플렉스 간판', '플렉스 간판', event)">플렉스
                                간판</div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="채널 간판" onclick="selectOptionWrite('wr_1', '채널 간판', '채널 간판', event)">채널 간판
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="돌출 간판" onclick="selectOptionWrite('wr_1', '돌출 간판', '돌출 간판', event)">돌출 간판
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="실사 출력" onclick="selectOptionWrite('wr_1', '실사 출력', '실사 출력', event)">실사
                                출력(유리창선팅,현수막 등)</div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="지주 간판" onclick="selectOptionWrite('wr_1', '지주 간판', '지주 간판', event)">지주 간판
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="네온 사인" onclick="selectOptionWrite('wr_1', '네온 사인', '네온 사인', event)">네온 사인
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="실내 사인물" onclick="selectOptionWrite('wr_1', '실내 사인물', '실내 사인물', event)">실내
                                사인물</div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="간판 철거" onclick="selectOptionWrite('wr_1', '간판 철거', '간판 철거', event)">간판 철거
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="프랜차이즈/기업상담"
                                onclick="selectOptionWrite('wr_1', '프랜차이즈/기업상담', '프랜차이즈/기업상담', event)">프랜차이즈/기업상담</div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700 font-medium text-orange-600"
                                data-value="잘 모르겠어요(상담이 필요해요)"
                                onclick="selectOptionWrite('wr_1', '잘 모르겠어요(상담이 필요해요)', '잘 모르겠어요(상담이 필요해요)', event)">잘
                                모르겠어요(상담이 필요해요)</div>
                        </div>
                        <input type="hidden" id="wr_1" name="wr_1"
                            value="<?php echo isset($write['wr_1']) ? $write['wr_1'] : ''; ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5">
                    <div>
                        <label for="wr_3" class="block text-gray-700 text-sm font-bold mb-1">예상 예산대</label>
                        <div
                            class="custom-select w-full bg-white border border-gray-200 rounded-xl relative hover:border-gray-400 transition-colors">
                            <div class="selected-option flex items-center justify-between cursor-pointer p-2.5"
                                onclick="toggleDropdownWrite('wr_3', event)">
                                <span
                                    class="selected-text text-gray-600"><?php echo isset($write['wr_3']) && $write['wr_3'] ? $write['wr_3'] : '선택하세요'; ?></span>
                                <i class="fa fa-chevron-down text-gray-400"></i>
                            </div>
                            <div
                                class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-200 max-h-60 overflow-y-auto z-50 hidden rounded-xl shadow-xl mt-2 p-1">
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="" onclick="selectOptionWrite('wr_3', '', '선택하세요', event)">선택하세요</div>
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="100만원 이하"
                                    onclick="selectOptionWrite('wr_3', '100만원 이하', '100만원 이하', event)">100만원 이하</div>
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="100 ~ 200만원"
                                    onclick="selectOptionWrite('wr_3', '100 ~ 200만원', '100 ~ 200만원', event)">100 ~ 200만원
                                </div>
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="200 ~ 300만원"
                                    onclick="selectOptionWrite('wr_3', '200 ~ 300만원', '200 ~ 300만원', event)">200 ~ 300만원
                                </div>
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="300만원 이상"
                                    onclick="selectOptionWrite('wr_3', '300만원 이상', '300만원 이상', event)">300만원 이상</div>
                                <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                    data-value="미정/상담후결정"
                                    onclick="selectOptionWrite('wr_3', '미정/상담후결정', '미정/상담후결정', event)">미정/상담후결정</div>
                            </div>
                            <input type="hidden" id="wr_3" name="wr_3"
                                value="<?php echo isset($write['wr_3']) ? $write['wr_3'] : ''; ?>">
                        </div>
                    </div>

                    <div>
                        <label for="wr_4" class="block text-gray-700 text-sm font-bold mb-1">오픈 예정일</label>
                        <input type="date" id="wr_4" name="wr_4"
                            value="<?php echo isset($write['wr_4']) ? $write['wr_4'] : ''; ?>"
                            class="w-full bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5 transition-colors cursor-pointer">
                    </div>
                </div>

                <div>
                    <label for="wr_2" class="block text-gray-700 text-sm font-bold mb-1">디자인 파일 보유 여부</label>
                    <div
                        class="custom-select w-full bg-white border border-gray-200 rounded-xl relative hover:border-gray-400 transition-colors">
                        <div class="selected-option flex items-center justify-between cursor-pointer p-2.5"
                            onclick="toggleDropdownWrite('wr_2', event)">
                            <span
                                class="selected-text text-gray-600"><?php echo isset($write['wr_2']) && $write['wr_2'] ? $write['wr_2'] : '선택하세요'; ?></span>
                            <i class="fa fa-chevron-down text-gray-400"></i>
                        </div>
                        <div
                            class="dropdown-options absolute top-full left-0 right-0 bg-white border border-gray-200 max-h-60 overflow-y-auto z-50 hidden rounded-xl shadow-xl mt-2 p-1">
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="" onclick="selectOptionWrite('wr_2', '', '선택하세요', event)">선택하세요</div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="디자인 파일이 있어요"
                                onclick="selectOptionWrite('wr_2', '디자인 파일이 있어요', '디자인 파일이 있어요', event)">디자인 파일이 있어요
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="참고할 이미지가 있어요"
                                onclick="selectOptionWrite('wr_2', '참고할 이미지가 있어요', '참고할 이미지가 있어요', event)">참고할 이미지가 있어요
                            </div>
                            <div class="option-item px-4 py-2.5 cursor-pointer hover:bg-orange-50 rounded-lg text-sm text-gray-700"
                                data-value="디자인이 없어요 (신규 제작)"
                                onclick="selectOptionWrite('wr_2', '디자인이 없어요 (신규 제작)', '디자인이 없어요 (신규 제작)', event)">디자인이
                                없어요 (신규 제작)</div>
                        </div>
                        <input type="hidden" id="wr_2" name="wr_2"
                            value="<?php echo isset($write['wr_2']) ? $write['wr_2'] : ''; ?>">
                    </div>
                </div>

                <!-- 파일 첨부 -->
                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label for="bf_file" class="block text-gray-600 text-sm font-medium mb-2">첨부파일 (참고 이미지 등)</label>
                    <div class="w-full">
                        <label for="bf_file_<?php echo $i + 1 ?>" class="lb_icon">
                            <span class="sound_only"> 파일 #<?php echo $i + 1 ?></span></label>
                        <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1 ?>"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all cursor-pointer">
                    </div>
                </div>

                <div>
                    <label for="wr_5" class="block text-gray-700 text-sm font-bold mb-1">전달하고 싶은 내용 <span
                            class="text-orange-500">*</span></label>
                    <textarea id="wr_5" name="wr_5" rows="5" required
                        class="w-full bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-4 transition-colors resize-none"
                        placeholder="문의하실 내용을 자유롭게 적어주세요. 상세할수록 정확한 견적이 가능합니다."><?php echo isset($write['wr_5']) ? $write['wr_5'] : ''; ?></textarea>
                </div>
            </div>

            <!-- wr_content는 숨겨진 textarea로 자동 생성됨 (제출 시 wr_5 내용으로 채워짐) -->
            <textarea name="wr_content" id="wr_content" style="display:none;"></textarea>

            <?php if ($is_use_captcha) { //자동등록방지  ?>
                <div class="write_div mt-6">
                    <?php echo $captcha_html ?>
                </div>
            <?php } ?>

            <div class="mt-10 flex justify-center">
                <button type="submit" id="btn_submit" accesskey="s"
                    class="w-full sm:w-auto px-8 py-2.5 bg-gray-900 hover:bg-black text-white text-base font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <span>상담 신청하기</span>
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>

    <script>
        // 커스텀 셀렉트 박스 함수들 (write 페이지 전용) - 전역 함수로 정의
        function toggleDropdownWrite(selectId, e) {
            if (e) {
                e.stopPropagation(); // 이벤트 버블링 방지
            }

            const boW = document.getElementById('bo_w');
            if (!boW) return;

            // selectId로 해당 custom-select 찾기
            const hiddenInput = boW.querySelector('#' + selectId);
            if (!hiddenInput) return;

            const customSelect = hiddenInput.closest('.custom-select');
            if (!customSelect) return;

            const dropdown = customSelect.querySelector('.dropdown-options');
            if (!dropdown) return;

            const allDropdowns = boW.querySelectorAll('.dropdown-options');

            // 다른 드롭다운들 닫기
            allDropdowns.forEach(function (dd) {
                if (dd !== dropdown) {
                    dd.classList.add('hidden');
                }
            });

            // 현재 드롭다운 토글
            dropdown.classList.toggle('hidden');
        }

        function selectOptionWrite(selectId, value, text, e) {
            if (e) {
                e.stopPropagation(); // 이벤트 버블링 방지
            }

            const boW = document.getElementById('bo_w');
            if (!boW) return;

            // selectId로 해당 custom-select 찾기
            const hiddenInput = boW.querySelector('#' + selectId);
            if (!hiddenInput) return;

            const customSelect = hiddenInput.closest('.custom-select');
            if (!customSelect) return;

            const selectedText = customSelect.querySelector('.selected-text');
            const dropdown = customSelect.querySelector('.dropdown-options');

            if (selectedText) selectedText.textContent = text;
            if (hiddenInput) hiddenInput.value = value;
            if (dropdown) dropdown.classList.add('hidden');
        }

        // 외부 클릭 시 드롭다운 닫기 (write 페이지 전용)
        document.addEventListener('click', function (e) {
            const boW = document.getElementById('bo_w');
            if (!boW) return;

            if (!e.target.closest('#bo_w .custom-select')) {
                boW.querySelectorAll('.dropdown-options').forEach(function (dropdown) {
                    dropdown.classList.add('hidden');
                });
            }
        });

        <?php if ($write_min || $write_max) { ?>
            // 글자수 제한
            var char_min = parseInt(<?php echo $write_min; ?>); // 최소
            var char_max = parseInt(<?php echo $write_max; ?>); // 최대
            check_byte("wr_content", "char_count");

            $(function () {
                $("#wr_content").on("keyup", function () {
                    check_byte("wr_content", "char_count");
                });
            });

        <?php } ?>
        function html_auto_br(obj) {
            if (obj.checked) {
                result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
                if (result)
                    obj.value = "html2";
                else
                    obj.value = "html1";
            }
            else
                obj.value = "";
        }

        function fwrite_submit(f) {
            // wr_5의 내용을 wr_content로 변환 (상담 신청 정보 포맷팅)
            var name = f.wr_name ? f.wr_name.value : '';
            var phone = f.wr_phone ? f.wr_phone.value : '';
            var email = f.wr_email ? f.wr_email.value : '';
            var signboardType = f.wr_1 ? f.wr_1.value : '';
            var designFile = f.wr_2 ? f.wr_2.value : '';
            var budget = f.wr_3 ? f.wr_3.value : '';
            var openDate = f.wr_4 ? f.wr_4.value : '';
            var content = f.wr_5 ? f.wr_5.value : '';

            // 현재 시간
            var now = new Date();
            var dateStr = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');

            // 제목 자동 생성: "2025-11-06 11:43:26 퀵상담 신청" 형식
            var autoSubject = dateStr + ' 퀵상담 신청';
            if (f.wr_subject) {
                f.wr_subject.value = autoSubject;
            }

            // 상담 신청 정보 포맷팅
            var formattedContent =
                '[상담 신청 정보]\n' +
                '신청일시: ' + dateStr + '\n\n' +
                '이름/업체명: ' + (name || '미입력') + '\n' +
                '연락처: ' + (phone || '미입력') + '\n' +
                '이메일: ' + (email || '미입력') + '\n\n' +
                '간판 종류: ' + (signboardType || '미입력') + '\n' +
                '디자인 파일: ' + (designFile || '미입력') + '\n' +
                '예상 예산: ' + (budget || '미입력') + '\n' +
                '오픈 예정일: ' + (openDate || '미입력') + '\n\n' +
                '문의내용:\n' + (content || '미입력');

            // wr_content에 설정
            if (typeof (ed_wr_content) != "undefined" && ed_wr_content.setContent) {
                ed_wr_content.setContent(formattedContent);
            } else {
                var wrContentEl = f.wr_content || document.getElementById("wr_content");
                if (wrContentEl) {
                    wrContentEl.value = formattedContent;
                }
            }

            <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

            var subject = "";
            var content = "";
            // 제목은 이미 autoSubject로 설정되었으므로 f.wr_subject.value 사용
            var subjectValue = (f.wr_subject && f.wr_subject.value) ? f.wr_subject.value : autoSubject;
            $.ajax({
                url: g5_bbs_url + "/ajax.filter.php",
                type: "POST",
                data: {
                    "subject": subjectValue,
                    "content": formattedContent
                },
                dataType: "json",
                async: false,
                cache: false,
                success: function (data, textStatus) {
                    subject = data.subject;
                    content = data.content;
                }
            });

            if (subject) {
                alert("제목에 금지단어('" + subject + "')가 포함되어있습니다");
                if (f.wr_subject) {
                    f.wr_subject.focus();
                }
                return false;
            }

            if (content) {
                alert("내용에 금지단어('" + content + "')가 포함되어있습니다");
                if (typeof (ed_wr_content) != "undefined")
                    ed_wr_content.returnFalse();
                else
                    f.wr_content.focus();
                return false;
            }

            if (document.getElementById("char_count")) {
                if (char_min > 0 || char_max > 0) {
                    var cnt = parseInt(check_byte("wr_content", "char_count"));
                    if (char_min > 0 && char_min > cnt) {
                        alert("내용은 " + char_min + "글자 이상 쓰셔야 합니다.");
                        return false;
                    }
                    else if (char_max > 0 && char_max < cnt) {
                        alert("내용은 " + char_max + "글자 이하로 쓰셔야 합니다.");
                        return false;
                    }
                }
            }

            <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

            document.getElementById("btn_submit").disabled = "disabled";

            return true;
        }
    </script>

    <!-- Flatpickr (달력 라이브러리) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/ko.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#wr_4", {
                locale: "ko",
                dateFormat: "Y-m-d",
                minDate: "today",
                disableMobile: "true" // 모바일에서도 리더블한 flatpickr 테마 강제 적용
            });

            // --- Auto Scroll Logic ---
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                });
            });

            // For custom selects (delegated or direct)
            const customSelects = document.querySelectorAll('.custom-select .selected-option');
            customSelects.forEach(select => {
                select.addEventListener('click', function () {
                    setTimeout(() => {
                        this.closest('.custom-select').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                });
            });
        });
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->
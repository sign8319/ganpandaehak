<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">

<style>
    /* 크롭 모달 스타일 */
    .crop-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
        overflow: hidden;
        /* 모달 전체 스크롤 방지 */
        display: flex;
        /* Flexbox for centering */
        align-items: center;
        justify-content: center;
    }

    .crop-modal-content {
        background-color: #fefefe;
        margin: 0;
        padding: 20px;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .crop-header {
        flex-shrink: 0;
    }

    .crop-body {
        flex: 1;
        overflow-y: auto;
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .crop-footer {
        flex-shrink: 0;
        margin-top: 16px;
        border-top: 1px solid #eee;
        padding-top: 16px;
    }

    .crop-container {
        width: 100%;
        max-height: 400px;
        /* Limit height */
        margin: 10px 0;
        background: #f8f8f8;
    }

    .crop-container img {
        max-width: 100%;
        max-height: 400px;
        /* Ensure image fits */
    }

    .crop-preview {
        width: 150px;
        height: 150px;
        overflow: hidden;
        border: 2px solid #F97316;
        border-radius: 8px;
        margin-top: 10px;
    }
</style>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-bold text-gray-900">상품 등록</h2>
        <p class="text-gray-500 mt-2">나만의 특별한 디자인 간판을 등록해주세요.</p>
    </div>

    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);"
        method="post" enctype="multipart/form-data" autocomplete="off" class="space-y-6">
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

        <?php
        $option = '';
        $option_hidden = '';
        if ($is_notice || $is_html || $is_secret || $is_mail) {
            $option = '';
            if ($is_notice) {
                $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="notice" name="notice" class="form-checkbox text-brand-orange rounded" value="1" ' . $notice_checked . '> <span class="ml-2">공지</span></label>';
            }
            if ($is_html) {
                if ($is_dhtml_editor) {
                    $option_hidden .= '<input type="hidden" value="html1" name="html">';
                } else {
                    $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="form-checkbox text-brand-orange rounded" value="' . $html_value . '" ' . $html_checked . '> <span class="ml-2">HTML</span></label>';
                }
            }
            if ($is_secret) {
                if ($is_admin || $is_secret == 1) {
                    $option .= '<label class="inline-flex items-center mr-4"><input type="checkbox" id="secret" name="secret" class="form-checkbox text-brand-orange rounded" value="secret" ' . $secret_checked . '> <span class="ml-2">비밀글</span></label>';
                } else {
                    $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                }
            }
        }
        echo $option_hidden;
        ?>

        <!-- 옵션 체크박스 -->
        <?php if ($option) { ?>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <?php echo $option ?>
            </div>
        <?php } ?>

        <!-- 카테고리 -->
        <?php if ($is_category) { ?>
            <div>
                <label for="ca_name" class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                <select name="ca_name" id="ca_name" required
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3">
                    <option value="">분류를 선택하세요</option>
                    <?php echo $category_option ?>
                </select>
            </div>
        <?php } ?>

        <!-- 제목 -->
        <div>
            <label for="wr_subject" class="block text-sm font-medium text-gray-700 mb-1">상품명</label>
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3"
                placeholder="상품명을 입력하세요">
        </div>

        <!-- 시공 현장 / 위치 (wr_1) -->
        <div>
            <label for="wr_1" class="block text-sm font-medium text-gray-700 mb-1">시공 현장 / 위치</label>
            <input type="text" name="wr_1" value="<?php echo isset($write['wr_1']) ? $write['wr_1'] : ''; ?>" id="wr_1"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3"
                placeholder="예: 서울 강남구 (해시태그 # 사용 가능)">
            <p class="text-xs text-gray-500 mt-1">* 리스트와 메인 배너에 표시될 위치 정보입니다.</p>
        </div>

        <!-- 가격 (wr_2) -->
        <div>
            <label for="wr_2" class="block text-sm font-medium text-gray-700 mb-1">예상 견적 / 가격</label>
            <div class="relative">
                <input type="text" name="wr_2" value="<?php echo isset($write['wr_2']) ? $write['wr_2'] : ''; ?>"
                    id="wr_2"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3 pl-3 pr-16"
                    placeholder="예: 350">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500">만원</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">* 숫자만 입력하세요 (만원 단위).</p>
        </div>

        <!-- 내용 -->
        <div>
            <label for="wr_content" class="block text-sm font-medium text-gray-700 mb-1">상품 설명</label>
            <div class="wr_content">
                <?php if ($write_min || $write_max) { ?>
                    <p id="char_count_desc" class="text-xs text-gray-500 mb-1">최소
                        <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하
                    </p>
                <?php } ?>
                <?php echo $editor_html; ?>
                <p class='text-sm text-red-500 mt-2'>※ 본문에 삽입된 이미지는 에디터상에서 크게 보여도, 실제 등록 후에는 화면 크기에 맞춰 자동으로 조절됩니다.</p>
            </div>
        </div>

        <!-- 링크 -->
        <?php for ($i = 1; $is_link && $i <= G5_LINK_COUNT; $i++) { ?>
            <div>
                <label for="wr_link<?php echo $i ?>" class="block text-sm font-medium text-gray-700 mb-1">링크
                    #<?php echo $i ?></label>
                <input type="text" name="wr_link<?php echo $i ?>" value="<?php if ($w == "u") {
                       echo $write['wr_link' . $i];
                   } ?>" id="wr_link<?php echo $i ?>"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-orange focus:ring focus:ring-brand-orange focus:ring-opacity-50 p-3">
            </div>
        <?php } ?>

        <!-- 파일 첨부 - 크롭 기능 추가 -->
        <div class="space-y-4">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>이미지 업로드 방법:</strong><br>
                            1. 아래에서 이미지들을 업로드하세요<br>
                            2. 업로드 후 <strong class="text-red-600">대표 이미지(썸네일)</strong>로 사용할 이미지를 선택하세요<br>
                            3. <strong class="text-purple-600">[구도 조정]</strong> 버튼으로 정사각형으로 자를 수 있습니다<br>
                            4. 선택한 이미지가 목록에 표시됩니다
                        </p>
                    </div>
                </div>
            </div>

            <?php for ($i = 0; $is_file && $i < $file_count; $i++) { ?>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 file-upload-item"
                    data-index="<?php echo $i; ?>">
                    <label for="bf_file_<?php echo $i + 1 ?>" class="block text-sm font-medium text-gray-700 mb-2">
                        이미지 #<?php echo $i + 1; ?>
                    </label>
                    <input type="file" name="bf_file[]" id="bf_file_<?php echo $i + 1 ?>" accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-600 file:text-white hover:file:bg-orange-700 file-input"
                        onchange="handleFileSelect(<?php echo $i; ?>, this)">

                    <!-- 이미지 미리보기 영역 -->
                    <div id="preview_<?php echo $i; ?>" class="mt-3 hidden">
                        <img src="" alt="미리보기" class="max-w-full h-48 object-cover rounded border border-gray-300">
                    </div>

                    <?php if ($is_file_content) { ?>
                        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>"
                            class="mt-2 w-full border-gray-300 rounded shadow-sm text-sm p-2" placeholder="파일 설명">
                    <?php } ?>

                    <?php if ($w == 'u' && $file[$i]['file']) { ?>
                        <div class="mt-2 flex items-center text-sm">
                            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i; ?>]"
                                value="1" class="mr-2 rounded text-red-500">
                            <label for="bf_file_del<?php echo $i ?>"
                                class="text-gray-600"><?php echo $file[$i]['source'] . '(' . $file[$i]['size'] . ')'; ?>
                                삭제</label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- 썸네일 선택 영역 -->
            <div id="thumbnail-selector"
                class="hidden bg-gradient-to-r from-orange-50 to-red-50 p-6 rounded-lg border-2 border-orange-300 shadow-md">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    대표 이미지 선택 (썸네일)
                </h3>
                <p class="text-sm text-gray-600 mb-4">목록에 표시될 대표 이미지를 선택하고 구도를 조정하세요</p>
                <div id="thumbnail-options" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- JavaScript로 동적 생성 -->
                </div>
                <input type="hidden" name="thumbnail_index" id="thumbnail_index" value="0">
                <input type="hidden" name="cropped_image" id="cropped_image" value="">
            </div>
        </div>

        <!-- 캡챠 -->
        <?php if ($is_use_captcha) { ?>
            <div>
                <?php echo $captcha_html ?>
            </div>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex justify-center gap-4 mt-8">
            <a href="<?php echo get_pretty_url($bo_table); ?>"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg text-lg font-bold hover:bg-gray-300 transition-colors">취소</a>
            <button type="submit" id="btn_submit" accesskey="s"
                class="px-8 py-3 bg-orange-600 text-white rounded-lg text-lg font-bold hover:bg-orange-700 shadow-lg transition-colors">작성완료</button>
        </div>

    </form>
</div>

<!-- 크롭 모달 -->
<div id="cropModal" class="crop-modal" style="display: none;">
    <div class="crop-modal-content">
        <div class="crop-header flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-900">이미지 구도 조정</h2>
            <button type="button" onclick="closeCropModal()"
                class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>

        <div class="crop-body">
            <p class="text-sm text-gray-600 mb-2 w-full text-center">드래그하여 영역 선택 (1:1 비율 자동 고정)</p>

            <div class="crop-container relative">
                <img id="crop-image" src="" alt="크롭할 이미지">
            </div>

            <div class="mt-4 flex flex-col items-center">
                <p class="text-sm text-gray-700 font-medium mb-1">미리보기</p>
                <div class="crop-preview"></div>
            </div>
        </div>

        <div class="crop-footer flex justify-end gap-4">
            <button type="button" onclick="closeCropModal()"
                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300 transition-colors shadow-sm">취소</button>
            <button type="button" onclick="applyCrop()"
                class="px-6 py-2 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 shadow-md transition-colors">이대로
                적용하기</button>
        </div>
    </div>
</div>

<!-- Cropper.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
    // 업로드된 파일 정보를 저장하는 배열
    var uploadedFiles = [];
    var cropper = null;
    var currentCropIndex = -1;

    <?php if ($w == 'u' && $is_file) { // 수정 모드일 때 기존 파일 정보 로드 
            for ($i = 0; $i < $file_count; $i++) {
                if ($file[$i]['file']) {
                    $file_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $file[$i]['file'];
                    ?>
                uploadedFiles[<?php echo $i; ?>] = {
                    index: <?php echo $i; ?>,
                    dataUrl: "<?php echo $file_url; ?>",
                    file: null, // 기존 파일은 File 객체가 없음
                    cropped: false,
                    croppedDataUrl: null
                };
                // 미리보기 이미지도 설정
                $(function () {
                    var preview = document.getElementById('preview_<?php echo $i; ?>');
                    if (preview) {
                        var img = preview.querySelector('img');
                        img.src = "<?php echo $file_url; ?>";
                        preview.classList.remove('hidden');
                    }
                });
                <?php
                }
            }
            ?>
        // 페이지 로드 후 썸네일 선택기 업데이트
        $(function () {
            setTimeout(updateThumbnailSelector, 100);
        });
    <?php } ?>

    // 파일 선택 시 처리
    function handleFileSelect(index, input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];

            // 이미지 파일인지 확인
            if (!file.type.match('image.*')) {
                alert('이미지 파일만 업로드 가능합니다.');
                input.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                // 미리보기 표시
                var preview = document.getElementById('preview_' + index);
                var img = preview.querySelector('img');
                img.src = e.target.result;
                preview.classList.remove('hidden');

                // 업로드된 파일 배열에 추가
                uploadedFiles[index] = {
                    index: index,
                    dataUrl: e.target.result,
                    file: file,
                    cropped: false,
                    croppedDataUrl: null
                };

                // 썸네일 선택 UI 업데이트
                updateThumbnailSelector();
            };
            reader.readAsDataURL(file);
        }
    }

    // 썸네일 선택 UI 업데이트
    function updateThumbnailSelector() {
        var validFiles = uploadedFiles.filter(function (f) { return f !== undefined; });

        if (validFiles.length === 0) {
            document.getElementById('thumbnail-selector').classList.add('hidden');
            return;
        }

        document.getElementById('thumbnail-selector').classList.remove('hidden');
        var container = document.getElementById('thumbnail-options');
        container.innerHTML = '';

        validFiles.forEach(function (fileInfo) {
            var option = document.createElement('div');
            option.className = 'thumbnail-option cursor-pointer relative group';
            option.setAttribute('data-index', fileInfo.index);

            var displayImg = fileInfo.cropped ? fileInfo.croppedDataUrl : fileInfo.dataUrl;
            var cropButtonText = fileInfo.cropped ? '✓ 조정완료' : '구도 조정';
            var cropButtonClass = fileInfo.cropped ? 'bg-green-500' : 'bg-purple-500';

            option.innerHTML = `
                <div class="relative border-4 rounded-lg overflow-hidden transition-all thumbnail-item" id="thumb_option_${fileInfo.index}">
                    <img src="${displayImg}" alt="이미지 ${fileInfo.index + 1}" class="w-full h-32 object-cover">
                    <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center pointer-events-none">
                        <span class="text-white font-bold bg-black bg-opacity-50 px-2 py-1 rounded">선택됨</span>
                    </div>
                    <div class="absolute top-2 right-2 bg-white rounded-full p-1 hidden" id="check_${fileInfo.index}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 space-y-1">
                    <button type="button" onclick="selectThumbnail(${fileInfo.index}); event.stopPropagation();" 
                        class="w-full text-xs py-2 px-3 bg-orange-500 text-white rounded-md font-bold hover:bg-orange-600 transition-colors shadow-sm">
                        대표 이미지로 선택
                    </button>
                    <button type="button" onclick="openCropModal(${fileInfo.index}); event.stopPropagation();" 
                        class="w-full text-xs py-2 px-3 ${cropButtonClass} text-white rounded-md font-bold hover:opacity-80 transition-colors shadow-sm">
                        ${cropButtonText}
                    </button>
                </div>
            `;

            container.appendChild(option);
        });

        // 첫 번째 이미지를 기본 선택
        var currentIndex = document.getElementById('thumbnail_index').value;
        if (!currentIndex || currentIndex == '0') {
            selectThumbnail(validFiles[0].index);
        }
    }

    // 썸네일 선택
    function selectThumbnail(index) {
        // 모든 선택 표시 제거
        var allThumbs = document.querySelectorAll('.thumbnail-item');
        allThumbs.forEach(function (thumb) {
            thumb.classList.remove('border-orange-500', 'border-4');
            thumb.classList.add('border-gray-300', 'border-2');
        });

        var allChecks = document.querySelectorAll('[id^="check_"]');
        allChecks.forEach(function (check) {
            check.classList.add('hidden');
        });

        // 선택된 항목 표시
        var selectedThumb = document.getElementById('thumb_option_' + index);
        if (selectedThumb) {
            selectedThumb.classList.remove('border-gray-300', 'border-2');
            selectedThumb.classList.add('border-orange-500', 'border-4');
        }

        var selectedCheck = document.getElementById('check_' + index);
        if (selectedCheck) {
            selectedCheck.classList.remove('hidden');
        }

        // hidden input에 선택된 인덱스 저장
        document.getElementById('thumbnail_index').value = index;
    }

    // 크롭 모달 열기
    function openCropModal(index) {
        var fileInfo = uploadedFiles[index];
        if (!fileInfo) return;

        currentCropIndex = index;

        // 모달 표시
        document.getElementById('cropModal').style.display = 'block';

        // 이미지 설정
        var image = document.getElementById('crop-image');
        image.src = fileInfo.dataUrl;

        // 기존 cropper 제거
        if (cropper) {
            cropper.destroy();
        }

        // 이미지 로드 후 cropper 초기화
        image.onload = function () {
            cropper = new Cropper(image, {
                aspectRatio: 1, // 정사각형 (1:1)
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                preview: '.crop-preview',
                guides: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        };
    }

    // 크롭 모달 닫기
    function closeCropModal() {
        document.getElementById('cropModal').style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        currentCropIndex = -1;
    }

    // 크롭 적용
    function applyCrop() {
        if (!cropper || currentCropIndex === -1) return;

        // 크롭된 이미지 가져오기
        var canvas = cropper.getCroppedCanvas({
            width: 600,
            height: 600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        var croppedDataUrl = canvas.toDataURL('image/jpeg', 0.9);

        // 파일 정보 업데이트
        uploadedFiles[currentCropIndex].cropped = true;
        uploadedFiles[currentCropIndex].croppedDataUrl = croppedDataUrl;

        // hidden input에 크롭된 이미지 저장
        document.getElementById('cropped_image').value = croppedDataUrl;

        // 썸네일 선택 UI 업데이트
        updateThumbnailSelector();

        // 모달 닫기
        closeCropModal();

        alert('구도 조정이 완료되었습니다!');
    }

    // 모달 외부 클릭 시 닫기
    window.onclick = function (event) {
        var modal = document.getElementById('cropModal');
        if (event.target == modal) {
            closeCropModal();
        }
    }

    <?php if ($write_min || $write_max) { ?>
        // 글자수 제한
        var char_min = parseInt(<?php echo $write_min; ?>);
        var char_max = parseInt(<?php echo $write_max; ?>);
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
        <?php echo $editor_js; ?>

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url + "/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
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
            f.wr_subject.focus();
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

        <?php echo $captcha_js; ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
</script>
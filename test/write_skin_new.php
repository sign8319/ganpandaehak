<?php
if (!defined('_GNUBOARD_'))
    exit;
add_stylesheet('<link rel="stylesheet" href="' . $board_skin_url . '/style.css">', 0);
?>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<style>
    /* 대표 이미지 선택 UI */
    .thumbnail-selector {
        background: linear-gradient(135deg, #fff7ed 0%, #fff 100%);
        border: 3px solid #f97316;
        border-radius: 20px;
        padding: 48px;
        margin: 32px 0;
        box-shadow: 0 10px 40px rgba(249, 115, 22, 0.15);
    }

    .thumbnail-selector h3 {
        font-size: 28px;
        font-weight: 900;
        color: #1f2937;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .thumbnail-selector h3 svg {
        color: #f97316;
        flex-shrink: 0;
    }

    .thumbnail-selector > p {
        color: #6b7280;
        font-size: 15px;
        margin-bottom: 32px;
        line-height: 1.6;
    }

    /* 탭 스타일 */
    .thumbnail-tabs {
        display: flex;
        gap: 12px;
        margin-bottom: 32px;
        border-bottom: 3px solid #e5e7eb;
        padding-bottom: 0;
    }

    .thumbnail-tab {
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 700;
        color: #9ca3af;
        background: transparent;
        border: none;
        border-bottom: 4px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        bottom: -3px;
    }

    .thumbnail-tab:hover {
        color: #f97316;
        background: rgba(249, 115, 22, 0.05);
    }

    .thumbnail-tab.active {
        color: #f97316;
        border-bottom-color: #f97316;
        background: rgba(249, 115, 22, 0.05);
    }

    /* 탭 컨텐츠 */
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* 업로드 박스 */
    .upload-box {
        position: relative;
        background: white;
        border: 3px dashed #d1d5db;
        border-radius: 16px;
        padding: 48px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 320px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .upload-box:hover {
        border-color: #f97316;
        background: #fff7ed;
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(249, 115, 22, 0.2);
    }

    .upload-box.has-image {
        padding: 0;
        border-style: solid;
        border-color: #10b981;
    }

    .upload-box.small {
        min-height: 240px;
        padding: 32px 16px;
    }

    .upload-icon {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.6;
    }

    .upload-box.small .upload-icon {
        font-size: 48px;
    }

    .upload-text {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .upload-box.small .upload-text {
        font-size: 14px;
    }

    .upload-hint {
        font-size: 13px;
        color: #9ca3af;
    }

    .upload-box.small .upload-hint {
        font-size: 11px;
    }

    /* 미리보기 */
    .preview-wrapper {
        display: none;
    }

    .preview-wrapper.active {
        display: block;
    }

    .preview-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto 20px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        border: 3px solid #10b981;
    }

    .preview-container.small {
        max-width: 320px;
    }

    .preview-container img {
        width: 100%;
        height: auto;
        display: block;
    }

    .delete-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .delete-btn:hover {
        background: #ef4444;
        transform: scale(1.15) rotate(90deg);
    }

    /* 액션 버튼 */
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary,
    .btn-secondary {
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }

    .btn-primary.small,
    .btn-secondary.small {
        padding: 10px 20px;
        font-size: 14px;
    }

    /* Before & After 그리드 */
    .ba-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-bottom: 32px;
    }

    @media (max-width: 768px) {
        .ba-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    .ba-upload-section h4 {
        font-size: 18px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 16px;
        text-align: center;
        padding: 12px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-radius: 12px;
        border-left: 4px solid #f97316;
    }

    /* 상세 페이지 표시 옵션 */
    .detail-display-options {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 3px solid #fbbf24;
        border-radius: 16px;
        padding: 24px;
        margin-top: 24px;
    }

    .detail-display-options h4 {
        font-size: 18px;
        font-weight: 800;
        color: #92400e;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .radio-group {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .radio-label {
        flex: 1;
        min-width: 180px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 16px 20px;
        background: white;
        border-radius: 12px;
        border: 3px solid #e5e7eb;
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .radio-label:hover {
        border-color: #f97316;
        background: #fff7ed;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
    }

    .radio-label input[type="radio"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #f97316;
    }

    .radio-label input[type="radio"]:checked ~ span {
        color: #f97316;
    }

    .radio-label.checked {
        border-color: #f97316;
        background: #fff7ed;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }

    .radio-label span {
        font-size: 15px;
        color: #374151;
    }

    /* 크롭 모달 */
    .crop-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
    }

    .crop-modal.active {
        display: flex;
    }

    .crop-modal-content {
        background: white;
        padding: 32px;
        border-radius: 20px;
        width: 90%;
        max-width: 900px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }

    .crop-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }

    .crop-header h2 {
        font-size: 24px;
        font-weight: 900;
        color: #1f2937;
    }

    .close-btn {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.3s ease;
    }

    .close-btn:hover {
        background: #dc2626;
        transform: rotate(90deg) scale(1.1);
    }

    .crop-body {
        flex: 1;
        overflow: auto;
        margin-bottom: 24px;
    }

    .crop-container {
        width: 100%;
        max-height: 500px;
        background: #f9fafb;
        border-radius: 12px;
        overflow: hidden;
    }

    .crop-container img {
        max-width: 100%;
        display: block;
    }

    .crop-footer {
        display: flex;
        justify-content: center;
        gap: 16px;
        padding-top: 24px;
        border-top: 2px solid #e5e7eb;
    }

    /* 드래그 앤 드롭 */
    .upload-box.drag-over {
        border-color: #10b981;
        background: #ecfdf5;
        transform: scale(1.02);
    }

    /* 반응형 */
    @media (max-width: 768px) {
        .thumbnail-selector {
            padding: 24px;
        }

        .thumbnail-selector h3 {
            font-size: 20px;
        }

        .thumbnail-tab {
            padding: 12px 16px;
            font-size: 14px;
        }

        .radio-group {
            flex-direction: column;
        }

        .radio-label {
            min-width: 100%;
        }
    }
</style>

<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="mb-12 text-center">
        <h2 class="text-4xl font-black text-gray-900 mb-3">포트폴리오 등록</h2>
        <p class="text-gray-600 text-lg">나만의 특별한 작품을 등록해주세요</p>
    </div>

    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);"
        method="post" enctype="multipart/form-data" autocomplete="off">
        
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
            if ($is_notice) {
                $option .= '<input type="checkbox" id="notice" name="notice" value="1" ' . $notice_checked . '><label for="notice">공지</label>';
            }
            if ($is_html) {
                if ($is_dhtml_editor) {
                    $option_hidden .= '<input type="hidden" value="html1" name="html">';
                } else {
                    $option .= '<input type="checkbox" id="html" name="html" value="' . $html_value . '" ' . $html_checked . '><label for="html">HTML</label>';
                }
            }
            if ($is_secret) {
                if ($is_admin || $is_secret == 1) {
                    $option .= '<input type="checkbox" id="secret" name="secret" value="secret" ' . $secret_checked . '><label for="secret">비밀글</label>';
                } else {
                    $option_hidden .= '<input type="hidden" name="secret" value="secret">';
                }
            }
            if ($is_mail) {
                $option .= '<input type="checkbox" id="mail" name="mail" value="mail" ' . $recv_email_checked . '><label for="mail">답변메일받기</label>';
            }
        }
        echo $option_hidden;
        ?>

        <!-- 기본 정보 -->
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 border border-gray-100">
            <?php if ($is_category) { ?>
                <div class="mb-6">
                    <label for="ca_name" class="block text-gray-800 text-base font-bold mb-2">분류</label>
                    <select name="ca_name" id="ca_name" required
                        class="w-full border-2 border-gray-300 rounded-xl shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3 text-base">
                        <option value="">분류를 선택하세요</option>
                        <?php echo $category_option ?>
                    </select>
                </div>
            <?php } ?>

            <div class="mb-6">
                <label for="wr_subject" class="block text-gray-800 text-base font-bold mb-2">
                    제목 <span class="text-red-500">*</span>
                </label>
                <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required
                    class="w-full border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3 text-base"
                    placeholder="예: 강남구 신논현역 LED 채널 간판 시공">
            </div>

            <div class="mb-6">
                <label for="wr_content" class="block text-gray-800 text-base font-bold mb-2">
                    내용 <span class="text-red-500">*</span>
                </label>
                <?php echo $editor_html; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="wr_1" class="block text-gray-800 text-base font-bold mb-2">위치</label>
                    <input type="text" name="wr_1" value="<?php echo $write['wr_1'] ?>" id="wr_1"
                        class="w-full border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3 text-base"
                        placeholder="예: 서울 강남구">
                </div>
                <div>
                    <label for="wr_2" class="block text-gray-800 text-base font-bold mb-2">예상 견적 (만원)</label>
                    <input type="text" name="wr_2" value="<?php echo $write['wr_2'] ?>" id="wr_2"
                        class="w-full border-2 border-gray-300 rounded-xl focus:border-orange-500 focus:ring-2 focus:ring-orange-200 p-3 text-base"
                        placeholder="예: 500">
                </div>
            </div>
        </div>

        <!-- 대표 이미지 선택 -->
        <div class="thumbnail-selector">
            <h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                대표 이미지 선택
            </h3>
            <p>업로드 방식을 선택하고 이미지를 등록하세요. 드래그 앤 드롭도 가능합니다!</p>

            <!-- 탭 -->
            <div class="thumbnail-tabs">
                <button type="button" class="thumbnail-tab active" data-tab="fixed" onclick="switchTab('fixed')">
                    📷 고정형 썸네일
                </button>
                <button type="button" class="thumbnail-tab" data-tab="beforeafter" onclick="switchTab('beforeafter')">
                    🔄 Before & After
                </button>
            </div>

            <!-- 고정형 탭 -->
            <div id="fixed-content" class="tab-content active">
                <div class="upload-box" id="fixed-upload-box" 
                     onclick="document.getElementById('fixed-file-input').click()"
                     ondrop="handleDrop(event, 'fixed')" 
                     ondragover="handleDragOver(event)" 
                     ondragleave="handleDragLeave(event)">
                    <div class="upload-icon">📸</div>
                    <p class="upload-text">클릭하거나 이미지를 드래그하세요</p>
                    <p class="upload-hint">권장: 800x800px 이상 | JPG, PNG, WebP</p>
                    <input type="file" id="fixed-file-input" name="bf_file[]" accept="image/*" style="display:none" onchange="handleFixedUpload(this)">
                </div>
                
                <div id="fixed-preview" class="preview-wrapper">
                    <div class="preview-container">
                        <img id="fixed-preview-img" src="" alt="미리보기">
                        <button type="button" class="delete-btn" onclick="deleteImage('fixed')" title="삭제">✕</button>
                    </div>
                    <div class="action-buttons">
                        <button type="button" class="btn-secondary" onclick="document.getElementById('fixed-file-input').click()">
                            🔄 교체
                        </button>
                        <button type="button" class="btn-primary" onclick="openCropModal('fixed')">
                            ✂️ 구도 조정
                        </button>
                    </div>
                </div>
            </div>

            <!-- Before & After 탭 -->
            <div id="beforeafter-content" class="tab-content">
                <div class="ba-grid">
                    <!-- Before -->
                    <div class="ba-upload-section">
                        <h4>📌 Before (작업 전)</h4>
                        <div class="upload-box small" id="before-upload-box" 
                             onclick="document.getElementById('before-file-input').click()"
                             ondrop="handleDrop(event, 'before')" 
                             ondragover="handleDragOver(event)" 
                             ondragleave="handleDragLeave(event)">
                            <div class="upload-icon">📷</div>
                            <p class="upload-text">Before 이미지</p>
                            <p class="upload-hint">작업 전 사진</p>
                            <input type="file" id="before-file-input" name="bf_file[]" accept="image/*" style="display:none" onchange="handleBeforeUpload(this)">
                        </div>
                        
                        <div id="before-preview" class="preview-wrapper">
                            <div class="preview-container small">
                                <img id="before-preview-img" src="" alt="Before">
                                <button type="button" class="delete-btn" onclick="deleteImage('before')" title="삭제">✕</button>
                            </div>
                            <div class="action-buttons">
                                <button type="button" class="btn-secondary small" onclick="document.getElementById('before-file-input').click()">
                                    🔄 교체
                                </button>
                                <button type="button" class="btn-primary small" onclick="openCropModal('before')">
                                    ✂️ 구도 조정
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- After -->
                    <div class="ba-upload-section">
                        <h4>✨ After (작업 후)</h4>
                        <div class="upload-box small" id="after-upload-box" 
                             onclick="document.getElementById('after-file-input').click()"
                             ondrop="handleDrop(event, 'after')" 
                             ondragover="handleDragOver(event)" 
                             ondragleave="handleDragLeave(event)">
                            <div class="upload-icon">📷</div>
                            <p class="upload-text">After 이미지</p>
                            <p class="upload-hint">작업 후 사진</p>
                            <input type="file" id="after-file-input" name="bf_file[]" accept="image/*" style="display:none" onchange="handleAfterUpload(this)">
                        </div>
                        
                        <div id="after-preview" class="preview-wrapper">
                            <div class="preview-container small">
                                <img id="after-preview-img" src="" alt="After">
                                <button type="button" class="delete-btn" onclick="deleteImage('after')" title="삭제">✕</button>
                            </div>
                            <div class="action-buttons">
                                <button type="button" class="btn-secondary small" onclick="document.getElementById('after-file-input').click()">
                                    🔄 교체
                                </button>
                                <button type="button" class="btn-primary small" onclick="openCropModal('after')">
                                    ✂️ 구도 조정
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상세 페이지 표시 옵션 -->
                <div class="detail-display-options">
                    <h4>📺 상세 페이지 표시 방식</h4>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="detail_display" value="before">
                            <span>Before만 표시</span>
                        </label>
                        <label class="radio-label checked">
                            <input type="radio" name="detail_display" value="after" checked>
                            <span>After만 표시 (추천)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="detail_display" value="auto">
                            <span>자동 전환 (2.5초)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" name="thumbnail_type" id="thumbnail_type" value="fixed">
            <input type="hidden" name="cropped_image_fixed" id="cropped_image_fixed" value="">
            <input type="hidden" name="cropped_image_before" id="cropped_image_before" value="">
            <input type="hidden" name="cropped_image_after" id="cropped_image_after" value="">
        </div>

        <!-- 제출 버튼 -->
        <div class="flex justify-center gap-6 mt-12">
            <a href="<?php echo get_pretty_url($bo_table); ?>"
                class="px-10 py-4 bg-gray-200 text-gray-700 rounded-xl text-lg font-bold hover:bg-gray-300 transition-all transform hover:scale-105">
                취소
            </a>
            <button type="submit" id="btn_submit"
                class="px-12 py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl text-lg font-bold hover:from-orange-600 hover:to-red-600 shadow-xl transition-all transform hover:scale-105">
                ✨ 등록하기
            </button>
        </div>
    </form>
</div>

<!-- 크롭 모달 -->
<div id="cropModal" class="crop-modal">
    <div class="crop-modal-content">
        <div class="crop-header">
            <h2>✂️ 이미지 구도 조정</h2>
            <button type="button" class="close-btn" onclick="closeCropModal()">✕</button>
        </div>
        <div class="crop-body">
            <div class="crop-container">
                <img id="crop-image" src="" alt="크롭할 이미지">
            </div>
        </div>
        <div class="crop-footer">
            <button type="button" class="btn-secondary" onclick="closeCropModal()">
                취소
            </button>
            <button type="button" class="btn-primary" onclick="applyCrop()">
                ✅ 적용하기
            </button>
        </div>
    </div>
</div>

<script>
// 전역 변수
let currentCropType = '';
let cropper = null;
let uploadedFiles = {
    fixed: null,
    before: null,
    after: null
};

// 탭 전환
function switchTab(tabName) {
    document.querySelectorAll('.thumbnail-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`${tabName}-content`).classList.add('active');
    
    document.getElementById('thumbnail_type').value = tabName;
}

// 드래그 앤 드롭
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e, type) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (file.type.startsWith('image/')) {
            processImageFile(file, type);
        } else {
            alert('이미지 파일만 업로드 가능합니다.');
        }
    }
}

// 파일 업로드 핸들러
function handleFixedUpload(input) {
    if (input.files && input.files[0]) {
        processImageFile(input.files[0], 'fixed');
    }
}

function handleBeforeUpload(input) {
    if (input.files && input.files[0]) {
        processImageFile(input.files[0], 'before');
    }
}

function handleAfterUpload(input) {
    if (input.files && input.files[0]) {
        processImageFile(input.files[0], 'after');
    }
}

function processImageFile(file, type) {
    if (file.size > 10 * 1024 * 1024) {
        alert('파일 크기는 10MB 이하여야 합니다.');
        return;
    }
    
    uploadedFiles[type] = file;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const imgElement = document.getElementById(`${type}-preview-img`);
        imgElement.src = e.target.result;
        
        document.getElementById(`${type}-upload-box`).style.display = 'none';
        document.getElementById(`${type}-preview`).classList.add('active');
    };
    reader.readAsDataURL(file);
}

// 이미지 삭제
function deleteImage(type) {
    if (confirm('이미지를 삭제하시겠습니까?')) {
        uploadedFiles[type] = null;
        document.getElementById(`${type}-file-input`).value = '';
        document.getElementById(`${type}-preview-img`).src = '';
        document.getElementById(`${type}-preview`).classList.remove('active');
        document.getElementById(`${type}-upload-box`).style.display = 'flex';
        document.getElementById(`cropped_image_${type}`).value = '';
    }
}

// 크롭 모달
function openCropModal(type) {
    currentCropType = type;
    const imgSrc = document.getElementById(`${type}-preview-img`).src;
    
    if (!imgSrc) {
        alert('먼저 이미지를 업로드해주세요.');
        return;
    }
    
    const cropImage = document.getElementById('crop-image');
    cropImage.src = imgSrc;
    
    document.getElementById('cropModal').classList.add('active');
    
    setTimeout(() => {
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }, 100);
}

function closeCropModal() {
    document.getElementById('cropModal').classList.remove('active');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

function applyCrop() {
    if (!cropper) return;
    
    const canvas = cropper.getCroppedCanvas({
        width: 800,
        height: 800,
        imageSmoothingQuality: 'high'
    });
    
    canvas.toBlob((blob) => {
        const croppedFile = new File([blob], `cropped_${currentCropType}.jpg`, {
            type: 'image/jpeg'
        });
        
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedFile);
        document.getElementById(`${currentCropType}-file-input`).files = dataTransfer.files;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(`${currentCropType}-preview-img`).src = e.target.result;
        };
        reader.readAsDataURL(croppedFile);
        
        document.getElementById(`cropped_image_${currentCropType}`).value = canvas.toDataURL('image/jpeg', 0.9);
        
        closeCropModal();
    }, 'image/jpeg', 0.9);
}

// 라디오 버튼 스타일
document.addEventListener('DOMContentLoaded', function() {
    const radioLabels = document.querySelectorAll('.radio-label');
    radioLabels.forEach(label => {
        label.addEventListener('click', function() {
            radioLabels.forEach(l => l.classList.remove('checked'));
            this.classList.add('checked');
        });
    });
});

// 폼 제출 검증
function fwrite_submit(f) {
    const thumbnailType = document.getElementById('thumbnail_type').value;
    
    if (thumbnailType === 'fixed') {
        if (!uploadedFiles.fixed) {
            alert('고정형 썸네일 이미지를 업로드해주세요.');
            return false;
        }
    } else if (thumbnailType === 'beforeafter') {
        if (!uploadedFiles.before || !uploadedFiles.after) {
            alert('Before와 After 이미지를 모두 업로드해주세요.');
            return false;
        }
    }
    
    if (typeof(f.wr_subject) != 'undefined') {
        if (f.wr_subject.value == "") {
            alert("제목을 입력해주세요.");
            f.wr_subject.focus();
            return false;
        }
    }
    
    <?php echo $editor_js; ?>
    
    return true;
}
</script>

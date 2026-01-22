/**
 * Admin Quote Step 1 (현장측정) JavaScript
 * 파일: admin_quote_step1.js
 */

// Global State
let hasUnsavedChanges = false;
let currentSignTypes = [];
let confirmCallback = null;
let pendingNavUrl = null;

// ============================================================================
// Initialization
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    initFormTracking();
    initStatusHandlers();
    initSignTypeInputHandlers();
    initModalBindings();
});

function initFormTracking() {
    const form = document.getElementById('step1Form');
    if (form) {
        form.addEventListener('input', function () {
            hasUnsavedChanges = true;
        });
    }
}

function initStatusHandlers() {
    const input = document.getElementById('qa_status_input');
    const select = document.getElementById('qa_status_select');

    if (input && select) {
        input.addEventListener('blur', function () {
            if (!input.value.trim()) {
                input.classList.add('hidden');
                select.classList.remove('hidden');
                select.value = select.options[0].value;
                input.value = select.value;
            }
        });

        input.addEventListener('click', function () {
            if (!input.classList.contains('hidden')) {
                input.select();
            }
        });
    }
}

function initSignTypeInputHandlers() {
    document.addEventListener('focusout', function (e) {
        if (e.target.classList.contains('sign-type-input')) {
            const input = e.target;
            const container = input.parentElement;
            const select = container.querySelector('.sign-type-select');

            if (!input.value.trim()) {
                input.classList.add('hidden');
                select.classList.remove('hidden');
                select.value = '';
            }
        }
    });
}

function initModalBindings() {
    const modal = document.getElementById('back_confirm_modal_safe');
    if (modal) {
        document.body.appendChild(modal);
    }

    const btnConfirm = document.getElementById('btn_confirm_yes');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            if (confirmCallback) confirmCallback();
            close_confirm_modal();
        });
    }
}

// ============================================================================
// Store Search Functions
// ============================================================================

function searchStore() {
    const keyword = document.getElementById('store_search').value.trim();
    if (!keyword) {
        alert('검색어를 입력하세요');
        return;
    }

    fetch('?w=ajax_search_store&keyword=' + encodeURIComponent(keyword))
        .then(res => res.json())
        .then(data => {
            const resultsDiv = document.getElementById('store_results');
            if (data.stores.length === 0) {
                resultsDiv.innerHTML = '<div class="text-sm text-gray-500 p-3">검색 결과가 없습니다</div>';
                return;
            }

            let html = '<div class="border border-gray-200 rounded-lg divide-y">';
            data.stores.forEach(store => {
                html += `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer" onclick="selectStore(${store.st_id}, '${store.st_name}', '${store.st_addr}')">
                        <div class="font-semibold text-gray-800">${store.st_name}</div>
                        <div class="text-sm text-gray-600">${store.st_addr}</div>
                    </div>
                `;
            });
            html += '</div>';
            resultsDiv.innerHTML = html;
        });
}

function selectStore(id, name, addr) {
    document.getElementById('qa_store_id').value = id;
    document.getElementById('selected_store').innerHTML = `
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
            <div class="font-semibold text-orange-800">${name}</div>
            <div class="text-sm text-gray-600">${addr}</div>
        </div>
    `;
    document.getElementById('store_results').innerHTML = '';
    document.getElementById('store_search').value = '';
    hasUnsavedChanges = true;
}

// ============================================================================
// Measure Row Management
// ============================================================================

function addMeasureRow() {
    const container = document.getElementById('measure_items');
    const index = container.querySelectorAll('.measure-row').length;

    const html = `
        <div class="measure-row border border-gray-200 rounded-lg p-4 mb-3 bg-gray-50" data-index="${index}">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 w-full">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            간판 종류
                            <button type="button" onclick="openSignTypeModal()"
                                class="ml-2 text-orange-600 hover:text-orange-700" title="간판 종류 관리">
                                <i class="fas fa-cog"></i>
                            </button>
                        </label>
                        <div class="relative">
                            <input type="text" name="qm_type[]" 
                                class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base focus:ring-2 focus:ring-orange-500"
                                placeholder="직접 입력 (예: 스타렉스 7호차)">
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">가로(W)</label>
                        <input type="text" name="qm_width[]" placeholder="450" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">세로(H)</label>
                        <input type="text" name="qm_height[]" placeholder="450" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">수량</label>
                        <input type="number" name="qm_qty[]" value="1" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">메모</label>
                        <input type="text" name="qm_memo[]" placeholder="메모" class="w-full px-3 py-2.5 md:py-2 border border-gray-300 rounded text-sm md:text-base">
                    </div>
                </div>
            <button type="button" onclick="removeMeasureRow(this)" class="w-full md:w-auto mt-2 md:mt-6 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                <i class="fas fa-trash"></i> <span class="md:hidden">삭제</span>
            </button>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">사진 1</label>
                    <input type="hidden" name="qm_img1_prev[]" value="">
                    <input type="hidden" name="qm_img1_del[]" value="0" class="img-del-flag">
                    <label class="cursor-pointer block">
                        <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-camera mr-2"></i>
                            <span>파일 선택</span>
                        </span>
                        <input type="file" name="qm_img1[]" accept="image/*" class="hidden" onchange="handleImagePreview(this)">
                    </label>
                    <div class="preview-container mt-2 relative inline-block"></div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">사진 2</label>
                    <input type="hidden" name="qm_img2_prev[]" value="">
                    <input type="hidden" name="qm_img2_del[]" value="0" class="img-del-flag">
                    <label class="cursor-pointer block">
                        <span class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <i class="fas fa-camera mr-2"></i>
                            <span>파일 선택</span>
                        </span>
                        <input type="file" name="qm_img2[]" accept="image/*" class="hidden" onchange="handleImagePreview(this)">
                    </label>
                    <div class="preview-container mt-2 relative inline-block"></div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    hasUnsavedChanges = true;
}

function removeMeasureRow(btn) {
    open_confirm('이 항목을 삭제하시겠습니까?', function () {
        btn.closest('.measure-row').remove();
        hasUnsavedChanges = true;
    });
}

// ============================================================================
// Image Handling
// ============================================================================

function handleImagePreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const parentDiv = input.closest('div');
            let previewContainer = parentDiv.querySelector('.preview-container');

            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.className = 'preview-container mt-2 relative inline-block';
                parentDiv.appendChild(previewContainer);
            }

            previewContainer.innerHTML = '';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'w-20 h-20 object-cover rounded border';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs hover:bg-red-600 flex items-center justify-center';
            btn.innerHTML = '×';
            btn.onclick = function () { removePreview(this); };

            previewContainer.appendChild(img);
            previewContainer.appendChild(btn);
        }

        reader.readAsDataURL(input.files[0]);
        hasUnsavedChanges = true;
    }
}

function removePreview(btn) {
    const previewContainer = btn.parentElement;
    const parentDiv = previewContainer.parentElement;
    const input = parentDiv.querySelector('input[type="file"]');

    if (input) input.value = '';
    previewContainer.innerHTML = '';
    hasUnsavedChanges = true;
}

function deleteImage(btn) {
    open_confirm('이미지를 삭제하시겠습니까?', function () {
        const imgContainer = btn.closest('div').closest('div');
        const delInput = imgContainer.querySelector('.img-del-flag');
        if (delInput) delInput.value = '1';

        const fileInput = imgContainer.querySelector('input[type="file"]');
        if (fileInput) fileInput.value = '';

        btn.parentElement.remove();
        hasUnsavedChanges = true;
    });
}

// ============================================================================
// Modal Functions
// ============================================================================

function open_confirm(msg, callback) {
    document.getElementById('confirm_msg').innerText = msg;
    document.getElementById('custom_confirm_modal').classList.remove('hidden');
    confirmCallback = callback;
}

function close_confirm_modal() {
    document.getElementById('custom_confirm_modal').classList.add('hidden');
    confirmCallback = null;
}

function open_save_confirm() {
    document.getElementById('save_confirm_modal').classList.remove('hidden');
}

function close_save_confirm() {
    document.getElementById('save_confirm_modal').classList.add('hidden');
}

function execute_save() {
    document.getElementById('save_confirm_modal').classList.add('hidden');
    hasUnsavedChanges = false;
    document.getElementById('step1Form').submit();
}

function close_back_confirm() {
    document.getElementById('back_confirm_modal_safe').classList.add('hidden');
    pendingNavUrl = null;
}

function confirm_back_save() {
    document.getElementById('back_confirm_modal_safe').classList.add('hidden');
    hasUnsavedChanges = false;

    const form = document.getElementById('step1Form');

    if (pendingNavUrl === 'NEXT_STEP_2') {
        document.getElementById('next_step').value = '2';
    } else if (pendingNavUrl) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'redirect_url';
        input.value = pendingNavUrl;
        form.appendChild(input);
    }

    form.submit();
}

function confirm_back_nosave() {
    document.getElementById('back_confirm_modal_safe').classList.add('hidden');
    hasUnsavedChanges = false;

    if (pendingNavUrl === 'NEXT_STEP_2') {
        const qa_id = document.querySelector('input[name="qa_id"]').value;
        if (qa_id && qa_id != 0) {
            location.href = './admin_quote.php?w=form&qa_id=' + qa_id + '&step=2';
        } else {
            alert('저장되지 않은 견적은 다음 단계로 이동할 수 없습니다.');
        }
    } else if (pendingNavUrl) {
        location.href = pendingNavUrl;
    }
}

// ============================================================================
// Navigation Functions
// ============================================================================

function navigateToPage(url) {
    if (hasUnsavedChanges) {
        pendingNavUrl = url;
        document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
    } else {
        location.href = url;
    }
}

function go_list_safe() {
    if (hasUnsavedChanges) {
        pendingNavUrl = './admin_quote.php';
        document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
    } else {
        location.href = './admin_quote.php';
    }
}

function goToStep2() {
    if (hasUnsavedChanges) {
        pendingNavUrl = 'NEXT_STEP_2';
        document.getElementById('back_confirm_modal_safe').classList.remove('hidden');
    } else {
        document.getElementById('next_step').value = '2';
        document.getElementById('step1Form').submit();
    }
}

function beforeSubmit() {
    hasUnsavedChanges = false;
    return true;
}

// ============================================================================
// Utility Functions
// ============================================================================

function formatPhoneNumber(input) {
    const numbers = input.value.replace(/[^0-9]/g, '');
    let formatted = '';

    if (numbers.startsWith('02')) {
        if (numbers.length < 3) {
            formatted = numbers;
        } else if (numbers.length < 6) {
            formatted = numbers.slice(0, 2) + '-' + numbers.slice(2);
        } else if (numbers.length < 10) {
            formatted = numbers.slice(0, 2) + '-' + numbers.slice(2, 5) + '-' + numbers.slice(5);
        } else {
            formatted = numbers.slice(0, 2) + '-' + numbers.slice(2, 6) + '-' + numbers.slice(6, 10);
        }
    } else if (numbers.startsWith('01')) {
        if (numbers.length < 4) {
            formatted = numbers;
        } else if (numbers.length < 8) {
            formatted = numbers.slice(0, 3) + '-' + numbers.slice(3);
        } else {
            formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
        }
    } else {
        if (numbers.length < 4) {
            formatted = numbers;
        } else if (numbers.length < 7) {
            formatted = numbers.slice(0, 3) + '-' + numbers.slice(3);
        } else if (numbers.length < 11) {
            formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 6) + '-' + numbers.slice(6);
        } else {
            formatted = numbers.slice(0, 3) + '-' + numbers.slice(3, 7) + '-' + numbers.slice(7, 11);
        }
    }

    input.value = formatted;
}

function handle_status_change(select) {
    const input = document.getElementById('qa_status_input');
    const value = select.value;

    if (value === '__custom__') {
        select.classList.add('hidden');
        input.classList.remove('hidden');
        input.value = '';
        input.focus();
    } else {
        input.value = value;
    }
}

// ============================================================================
// Daum Postcode API
// ============================================================================

const element_wrap = document.getElementById('wrap');

function foldDaumPostcode() {
    if (element_wrap) {
        element_wrap.style.display = 'none';
    }
}

function execDaumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            const addr = data.address;
            document.getElementById('qa_client_addr').value = addr;
            document.getElementById('qa_client_addr2').focus();
            foldDaumPostcode();
        },
        width: '100%',
        height: '100%'
    }).embed(element_wrap);
    element_wrap.style.display = 'block';
}

// ============================================================================
// Sign Type Management
// ============================================================================

function openSignTypeModal() {
    document.getElementById('signTypeModal').classList.remove('hidden');
    loadSignTypes();
}

function closeSignTypeModal() {
    document.getElementById('signTypeModal').classList.add('hidden');
}

function loadSignTypes() {
    fetch('?w=ajax_sign_types', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=list'
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentSignTypes = data.types;
                renderSignTypeList();
                updateAllSignTypeSelects(data.types);
            }
        });
}

function renderSignTypeList() {
    const container = document.getElementById('signTypeList');
    if (currentSignTypes.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">등록된 간판 종류가 없습니다</div>';
        return;
    }

    let html = '';
    currentSignTypes.forEach((type, index) => {
        html += `
        <div class="flex items-center justify-between p-3 border-b border-gray-200 hover:bg-gray-50">
            <span class="font-medium text-gray-800">${type}</span>
            <div class="flex gap-2">
                <button type="button" onclick="editSignType(${index}, '${type}')" 
                    class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-edit"></i> 수정
                </button>
                <button type="button" onclick="deleteSignType('${type}')" 
                    class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-trash"></i> 삭제
                </button>
            </div>
        </div>
        `;
    });
    container.innerHTML = html;
}

function addSignType() {
    const input = document.getElementById('newSignTypeName');
    const typeName = input.value.trim();

    if (!typeName) {
        alert('간판 종류명을 입력하세요');
        return;
    }

    fetch('?w=ajax_sign_types', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&type_name=${encodeURIComponent(typeName)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadSignTypes();
            } else {
                alert(data.message || '추가 실패');
            }
        });
}

function editSignType(index, oldName) {
    const newName = prompt('새 이름을 입력하세요', oldName);
    if (!newName || newName === oldName) return;

    fetch('?w=ajax_sign_types', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&old_name=${encodeURIComponent(oldName)}&new_name=${encodeURIComponent(newName)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadSignTypes();
            } else {
                alert(data.message || '수정 실패');
            }
        });
}

function deleteSignType(typeName) {
    if (!confirm(`"${typeName}"을(를) 삭제하시겠습니까?`)) return;

    fetch('?w=ajax_sign_types', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&type_name=${encodeURIComponent(typeName)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadSignTypes();
            } else {
                alert(data.message || '삭제 실패');
            }
        });
}

function updateAllSignTypeSelects(types) {
    const selects = document.querySelectorAll('.sign-type-select');
    selects.forEach(select => {
        const currentValue = select.value;
        let html = '<option value="">선택</option>';
        types.forEach(type => {
            html += `<option value="${type}">${type}</option>`;
        });
        html += '<option value="__custom__">직접입력</option>';
        select.innerHTML = html;

        if (currentValue && types.includes(currentValue)) {
            select.value = currentValue;
        }
    });
}

function handleSignTypeChange(select) {
    const container = select.parentElement;
    const input = container.querySelector('.sign-type-input');

    if (select.value === '__custom__') {
        select.classList.add('hidden');
        input.classList.remove('hidden');
        input.value = '';
        input.focus();
    } else {
        input.value = select.value;
    }
}

// ============================================================================
// Prevent Accidental Navigation
// ============================================================================

window.addEventListener('beforeunload', function (e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});

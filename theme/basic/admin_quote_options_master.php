<?php
include_once('./_common.php');
include_once(G5_THEME_PATH.'/head.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}
?>

<div class="flex h-screen bg-gray-100">
    <!-- 사이드바 -->
    <?php include_once(G5_THEME_PATH.'/basic/admin_project_sidebar.php'); ?>

    <!-- 메인 컨텐츠 -->
    <div class="flex-1 overflow-auto">
        <div class="p-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <!-- 헤더 -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">옵션 마스터 관리</h2>
                        <p class="text-gray-600 mt-1">모든 제품에서 공통으로 사용할 옵션을 등록하고 관리합니다.</p>
                    </div>
                    <button onclick="openOptionModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        옵션 추가
                    </button>
                </div>

                <!-- 옵션 목록 테이블 -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">정렬</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">그룹</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">옵션명</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">단가</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">유형</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                            </tr>
                        </thead>
                        <tbody id="options-list" class="bg-white divide-y divide-gray-200">
                            <!-- JS로 로드됨 -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 옵션 추가/수정 모달 -->
<div id="optionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800" id="optionModalTitle">옵션 추가</h3>
            <button onclick="closeOptionModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="optionForm" class="p-6">
            <input type="hidden" id="option_id" name="option_id">
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">그룹명</label>
                        <input type="text" name="group_name" id="option_group_name" list="group_list"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="예: 후가공">
                        <datalist id="group_list">
                            <option value="기본">
                            <option value="가공">
                            <option value="부자재">
                            <option value="시공">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">옵션명</label>
                        <input type="text" name="name" id="option_name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="예: 타공">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">단가 (원)</label>
                        <input type="number" name="price" id="option_price" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                               placeholder="500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">단위 유형</label>
                        <select name="unit_type" id="option_unit_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="fixed">고정가 (개당)</option>
                            <option value="area">면적 비례 (㎡당)</option>
                            <option value="length">길이 비례 (m당)</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">정렬 순서</label>
                        <input type="number" name="sort_order" id="option_sort_order" value="0" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">고급 설정 (수량 할인/할증)</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-blue-700 mb-1">무료 수량</label>
                            <input type="number" name="free_qty" id="option_free_qty" value="0"
                                   class="w-full px-2 py-1 text-sm border border-blue-200 rounded focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-blue-700 mb-1">추가 단가</label>
                            <input type="number" name="qty_unit_price" id="option_qty_unit_price" value="0"
                                   class="w-full px-2 py-1 text-sm border border-blue-200 rounded focus:ring-blue-500">
                        </div>
                         <div>
                            <label class="block text-xs text-blue-700 mb-1">기본 수량</label>
                            <input type="number" name="default_qty" id="option_default_qty" value="0"
                                   class="w-full px-2 py-1 text-sm border border-blue-200 rounded focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-blue-700 mb-1">할인율 (%)</label>
                            <input type="number" name="discount" id="option_discount" value="0" step="0.1"
                                   class="w-full px-2 py-1 text-sm border border-blue-200 rounded focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeOptionModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-medium">취소</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium shadow-sm">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadOptions();
});

function loadOptions() {
    fetch('admin_quote_api.php?action=get_options').then(r => r.json()).then(data => {
        const tbody = document.getElementById('options-list');
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">등록된 옵션이 없습니다</td></tr>';
            return;
        }
        
        data.forEach(opt => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors border-b border-gray-100';
            
            let typeLabel = '고정가';
            if (opt.unit_type === 'area') typeLabel = '면적비례';
            else if (opt.unit_type === 'length') typeLabel = '길이비례';

            tr.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-500">${opt.sort_order}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-800"><span class="px-2 py-1 bg-gray-100 rounded text-xs">${opt.group_name || '기본'}</span></td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${opt.name}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${parseInt(opt.price).toLocaleString()}원</td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">${typeLabel}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex justify-center gap-2">
                        <button onclick="editOption(${opt.id})" class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" title="수정">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button onclick="deleteOption(${opt.id})" class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors" title="삭제">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function openOptionModal(id = null) {
    const modal = document.getElementById('optionModal');
    const form = document.getElementById('optionForm');
    modal.classList.remove('hidden');
    
    if (id) {
        document.getElementById('optionModalTitle').textContent = '옵션 수정';
        fetch(`admin_quote_api.php?action=get_option&id=${id}`).then(r => r.json()).then(data => {
            document.getElementById('option_id').value = data.id;
            document.getElementById('option_group_name').value = data.group_name || '기본';
            document.getElementById('option_name').value = data.name;
            document.getElementById('option_price').value = data.price;
            document.getElementById('option_unit_type').value = data.unit_type;
            document.getElementById('option_sort_order').value = data.sort_order;
            document.getElementById('option_free_qty').value = data.free_qty || 0;
            document.getElementById('option_qty_unit_price').value = data.qty_unit_price || 0;
            document.getElementById('option_default_qty').value = data.default_qty || 0;
            document.getElementById('option_discount').value = data.discount || 0;
        });
    } else {
        document.getElementById('optionModalTitle').textContent = '옵션 추가';
        form.reset();
        document.getElementById('option_id').value = '';
        document.getElementById('option_group_name').value = '기본';
    }
}

function closeOptionModal() {
    document.getElementById('optionModal').classList.add('hidden');
}

document.getElementById('optionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const id = document.getElementById('option_id').value;
    fd.append('action', id ? 'update_option' : 'add_option');
    
    fetch('admin_quote_api.php', {
        method: 'POST',
        body: fd
    }).then(r => r.json()).then(data => {
        if (data.success) {
            closeOptionModal();
            loadOptions();
        } else {
            alert('오류: ' + data.message);
        }
    });
});

function editOption(id) {
    openOptionModal(id);
}

function deleteOption(id) {
    if (!confirm('정말 이 옵션을 삭제하시겠습니까? 연결된 모든 제품에서 옵션이 제거됩니다.')) return;
    
    const fd = new FormData();
    fd.append('action', 'delete_option');
    fd.append('id', id);
    
    fetch('admin_quote_api.php', {
        method: 'POST',
        body: fd
    }).then(r => r.json()).then(data => {
        if (data.success) {
            loadOptions();
        } else {
            alert('오류: ' + data.message);
        }
    });
}
</script>

<?php include_once(G5_THEME_PATH.'/basic/tail.php'); ?>
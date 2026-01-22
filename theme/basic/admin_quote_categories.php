<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

    <!-- Add New Category Button -->
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-900">카테고리 목록</h2>
        <button onclick="openCategoryModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            + 카테고리 추가
        </button>
    </div>

    <!-- Categories Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">순서</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">아이콘</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">카테고리명</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">키값</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">관리</th>
                </tr>
            </thead>
            <tbody id="categories-list" class="divide-y divide-gray-200">
                <!-- Dynamic Content -->
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <div class="animate-pulse">로딩 중...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Category Modal -->
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900" id="modalTitle">카테고리 추가</h3>
            <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <form id="categoryForm" class="p-6 space-y-4">
            <input type="hidden" id="category_id" name="category_id">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">카테고리명 *</label>
                <input type="text" id="category_name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">키값 (영문) *</label>
                <input type="text" id="category_key" name="category_key" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="예: flex, channel">
                <p class="text-xs text-gray-500 mt-1">영문 소문자, 숫자, 언더스코어(_)만 사용 가능</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">아이콘</label>
                <div class="flex gap-3">
                    <input type="text" id="category_icon" name="icon" value="📦"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="이모지 입력">
                    <button type="button" onclick="openIconPicker()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        선택
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">이모지를 입력하거나 선택 버튼을 클릭하세요</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">정렬 순서</label>
                <input type="number" id="category_order" name="sort_order" value="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">낮은 숫자가 먼저 표시됩니다</p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="category_active" name="is_active" checked
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="category_active" class="ml-2 text-sm text-gray-700">활성화</label>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeCategoryModal()"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                    취소
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    저장
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Icon Picker Modal -->
<div id="iconPickerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900">아이콘 선택</h3>
            <button onclick="closeIconPicker()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-8 gap-2" id="iconGrid">
                <!-- Icons will be populated by JS -->
            </div>
        </div>
    </div>
</div>

<script>
    const icons = ['🏴', '⬜', '🔤', '🖨️', '🏪', '🔧', '📦', '🎨', '✨', '🔨', '🛠️', '⚙️', '📐', '📏', '✂️', '🖌️', '🎯', '💡', '🔥', '⭐', '🌟', '💫', '🎪', '🏭', '🏗️', '🏢', '🏬', '🏪', '🏡', '🏠', '🎭', '🎪', '🎨', '🖼️', '🎬', '📸', '📷', '📹', '🎥', '📺', '📻', '📡', '🔔', '🔕', '📢', '📣', '📯', '🎺', '🎸', '🎹', '🎼'];

    // Load categories on page load
    document.addEventListener('DOMContentLoaded', loadCategories);

    function loadCategories() {
        // AJAX call to load categories
        fetch('admin_quote_api.php?action=get_categories')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('categories-list');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">등록된 카테고리가 없습니다</td></tr>';
                    return;
                }

                data.forEach(cat => {
                    const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-1">
                                <button onclick="moveCategory(${cat.id}, 'up')" class="text-gray-400 hover:text-blue-600">▲</button>
                                <button onclick="moveCategory(${cat.id}, 'down')" class="text-gray-400 hover:text-blue-600">▼</button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-2xl">${cat.icon || '📦'}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${cat.name}</td>
                        <td class="px-4 py-3 text-sm text-gray-500"><code>${cat.category_key}</code></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full ${cat.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${cat.is_active ? '활성' : '비활성'}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <button onclick="editCategory(${cat.id})" class="text-blue-600 hover:text-blue-800 text-sm">수정</button>
                                <button onclick="deleteCategory(${cat.id})" class="text-red-600 hover:text-red-800 text-sm">삭제</button>
                            </div>
                        </td>
                    </tr>
                `;
                    tbody.innerHTML += row;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('카테고리 로딩 실패');
            });
    }

    function openCategoryModal(id = null) {
        document.getElementById('categoryModal').classList.remove('hidden');

        if (id) {
            // Edit mode
            document.getElementById('modalTitle').textContent = '카테고리 수정';
            // Load category data
            fetch(`admin_quote_api.php?action=get_category&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('category_id').value = data.id;
                    document.getElementById('category_name').value = data.name;
                    document.getElementById('category_key').value = data.category_key;
                    document.getElementById('category_icon').value = data.icon || '📦';
                    document.getElementById('category_order').value = data.sort_order;
                    document.getElementById('category_active').checked = data.is_active == 1;
                });
        } else {
            // Add mode
            document.getElementById('modalTitle').textContent = '카테고리 추가';
            document.getElementById('categoryForm').reset();
            document.getElementById('category_id').value = '';
        }
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        document.getElementById('categoryForm').reset();
    }

    function openIconPicker() {
        document.getElementById('iconPickerModal').classList.remove('hidden');
        const grid = document.getElementById('iconGrid');
        grid.innerHTML = '';

        icons.forEach(icon => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'text-2xl p-2 hover:bg-gray-100 rounded cursor-pointer';
            btn.textContent = icon;
            btn.onclick = () => selectIcon(icon);
            grid.appendChild(btn);
        });
    }

    function closeIconPicker() {
        document.getElementById('iconPickerModal').classList.add('hidden');
    }

    function selectIcon(icon) {
        document.getElementById('category_icon').value = icon;
        closeIconPicker();
    }

    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = document.getElementById('category_id').value;
        formData.append('action', id ? 'update_category' : 'add_category');

        fetch('admin_quote_api.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeCategoryModal();
                    loadCategories();
                } else {
                    alert('오류: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('저장 실패');
            });
    });

    function editCategory(id) {
        openCategoryModal(id);
    }

    function deleteCategory(id) {
        if (!confirm('정말 삭제하시겠습니까?')) return;

        fetch('admin_quote_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete_category&id=${id}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadCategories();
                } else {
                    alert('오류: ' + data.message);
                }
            });
    }

    function moveCategory(id, direction) {
        fetch('admin_quote_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=move_category&id=${id}&direction=${direction}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCategories();
                }
            });
    }
</script>
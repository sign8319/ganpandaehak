<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-gray-900">제품 목록</h2>
            <p class="text-sm text-gray-500 mt-1">서브카테고리별로 제품을 관리합니다</p>
        </div>
        <button onclick="openProductModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">+ 제품 추가</button>
    </div>
    <div class="mb-4 flex gap-3">
        <select id="filterCategory" onchange="loadProducts()"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">전체 카테고리</option>
        </select>
        <select id="filterSubcategory" onchange="loadProducts()"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">전체 서브카테고리</option>
        </select>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">카테고리</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">서브카테고리</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">제품명</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">단가</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">계산방식</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">올림</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">관리</th>
                </tr>
            </thead>
            <tbody id="products-list" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <div class="animate-pulse">로딩 중...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="productModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full my-8">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900" id="productModalTitle">제품 추가</h3>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form id="productForm" class="p-6 space-y-4">
            <input type="hidden" id="product_id" name="product_id">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">카테고리 *</label>
                    <select id="product_category" onchange="loadSubcategoriesForProduct()" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">선택하세요</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">서브카테고리 *</label>
                    <div class="flex gap-2">
                        <select id="product_subcategory" name="subcategory_id" required
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">선택하세요</option>
                        </select>
                        <button type="button" onclick="addNewSubcategory()"
                            class="px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">+</button>
                    </div>
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">제품명 *</label><input type="text"
                    id="product_name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="예: 양면Flex"></div>
            <div class="grid grid-cols-3 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-2">단가 *</label><input type="number"
                        id="product_price" name="unit_price" required step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="8500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-2">단위 *</label><select id="product_unit"
                        name="unit" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="㎡">㎡</option>
                        <option value="자">자</option>
                        <option value="mm">mm</option>
                        <option value="개">개</option>
                    </select></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-2">계산방식 *</label><select
                        id="product_calc_type" name="calc_type" required onchange="toggleCalcFields()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="area">면적</option>
                        <option value="text">글자</option>
                        <option value="length">길이</option>
                        <option value="fixed">고정가</option>
                    </select></div>
            </div>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center">
                    <input type="checkbox" id="product_rounding" name="apply_rounding"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="product_rounding" class="ml-2 text-sm text-gray-700">거래처 기준 올림 적용 (1m 미만 → 1m)</label>
                </div>
                <p class="text-xs text-gray-500 mt-2">면적 계산 시 가로/세로 1m 미만을 1m로 올림 처리합니다</p>
            </div>

            <!-- 폭별 단가 설정 -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg" id="widthPricingSection">
                <div class="flex items-center mb-3">
                    <input type="checkbox" id="product_use_width_pricing" name="use_width_pricing"
                        onchange="toggleWidthPricing()"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="product_use_width_pricing" class="ml-2 text-sm font-medium text-gray-700">폭별 단가 사용
                        (단폭/장폭/초장폭)</label>
                </div>
                <div id="widthPricingFields" class="hidden space-y-3">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">단폭 단가 (≤2100mm)</label>
                            <input type="number" id="product_price_small" name="price_small" step="0.01"
                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                placeholder="8000">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">장폭 단가 (2101~3100mm)</label>
                            <input type="number" id="product_price_large" name="price_large" step="0.01"
                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                placeholder="10000">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">초장폭 단가 (3101~4800mm)</label>
                            <input type="number" id="product_price_xlarge" name="price_xlarge" step="0.01"
                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                placeholder="15000">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">폭 ≥1800mm 할증 (원/㎡)</label>
                        <input type="number" id="product_width_surcharge" name="width_surcharge_1800" step="0.01"
                            value="0"
                            class="w-32 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="1000">
                        <span class="text-xs text-gray-500 ml-2">폭 1800mm 이상 시 ㎡당 추가금액</span>
                    </div>
                    <p class="text-xs text-gray-500">판정기준: roll_width = min(가로, 세로) / 4800mm 초과 시 제작불가</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4" id="advancedFields">
                <div id="minAreaField"><label class="block text-sm font-medium text-gray-700 mb-2">최소 면적
                        (㎡)</label><input type="number" id="product_min_area" name="min_area" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="0.5"></div>
                <div id="baseSizeField" class="hidden"><label class="block text-sm font-medium text-gray-700 mb-2">기준 크기
                        (mm)</label><input type="number" id="product_base_size" name="base_size"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                        placeholder="450"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">설명</label><textarea
                    id="product_description" name="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex items-center"><input type="checkbox" id="product_active" name="is_active" checked
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"><label
                    for="product_active" class="ml-2 text-sm text-gray-700">활성화</label></div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeProductModal()"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">취소</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () { loadCategoriesForFilter(); loadProducts(); });
    function loadCategoriesForFilter() { fetch('admin_quote_api.php?action=get_categories').then(r => r.json()).then(data => { ['filterCategory', 'product_category'].forEach(id => { const s = document.getElementById(id); data.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name; s.appendChild(o); }); }); }); }
    function loadProducts() { const cid = document.getElementById('filterCategory').value; const sid = document.getElementById('filterSubcategory').value; let url = 'admin_quote_api.php?action=get_products'; if (cid) url += `&category_id=${cid}`; if (sid) url += `&subcategory_id=${sid}`; fetch(url).then(r => r.json()).then(data => { const tbody = document.getElementById('products-list'); tbody.innerHTML = ''; if (data.length === 0) { tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">등록된 제품이 없습니다</td></tr>'; return; } const types = { area: '면적', text: '글자', length: '길이', fixed: '고정가' }; data.forEach(p => { tbody.innerHTML += `<tr class="hover:bg-gray-50"><td class="px-4 py-3 text-sm text-gray-900">${p.category_name}</td><td class="px-4 py-3 text-sm text-gray-700">${p.subcategory_name}</td><td class="px-4 py-3 text-sm font-medium text-gray-900">${p.name}</td><td class="px-4 py-3 text-sm text-gray-700">${parseInt(p.unit_price).toLocaleString()}원/${p.unit}</td><td class="px-4 py-3 text-sm text-gray-700">${types[p.calc_type]}</td><td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${p.apply_rounding ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">${p.apply_rounding ? '올림' : '정확'}</span></td><td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${p.is_active ? '활성' : '비활성'}</span></td><td class="px-4 py-3"><div class="flex justify-center gap-2"><button onclick="editProduct(${p.id})" class="text-blue-600 hover:text-blue-800 text-sm">수정</button><button onclick="deleteProduct(${p.id})" class="text-red-600 hover:text-red-800 text-sm">삭제</button></div></td></tr>`; }); }); }
    function openProductModal(id = null) { document.getElementById('productModal').classList.remove('hidden'); if (id) { document.getElementById('productModalTitle').textContent = '제품 수정'; fetch(`admin_quote_api.php?action=get_product&id=${id}`).then(r => r.json()).then(d => { document.getElementById('product_id').value = d.id; document.getElementById('product_category').value = d.category_id; loadSubcategoriesForProduct(d.subcategory_id); document.getElementById('product_name').value = d.name; document.getElementById('product_price').value = d.unit_price; document.getElementById('product_unit').value = d.unit; document.getElementById('product_calc_type').value = d.calc_type; document.getElementById('product_rounding').checked = d.apply_rounding == 1; document.getElementById('product_min_area').value = d.min_area || ''; document.getElementById('product_base_size').value = d.base_size || ''; document.getElementById('product_description').value = d.description || ''; document.getElementById('product_active').checked = d.is_active == 1; document.getElementById('product_use_width_pricing').checked = d.use_width_pricing == 1; document.getElementById('product_price_small').value = d.price_small || ''; document.getElementById('product_price_large').value = d.price_large || ''; document.getElementById('product_price_xlarge').value = d.price_xlarge || ''; document.getElementById('product_width_surcharge').value = d.width_surcharge_1800 || 0; toggleCalcFields(); toggleWidthPricing(); }); } else { document.getElementById('productModalTitle').textContent = '제품 추가'; document.getElementById('productForm').reset(); document.getElementById('product_id').value = ''; toggleCalcFields(); toggleWidthPricing(); } }
    function closeProductModal() { document.getElementById('productModal').classList.add('hidden'); }
    function loadSubcategoriesForProduct(selectedId = null) { const cid = document.getElementById('product_category').value; const s = document.getElementById('product_subcategory'); s.innerHTML = '<option value="">선택하세요</option>'; if (!cid) return; fetch(`admin_quote_api.php?action=get_subcategories&category_id=${cid}`).then(r => r.json()).then(data => { data.forEach(sub => { const o = document.createElement('option'); o.value = sub.id; o.textContent = sub.name; if (selectedId && sub.id == selectedId) o.selected = true; s.appendChild(o); }); }); }
    function toggleCalcFields() { const t = document.getElementById('product_calc_type').value; const m = document.getElementById('minAreaField'); const b = document.getElementById('baseSizeField'); m.classList.add('hidden'); b.classList.add('hidden'); if (t === 'area') m.classList.remove('hidden'); else if (t === 'text') b.classList.remove('hidden'); }
    function toggleWidthPricing() { const checked = document.getElementById('product_use_width_pricing').checked; const fields = document.getElementById('widthPricingFields'); if (checked) { fields.classList.remove('hidden'); } else { fields.classList.add('hidden'); } }
    function addNewSubcategory() { const cid = document.getElementById('product_category').value; if (!cid) { alert('먼저 카테고리를 선택하세요'); return; } const n = prompt('서브카테고리 이름:'); if (!n) return; const fd = new FormData(); fd.append('action', 'add_subcategory'); fd.append('category_id', cid); fd.append('name', n); fetch('admin_quote_api.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if (d.success) loadSubcategoriesForProduct(d.id); else alert('오류: ' + d.message); }); }
    document.getElementById('productForm').addEventListener('submit', function (e) { e.preventDefault(); const fd = new FormData(this); const id = document.getElementById('product_id').value; fd.append('action', id ? 'update_product' : 'add_product'); fetch('admin_quote_api.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if (d.success) { alert(d.message); closeProductModal(); loadProducts(); } else alert('오류: ' + d.message); }); });
    function editProduct(id) { openProductModal(id); }
    function deleteProduct(id) { if (!confirm('정말 삭제하시겠습니까?')) return; fetch('admin_quote_api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete_product&id=${id}` }).then(r => r.json()).then(d => { if (d.success) { alert(d.message); loadProducts(); } else alert('오류: ' + d.message); }); }
</script>
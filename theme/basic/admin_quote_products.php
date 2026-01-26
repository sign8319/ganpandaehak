<?php
include_once('./_common.php');
include_once(G5_THEME_PATH . '/head.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}
?>

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

            <!-- 계산 방식 선택 (pricing_mode) -->
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg" id="pricingModeSection">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-purple-600">💰</span>
                    <label class="text-sm font-medium text-gray-700">계산 방식 선택</label>
                </div>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-purple-100 cursor-pointer">
                        <input type="radio" name="pricing_mode" value="AREA_TIER" class="w-4 h-4 text-purple-600">
                        <span class="text-sm text-gray-700">면적 구간 요금 (장당/㎡당)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-purple-100 cursor-pointer">
                        <input type="radio" name="pricing_mode" value="WIDTH" class="w-4 h-4 text-purple-600">
                        <span class="text-sm text-gray-700">폭별 단가 (단폭/장폭/초장폭)</span>
                    </label>
                </div>
            </div>

            <!-- AREA_TIER 면적구간 요금 설정 -->
            <div class="p-4 bg-orange-50 border border-orange-200 rounded-lg hidden" id="areaTierSection">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-orange-600">📊</span>
                    <label class="text-sm font-medium text-gray-700">면적 구간 요금 설정</label>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">1㎡ 미만 (장당)</label>
                        <input type="number" id="area_tier_piece_under_1" name="area_tier_piece_under_1"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                            placeholder="3000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">1~3㎡ (장당)</label>
                        <input type="number" id="area_tier_piece_1_to_3" name="area_tier_piece_1_to_3"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                            placeholder="6000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">3㎡ 이상 (㎡당)</label>
                        <input type="number" id="area_tier_m2_over_3" name="area_tier_m2_over_3"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                            placeholder="2000">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">폭 1800mm 이상 할증 (㎡당)</label>
                        <input type="number" id="area_tier_surcharge_1800" name="area_tier_surcharge_1800"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                            placeholder="1000">
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">폭 = min(가로, 세로) / 3㎡ 이상 + 폭 1800mm 이상 시 할증 적용</p>
            </div>

            <!-- 폭별 단가 설정 (WIDTH 선택 시 표시) -->
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg hidden" id="widthPricingSection">
                <div class="flex items-center mb-3">
                    <!-- use_width_pricing은 JS에서 자동 제어됨 -->
                    <input type="checkbox" id="product_use_width_pricing" name="use_width_pricing"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 hidden">
                    <span class="text-green-600 mr-2">📏</span>
                    <span class="text-sm font-medium text-gray-700">폭별 단가 설정</span>
                </div>
                <div id="widthPricingFields" class="space-y-3">
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

            <!-- 옵션 연결 섹션 -->
            <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <input type="hidden" name="linked_details" id="product_linked_details">
                
                <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                    <span class="mr-2">🔗 연결된 옵션</span>
                    <span class="text-xs font-normal text-gray-500">순서를 변경하거나 상태를 설정하세요.</span>
                </h4>
                
                <!-- 연결된 옵션 리스트 (순서/상태 설정) -->
                <div class="bg-white rounded border border-gray-200 mb-4 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-3 py-2 text-center w-16">순서</th>
                                <th class="px-3 py-2 text-left">옵션명 (그룹)</th>
                                <th class="px-3 py-2 text-center w-20">상태</th>
                                <th class="px-3 py-2 text-center w-12">제거</th>
                            </tr>
                        </thead>
                        <tbody id="linked-options-tbody">
                            <!-- JS 로드 -->
                            <tr><td colspan="4" class="p-4 text-center text-gray-400">연결된 옵션이 없습니다. 아래 목록에서 선택하세요.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h4 class="font-bold text-gray-800 mb-2 text-sm">전체 옵션 목록 (선택)</h4>
                    <div id="all-options-container" class="space-y-4 max-h-60 overflow-y-auto pr-2">
                        <!-- JS로 그룹별 렌더링 -->
                    </div>
                </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        loadCategoriesForFilter();
        loadProducts();
        // pricing_mode 라디오 버튼 변경 이벤트
        document.querySelectorAll('input[name="pricing_mode"]').forEach(radio => {
            radio.addEventListener('change', togglePricingMode);
        });
    });
    function loadCategoriesForFilter() { fetch('admin_quote_api.php?action=get_categories').then(r => r.json()).then(data => { ['filterCategory', 'product_category'].forEach(id => { const s = document.getElementById(id); data.forEach(c => { const o = document.createElement('option'); o.value = c.id; o.textContent = c.name; s.appendChild(o); }); }); }); }
    function loadProducts() { const cid = document.getElementById('filterCategory').value; const sid = document.getElementById('filterSubcategory').value; let url = 'admin_quote_api.php?action=get_products'; if (cid) url += `&category_id=${cid}`; if (sid) url += `&subcategory_id=${sid}`; fetch(url).then(r => r.json()).then(data => { const tbody = document.getElementById('products-list'); tbody.innerHTML = ''; if (data.length === 0) { tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">등록된 제품이 없습니다</td></tr>'; return; } const types = { area: '면적', text: '글자', length: '길이', fixed: '고정가' }; data.forEach(p => { tbody.innerHTML += `<tr class="hover:bg-gray-50"><td class="px-4 py-3 text-sm text-gray-900">${p.category_name}</td><td class="px-4 py-3 text-sm text-gray-700">${p.subcategory_name}</td><td class="px-4 py-3 text-sm font-medium text-gray-900">${p.name}</td><td class="px-4 py-3 text-sm text-gray-700">${parseInt(p.unit_price).toLocaleString()}원/${p.unit}</td><td class="px-4 py-3 text-sm text-gray-700">${types[p.calc_type]}</td><td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${p.apply_rounding ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">${p.apply_rounding ? '올림' : '정확'}</span></td><td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full ${p.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${p.is_active ? '활성' : '비활성'}</span></td><td class="px-4 py-3"><div class="flex justify-center gap-2"><button onclick="editProduct(${p.id})" class="text-blue-600 hover:text-blue-800 text-sm">수정</button><button onclick="deleteProduct(${p.id})" class="text-red-600 hover:text-red-800 text-sm">삭제</button></div></td></tr>`; }); }); }
    function openProductModal(id = null) {
        document.getElementById('productModal').classList.remove('hidden');

        if (id) {
            document.getElementById('productModalTitle').textContent = '제품 수정';
            fetch(`admin_quote_api.php?action=get_product&id=${id}`).then(r => r.json()).then(d => {
                document.getElementById('product_id').value = d.id;
                document.getElementById('product_category').value = d.category_id;
                loadSubcategoriesForProduct(d.subcategory_id, d.subcategory_name);
                document.getElementById('product_name').value = d.name;
                document.getElementById('product_price').value = d.unit_price;
                document.getElementById('product_unit').value = d.unit;
                document.getElementById('product_calc_type').value = d.calc_type;
                document.getElementById('product_rounding').checked = d.apply_rounding == 1;
                document.getElementById('product_min_area').value = d.min_area || '';
                document.getElementById('product_base_size').value = d.base_size || '';
                document.getElementById('product_description').value = d.description || '';
                document.getElementById('product_active').checked = d.is_active == 1;
                document.getElementById('product_use_width_pricing').checked = d.use_width_pricing == 1;
                document.getElementById('product_price_small').value = d.price_small || '';
                document.getElementById('product_price_large').value = d.price_large || '';
                document.getElementById('product_price_xlarge').value = d.price_xlarge || '';
                document.getElementById('product_width_surcharge').value = d.width_surcharge_1800 || 0;
                // AREA_TIER 필드 로드
                document.getElementById('area_tier_piece_under_1').value = d.area_tier_piece_under_1 || '';
                document.getElementById('area_tier_piece_1_to_3').value = d.area_tier_piece_1_to_3 || '';
                document.getElementById('area_tier_m2_over_3').value = d.area_tier_m2_over_3 || '';
                document.getElementById('area_tier_surcharge_1800').value = d.area_tier_surcharge_1800 || '';
                // pricing_mode 설정 (기존 데이터 호환성 처리)
                let pricingMode = d.pricing_mode || 'DEFAULT';

                // 만약 DEFAULT라면 데이터 기반으로 추론
                // 만약 DEFAULT라면 데이터 기반으로 추론
                if (pricingMode === 'DEFAULT') {
                    // 1. 면적 구간 요금 값이 하나라도 있으면 AREA_TIER
                    if (d.area_tier_piece_under_1 > 0 || d.area_tier_piece_1_to_3 > 0 || d.area_tier_m2_over_3 > 0) {
                        pricingMode = 'AREA_TIER';
                    }
                    // 2. 폭별 단가 사용이면 WIDTH
                    else if (d.use_width_pricing == 1) {
                        pricingMode = 'WIDTH';
                    }
                    // 3. 수성현수막 이름 포함이면 AREA_TIER (최후의 수단)
                    else if (d.subcategory_name && d.subcategory_name.includes('수성현수막')) {
                        pricingMode = 'AREA_TIER';
                    }
                }

                const radio = document.querySelector(`input[name="pricing_mode"][value="${pricingMode}"]`);
                if (radio) {
                    radio.checked = true;
                } else {
                    // 매칭되는 라디오가 없으면(DEFAULT 등) 모든 라디오 체크 해제
                    document.querySelectorAll('input[name="pricing_mode"]').forEach(r => r.checked = false);
                }

                toggleCalcFields();
                // toggleWidthPricing 호출 제거
                togglePricingMode();
                loadOptionsForProduct(id);
            });
        } else {
            document.getElementById('productModalTitle').textContent = '제품 추가';
            document.getElementById('productForm').reset();
            document.getElementById('product_id').value = '';
            // 신규 생성: 초기에는 선택 안 함 (카테고리 선택 시 프리셋 적용)
            document.querySelectorAll('input[name="pricing_mode"]').forEach(r => r.checked = false);
            toggleCalcFields();
            // toggleWidthPricing 호출 제거
            togglePricingMode();
            loadOptionsForProduct(null);
        }
    }
    
    function closeProductModal() { document.getElementById('productModal').classList.add('hidden'); }

    function toggleCalcFields() { const t = document.getElementById('product_calc_type').value; const m = document.getElementById('minAreaField'); const b = document.getElementById('baseSizeField'); m.classList.add('hidden'); b.classList.add('hidden'); if (t === 'area') m.classList.remove('hidden'); else if (t === 'text') b.classList.remove('hidden'); }
    
    function togglePricingMode() {
        const mode = document.querySelector('input[name="pricing_mode"]:checked');
        const widthSection = document.getElementById('widthPricingSection');
        const areaTierSection = document.getElementById('areaTierSection');
        const useWidthCheck = document.getElementById('product_use_width_pricing');

        // 모두 숨김 & 체크 해제 초기화
        widthSection.classList.add('hidden');
        areaTierSection.classList.add('hidden');
        useWidthCheck.checked = false;

        if (mode) {
            if (mode.value === 'WIDTH') {
                widthSection.classList.remove('hidden');
                useWidthCheck.checked = true; // WIDTH 모드면 폭별단가 사용 체크
            } else if (mode.value === 'AREA_TIER') {
                areaTierSection.classList.remove('hidden');
                // AREA_TIER는 use_width_pricing false
            }
        }
    }

    let currentAllOptions = [];     // 전체 마스터 옵션
    let currentLinkedOptions = [];  // 현재 제품에 연결된 옵션 (순서/상태 포함)

    function loadOptionsForProduct(productId) {
        currentAllOptions = [];
        currentLinkedOptions = [];
        const container = document.getElementById('all-options-container'); // 올바른 ID 사용
        if(container) container.innerHTML = '<div class="text-xs text-gray-500">불러오는 중...</div>';
        
        // 1. 전체 마스터 옵션 가져오기
        fetch('admin_quote_api.php?action=get_options').then(r => r.json()).then(allOptions => {
            currentAllOptions = allOptions;
            
            // 2. 제품에 연결된 옵션 가져오기
            if (productId) {
                fetch(`admin_quote_api.php?action=get_options&product_id=${productId}`).then(r => r.json()).then(data => {
                    const details = data.linked_details || [];
                    
                    // linked_details에는 option 정보가 없으므로 allOptions에서 찾아 매핑
                    currentLinkedOptions = details.map(d => {
                        const master = allOptions.find(o => o.id == d.option_id);
                        if (!master) return null;
                        return {
                            ...master,
                            is_active: parseInt(d.is_active), // 제품별 활성상태
                            sort: parseInt(d.sort_order),
                            group_name: master.group_name || '기본'
                        };
                    }).filter(x => x !== null);
                    
                    renderUI();
                });
            } else {
                renderUI();
            }
        });
    }

    function renderUI() {
        renderLinkedTable();
        renderAllOptionsGrouped();
    }

    function renderLinkedTable() {
        const tbody = document.getElementById('linked-options-tbody');
        if (!tbody) return; 
        tbody.innerHTML = '';
        
        if (currentLinkedOptions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-gray-400">연결된 옵션이 없습니다. 아래 목록에서 선택하세요.</td></tr>';
            return;
        }

        currentLinkedOptions.forEach((opt, index) => {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-gray-100 hover:bg-gray-50';
            tr.innerHTML = `
                <td class="px-2 py-2 text-center">
                    <div class="flex flex-col items-center">
                        <button type="button" onclick="moveOption(${index}, -1)" class="text-gray-400 hover:text-blue-600 ${index === 0 ? 'invisible' : ''}">▲</button>
                        <button type="button" onclick="moveOption(${index}, 1)" class="text-gray-400 hover:text-blue-600 ${index === currentLinkedOptions.length - 1 ? 'invisible' : ''}">▼</button>
                    </div>
                </td>
                <td class="px-3 py-2 text-gray-800">
                    <div class="font-medium">${opt.name}</div>
                    <div class="text-xs text-gray-500">${opt.group_name} · ${parseInt(opt.price).toLocaleString()}원</div>
                </td>
                <td class="px-2 py-2 text-center">
                    <button type="button" onclick="toggleOptionActive(${index})" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none ${opt.is_active ? 'bg-green-500' : 'bg-gray-200'}">
                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform ${opt.is_active ? 'translate-x-6' : 'translate-x-1'}"></span>
                    </button>
                </td>
                <td class="px-2 py-2 text-center">
                    <button type="button" onclick="removeOption(${index})" class="text-red-400 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderAllOptionsGrouped() {
        const container = document.getElementById('all-options-container');
        if (!container) return;
        container.innerHTML = '';

        if (currentAllOptions.length === 0) {
            container.innerHTML = '<div class="text-sm text-gray-500">등록된 옵션이 없습니다.<br><a href="admin_quote_options_master.php" target="_blank" class="text-blue-600 underline">옵션 관리</a>에서 등록하세요.</div>';
            return;
        }

        // 그룹핑
        const groups = {};
        currentAllOptions.forEach(opt => {
            const gName = opt.group_name || '기본';
            if (!groups[gName]) groups[gName] = [];
            groups[gName].push(opt);
        });

        Object.keys(groups).forEach(gName => {
            const groupDiv = document.createElement('div');
            groupDiv.innerHTML = `<h5 class="text-xs font-bold text-gray-500 uppercase mb-1 px-1 border-b border-gray-100 pb-1 mt-2 first:mt-0">${gName}</h5>`;
            const gridDiv = document.createElement('div');
            gridDiv.className = 'grid grid-cols-2 gap-2 text-sm'; // 2열 그리드

            groups[gName].forEach(opt => {
                // 이미 연결되어 있는지 확인
                const isLinked = currentLinkedOptions.some(lo => lo.id == opt.id);
                
                const itemDiv = document.createElement('div');
                itemDiv.className = `p-2 rounded border transition-colors ${isLinked ? 'bg-blue-50 border-blue-200 cursor-default' : 'bg-white border-gray-200 hover:border-blue-300 cursor-pointer'}`;
                itemDiv.onclick = function() { if (!isLinked) addOptionToLink(opt.id); };
                
                itemDiv.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span class="${isLinked ? 'text-blue-700 font-medium' : 'text-gray-700'}">${opt.name}</span>
                        ${isLinked ? '<span class="text-xs text-blue-500 font-bold">✓ 추가됨</span>' : '<span class="text-xs text-gray-400">+</span>'}
                    </div>
                `;
                gridDiv.appendChild(itemDiv);
            });

            groupDiv.appendChild(gridDiv);
            container.appendChild(groupDiv);
        });
    }

    function addOptionToLink(optId) {
        const opt = currentAllOptions.find(o => o.id == optId);
        if (opt && !currentLinkedOptions.some(lo => lo.id == optId)) {
            // 새 옵션 추가 (기본 활성)
            currentLinkedOptions.push({ ...opt, is_active: 1, group_name: opt.group_name || '기본' });
            renderUI();
        }
    }

    function removeOption(index) {
        currentLinkedOptions.splice(index, 1);
        renderUI();
    }

    function moveOption(index, direction) {
        if (index + direction < 0 || index + direction >= currentLinkedOptions.length) return;
        const temp = currentLinkedOptions[index];
        currentLinkedOptions[index] = currentLinkedOptions[index + direction];
        currentLinkedOptions[index + direction] = temp;
        renderUI();
    }

    function toggleOptionActive(index) {
        currentLinkedOptions[index].is_active = currentLinkedOptions[index].is_active ? 0 : 1;
        renderUI(); // 스위치 상태 변경을 위해 재렌더링
    }

    document.getElementById('productForm').addEventListener('submit', function (e) { 
        e.preventDefault(); 
        
        // 현재 연결된 옵션 상태를 JSON으로 변환하여 hidden input에 저장
        // currentLinkedOptions 배열을 활용
        const details = currentLinkedOptions.map((opt, idx) => ({
            option_id: opt.id,
            sort_order: (idx + 1) * 10,
            is_active: opt.is_active // 1 or 0
        }));
        document.getElementById('product_linked_details').value = JSON.stringify(details);

        const fd = new FormData(this); 
        const id = document.getElementById('product_id').value; 
        fd.append('action', id ? 'update_product' : 'add_product'); 
        fetch('admin_quote_api.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if (d.success) { alert(d.message); closeProductModal(); loadProducts(); } else alert('오류: ' + d.message); }); 
    });

    function loadSubcategoriesForProduct(selectedId = null) {
        const cid = document.getElementById('product_category').value;
        const s = document.getElementById('product_subcategory');
        s.innerHTML = '<option value="">선택하세요</option>';
        if (!cid) return;
        fetch(`admin_quote_api.php?action=get_subcategories&category_id=${cid}`).then(r => r.json()).then(data => {
            data.forEach(sub => {
                const o = document.createElement('option');
                o.value = sub.id;
                o.textContent = sub.name;
                o.dataset.name = sub.name; // 이름 저장
                if (selectedId && sub.id == selectedId) o.selected = true;
                s.appendChild(o);
            });
            // 신규 생성 시(ID없음) 수성현수막이면 프리셋 적용
            s.onchange = function () {
                const currentProdId = document.getElementById('product_id').value;
                if (!currentProdId) { // 신규 생성일 때만
                    const selected = s.options[s.selectedIndex];
                    const subName = selected ? selected.dataset.name : '';
                    if (subName && subName.includes('수성현수막')) {
                        // AREA_TIER 자동 선택
                        document.querySelector('input[name="pricing_mode"][value="AREA_TIER"]').checked = true;
                        // 기본값 채우기 (이미 값이 있으면 유지)
                        const f1 = document.getElementById('area_tier_piece_under_1');
                        const f2 = document.getElementById('area_tier_piece_1_to_3');
                        const f3 = document.getElementById('area_tier_m2_over_3');
                        const f4 = document.getElementById('area_tier_surcharge_1800');
                        if (!f1.value) f1.value = 3000;
                        if (!f2.value) f2.value = 6000;
                        if (!f3.value) f3.value = 2000;
                        if (!f4.value) f4.value = 1000;

                        togglePricingMode();
                    }
                }
            };
        });
    }

    function addNewSubcategory() { const cid = document.getElementById('product_category').value; if (!cid) { alert('먼저 카테고리를 선택하세요'); return; } const n = prompt('서브카테고리 이름:'); if (!n) return; const fd = new FormData(); fd.append('action', 'add_subcategory'); fd.append('category_id', cid); fd.append('name', n); fetch('admin_quote_api.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { if (d.success) loadSubcategoriesForProduct(d.id); else alert('오류: ' + d.message); }); }

    function editProduct(id) { openProductModal(id); }
    function deleteProduct(id) { if (!confirm('정말 삭제하시겠습니까?')) return; fetch('admin_quote_api.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `action=delete_product&id=${id}` }).then(r => r.json()).then(d => { if (d.success) { alert(d.message); loadProducts(); } else alert('오류: ' + d.message); }); }
</script>

<?php include_once(G5_THEME_PATH . '/basic/tail.php'); ?>
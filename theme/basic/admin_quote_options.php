<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <!-- DEBUG: 버전 확인용 배지 (테스트 후 삭제) -->
    <div class="mb-4 px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full inline-block">
        ✅ NEW VERSION - per_m2 지원 (2026-01-22)
    </div>

    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-gray-900">옵션 목록</h2>
            <p class="text-sm text-gray-500 mt-1">제품별 추가 옵션을 관리합니다</p>
        </div>
        <button onclick="openOptionModal()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            + 옵션 추가
        </button>
    </div>

    <!-- Product Filter -->
    <div class="mb-4">
        <select id="filterProduct" onchange="loadOptions()"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">전체 제품</option>
        </select>
    </div>

    <!-- Options Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">제품명</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">옵션명</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">타입</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">값</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">관리</th>
                </tr>
            </thead>
            <tbody id="options-list" class="divide-y divide-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <div class="animate-pulse">로딩 중...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- Option Modal -->
<div id="optionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900" id="optionModalTitle">옵션 추가</h3>
            <button onclick="closeOptionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <form id="optionForm" class="p-6 space-y-4">
            <input type="hidden" id="option_id" name="option_id">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">제품 선택 *</label>
                <select id="option_product" name="product_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">선택하세요</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">옵션명 *</label>
                <input type="text" id="option_name" name="name" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="예: LED추가, 오전할인">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">옵션 타입 *</label>
                <select id="option_type" onchange="toggleOptionFields()" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="price">추가 금액</option>
                    <option value="discount">할인율</option>
                </select>
            </div>

            <div id="unitTypeField">
                <label class="block text-sm font-medium text-gray-700 mb-2">금액 계산 방식</label>
                <select id="option_unit_type" name="unit_type" onchange="togglePriceLabel()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="fixed">고정금액</option>
                    <option value="per_m2">㎡당 추가금액</option>
                    <option value="quantity">수량형(무료 포함)</option>
                </select>
            </div>

            <div id="priceField">
                <label id="priceLabelText" class="block text-sm font-medium text-gray-700 mb-2">추가 금액 (원)</label>
                <input type="number" id="option_price" name="price" step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="50000">
            </div>

            <div id="discountField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">할인율 (%)</label>
                <input type="number" id="option_discount" name="discount_percent" step="0.01" min="0" max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="6">
                <p class="text-xs text-gray-500 mt-1">할인율을 퍼센트로 입력 (예: 6% → 6 입력)</p>
            </div>

            <!-- 수량형 옵션 필드 -->
            <div id="quantityFields" class="hidden space-y-3">
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">무료 개수</label>
                        <input type="number" id="option_free_qty" name="free_qty" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="4">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">초과 단가 (원)</label>
                        <input type="number" id="option_qty_unit_price" name="qty_unit_price" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">기본 개수</label>
                        <input type="number" id="option_default_qty" name="default_qty" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="4">
                    </div>
                </div>
                <p class="text-xs text-gray-500">예: 무료 4개, 초과 1개당 500원 → 5개 선택 시 500원 추가</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">정렬 순서</label>
                <input type="number" id="option_order" name="sort_order" value="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeOptionModal()"
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadProductsForFilter();
        loadOptions();
    });

    function loadProductsForFilter() {
        fetch('admin_quote_api.php?action=get_products')
            .then(response => response.json())
            .then(data => {
                const selects = ['filterProduct', 'option_product'];
                selects.forEach(selectId => {
                    const select = document.getElementById(selectId);
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.category_name} > ${product.subcategory_name} > ${product.name}`;
                        select.appendChild(option);
                    });
                });
            });
    }

    function loadOptions() {
        const productId = document.getElementById('filterProduct').value;

        let url = 'admin_quote_api.php?action=get_options';
        if (productId) url += `&product_id=${productId}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('options-list');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">등록된 옵션이 없습니다</td></tr>';
                    return;
                }

                data.forEach(option => {
                    const isDiscount = option.discount && option.discount > 0;
                    const isPerM2 = option.unit_type === 'per_m2';
                    let valueText, typeText;

                    if (isDiscount) {
                        valueText = `${(option.discount * 100).toFixed(1)}% 할인`;
                        typeText = '할인';
                    } else if (isPerM2) {
                        valueText = `+${parseInt(option.price).toLocaleString()}원/㎡`;
                        typeText = '㎡당';
                    } else if (option.unit_type === 'quantity') {
                        valueText = `${option.free_qty}개 무료 / 초과 ${parseInt(option.qty_unit_price).toLocaleString()}원`;
                        typeText = '수량형';
                    } else {
                        valueText = `+${parseInt(option.price).toLocaleString()}원`;
                        typeText = '추가금액';
                    }

                    const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">${option.product_name}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${option.name}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full ${isDiscount ? 'bg-red-100 text-red-800' : (isPerM2 ? 'bg-green-100 text-green-800' : (option.unit_type === 'quantity' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'))}">
                                ${typeText}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium ${isDiscount ? 'text-red-600' : 'text-blue-600'}">
                            ${valueText}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-center gap-2">
                                <button onclick="editOption(${option.id})" class="text-blue-600 hover:text-blue-800 text-sm">수정</button>
                                <button onclick="deleteOption(${option.id})" class="text-red-600 hover:text-red-800 text-sm">삭제</button>
                            </div>
                        </td>
                    </tr>
                `;
                    tbody.innerHTML += row;
                });
            });
    }

    function openOptionModal(id = null) {
        document.getElementById('optionModal').classList.remove('hidden');

        if (id) {
            document.getElementById('optionModalTitle').textContent = '옵션 수정';
            fetch(`admin_quote_api.php?action=get_option&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('option_id').value = data.id;
                    document.getElementById('option_product').value = data.product_id;
                    document.getElementById('option_name').value = data.name;
                    document.getElementById('option_unit_type').value = data.unit_type || 'fixed';

                    if (data.discount && data.discount > 0) {
                        document.getElementById('option_type').value = 'discount';
                        document.getElementById('option_discount').value = (data.discount * 100).toFixed(2);
                    } else {
                        document.getElementById('option_type').value = 'price';
                        document.getElementById('option_price').value = data.price;
                    }

                    // 수량형 옵션 필드
                    document.getElementById('option_free_qty').value = data.free_qty || 0;
                    document.getElementById('option_qty_unit_price').value = data.qty_unit_price || 0;
                    document.getElementById('option_default_qty').value = data.default_qty || 0;

                    document.getElementById('option_order').value = data.sort_order;
                    toggleOptionFields();
                    togglePriceLabel();
                });
        } else {
            document.getElementById('optionModalTitle').textContent = '옵션 추가';
            document.getElementById('optionForm').reset();
            document.getElementById('option_id').value = '';
            toggleOptionFields();
        }
    }

    function closeOptionModal() {
        document.getElementById('optionModal').classList.add('hidden');
    }

    function toggleOptionFields() {
        const type = document.getElementById('option_type').value;
        const priceField = document.getElementById('priceField');
        const discountField = document.getElementById('discountField');
        const unitTypeField = document.getElementById('unitTypeField');

        if (type === 'discount') {
            priceField.classList.add('hidden');
            discountField.classList.remove('hidden');
            unitTypeField.classList.add('hidden');
            document.getElementById('option_price').value = 0;
            document.getElementById('option_unit_type').value = 'fixed';
        } else {
            priceField.classList.remove('hidden');
            discountField.classList.add('hidden');
            unitTypeField.classList.remove('hidden');
            document.getElementById('option_discount').value = 0;
            togglePriceLabel();
        }
    }

    function togglePriceLabel() {
        const unitType = document.getElementById('option_unit_type').value;
        const label = document.getElementById('priceLabelText');
        const priceField = document.getElementById('priceField');
        const quantityFields = document.getElementById('quantityFields');

        if (unitType === 'quantity') {
            priceField.classList.add('hidden');
            quantityFields.classList.remove('hidden');
            label.textContent = '추가 금액 (원)';
        } else {
            priceField.classList.remove('hidden');
            quantityFields.classList.add('hidden');
            if (unitType === 'per_m2') {
                label.textContent = '㎡당 추가금액 (원/㎡)';
            } else {
                label.textContent = '추가 금액 (원)';
            }
        }
    }

    document.getElementById('optionForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData();
        const id = document.getElementById('option_id').value;

        formData.append('action', id ? 'update_option' : 'add_option');
        if (id) formData.append('option_id', id);

        formData.append('product_id', document.getElementById('option_product').value);
        formData.append('name', document.getElementById('option_name').value);
        formData.append('sort_order', document.getElementById('option_order').value);

        const type = document.getElementById('option_type').value;
        const unitType = document.getElementById('option_unit_type').value;

        if (type === 'discount') {
            const discountPercent = document.getElementById('option_discount').value;
            formData.append('discount', (discountPercent / 100).toFixed(4));
            formData.append('price', 0);
            formData.append('unit_type', 'fixed');
        } else {
            formData.append('price', document.getElementById('option_price').value || 0);
            formData.append('discount', 0);
            formData.append('unit_type', unitType);
        }

        // 수량형 옵션 필드
        formData.append('free_qty', document.getElementById('option_free_qty').value || 0);
        formData.append('qty_unit_price', document.getElementById('option_qty_unit_price').value || 0);
        formData.append('default_qty', document.getElementById('option_default_qty').value || 0);

        fetch('admin_quote_api.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
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
        if (!confirm('정말 삭제하시겠습니까?')) return;

        fetch('admin_quote_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete_option&id=${id}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadOptions();
                } else {
                    alert('오류: ' + data.message);
                }
            });
    }
</script>
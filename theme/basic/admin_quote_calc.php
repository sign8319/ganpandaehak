<?php
include_once('./_common.php');
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}
$page_title = "간판 자동 견적 계산기";
include_once(G5_THEME_PATH . '/head.php');

$categories_sql = "SELECT * FROM g5_quote_categories WHERE is_active = 1 ORDER BY sort_order ASC";
$categories_result = sql_query($categories_sql);
$categories = [];
while ($row = sql_fetch_array($categories_result)) {
    $categories[] = $row;
}

$quote_data = [];
foreach ($categories as $cat) {
    $cat_key = $cat['category_key'];
    $quote_data[$cat_key] = ['name' => $cat['name'], 'icon' => $cat['icon'], 'subcategories' => []];
    $sub_sql = "SELECT * FROM g5_quote_subcategories WHERE category_id = {$cat['id']} ORDER BY sort_order ASC";
    $sub_result = sql_query($sub_sql);
    while ($sub = sql_fetch_array($sub_result)) {
        $products = [];
        $prod_sql = "SELECT * FROM g5_quote_products WHERE subcategory_id = {$sub['id']} AND is_active = 1 ORDER BY sort_order ASC";
        $prod_result = sql_query($prod_sql);
        while ($prod = sql_fetch_array($prod_result)) {
            $options = [];
            $opt_sql = "SELECT * FROM g5_quote_options WHERE product_id = {$prod['id']} ORDER BY sort_order ASC";
            $opt_result = sql_query($opt_sql);
            while ($opt = sql_fetch_array($opt_result)) {
                $option_data = [
                    'name' => $opt['name'],
                    'price' => (float) $opt['price'],
                    'unit_type' => $opt['unit_type'] ?? 'fixed',
                    'free_qty' => (int) ($opt['free_qty'] ?? 0),
                    'qty_unit_price' => (float) ($opt['qty_unit_price'] ?? 0),
                    'default_qty' => (int) ($opt['default_qty'] ?? 0)
                ];
                if ($opt['discount'] && $opt['discount'] > 0) {
                    $option_data['discount'] = (float) $opt['discount'];
                }
                $options[] = $option_data;
            }
            $product_data = [
                'name' => $prod['name'],
                'unit_price' => (float) $prod['unit_price'],
                'unit' => $prod['unit'],
                'calc_type' => $prod['calc_type'],
                'apply_rounding' => (int) $prod['apply_rounding'],
                'description' => $prod['description'],
                'options' => $options,
                'use_width_pricing' => (int) ($prod['use_width_pricing'] ?? 0),
                'price_small' => (float) ($prod['price_small'] ?? 0),
                'price_large' => (float) ($prod['price_large'] ?? 0),
                'price_xlarge' => (float) ($prod['price_xlarge'] ?? 0),
                'width_surcharge_1800' => (float) ($prod['width_surcharge_1800'] ?? 0)
            ];
            if ($prod['min_area']) {
                $product_data['min_area'] = (float) $prod['min_area'];
            }
            if ($prod['base_size']) {
                $product_data['base_size'] = (int) $prod['base_size'];
            }
            $products[] = $product_data;
        }
        $quote_data[$cat_key]['subcategories'][$sub['id']] = ['name' => $sub['name'], 'products' => $products];
    }
}
?>

<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>
    <div class="flex-1 min-w-0">
        <div class="p-4 lg:p-8 max-w-[1600px] mx-auto">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">견적 계산기</h1>
                    <p class="text-sm text-gray-500 mt-1">2024-2025 거래 데이터 기반 자동 견적 시스템 <a
                            href="admin_quote_manager.php" class="ml-4 text-blue-600 hover:text-blue-800">⚙️ 설정 관리</a>
                    </p>
                </div>
                <button onclick="resetForm()"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-red-600 transition shadow-sm">🔄
                    초기화</button>
            </div>

            <div class="grid lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div
                            class="grid grid-cols-3 lg:grid-cols-6 text-xs md:text-sm divide-x divide-gray-100 border-b border-gray-100 bg-gray-50/50">
                            <?php foreach ($categories as $idx => $cat): ?>
                                <button onclick="selectCategory('<?= $cat['category_key'] ?>')"
                                    id="cat-<?= $cat['category_key'] ?>"
                                    class="tab-btn <?= $idx === 0 ? 'active' : '' ?> py-4 text-center hover:bg-white transition-colors">
                                    <div class="text-2xl mb-1"><?= $cat['icon'] ?></div>
                                    <div class="font-bold text-gray-700"><?= $cat['name'] ?></div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <label class="block text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">1</span>제작
                            품목 선택
                        </label>
                        <div id="subcategory-container"></div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200" id="input-section">
                        <label class="block text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">2</span>규격
                            및 수량
                        </label>
                        <div id="area-input" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-xs text-gray-500 mb-2">가로 (mm)</label><input type="number"
                                        id="width"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                        placeholder="예: 3000" oninput="calculate()"></div>
                                <div><label class="block text-xs text-gray-500 mb-2">세로 (mm)</label><input type="number"
                                        id="height"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                        placeholder="예: 900" oninput="calculate()"></div>
                            </div>
                            <!-- 폭 판정 결과 표시 -->
                            <div id="width-info" class="hidden">
                                <div class="p-3 rounded-lg border" id="width-info-box">
                                    <div class="text-sm font-medium" id="width-info-text"></div>
                                </div>
                            </div>
                        </div>
                        <div id="text-input" class="space-y-4 hidden">
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-xs text-gray-500 mb-2">글자 수 (자)</label><input
                                        type="number" id="char-count"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                        placeholder="예: 5" value="1" oninput="calculate()"></div>
                                <div><label class="block text-xs text-gray-500 mb-2">글자 크기 (mm)</label><input
                                        type="number" id="char-size"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                        placeholder="예: 450" oninput="calculate()"></div>
                            </div>
                        </div>
                        <div id="length-input" class="space-y-4 hidden">
                            <div><label class="block text-xs text-gray-500 mb-2">길이 (mm)</label><input type="number"
                                    id="length"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="예: 5000" oninput="calculate()"></div>
                        </div>
                        <div id="fixed-input" class="hidden">
                            <p class="text-sm text-gray-600">수량만 선택해주세요.</p>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-4">
                            <label class="text-sm font-medium">주문 수량</label>
                            <div class="flex items-center border rounded-lg overflow-hidden">
                                <button onclick="adjustQty(-1)"
                                    class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700">-</button>
                                <input type="number" id="quantity" value="1"
                                    class="w-16 text-center outline-none border-l border-r" oninput="calculate()">
                                <button onclick="adjustQty(1)"
                                    class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">3</span>추가
                            옵션
                        </h3>
                        <div id="options-container" class="space-y-3">
                            <p class="text-sm text-gray-500">제품을 선택하면 옵션이 표시됩니다.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                        <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-6 rounded-t-xl">
                            <h2 class="text-lg font-bold mb-1">견적 결과</h2>
                            <p class="text-sm opacity-90" id="result-product">제품을 선택해주세요</p>
                        </div>
                        <div class="p-6">
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                <div class="text-xs text-gray-500 mb-1">규격 / 수량</div>
                                <div class="text-sm font-medium text-gray-900" id="result-spec">-</div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between py-2 text-sm border-b"><span class="text-gray-600">기본
                                        금액</span><span class="font-semibold text-gray-900" id="result-base">0원</span>
                                </div>
                                <div class="flex justify-between py-2 text-sm border-b" id="row-options"
                                    style="display:none;"><span class="text-gray-600">옵션 추가</span><span
                                        class="font-semibold text-gray-900" id="result-options">0원</span></div>
                                <div class="flex justify-between py-2 text-sm border-b" id="row-discount"
                                    style="display:none;"><span class="text-gray-600">할인</span><span
                                        class="font-semibold text-red-600" id="result-discount">0원</span></div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-5 text-center border-2 border-blue-200 mb-4">
                                <div class="text-sm text-gray-600 mb-2">최종 견적 금액</div>
                                <div class="text-3xl font-bold text-blue-600" id="result-total">0원</div>
                                <div class="text-xs text-gray-500 mt-2">VAT 별도</div>
                            </div>
                            <button onclick="addToCart()" id="add-cart-btn"
                                class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed"
                                disabled>🛒 장바구니에 추가</button>
                            <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex items-start gap-2">
                                    <div class="text-yellow-600 text-xl">💡</div>
                                    <div class="text-xs text-gray-700 leading-relaxed" id="result-tip">제품을 선택하면 상세 정보가
                                        표시됩니다.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200" id="cart-panel">
                        <div class="bg-gradient-to-r from-green-600 to-green-500 text-white p-4 rounded-t-xl">
                            <h3 class="text-base font-bold">견적 장바구니</h3>
                        </div>
                        <div class="p-4">
                            <div id="cart-items" class="space-y-3 mb-4">
                                <p class="text-sm text-gray-500 text-center py-4">장바구니가 비어있습니다</p>
                            </div>
                            <div class="border-t-2 border-gray-300 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-gray-900">총 합계</span>
                                    <span class="text-2xl font-bold text-green-600" id="cart-total">0원</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 text-right">VAT 별도</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-btn {
        transition: all 0.2s;
        border-bottom: 3px solid transparent;
    }

    .tab-btn.active {
        border-bottom-color: #2563eb;
        background-color: white;
    }

    .item-btn {
        transition: all 0.2s;
    }

    .item-btn.active {
        background-color: #2563eb;
        color: white;
        border-color: #2563eb;
    }

    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

<script>
    const QUOTE_DATA = <?= json_encode($quote_data, JSON_UNESCAPED_UNICODE) ?>;
    let currentCategory = '<?= $categories[0]['category_key'] ?? 'flex' ?>';
    let currentProduct = null;
    let selectedOptions = [];
    let cart = [];

    document.addEventListener('DOMContentLoaded', function () { selectCategory(currentCategory); });

    function selectCategory(category) {
        currentCategory = category;
        currentProduct = null;
        selectedOptions = [];
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('cat-' + category).classList.add('active');
        renderSubcategories();
        clearInputs();
        calculate();
    }

    function renderSubcategories() {
        const container = document.getElementById('subcategory-container');
        container.innerHTML = '';
        const data = QUOTE_DATA[currentCategory];
        if (!data) return;
        Object.keys(data.subcategories).forEach(subKey => {
            const subcategory = data.subcategories[subKey];
            const subDiv = document.createElement('div');
            subDiv.className = 'mb-4';
            const subTitle = document.createElement('div');
            subTitle.className = 'text-xs font-semibold text-gray-500 mb-2 uppercase';
            subTitle.textContent = subcategory.name;
            subDiv.appendChild(subTitle);
            const productGrid = document.createElement('div');
            productGrid.className = 'grid grid-cols-2 md:grid-cols-3 gap-2';
            subcategory.products.forEach(product => {
                const btn = document.createElement('button');
                btn.className = 'item-btn border-2 border-gray-200 rounded-lg p-3 text-left hover:border-blue-400';
                btn.innerHTML = `<div class="font-medium text-sm">${product.name}</div><div class="text-xs text-gray-500 mt-1">${parseInt(product.unit_price).toLocaleString()}원/${product.unit}</div>`;
                btn.onclick = () => selectProduct(product, btn);
                productGrid.appendChild(btn);
            });
            subDiv.appendChild(productGrid);
            container.appendChild(subDiv);
        });
    }

    function selectProduct(product, btn) {
        currentProduct = product;
        selectedOptions = [];
        document.querySelectorAll('.item-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        showInputFields();
        renderOptions();
        document.getElementById('result-product').textContent = product.name;
        document.getElementById('result-tip').textContent = product.description || '규격을 입력해주세요.';
        calculate();
    }

    function showInputFields() {
        const types = ['area', 'text', 'length', 'fixed'];
        types.forEach(type => { const el = document.getElementById(type + '-input'); if (el) el.classList.add('hidden'); });
        const el = document.getElementById(currentProduct.calc_type + '-input');
        if (el) el.classList.remove('hidden');
    }

    function renderOptions() {
        const container = document.getElementById('options-container');
        container.innerHTML = '';
        if (!currentProduct || !currentProduct.options || currentProduct.options.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500">추가 옵션이 없습니다.</p>';
            return;
        }
        currentProduct.options.forEach((option, idx) => {
            const optDiv = document.createElement('div');
            optDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100';

            let priceText;
            if (option.discount) {
                priceText = `${(option.discount * 100).toFixed(1)}% 할인`;
            } else if (option.unit_type === 'per_m2') {
                priceText = `${option.price.toLocaleString()}원/㎡ 추가`;
            } else if (option.unit_type === 'quantity') {
                priceText = `${option.free_qty}개 무료, 초과 ${option.qty_unit_price.toLocaleString()}원/개`;
            } else {
                priceText = `${option.price.toLocaleString()}원 추가`;
            }

            const label = document.createElement('div');
            label.className = 'flex-1';
            label.innerHTML = `<span class="text-sm font-medium text-gray-700">${option.name}</span><p class="text-xs text-gray-500">${priceText}</p>`;

            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-2';

            // 수량형 옵션인 경우 수량 스테퍼 추가
            if (option.unit_type === 'quantity') {
                const defaultQty = option.default_qty || option.free_qty || 0;
                const stepperDiv = document.createElement('div');
                stepperDiv.className = 'flex items-center border rounded-lg overflow-hidden bg-white hidden';
                stepperDiv.id = `stepper-${idx}`;
                stepperDiv.innerHTML = `
                    <button type="button" class="px-2 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm" onclick="adjustOptionQty(${idx}, -1)">-</button>
                    <input type="number" id="opt-qty-${idx}" value="${defaultQty}" min="0" 
                        class="w-12 text-center text-sm outline-none border-l border-r" 
                        oninput="calculate()">
                    <button type="button" class="px-2 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm" onclick="adjustOptionQty(${idx}, 1)">+</button>
                `;
                controls.appendChild(stepperDiv);
            }

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'w-5 h-5 text-blue-600 rounded focus:ring-blue-500';
            checkbox.id = `opt-check-${idx}`;
            checkbox.onchange = (e) => {
                if (option.unit_type === 'quantity') {
                    const stepper = document.getElementById(`stepper-${idx}`);
                    if (e.target.checked) {
                        stepper.classList.remove('hidden');
                        selectedOptions.push({ ...option, optIdx: idx });
                    } else {
                        stepper.classList.add('hidden');
                        selectedOptions = selectedOptions.filter(o => o.name !== option.name);
                    }
                } else {
                    if (e.target.checked) {
                        selectedOptions.push(option);
                    } else {
                        selectedOptions = selectedOptions.filter(o => o.name !== option.name);
                    }
                }
                calculate();
            };
            controls.appendChild(checkbox);

            optDiv.appendChild(label);
            optDiv.appendChild(controls);
            container.appendChild(optDiv);
        });
    }

    function adjustOptionQty(idx, delta) {
        const input = document.getElementById(`opt-qty-${idx}`);
        let val = parseInt(input.value) || 0;
        val += delta;
        if (val < 0) val = 0;
        input.value = val;
        calculate();
    }

    function adjustQty(delta) {
        const input = document.getElementById('quantity');
        let val = parseInt(input.value) || 1;
        val += delta;
        if (val < 1) val = 1;
        input.value = val;
        calculate();
    }

    function clearInputs() {
        document.getElementById('width').value = '';
        document.getElementById('height').value = '';
        document.getElementById('char-count').value = '1';
        document.getElementById('char-size').value = '';
        document.getElementById('length').value = '';
        document.getElementById('quantity').value = '1';
    }

    function resetForm() {
        clearInputs();
        selectedOptions = [];
        document.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
        calculate();
    }

    function calculate() {
        if (!currentProduct) {
            document.getElementById('result-total').textContent = '0원';
            document.getElementById('result-spec').textContent = '-';
            document.getElementById('result-base').textContent = '0원';
            document.getElementById('add-cart-btn').disabled = true;
            return;
        }

        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        let basePrice = 0;
        let spec = '';

        if (currentProduct.calc_type === 'area') {
            const inputWidth = parseFloat(document.getElementById('width').value) || 0;
            const inputHeight = parseFloat(document.getElementById('height').value) || 0;
            let width = inputWidth;
            let height = inputHeight;

            if (currentProduct.apply_rounding && width > 0 && height > 0) {
                if (width > 0 && width < 1000) width = 1000;
                if (height > 0 && height < 1000) height = 1000;
                if (width >= 1000) width = Math.ceil(width / 100) * 100;
                if (height >= 1000) height = Math.ceil(height / 100) * 100;
            }

            const area = (width * height) / 1000000;

            // 폭 판정 로직
            const rollWidth = Math.min(inputWidth, inputHeight);
            let unitPrice = currentProduct.unit_price;
            let widthType = '';
            let widthInfoHtml = '';
            const widthInfoDiv = document.getElementById('width-info');
            const widthInfoBox = document.getElementById('width-info-box');
            const widthInfoText = document.getElementById('width-info-text');

            if (currentProduct.use_width_pricing && rollWidth > 0) {
                if (rollWidth > 4800) {
                    // 제작불가
                    widthType = '제작불가';
                    widthInfoBox.className = 'p-3 rounded-lg border bg-red-50 border-red-200';
                    widthInfoText.innerHTML = `<span class="text-red-600">⚠️ 폭 ${rollWidth}mm → <strong>제작불가</strong> (4800mm 초과)</span>`;
                    widthInfoDiv.classList.remove('hidden');
                    document.getElementById('result-total').textContent = '제작불가';
                    document.getElementById('result-spec').textContent = `${inputWidth} × ${inputHeight}mm`;
                    document.getElementById('add-cart-btn').disabled = true;
                    return;
                } else if (rollWidth > 3100) {
                    unitPrice = currentProduct.price_xlarge || currentProduct.unit_price;
                    widthType = '초장폭';
                    widthInfoBox.className = 'p-3 rounded-lg border bg-purple-50 border-purple-200';
                } else if (rollWidth > 2100) {
                    unitPrice = currentProduct.price_large || currentProduct.unit_price;
                    widthType = '장폭';
                    widthInfoBox.className = 'p-3 rounded-lg border bg-orange-50 border-orange-200';
                } else {
                    unitPrice = currentProduct.price_small || currentProduct.unit_price;
                    widthType = '단폭';
                    widthInfoBox.className = 'p-3 rounded-lg border bg-green-50 border-green-200';
                }

                // 폭 할증
                let surchargeText = '';
                if (rollWidth >= 1800 && currentProduct.width_surcharge_1800 > 0) {
                    unitPrice += currentProduct.width_surcharge_1800;
                    surchargeText = ` + 할증 ${currentProduct.width_surcharge_1800.toLocaleString()}원`;
                }

                widthInfoText.innerHTML = `폭 ${rollWidth}mm → <strong>${widthType}</strong> 적용 (㎡당 ${unitPrice.toLocaleString()}원${surchargeText})`;
                widthInfoDiv.classList.remove('hidden');
            } else {
                widthInfoDiv.classList.add('hidden');
            }

            if (area > 0) {
                let calcArea = area;
                if (currentProduct.min_area && area < currentProduct.min_area) { calcArea = currentProduct.min_area; }
                basePrice = calcArea * unitPrice * quantity;
                if (currentProduct.apply_rounding && (inputWidth !== width || inputHeight !== height)) {
                    spec = `${inputWidth} × ${inputHeight}mm → ${width} × ${height}mm (${area.toFixed(2)}㎡) × ${quantity}개`;
                } else {
                    spec = `${width} × ${height}mm (${area.toFixed(2)}㎡) × ${quantity}개`;
                }
                if (widthType) {
                    spec += ` [${widthType}]`;
                }
            }
        } else if (currentProduct.calc_type === 'text') {
            const charCount = parseInt(document.getElementById('char-count').value) || 0;
            const charSize = parseFloat(document.getElementById('char-size').value) || 0;
            if (charCount > 0 && charSize > 0) {
                const sizeRatio = charSize / (currentProduct.base_size || 450);
                basePrice = currentProduct.unit_price * sizeRatio * charCount * quantity;
                spec = `${charSize}mm × ${charCount}자 × ${quantity}세트`;
            }
        } else if (currentProduct.calc_type === 'length') {
            const length = parseFloat(document.getElementById('length').value) || 0;
            if (length > 0) {
                basePrice = (length / 1000) * currentProduct.unit_price * quantity;
                spec = `${length}mm × ${quantity}개`;
            }
        } else if (currentProduct.calc_type === 'fixed') {
            basePrice = currentProduct.unit_price * quantity;
            spec = `${quantity}개`;
        }

        let optionPrice = 0;
        let discount = 0;

        // 면적 계산 (per_m2 옵션용)
        let area = 0;
        if (currentProduct.calc_type === 'area') {
            const inputWidth = parseFloat(document.getElementById('width').value) || 0;
            const inputHeight = parseFloat(document.getElementById('height').value) || 0;
            area = (inputWidth * inputHeight) / 1000000;
        }

        selectedOptions.forEach(option => {
            if (option.discount) {
                discount += basePrice * option.discount;
            } else if (option.unit_type === 'per_m2' && area > 0) {
                // ㎡당 추가금액: 면적 × 옵션금액 × 수량
                optionPrice += option.price * area * quantity;
            } else if (option.unit_type === 'quantity') {
                // 수량형 옵션: (선택수량 - 무료수량) × 초과단가 × 제품수량
                const optQtyInput = document.getElementById(`opt-qty-${option.optIdx}`);
                const optQty = optQtyInput ? parseInt(optQtyInput.value) || 0 : 0;
                const extraQty = Math.max(0, optQty - (option.free_qty || 0));
                optionPrice += extraQty * option.qty_unit_price * quantity;
            } else {
                optionPrice += option.price * quantity;
            }
        });

        const total = basePrice + optionPrice - discount;

        document.getElementById('result-spec').textContent = spec || '-';
        document.getElementById('result-base').textContent = Math.round(basePrice).toLocaleString() + '원';

        const optRow = document.getElementById('row-options');
        if (optionPrice > 0) { optRow.style.display = 'flex'; document.getElementById('result-options').textContent = Math.round(optionPrice).toLocaleString() + '원'; } else { optRow.style.display = 'none'; }

        const discRow = document.getElementById('row-discount');
        if (discount > 0) { discRow.style.display = 'flex'; document.getElementById('result-discount').textContent = '-' + Math.round(discount).toLocaleString() + '원'; } else { discRow.style.display = 'none'; }

        document.getElementById('result-total').textContent = Math.round(total).toLocaleString() + '원';
        document.getElementById('add-cart-btn').disabled = total === 0;
    }

    function addToCart() {
        if (!currentProduct) return;
        const total = parseInt(document.getElementById('result-total').textContent.replace(/[^0-9]/g, ''));
        if (total === 0) return;

        const item = {
            name: currentProduct.name,
            spec: document.getElementById('result-spec').textContent,
            price: total,
            options: selectedOptions.map(o => o.name)
        };

        cart.push(item);
        updateCart();
        alert('장바구니에 추가되었습니다!');
    }

    function updateCart() {
        const container = document.getElementById('cart-items');
        if (cart.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">장바구니가 비어있습니다</p>';
            document.getElementById('cart-total').textContent = '0원';
            return;
        }

        container.innerHTML = '';
        let grandTotal = 0;

        cart.forEach((item, idx) => {
            grandTotal += item.price;
            const itemDiv = document.createElement('div');
            itemDiv.className = 'p-3 bg-gray-50 rounded-lg border border-gray-200';
            itemDiv.innerHTML = `
            <div class="flex justify-between items-start mb-1">
                <div class="flex-1">
                    <div class="font-medium text-sm text-gray-900">${item.name}</div>
                    <div class="text-xs text-gray-500 mt-1">${item.spec}</div>
                    ${item.options.length > 0 ? `<div class="text-xs text-blue-600 mt-1">옵션: ${item.options.join(', ')}</div>` : ''}
                </div>
                <button onclick="removeFromCart(${idx})" class="ml-2 text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="text-right text-sm font-semibold text-gray-900">${item.price.toLocaleString()}원</div>
        `;
            container.appendChild(itemDiv);
        });

        document.getElementById('cart-total').textContent = grandTotal.toLocaleString() + '원';
    }

    function removeFromCart(idx) {
        cart.splice(idx, 1);
        updateCart();
    }
</script>

<?php include_once(G5_THEME_PATH . '/tail.php'); ?>
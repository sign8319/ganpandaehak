<?php
if (!defined('_GNUBOARD_'))
    exit;

/**
 * [STEP 1: 데이터 통합 및 접근 해제] 
 * 통합 헤더 컴포넌트: 견적명, 상호, 고객명, 연락처, 주소 섹션
 * 모든 페이지(1, 2, 3단계)에서 동일한 위치에 보이게 처리
 */
?>
<style>
    :root {
        --primary-color: #FF6B2C;
        --bg-gray: #F8F9FA;
        --border-color: #F1F3F5;
        --text-main: #333D4B;
        --text-sub: #8B95A1;
        --radius: 12px;
        --shadow-soft: 0 8px 30px rgba(0, 0, 0, 0.04);
    }

    .quote-header-card {
        background: #FFFFFF;
        border-radius: var(--radius);
        box-shadow: var(--shadow-soft);
        padding: 40px;
        margin-bottom: 24px;
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .step-nav-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 40px;
    }

    .step-nav-item {
        flex: 1;
        padding: 20px;
        background: var(--bg-gray);
        border-radius: var(--radius);
        text-align: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
    }

    .step-nav-item.active {
        background: #FFF9F5;
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .step-num {
        font-size: 12px;
        font-weight: 600;
        color: #ADB5BD;
        margin-bottom: 6px;
        display: block;
        letter-spacing: 0.5px;
    }

    .step-nav-item.active .step-num {
        color: var(--primary-color);
    }

    .step-txt {
        font-size: 16px;
        color: var(--text-main);
        font-weight: 500;
    }

    .step-nav-item.active .step-txt {
        color: var(--primary-color);
        font-weight: 700;
    }

    .header-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 24px;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .input-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-sub);
        margin-left: 2px;
    }

    .input-field {
        height: 44px;
        padding: 0 18px;
        background: var(--bg-gray);
        border: 1px solid transparent;
        border-radius: var(--radius);
        font-size: 15px;
        color: var(--text-main);
        transition: all 0.2s ease;
    }

    .input-field::placeholder {
        color: #ADB5BD;
    }

    .input-field:focus {
        background: #FFFFFF;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(255, 107, 44, 0.1);
        outline: none;
    }

    .btn-zip {
        height: 52px;
        padding: 0 24px;
        background: var(--text-main);
        color: white;
        font-weight: 600;
        border-radius: var(--radius);
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .btn-zip:hover {
        background: #000000;
        transform: translateY(-1px);
    }

    /* Postcode Embed Container */
    #postcode_wrap {
        position: relative;
        background: white;
        border: 2px solid var(--primary-color);
        border-radius: var(--radius);
        overflow: hidden;
        margin-top: 12px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #postcode_wrap.hidden {
        display: none;
    }

    .postcode-close-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 10;
        width: 36px;
        height: 36px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.2s ease;
    }

    .postcode-close-btn:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(1.1);
    }

    .autocomplete-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        background: white;
        border-radius: var(--radius);
        margin-top: 5px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
        max-height: 300px;
        overflow-y: auto;
    }

    .autocomplete-item {
        padding: 12px 18px;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f8f9fa;
        text-align: left;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #FFF9F5;
    }
</style>

<?php
if (isset($hide_header_inputs) && $hide_header_inputs): ?>
    <!-- 목록 페이지: 헤더 전체 숨김 -->
<?php else: ?>
    <div class="quote-header-card" x-data="quoteHeader()">
        <!-- Step Navigation (접근 제한 해제) -->
        <div class="step-nav-bar">
            <div class="step-nav-item" :class="currentStep === 1 ? 'active' : ''" @click="goToStep(1)">
                <span class="step-num">Step 01</span>
                <span class="step-txt">현장 실측</span>
            </div>
            <div class="step-nav-item" :class="currentStep === 2 ? 'active' : ''" @click="goToStep(2)">
                <span class="step-num">Step 02</span>
                <span class="step-txt">견적 작성</span>
            </div>
            <div class="step-nav-item" :class="currentStep === 3 ? 'active' : ''" @click="goToStep(3)">
                <span class="step-num">Step 03</span>
                <span class="step-txt">고객 등록</span>
            </div>
        </div>

        <!-- 통합 헤더 정보 -->
        <!-- 통합 헤더 정보 -->
        <div class="header-grid">
            <div class="col-span-12 lg:col-span-8 input-group">
                <span class="input-label">견적명 (Project Name)</span>
                <input type="text" name="qa_subject" value="<?php echo htmlspecialchars($quote['qa_subject'] ?? ''); ?>"
                    placeholder="프로젝트명을 입력하세요" class="input-field" @input="debouncedSave">
            </div>

            <!-- Customer Name & Search -->
            <div class="col-span-12 lg:col-span-4 input-group relative" x-data="{ 
                results: [], 
                showResults: false,
                selectedIndex: -1,
                search() {
                    let keyword = $el.querySelector('input[name=qa_tax_company_name]').value;
                    if(keyword.length < 2) { // 2자 이상 입력 시 검색
                        this.results = [];
                        this.showResults = false;
                        return;
                    }
                    fetch(location.pathname + '?w=ajax_search&keyword=' + encodeURIComponent(keyword))
                        .then(res => res.json())
                        .then(data => {
                            this.results = data.customers || [];
                            this.showResults = this.results.length > 0;
                            this.selectedIndex = -1;
                        });
                },
                select(c) {
                    if(window.selectCustomer) {
                        // selectCustomer expects encoded string
                        window.selectCustomer(encodeURIComponent(JSON.stringify(c)));
                    }
                    this.showResults = false;
                }
            }" @click.away="showResults = false">
                <span class="input-label flex justify-between">
                    <span>상호 (Business Name)</span>
                    <button type="button" onclick="openCustomerSearchModal()"
                        class="text-xs text-orange-600 font-bold hover:underline cursor-pointer">
                        <i class="fas fa-search"></i> 고객 검색
                    </button>
                </span>
                <input type="text" name="qa_tax_company_name"
                    value="<?php echo htmlspecialchars($quote['qa_tax_company_name'] ?? ''); ?>" placeholder="사업자명을 입력하세요"
                    class="input-field" @input="search(); debouncedSave();"
                    @keydown.down.prevent="selectedIndex = (selectedIndex + 1) % results.length; showResults = true"
                    @keydown.up.prevent="selectedIndex = (selectedIndex - 1 + results.length) % results.length"
                    @keydown.enter.prevent="if(selectedIndex > -1) select(results[selectedIndex])" autocomplete="off">

                <!-- Autocomplete Results -->
                <div class="autocomplete-results" x-show="showResults" x-cloak>
                    <template x-for="(c, index) in results" :key="c.customer_id">
                        <div class="autocomplete-item" :class="selectedIndex === index ? 'active' : ''" @click="select(c)"
                            @mouseenter="selectedIndex = index">
                            <div class="text-sm font-bold text-gray-800"
                                x-text="c.customer_company ? c.customer_company : c.customer_name"></div>
                            <div class="text-xs text-gray-500">
                                <span x-text="c.customer_company ? c.customer_name : (c.customer_manager || '-')"></span>
                                <span x-text="' | ' + (c.customer_hp || '-')"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Hidden Customer ID -->
                <input type="hidden" name="qa_customer_id" value="<?php echo $quote['qa_customer_id'] ?? 0; ?>">
            </div>

            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">고객명 / 담당자</span>
                <input type="text" name="qa_client_name"
                    value="<?php echo htmlspecialchars($quote['qa_client_name'] ?? ''); ?>" placeholder="실명을 입력해 주세요"
                    class="input-field" @input="debouncedSave">
            </div>
            <!-- Remove old simple block if lines match effectively replacing previous -->
            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">연락처 (Contact)</span>
                <input type="text" name="qa_client_hp" value="<?php echo htmlspecialchars($quote['qa_client_hp'] ?? ''); ?>"
                    placeholder="010-0000-0000" class="input-field" oninput="autoFormatPhoneNumber(this)"
                    @input="debouncedSave">
            </div>
            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">이메일 (Email)</span>
                <input type="email" name="qa_client_email"
                    value="<?php echo htmlspecialchars($quote['qa_client_email'] ?? ''); ?>" placeholder="example@mail.com"
                    class="input-field" @input="debouncedSave">
            </div>
            <div class="col-span-12 lg:col-span-12 input-group mt-2">
                <span class="input-label">시공 주소 (Address)</span>
                <div class="grid grid-cols-12 gap-3">
                    <input type="text" name="qa_client_addr" id="qa_client_addr"
                        value="<?php echo htmlspecialchars($quote['qa_client_addr'] ?? ''); ?>"
                        placeholder="주소 검색 버튼을 이용해 주세요" class="input-field col-span-9" readonly @input="debouncedSave">
                    <button type="button" onclick="openPostcodeSearch()"
                        class="admin-btn admin-btn-black col-span-3 w-full">주소
                        검색</button>
                </div>
                <input type="text" name="qa_client_addr2"
                    value="<?php echo htmlspecialchars($quote['qa_client_addr2'] ?? ''); ?>" placeholder="나머지 상세 주소를 입력하세요"
                    class="input-field mt-1" @input="debouncedSave">

                <!-- Kakao Postcode Embed Container -->
                <div id="postcode_wrap" class="hidden">
                    <button type="button" class="postcode-close-btn" onclick="closePostcodeEmbed()" title="닫기">×</button>
                    <div id="postcode_embed_area" style="height: 450px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Include Search Modal -->
    <?php include_once(G5_THEME_PATH . '/includes/modals/search_customer_modal.php'); ?>
<?php endif; ?>

<script>
    // Global Debounce Utility
    function debounce(func, timeout = 500) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    // Global Sync Deposit & Balance
    window.sync_deposit = function (input) {
        let val = input.value.replace(/[^0-9]/g, '');
        input.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

        let totalVal = parseInt(document.getElementById('txt_total').innerText.replace(/[^0-9]/g, '')) || 0;
        let depositVal = parseInt(val) || 0;
        let balance = totalVal - depositVal;

        document.getElementById('txt_balance').innerText = balance.toLocaleString();

        // Auto save on deposit change
        window.debouncedSave();
    };

    // Global Phone Number Formatter
    window.autoFormatPhoneNumber = function (input) {
        let str = input.value.replace(/[^0-9]/g, '');
        let tmp = '';
        if (str.length < 4) {
            tmp = str;
        } else if (str.substring(0, 2) == '02') {
            if (str.length < 6) {
                tmp = str.substr(0, 2) + '-' + str.substr(2);
            } else if (str.length < 10) {
                tmp = str.substr(0, 2) + '-' + str.substr(2, 3) + '-' + str.substr(5);
            } else {
                tmp = str.substr(0, 2) + '-' + str.substr(2, 4) + '-' + str.substr(6);
            }
        } else {
            if (str.length < 7) {
                tmp = str.substr(0, 3) + '-' + str.substr(3);
            } else if (str.length < 11) {
                tmp = str.substr(0, 3) + '-' + str.substr(3, 3) + '-' + str.substr(6);
            } else {
                tmp = str.substr(0, 3) + '-' + str.substr(3, 4) + '-' + str.substr(7);
            }
        }
        input.value = tmp;
    };

    // Global Phone Number Formatter
    window.autoFormatPhoneNumber = function (input) {
        let str = input.value.replace(/[^0-9]/g, '');
        let tmp = '';
        if (str.length < 4) {
            tmp = str;
        } else if (str.substring(0, 2) == '02') {
            if (str.length < 6) {
                tmp = str.substr(0, 2) + '-' + str.substr(2);
            } else if (str.length < 10) {
                tmp = str.substr(0, 2) + '-' + str.substr(2, 3) + '-' + str.substr(5);
            } else {
                tmp = str.substr(0, 2) + '-' + str.substr(2, 4) + '-' + str.substr(6);
            }
        } else {
            if (str.length < 7) {
                tmp = str.substr(0, 3) + '-' + str.substr(3);
            } else if (str.length < 11) {
                tmp = str.substr(0, 3) + '-' + str.substr(3, 3) + '-' + str.substr(6);
            } else {
                tmp = str.substr(0, 3) + '-' + str.substr(3, 4) + '-' + str.substr(7);
            }
        }
        input.value = tmp;
    };

    function quoteHeader() {
        return {
            currentStep: <?php
            $script_name = basename($_SERVER['SCRIPT_NAME']);
            if ($script_name == 'admin_quote_step1.php')
                echo 1;
            else if ($script_name == 'admin_quote.php' && isset($w) && $w == 'form')
                echo 2;
            else if ($script_name == 'admin_customer.php')
                echo 3;
            else
                echo 0; // 0 = 목록 페이지 (스텝 없음)
            ?>,
            qa_id: '<?php echo $qa_id ?: 0; ?>',

            init() {
                // Register global save function
                window.saveHeader = () => this.saveHeader();
                window.debouncedSave = debounce(() => this.saveHeader(), 1000);
            },

            saveHeader() {
                const taxName = (document.getElementsByName('qa_tax_company_name')[0]?.value || '').trim();
                const clientName = (document.getElementsByName('qa_client_name')[0]?.value || '').trim();
                const clientHp = (document.getElementsByName('qa_client_hp')[0]?.value || '').trim();
                const clientEmail = (document.getElementsByName('qa_client_email')[0]?.value || '').trim();
                const customerId = (document.getElementsByName('qa_customer_id')[0]?.value || '0');

                if (!this.qa_id || this.qa_id === '0') {
                    // [Delayed Creation] Only create if there is meaningful input
                    if (taxName || clientName || clientHp || (customerId && customerId !== '0')) {
                        const createData = new FormData();
                        createData.append('w', 'ajax_create_quote');
                        createData.append('qa_subject', document.getElementsByName('qa_subject')[0].value || '신규 견적');
                        createData.append('qa_tax_company_name', taxName);
                        createData.append('qa_client_name', clientName);
                        createData.append('qa_client_hp', clientHp);
                        createData.append('qa_client_email', clientEmail);
                        createData.append('qa_customer_id', customerId);

                        fetch(location.pathname, { method: 'POST', body: createData })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.qa_id) {
                                    this.qa_id = data.qa_id;
                                    // Update URL immediately
                                    const newUrl = new URL(window.location);
                                    newUrl.searchParams.set('qa_id', this.qa_id);
                                    if (!newUrl.searchParams.has('w')) newUrl.searchParams.set('w', 'form');
                                    window.history.replaceState({ qa_id: this.qa_id }, '', newUrl);

                                    // Update all hidden qa_id inputs in the document
                                    document.querySelectorAll('input[name="qa_id"]').forEach(el => el.value = this.qa_id);

                                    // Continue to save other header fields
                                    this.saveHeader();
                                }
                            });
                    }
                    return;
                }

                const formData = new FormData();
                formData.append('w', 'ajax_save_header');
                formData.append('qa_id', this.qa_id);
                formData.append('qa_subject', document.getElementsByName('qa_subject')[0].value);
                formData.append('qa_tax_company_name', taxName);
                formData.append('qa_client_name', clientName);
                formData.append('qa_client_hp', clientHp);
                formData.append('qa_client_email', clientEmail);
                formData.append('qa_customer_id', customerId);

                // Add Status if present in sidebar
                const stSelect = document.getElementsByName('qa_status')[0];
                if (stSelect) formData.append('qa_status', stSelect.value);

                const addrInput = document.getElementById('qa_client_addr');
                const addr2Input = document.getElementsByName('qa_client_addr2')[0];
                formData.append('qa_client_addr', addrInput ? addrInput.value : '');
                formData.append('qa_client_addr2', addr2Input ? addr2Input.value : '');

                // Add Deposit if present
                const depositDummy = document.getElementById('qa_deposit_dummy');
                if (depositDummy) {
                    formData.append('qa_deposit', depositDummy.value.replace(/[^0-9]/g, ''));
                }

                fetch(location.pathname, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Header saved:', data.success);
                    });
            },

            goToStep(step) {
                let targetPage = '';
                switch (step) {
                    case 1: targetPage = 'admin_quote_step1.php'; break;
                    case 2: targetPage = 'admin_quote.php'; break;
                    case 3: targetPage = 'admin_customer.php'; break;
                }

                // Get current URL parameters
                const params = new URLSearchParams(window.location.search);
                const customerId = params.get('customer_id');
                const wMode = params.get('w') || 'form';

                // Build URL with preserved parameters
                let url = './' + targetPage + '?w=' + wMode;

                // Add qa_id if exists
                if (this.qa_id && this.qa_id !== '0') {
                    url += '&qa_id=' + this.qa_id;
                }

                // Add customer_id if exists
                if (customerId) {
                    url += '&customer_id=' + customerId;
                }

                // [Step 1 Integration]
                // If specific unified navigation function exists (Step 1), delegate to it.
                // This will trigger the "Save Changes" modal if active.
                if (typeof window.navigateToPage === 'function') {
                    window.navigateToPage(url);
                    return;
                }

                // Otherwise just go
                location.href = url;
            }
        };
    }

    // [FIX] Dynamic Kakao Postcode Loading with Fallback
    let postcodeScriptLoaded = false;
    let postcodeScriptLoading = false;

    window.openPostcodeSearch = function () {
        // Check if script is already loaded
        if (window.daum && window.daum.Postcode) {
            executePostcodeSearch();
            return;
        }

        // Check if script is currently loading
        if (postcodeScriptLoading) {
            alert('주소 검색 스크립트를 로딩 중입니다. 잠시만 기다려주세요.');
            return;
        }

        // Load script dynamically
        postcodeScriptLoading = true;
        const script = document.createElement('script');
        script.src = 'https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js';
        script.async = true;

        script.onload = function () {
            postcodeScriptLoaded = true;
            postcodeScriptLoading = false;
            console.log('Kakao Postcode script loaded successfully');
            executePostcodeSearch();
        };

        script.onerror = function () {
            postcodeScriptLoading = false;
            console.error('Failed to load Kakao Postcode script');

            // Fallback: Allow manual input
            if (confirm('주소 검색 서비스를 불러올 수 없습니다.\n(네트워크 문제 또는 광고 차단기에 의해 차단되었을 수 있습니다)\n\n주소를 직접 입력하시겠습니까?')) {
                const addrInput = document.getElementById('qa_client_addr');
                if (addrInput) {
                    addrInput.removeAttribute('readonly');
                    addrInput.focus();
                    addrInput.placeholder = '주소를 직접 입력해주세요';
                }
            }
        };

        document.head.appendChild(script);
    };

    function executePostcodeSearch() {
        // Show embed container
        const postcodeWrap = document.getElementById('postcode_wrap');
        const embedArea = document.getElementById('postcode_embed_area');

        if (!postcodeWrap || !embedArea) {
            console.error('Postcode container not found');
            return;
        }

        postcodeWrap.classList.remove('hidden');

        // Embed Kakao Postcode
        new daum.Postcode({
            oncomplete: function (data) {
                // 도로명 주소 우선, 없으면 지번 주소
                let fullAddr = data.roadAddress || data.jibunAddress;

                // 주소 입력
                const addrInput = document.getElementById('qa_client_addr');
                if (addrInput) {
                    addrInput.value = fullAddr;

                    // Trigger Alpine.js @input event for auto-save
                    const event = new Event('input', { bubbles: true });
                    addrInput.dispatchEvent(event);
                }

                // Close embed container
                closePostcodeEmbed();

                // 상세주소 입력 필드로 포커스 이동
                const addr2Input = document.querySelector('input[name="qa_client_addr2"]');
                if (addr2Input) {
                    setTimeout(() => addr2Input.focus(), 100);
                }
            },
            width: '100%',
            height: '100%'
        }).embed(embedArea);

        // Scroll to postcode area
        setTimeout(() => {
            postcodeWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }

    window.closePostcodeEmbed = function () {
        const postcodeWrap = document.getElementById('postcode_wrap');
        const embedArea = document.getElementById('postcode_embed_area');

        if (postcodeWrap) {
            postcodeWrap.classList.add('hidden');
        }

        if (embedArea) {
            embedArea.innerHTML = ''; // Clear embed content
        }
    }
</script>
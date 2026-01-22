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
        height: 52px;
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
</style>

<?php if (!isset($hide_header_inputs) || !$hide_header_inputs): ?>
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
        <div class="header-grid">
            <div class="col-span-12 lg:col-span-8 input-group">
                <span class="input-label">견적명 (Project Name)</span>
                <input type="text" name="qa_subject" value="<?php echo htmlspecialchars($quote['qa_subject'] ?? ''); ?>"
                    placeholder="프로젝트명을 입력하세요" class="input-field" @input="debouncedSave">
            </div>
            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">상호 (Business Name)</span>
                <input type="text" name="qa_tax_company_name"
                    value="<?php echo htmlspecialchars($quote['qa_tax_company_name'] ?? ''); ?>" placeholder="사업자명을 입력하세요"
                    class="input-field" @input="debouncedSave">
            </div>
            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">고객명 / 담당자</span>
                <input type="text" name="qa_client_name"
                    value="<?php echo htmlspecialchars($quote['qa_client_name'] ?? ''); ?>" placeholder="실명을 입력해 주세요"
                    class="input-field" @input="debouncedSave">
            </div>
            <div class="col-span-12 lg:col-span-4 input-group">
                <span class="input-label">연락처 (Contact)</span>
                <input type="text" name="qa_client_hp" value="<?php echo htmlspecialchars($quote['qa_client_hp'] ?? ''); ?>"
                    placeholder="010-0000-0000" class="input-field" @input="debouncedSave">
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
                    <button type="button"
                        onclick="win_zip('fquote', 'qa_client_addr', 'qa_client_addr2', 'qa_client_addr3', 'qa_client_addr_jibeon');"
                        class="btn-zip col-span-3">주소 검색</button>
                </div>
                <input type="text" name="qa_client_addr2"
                    value="<?php echo htmlspecialchars($quote['qa_client_addr2'] ?? ''); ?>" placeholder="나머지 상세 주소를 입력하세요"
                    class="input-field mt-1" @input="debouncedSave">
            </div>
        </div>
    </div>
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
                if (!this.qa_id || this.qa_id === '0') {
                    const createData = new FormData();
                    createData.append('w', 'ajax_create_quote');
                    createData.append('qa_subject', document.getElementsByName('qa_subject')[0].value || '신규 견적');
                    createData.append('qa_client_name', document.getElementsByName('qa_client_name')[0].value || '');
                    createData.append('qa_client_hp', document.getElementsByName('qa_client_hp')[0].value || '');
                    createData.append('qa_client_email', document.getElementsByName('qa_client_email')[0].value || '');

                    fetch(location.pathname, { method: 'POST', body: createData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.qa_id) {
                                this.qa_id = data.qa_id;
                                const newUrl = new URL(window.location);
                                newUrl.searchParams.set('qa_id', this.qa_id);
                                if (!newUrl.searchParams.has('w')) newUrl.searchParams.set('w', 'form');
                                window.history.replaceState({}, '', newUrl);
                                this.saveHeader();
                            }
                        });
                    return;
                }

                const formData = new FormData();
                formData.append('w', 'ajax_save_header');
                formData.append('qa_id', this.qa_id);
                formData.append('qa_subject', document.getElementsByName('qa_subject')[0].value);
                formData.append('qa_tax_company_name', document.getElementsByName('qa_tax_company_name')[0].value);
                formData.append('qa_client_name', document.getElementsByName('qa_client_name')[0].value);
                formData.append('qa_client_hp', document.getElementsByName('qa_client_hp')[0].value);
                formData.append('qa_client_email', document.getElementsByName('qa_client_email')[0].value);

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

                let url = './' + targetPage + '?w=form';
                if (this.qa_id && this.qa_id !== '0') {
                    url += '&qa_id=' + this.qa_id;
                }
                location.href = url;
            }
        };
    }
</script>
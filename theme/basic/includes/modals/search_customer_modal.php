<?php
if (!defined('_GNUBOARD_'))
    exit;
?>
<!-- Customer Search Modal -->
<div id="customerSearchModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCustomerSearchModal()"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-search text-orange-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">고객 검색</h3>
                            <div class="mt-2">
                                <div class="relative">
                                    <input type="text" id="customerSearchInput" placeholder="고객명 또는 담당자명을 입력하세요"
                                        class="w-full rounded-md border-0 py-2.5 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6"
                                        onkeyup="if(event.key === 'Enter') searchCustomer()">
                                    <button type="button" onclick="searchCustomer()"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-orange-600">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div id="customerSearchResults"
                                    class="mt-4 max-h-60 overflow-y-auto divide-y divide-gray-100">
                                    <!-- Search results will appear here -->
                                    <div class="text-center py-4 text-gray-500 text-sm">검색어를 입력해주세요</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" onclick="closeCustomerSearchModal()"
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">닫기</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openCustomerSearchModal() {
        document.getElementById('customerSearchModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('customerSearchInput').focus(), 100);
    }

    function closeCustomerSearchModal() {
        document.getElementById('customerSearchModal').classList.add('hidden');
    }

    function searchCustomer() {
        const keyword = document.getElementById('customerSearchInput').value;
        if (!keyword) {
            alert('검색어를 입력해주세요.');
            return;
        }

        const resultsDiv = document.getElementById('customerSearchResults');
        resultsDiv.innerHTML = '<div class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin"></i> 검색 중...</div>';

        // Assuming ajax_search logic is in admin_customer.php
        fetch('./admin_customer.php?w=ajax_search&keyword=' + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                const list = data.customers || [];
                if (list.length === 0) {
                    resultsDiv.innerHTML = '<div class="text-center py-4 text-gray-500">검색 결과가 없습니다.</div>';
                    return;
                }

                let html = '<ul role="list" class="divide-y divide-gray-100">';
                list.forEach(c => {
                    // Safe handling of nulls
                    const name = c.customer_name || '';
                    const manager = c.customer_manager || '-';
                    const hp = c.customer_hp || '-';

                    // Encode data to pass to function
                    const dataStr = encodeURIComponent(JSON.stringify(c));

                    html += `
                    <li class="flex justify-between gap-x-6 py-3 cursor-pointer hover:bg-gray-50 px-2 rounded" onclick="selectCustomer('${dataStr}')">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto text-left">
                                <p class="text-sm font-semibold leading-6 text-gray-900">${name}</p>
                                <p class="mt-1 truncate text-xs leading-5 text-gray-500">${manager} | ${hp}</p>
                            </div>
                        </div>
                        <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                            <button type="button" class="text-xs font-semibold text-orange-600 hover:text-orange-500">선택</button>
                        </div>
                    </li>`;
                });
                html += '</ul>';
                resultsDiv.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                resultsDiv.innerHTML = '<div class="text-center py-4 text-red-500">오류가 발생했습니다.</div>';
            });
    }

    function selectCustomer(encodedData) {
        try {
            const customer = JSON.parse(decodeURIComponent(encodedData));

            // 1. Fill Step 1 / Header Info
            // Name
            const clientNameInput = document.querySelector('input[name="qa_client_name"]');
            if (clientNameInput) {
                clientNameInput.value = customer.customer_manager || customer.customer_name;
                clientNameInput.dispatchEvent(new Event('input'));
            }

            // Company Code/Tax Name
            const taxCompanyInput = document.querySelector('input[name="qa_tax_company_name"]');
            if (taxCompanyInput) {
                taxCompanyInput.value = customer.customer_name; // Or tax_company_name if exists in basic info
                taxCompanyInput.dispatchEvent(new Event('input'));
            }

            // Contact
            const hpInput = document.querySelector('input[name="qa_client_hp"]');
            if (hpInput) {
                hpInput.value = customer.customer_hp;
                // Auto format phone number if function exists
                if (window.autoFormatPhoneNumber) window.autoFormatPhoneNumber(hpInput);
                hpInput.dispatchEvent(new Event('input'));
            }

            // Email
            const emailInput = document.querySelector('input[name="qa_client_email"]');
            if (emailInput) {
                emailInput.value = customer.customer_email;
                emailInput.dispatchEvent(new Event('input'));
            }

            // Address
            const addrInput = document.querySelector('input[name="qa_client_addr"]');
            if (addrInput) {
                addrInput.value = customer.customer_addr;
                addrInput.dispatchEvent(new Event('input'));
            }
            // Address 2 - customer usually doesn't have split address in basic table yet?
            // If g5_customer has manual address splitting logic, handle it. 
            // For now assuming customer_addr is full or primary.

            // 2. Set Hidden Customer ID
            const custIdInput = document.querySelector('input[name="qa_customer_id"]');
            if (custIdInput) {
                custIdInput.value = customer.customer_id;
            }

            // 3. Trigger Backend Sync for Tax Info (Step 3)
            // If we are in Step 3, we might need to update UI immediately too.
            // But let's first save header to trigger backend "populate from default"
            if (window.saveHeader) {
                // Wait a bit for inputs to propagate
                setTimeout(() => {
                    window.saveHeader();
                    // If on Step 3, reload or fetch updated tax info? 
                    // Or populate Step 3 UI directly from customer data here.
                    populateStep3TaxInfo(customer);
                }, 200);
            }

            closeCustomerSearchModal();

        } catch (e) {
            console.error('Error selecting customer:', e);
            alert('고객 정보를 불러오는 중 오류가 발생했습니다.');
        }
    }

    function populateStep3TaxInfo(c) {
        // Only run if elements exist (Step 3 or View)

        // Tax Checkbox
        const taxCheck = document.getElementById('qa_tax_yn_check');
        if (taxCheck && (c.tax_biz_num || c.tax_company_name)) {
            // If customer has tax info, assume Y? Or check if tax_biz_num is not empty.
            if (!taxCheck.checked) {
                taxCheck.click(); // Trigger toggle logic
            }
        }

        const map = {
            'qa_construct_date': null, // Not from customer
            'qa_deposit_status': c.qa_deposit_status || '입금대기',
            'qa_payment_method': c.payment_method,
            'qa_tax_type': c.tax_type,
            'qa_tax_claim_type': '01', // Default to Receipts
            'qa_tax_trade_name': null, // Default
            'qa_tax_ceo_name': c.tax_ceo_name,
            'qa_tax_company_name': c.tax_company_name || c.customer_name,
            'qa_tax_email': c.tax_email || c.customer_email,
            'qa_tax_biz_num': c.tax_biz_num,
            'qa_tax_addr': c.tax_addr,
            'qa_tax_item_name': c.tax_item_name,
            'qa_tax_condition': c.tax_condition, // New
            'qa_tax_sector': c.tax_sector // New
        };

        for (const [key, val] of Object.entries(map)) {
            if (val === null || val === undefined) continue;
            const input = document.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = val;
                // Dispatch events if needed
                input.dispatchEvent(new Event('input'));
            }
            // For Selects
            const select = document.querySelector(`select[name="${key}"]`);
            if (select) {
                select.value = val;
                select.dispatchEvent(new Event('change'));
            }
        }
    }
</script>
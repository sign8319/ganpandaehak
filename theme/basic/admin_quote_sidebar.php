<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
?>
<!-- Right: Sticky Sidebar (30%) -->
<div class="lg:col-span-3">
    <div class="sticky top-6 space-y-6">
        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-100">
                <button type="button" id="tab_btn_summary" onclick="switch_sidebar_tab('summary')"
                    class="flex-1 py-3 text-sm font-bold text-orange-600 border-b-2 border-orange-600 bg-white transition">
                    📊 요약
                </button>
                <button type="button" id="tab_btn_preview" onclick="switch_sidebar_tab('preview')"
                    class="flex-1 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                    👁️ 미리보기
                </button>
            </div>

            <!-- Tab: Summary -->
            <div id="side_tab_summary" class="p-6 space-y-6">
                <!-- Price Summary -->
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">공급가액</span>
                        <span id="txt_supply" class="font-bold text-gray-800">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">부가세 (10%)</span>
                        <span id="txt_vat" class="font-bold text-gray-800">0</span>
                    </div>
                    <div class="h-px bg-gray-200"></div>
                    <div class="flex justify-between text-lg">
                        <span class="font-bold text-gray-900">총 금액</span>
                        <span id="txt_total" class="font-extrabold text-orange-600">0</span>
                    </div>
                </div>

                <!-- Deposit -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">계약금</label>
                    <input type="text" id="qa_deposit_dummy"
                        value="<?php echo number_format($quote['qa_deposit'] ?? 0); ?>" oninput="sync_deposit(this)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-right font-bold text-gray-800 focus:ring-2 focus:ring-orange-500">
                    <input type="hidden" name="qa_deposit" value="<?php echo $quote['qa_deposit'] ?? 0; ?>">
                </div>

                <div class="h-px bg-gray-200"></div>

                <!-- Balance -->
                <div class="flex justify-between text-base">
                    <span class="font-bold text-gray-700">잔금</span>
                    <span id="txt_balance" class="font-extrabold text-blue-600">0</span>
                </div>

                <!-- Save Button -->
                <button type="button" onclick="fquote_submit('save'); return false;"
                    class="admin-btn w-full admin-btn-primary shadow-lg mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    저장하기
                </button>

                <!-- Delete/Cancel Button -->
                <button type="button" onclick="deleteQuote(<?php echo isset($qa_id) ? (int) $qa_id : 0; ?>)"
                    class="admin-btn w-full bg-white border border-gray-200 text-red-500 font-bold hover:bg-gray-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    작성 취소 (삭제)
                </button>
            </div>

            <!-- Tab: Preview -->
            <div id="side_tab_preview" class="p-6 space-y-4 hidden">
                <p class="text-sm text-gray-500 text-center py-8">
                    견적서를 저장한 후<br>미리보기를 확인할 수 있습니다.
                </p>

                <?php if ($qa_id): ?>
                    <div class="space-y-3">
                        <button type="button" onclick="open_preview_modal(<?php echo $qa_id; ?>)"
                            class="admin-btn w-full admin-btn-outline-orange">
                            <span>👁️</span> 견적서 미리보기
                        </button>

                        <button type="button" onclick="copy_share_link(<?php echo $qa_id; ?>)"
                            class="admin-btn w-full admin-btn-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            링크 복사
                        </button>

                        <button type="button" onclick="send_email(<?php echo $qa_id; ?>)"
                            class="admin-btn w-full admin-btn-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            이메일 발송
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Toss Style) -->
<div id="delete_confirm_modal" class="hidden fixed inset-0 z-[99999]" role="dialog" aria-modal="true">
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="close_delete_confirm()"></div>
        <div
            class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative z-[100000]">
            <div class="bg-white px-6 pt-8 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-5">
                    <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">정말 삭제하시겠습니까?</h3>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    작성 중인 내용이 모두 사라지며,<br>삭제된 데이터는 복구할 수 없습니다.
                </p>
                <!-- [NEW] Delete Customer Option -->
                <div
                    class="flex items-center justify-center gap-2 mb-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <input type="checkbox" id="del_cust_chk_sidebar"
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <label for="del_cust_chk_sidebar"
                        class="text-sm font-bold text-gray-700 cursor-pointer select-none">
                        연관된 고객 정보도 함께 삭제
                    </label>
                </div>
            </div>
            <div class="bg-white px-5 pb-5 flex gap-3">
                <button type="button" onclick="close_delete_confirm()"
                    class="flex-1 rounded-xl px-4 py-3 bg-gray-100 text-sm font-bold text-gray-700 hover:bg-gray-200 transition">
                    취소
                </button>
                <button type="button" onclick="exec_delete_quote()"
                    class="flex-1 rounded-xl px-4 py-3 bg-red-600 text-sm font-bold text-white hover:bg-red-700 transition shadow-md">
                    삭제하기
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteTargetId = 0;

    function deleteQuote(qaId) {
        if (!qaId) {
            // ID that doesn't exist yet (unsaved) -> Just redirect to list
            if (confirm('작성 중인 내용을 취소하고 목록으로 돌아갑니다.')) {
                location.href = './admin_quote.php';
            }
            return;
        }
        deleteTargetId = qaId;
        // Reset Checkbox
        const chk = document.getElementById('del_cust_chk_sidebar');
        if (chk) chk.checked = false;

        document.getElementById('delete_confirm_modal').classList.remove('hidden');
    }

    function close_delete_confirm() {
        document.getElementById('delete_confirm_modal').classList.add('hidden');
        deleteTargetId = 0;
    }

    function exec_delete_quote() {
        if (!deleteTargetId) return;

        // Check customer delete option
        const chk = document.getElementById('del_cust_chk_sidebar');
        const deleteCustomer = (chk && chk.checked) ? '1' : '0';

        // AJAX Delete
        const xhr = new XMLHttpRequest();
        xhr.open('POST', './admin_quote.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                try {
                    const res = JSON.parse(this.responseText);
                    if (res.success) {
                        location.href = './admin_quote.php';
                    } else {
                        alert('삭제 실패: ' + (res.message || '오류가 발생했습니다.'));
                    }
                } catch (e) {
                    console.error(e);
                    alert('서버 응답 오류');
                }
            }
        };
        xhr.send('w=ajax_delete_quote&qa_id=' + deleteTargetId + '&delete_customer=' + deleteCustomer);
    }
</script>
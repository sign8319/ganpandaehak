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
                    class="admin-btn w-full admin-btn-primary shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    저장하기
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
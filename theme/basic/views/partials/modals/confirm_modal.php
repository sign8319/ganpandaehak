<?php
/**
 * Confirm Modal Component
 * 일반 확인 모달
 */
if (!defined('_GNUBOARD_'))
    exit;
?>

<!-- Custom Confirm Modal -->
<div id="custom_confirm_modal" class="hidden fixed inset-0 z-[99999]" role="dialog" aria-modal="true">
    <div class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="pointer-events: none;">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            style="z-index: 99998 !important; pointer-events: auto;" onclick="close_confirm_modal()"></div>

        <!-- Modal Panel -->
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative"
            style="z-index: 100000 !important; pointer-events: auto;">
            <div class="bg-white px-6 pt-8 pb-6 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-50 mb-5">
                    <svg class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">확인</h3>
                <p id="confirm_msg" class="text-sm text-gray-600 leading-relaxed">
                    작업을 진행하시겠습니까?
                </p>
            </div>
            <div class="bg-white px-5 pb-5 flex gap-2.5">
                <button type="button" id="btn_confirm_yes"
                    class="flex-1 inline-flex justify-center items-center rounded-xl px-6 py-3.5 bg-blue-500 text-base font-bold text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-sm">
                    확인
                </button>
                <button type="button" onclick="close_confirm_modal()"
                    class="flex-1 inline-flex justify-center items-center rounded-xl px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 border border-gray-200 focus:outline-none transition">
                    취소
                </button>
            </div>
        </div>
    </div>
</div>
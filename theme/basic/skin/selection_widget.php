<!-- ==============================================
     Floating Portfolio Selection Widget (개선 버전)
     ============================================== -->
<div id="floatingSelectionWidget"
    class="fixed right-6 z-[9000] hidden <?php echo (isset($bo_table) && $bo_table == 'ca_portfolio') ? 'md:flex' : ''; ?> flex-col items-end gap-4 font-sans pointer-events-none"
    style="top: 150px;">

    <!-- Persistent Vertical Sidebar (Desktop) -->
    <div id="selectionPanel"
        class="bg-white rounded-3xl shadow-2xl border-2 border-orange-100 overflow-hidden pointer-events-auto transition-all duration-300 hidden md:flex flex-col"
        style="width: 230px; max-height: 700px;">

        <!-- Header -->
        <div
            class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-4 flex justify-between items-center shrink-0">
            <div>
                <h3 class="font-extrabold text-base flex items-center gap-1.5">
                    <i class="fa fa-shopping-bag text-sm"></i> 디자인 보관함
                </h3>
                <p class="text-[10px] text-orange-100 mt-0.5 opacity-90">견적을 받아보세요</p>
            </div>
            <span id="selectionCount"
                class="bg-white text-orange-600 font-black text-xs w-6 h-6 flex items-center justify-center rounded-full shadow-sm">0</span>
        </div>

        <!-- [NEW] 현재 보는 디자인 섹션 -->
        <div id="currentPortfolioSection" class="shrink-0" style="display: none;">
            <!-- 담지 않은 상태 -->
            <div id="notSavedState" class="px-3 py-3 bg-orange-50 border-b border-orange-100">
                <p class="text-[11px] font-bold text-gray-700 mb-2 flex items-center gap-1">
                    <i class="fa fa-eye text-orange-500"></i>
                    보고 계신 디자인
                </p>
                <div class="relative group cursor-pointer" onclick="addCurrentToCart()">
                    <img id="currentThumb" src="" class="w-full aspect-square object-cover rounded-lg" alt="현재 디자인">
                    <div
                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                        <span class="text-white text-xs font-bold">담기</span>
                    </div>
                </div>
                <p id="currentTitle" class="text-[11px] font-medium text-gray-600 mt-1.5 truncate leading-tight"></p>
                <button onclick="addCurrentToCart()"
                    class="w-full mt-2 py-2 bg-white border border-orange-300 text-orange-600 rounded-lg text-[11px] font-bold hover:bg-orange-50 transition-colors">
                    <i class="fa fa-plus"></i> 이 디자인 담기
                </button>
            </div>

            <!-- 이미 담은 상태 -->
            <div id="alreadySavedState" class="px-3 py-3 bg-green-50 border-b border-green-100" style="display:none;">
                <p class="text-[11px] font-bold text-green-600 mb-2 flex items-center gap-1">
                    <i class="fa fa-check-circle"></i>
                    이미 담은 디자인
                </p>
                <div class="relative">
                    <img id="currentThumbSaved" src=""
                        class="w-full aspect-square object-cover rounded-lg cursor-pointer" onclick="goToCurrentPage()"
                        alt="담은 디자인">
                    <button onclick="removeCurrentFromCart()"
                        class="absolute top-1.5 right-1.5 w-6 h-6 bg-white rounded-full shadow-md hover:bg-red-50 flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors">
                        <i class="fa fa-times text-xs"></i>
                    </button>
                </div>
                <p id="currentTitleSaved" class="text-[11px] font-medium text-gray-600 mt-1.5 truncate leading-tight">
                </p>
            </div>
        </div>

        <!-- 담은 디자인 리스트 -->
        <div class="shrink-0 px-3 py-2 bg-gray-50 border-b border-gray-200" id="savedListHeader" style="display:none;">
            <p class="text-[11px] font-bold text-gray-700">담은 디자인</p>
        </div>

        <!-- List Area -->
        <div id="selectionList"
            class="flex-1 overflow-y-auto p-3 bg-gray-50/50 scrollbar-thin scrollbar-thumb-orange-200 scrollbar-track-transparent">
            <!-- Items will be injected here -->
            <div
                class="h-full flex flex-col items-center justify-center text-center p-4 text-gray-400 gap-2 opacity-60">
                <i class="fa fa-images text-3xl text-gray-300"></i>
                <div class="text-[10px]">
                    <p class="font-bold mb-0.5">보관함이 비어있습니다</p>
                    <p class="leading-tight">마음에 드는 포트폴리오를<br>담아보세요</p>
                </div>
            </div>
        </div>

        <!-- Action Area -->
        <div class="p-4 border-t border-gray-100 bg-white space-y-2.5 shrink-0">
            <!-- Summary Stats -->
            <div id="selectionSummary" class="hidden bg-orange-50 rounded-lg p-2.5 flex justify-between items-center">
                <span class="text-[10px] font-bold text-gray-600">선택된 디자인</span>
                <span class="text-xs font-black text-orange-600"><span id="summaryCount">0</span>개</span>
            </div>

            <button onclick="submitQuoteRequest()"
                class="w-full py-3 bg-gray-900 hover:bg-black text-white font-bold rounded-xl shadow-lg transition-all hover:shadow-xl flex items-center justify-center gap-2 relative overflow-hidden group text-sm">
                <span class="relative z-10">무료 견적 신청하기</span>
                <i class="fa fa-paper-plane relative z-10 group-hover:translate-x-1 transition-transform text-xs"></i>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
            </button>

            <div class="grid grid-cols-2 gap-1.5 text-[10px] font-bold text-gray-500">
                <button onclick="clearPortfolios()"
                    class="py-2 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center gap-1">
                    <i class="fa fa-trash-alt text-[9px]"></i> 전체 삭제
                </button>
                <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=ca_portfolio"
                    class="py-2 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors flex items-center justify-center gap-1">
                    <i class="fa fa-search text-[9px]"></i> 더 찾기
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Bar -->
<div id="mobileSelectionBar"
    class="fixed bottom-0 left-0 right-0 z-[9000] bg-white border-t border-gray-200 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] p-4 md:hidden transform translate-y-full transition-transform duration-300 flex justify-between items-center">
    <button onclick="openMobileSheet()" class="flex items-center gap-2 text-gray-800">
        <span class="bg-orange-100 text-orange-600 p-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </span>
        <span class="font-bold text-sm"><span id="mobileCount">0</span>개 선택됨</span>
        <i class="fa fa-chevron-up text-gray-400 text-xs ml-1"></i>
    </button>
    <button onclick="submitQuoteRequest()"
        class="bg-orange-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-md hover:bg-orange-700 transition-colors">
        견적 신청하기
    </button>
</div>

<!-- Mobile Bottom Sheet (Expanded) -->
<div id="mobileSheetBackdrop"
    class="fixed inset-0 bg-black/50 z-[9001] hidden opacity-0 transition-opacity duration-300"
    onclick="closeMobileSheet()"></div>
<div id="mobileSelectionSheet"
    class="fixed bottom-0 left-0 right-0 z-[9002] bg-white rounded-t-2xl shadow-2xl transform translate-y-full transition-transform duration-300 md:hidden max-h-[80vh] flex flex-col">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-lg">참고 디자인 선택</h3>
        <button onclick="closeMobileSheet()" class="p-2 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div id="mobileSelectionList" class="overflow-y-auto p-4 space-y-3 bg-gray-50 flex-1 min-h-[150px]">
        <!-- Items injected here -->
    </div>

    <div class="p-4 border-t border-gray-100 bg-white grid grid-cols-2 gap-3">
        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=ca_portfolio"
            class="col-span-2 py-3 bg-gray-100 text-gray-600 font-medium rounded-xl text-center">
            + 디자인 더 찾기
        </a>
        <button onclick="clearPortfolios()"
            class="py-3 bg-white border border-gray-300 text-gray-600 font-medium rounded-xl">
            전체 삭제
        </button>
        <button onclick="submitQuoteRequest()" class="py-3 bg-orange-600 text-white font-bold rounded-xl shadow-md">
            견적 신청
        </button>
    </div>
</div>

<!-- Toast Notification (Desktop) - 개선된 위치 -->
<div id="designToast"
    class="fixed left-1/2 -translate-x-1/2 bg-white rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.2)] border-2 border-orange-400 p-5 min-w-[360px] z-[9999] opacity-0 pointer-events-none transition-all duration-300"
    style="top: 100px;">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 shrink-0">
            <i class="fa fa-check text-lg"></i>
        </div>
        <div>
            <p class="font-bold text-gray-900 text-base">디자인이 담겼습니다!</p>
            <p class="text-xs text-gray-500 mt-0.5">우측 보관함을 확인해보세요</p>
        </div>
    </div>
    <div class="flex gap-2 pointer-events-auto">
        <button onclick="submitQuoteRequest()"
            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2.5 rounded-xl font-bold transition-colors shadow-md text-sm">
            바로 상담신청
        </button>
        <button onclick="closeToast()"
            class="flex-1 bg-white border-2 border-gray-200 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl font-bold transition-colors text-sm">
            계속 둘러보기
        </button>
    </div>
</div>

<!-- Mobile Confirmation Sheet -->
<div id="mobileConfirmSheet"
    class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-[0_-5px_20px_rgba(0,0,0,0.15)] z-[9999] p-6 transform translate-y-full transition-transform duration-300 md:hidden flex flex-col gap-4">
    <div class="flex items-center gap-2 mb-2">
        <i class="fa fa-check-circle text-green-500 text-xl"></i>
        <span class="font-bold text-lg text-gray-900">디자인을 담았어요</span>
    </div>

    <button onclick="submitQuoteRequest()"
        class="w-full bg-orange-600 text-white py-4 rounded-xl font-bold text-lg shadow-md flex justify-between items-center px-6">
        <span>담은 디자인으로 상담 신청하기</span>
        <span class="bg-orange-700 text-orange-100 text-xs px-2 py-1 rounded-full" id="mobileConfirmCount">0개</span>
    </button>

    <div class="text-center">
        <p class="text-xs text-gray-400 mb-2">다른 디자인도 함께 선택할 수 있어요</p>
        <button onclick="closeMobileConfirm()" class="w-full bg-gray-100 text-gray-600 py-3 rounded-xl font-bold">
            계속 둘러보기
        </button>
    </div>
</div>

<script>
    // Global Portfolio Manager
    const MAX_ITEMS = 5;
    const STORAGE_KEY = 'selected_portfolios';
    let toastTimer = null;
    let currentPagePortfolio = null; // 현재 페이지 포트폴리오 정보 저장

    // --- Storage Helpers ---
    function getPortfolios() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function savePortfolios(items) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        updateSelectionWidget();
    }

    // --- [NEW] 현재 페이지 포트폴리오 설정 ---
    function setCurrentPagePortfolio(id, title, img, url) {
        currentPagePortfolio = { id, title, img, url };
        updateCurrentSection();
    }

    function updateCurrentSection() {
        if (!currentPagePortfolio) return;

        const section = document.getElementById('currentPortfolioSection');
        const items = getPortfolios();
        const isSelected = items.some(i => i.id === currentPagePortfolio.id);

        section.style.display = 'block';

        if (isSelected) {
            document.getElementById('notSavedState').style.display = 'none';
            document.getElementById('alreadySavedState').style.display = 'block';
            document.getElementById('currentThumbSaved').src = currentPagePortfolio.img;
            document.getElementById('currentTitleSaved').textContent = currentPagePortfolio.title;
            document.getElementById('savedListHeader').style.display = items.length > 1 ? 'block' : 'none';
        } else {
            document.getElementById('notSavedState').style.display = 'block';
            document.getElementById('alreadySavedState').style.display = 'none';
            document.getElementById('currentThumb').src = currentPagePortfolio.img;
            document.getElementById('currentTitle').textContent = currentPagePortfolio.title;
            document.getElementById('savedListHeader').style.display = items.length > 0 ? 'block' : 'none';
        }
    }

    function addCurrentToCart() {
        if (!currentPagePortfolio) return;
        addPortfolio(currentPagePortfolio);
    }

    function removeCurrentFromCart() {
        if (!currentPagePortfolio) return;
        removePortfolio(currentPagePortfolio.id);
    }

    function goToCurrentPage() {
        if (currentPagePortfolio && currentPagePortfolio.url) {
            location.href = currentPagePortfolio.url;
        }
    }

    // --- Core Functions ---
    function addPortfolio(item) {
        const items = getPortfolios();

        if (items.some(i => i.id === item.id)) {
            alert('이미 선택된 디자인입니다.');
            return;
        }

        if (items.length >= MAX_ITEMS) {
            alert(`최대 ${MAX_ITEMS}개까지 선택할 수 있습니다.`);
            return;
        }

        items.push(item);
        savePortfolios(items);

        // Trigger Feedback Flow
        if (window.innerWidth >= 768) {
            showToast(items.length);
            // Shake panel after toast shows
            setTimeout(highlightSidePanel, 300);
        } else {
            showMobileConfirm(items.length);
        }
    }

    function removePortfolio(id) {
        const items = getPortfolios().filter(i => i.id !== id);
        savePortfolios(items);
    }

    function clearPortfolios() {
        if (confirm('선택한 모든 디자인을 삭제하시겠습니까?')) {
            savePortfolios([]);
        }
    }

    // --- Feedback UI Functions ---
    function showToast(count) {
        const toast = document.getElementById('designToast');

        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        toast.style.pointerEvents = 'auto';

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(closeToast, 2000);
    }

    function closeToast() {
        const toast = document.getElementById('designToast');
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');
        toast.style.pointerEvents = 'none';
    }

    function highlightSidePanel() {
        const panel = document.getElementById('selectionPanel');
        panel.classList.add('animate-bounce-short', 'border-orange-400');
        setTimeout(() => {
            panel.classList.remove('animate-bounce-short', 'border-orange-400');
        }, 600);
    }

    function showMobileConfirm(count) {
        const sheet = document.getElementById('mobileConfirmSheet');
        document.getElementById('mobileConfirmCount').textContent = `${count}개`;
        sheet.classList.remove('translate-y-full');
    }

    function closeMobileConfirm() {
        document.getElementById('mobileConfirmSheet').classList.add('translate-y-full');
    }

    // --- Update Logic ---
    function updateSelectionWidget() {
        const items = getPortfolios();
        const count = items.length;

        // Update Counters
        document.querySelectorAll('#selectionCount, #triggerCount, #mobileCount, #summaryCount').forEach(el => el.textContent = count);

        // [NEW] Update Current Section
        updateCurrentSection();

        // Render Lists - 현재 페이지 포트폴리오는 제외
        const filteredItems = currentPagePortfolio
            ? items.filter(i => i.id !== currentPagePortfolio.id)
            : items;

        const html = filteredItems.map(item => `
            <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-100 shadow-sm hover:border-orange-400 transition-all cursor-pointer"
                 onclick="location.href='<?php echo G5_BBS_URL ?>/board.php?bo_table=ca_portfolio&wr_id=${item.id}'">
                <img src="${item.img}" class="w-full h-full object-cover">
                
                <button onclick="event.stopPropagation(); removePortfolio('${item.id}')" 
                        class="absolute top-1.5 right-1.5 w-6 h-6 bg-white rounded-full shadow-md hover:bg-red-50 flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors z-10">
                    <i class="fa fa-times text-xs"></i>
                </button>
                
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                    <p class="text-white text-[10px] font-medium truncate leading-tight">${item.title || item.subject}</p>
                </div>
            </div>
        `).join('');

        // Desktop Logic
        const desktopList = document.getElementById('selectionList');
        const summaryPanel = document.getElementById('selectionSummary');
        const widget = document.getElementById('floatingSelectionWidget'); // Parent Widget
        const isPortfolioPage = location.href.indexOf('bo_table=ca_portfolio') > -1;

        if (filteredItems.length > 0) {
            desktopList.className = 'flex-1 overflow-y-auto p-3 bg-gray-50/50 scrollbar-thin scrollbar-thumb-orange-200 scrollbar-track-transparent grid grid-cols-2 gap-2 content-start';
            desktopList.innerHTML = html;
            summaryPanel.classList.remove('hidden');
            summaryPanel.classList.add('flex');
        } else {
            desktopList.className = 'flex-1 overflow-y-auto p-3 bg-gray-50/50 scrollbar-thin scrollbar-thumb-orange-200 scrollbar-track-transparent';
            desktopList.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center text-center p-4 text-gray-400 gap-2 opacity-60">
                    <i class="fa fa-images text-3xl text-gray-300"></i>
                    <div class="text-[10px]">
                        <p class="font-bold mb-0.5">보관함이 비어있습니다</p>
                        <p class="leading-tight">마음에 드는 포트폴리오를<br>담아보세요</p>
                    </div>
                </div>`;
            summaryPanel.classList.add('hidden');
            summaryPanel.classList.remove('flex');
        }

        // [NEW] Visibility Logic
        if (count > 0 || isPortfolioPage) {
            // Show Widget
            widget.classList.remove('hidden');
            widget.classList.add('md:flex');
        } else {
            // Hide Widget
            widget.classList.remove('md:flex');
            widget.classList.add('hidden');
        }

        // Mobile Logic
        if (count > 0) {
            document.getElementById('mobileSelectionBar').classList.remove('translate-y-full');
            document.getElementById('mobileSelectionList').innerHTML = items.map(item => `
                <div class="relative aspect-square rounded-xl overflow-hidden border border-gray-100 shadow-sm">
                    <img src="${item.img}" class="w-full h-full object-cover">
                    <button onclick="removePortfolio('${item.id}')" 
                            class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md hover:bg-red-50 flex items-center justify-center text-gray-600 hover:text-red-500">
                        <i class="fa fa-times"></i>
                    </button>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-white text-xs font-medium truncate">${item.title || item.subject}</p>
                    </div>
                </div>
            `).join('');
        } else {
            document.getElementById('mobileSelectionBar').classList.add('translate-y-full');
            closeMobileSheet();
        }
    }

    // UI Helpers (Mobile Only)
    function openMobileSheet() {
        document.getElementById('mobileSheetBackdrop').classList.remove('hidden');
        document.getElementById('mobileSelectionSheet').classList.remove('translate-y-full');
        setTimeout(() => {
            document.getElementById('mobileSheetBackdrop').classList.remove('opacity-0');
        }, 10);
    }

    function closeMobileSheet() {
        document.getElementById('mobileSheetBackdrop').classList.add('opacity-0');
        document.getElementById('mobileSelectionSheet').classList.add('translate-y-full');
        setTimeout(() => {
            document.getElementById('mobileSheetBackdrop').classList.add('hidden');
        }, 300);
    }

    function submitQuoteRequest() {
        const items = getPortfolios();
        if (items.length === 0) {
            alert('의뢰할 디자인을 선택해주세요.');
            return;
        }

        const ids = items.map(i => i.id).join(',');
        location.href = "<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&sca=견적문의&portfolio_ids=" + ids;
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', function () {
        updateSelectionWidget();
    });

    // Storage change listener
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY) {
            updateSelectionWidget();
        }
    });
</script>

<style>
    /* 반응형 크기 조정 */
    @media (max-width: 1400px) {
        #selectionPanel {
            width: 200px !important;
            font-size: 0.9em;
        }
    }

    @media (max-width: 1200px) {
        #selectionPanel {
            width: 180px !important;
            font-size: 0.85em;
        }

        #floatingSelectionWidget {
            right: 16px !important;
        }
    }

    @keyframes bounce-short {

        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-10px) scale(1.02);
        }
    }

    .animate-bounce-short {
        animation: bounce-short 0.5s ease-in-out;
    }

    /* 스크롤바 스타일 */
    .scrollbar-thin::-webkit-scrollbar {
        width: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #fed7aa;
        border-radius: 10px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #fdba74;
    }
</style>
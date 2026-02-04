<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

// 공통 함수 라이브러리 로드
include_once('./includes/quote_functions.php');

// 현재 페이지 파악을 위한 로직
$current_file = basename($_SERVER['PHP_SELF']);
$bo_table = isset($board['bo_table']) ? $board['bo_table'] : (isset($_REQUEST['bo_table']) ? $_REQUEST['bo_table'] : '');
?>

<style>
    /* [Admin Specific] 모바일에서 사이트 기본 메뉴(GNB) 완전 비활성화 */
    /* admin-page 클래스는 하단 JS에서 body에 주입됨 */
    @media (max-width: 1024px) {

        body.admin-page #gnb_open,
        body.admin-page #gnb,
        body.admin-page #user_btn,
        body.admin-page #user_menu,
        body.admin-page #hd_wrapper,
        body.admin-page #hd {
            display: none !important;
        }

        /* 관리자 페이지의 메인 컨텐츠 여백 제거 (헤더가 사라지므로) */
        body.admin-page #wrapper {
            padding-top: 0 !important;
        }
    }
</style>

<!-- Mobile Toggle Button (Visible < lg) -->
<div class="lg:hidden fixed top-4 left-4 z-[50]">
    <button type="button" onclick="toggleAdminSidebar()"
        class="p-3 bg-white rounded-lg shadow-md border border-gray-200 text-gray-700 hover:bg-gray-50 focus:outline-none">
        <i class="fas fa-bars text-xl"></i>
    </button>
</div>

<!-- Overlay -->
<div id="admin-sidebar-overlay" onclick="toggleAdminSidebar()"
    class="fixed inset-0 bg-black/50 z-[90] hidden transition-opacity opacity-0 lg:hidden backdrop-blur-sm">
</div>

<!-- Sidebar -->
<aside id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-[100] w-64 bg-white border-r border-gray-200 transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:block flex-shrink-0 overflow-y-auto"
    style="height: 100vh;">

    <!-- Mobile Close Button -->
    <div class="lg:hidden absolute top-4 right-4 z-10">
        <button onclick="toggleAdminSidebar()" class="text-gray-500 hover:text-gray-700 p-2">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="p-6">
        <!-- Title / Brand -->
        <div class="mb-8">
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">관리자 페이지</h2>
            <p class="text-xs text-gray-400 mt-1">간판대학 통합 관리</p>
        </div>

        <!-- Menu List -->
        <nav class="space-y-1">
            <!-- Schedule (Popup) -->
            <button
                onclick="window.open('<?php echo G5_ADMIN_URL; ?>/admin_schedule.php?mode=app', 'schedule_app', 'width=1200,height=900,scrollbars=yes');"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border border-transparent rounded-xl text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-colors group text-left">
                <i class="fas fa-calendar-alt w-5 text-center group-hover:scale-110 transition-transform"></i>
                일정관리
            </button>

            <!-- Customer Management -->
            <?php
            $is_active = ($current_file == 'admin_quote.php' || $current_file == 'admin_customer.php');
            $w_param = isset($_GET['w']) ? $_GET['w'] : '';
            if ($current_file == 'admin_quote.php' && $w_param == 'form')
                $is_active = false;
            $is_customer_system = ($current_file == 'admin_quote.php' && $w_param != 'form');
            ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_quote.php"
                class="<?php echo get_sidebar_active_class($is_customer_system); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors">
                <i class="fas fa-user-friends w-5 text-center"></i>
                고객관리시스템
            </a>

            <!-- Estimate Request -->
            <?php $is_consult = ($bo_table == 'consult'); ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_consult.php?bo_table=consult"
                class="<?php echo get_sidebar_active_class($is_consult); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-file-invoice w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                견적신청
            </a>

            <!-- Payment Center -->
            <?php $is_payment = ($bo_table == 'payment'); ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_payment.php?bo_table=payment"
                class="<?php echo get_sidebar_active_class($is_payment); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-credit-card w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                결제센터
            </a>

            <!-- Quote Calculation -->
            <?php $is_calc = ($current_file == 'admin_quote_calc.php'); ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_quote_calc.php"
                class="<?php echo get_sidebar_active_class($is_calc); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-calculator w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                견적계산
            </a>

            <!-- Image Assets -->
            <?php $is_assets = ($current_file == 'admin_assets.php'); ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_assets.php"
                class="<?php echo get_sidebar_active_class($is_assets); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-images w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                이미지자산
            </a>

            <!-- Settings (Modal) -->
            <button onclick="document.getElementById('biz_config_modal').showModal()"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold border border-transparent rounded-xl text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-colors group text-left mt-2">
                <i class="fas fa-cog w-5 text-center group-hover:rotate-90 transition-transform duration-500"></i>
                설정
            </button>
        </nav>
    </div>
</aside>

<script>
    // Mobile Sidebar Toggle Logic
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('admin-sidebar-overlay');
        const body = document.body;

        // Check if open or closed
        if (sidebar.classList.contains('-translate-x-full')) {
            // Open Sidebar
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            // Small delay for fade-in effect
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
            }, 10);
            // Lock body scroll
            body.style.overflow = 'hidden';
        } else {
            // Close Sidebar
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300); // Match transition duration
            // Unlock body scroll
            body.style.overflow = '';
        }
    }

    // Ensure proper state on resize
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            document.body.style.overflow = '';
            document.getElementById('admin-sidebar-overlay').classList.add('hidden', 'opacity-0');
            // Reset transform classes if needed, but lg:translate-x-0 handles it
        }
    });

    // [Admin Specific] 관리자 페이지 식별 클래스 주입
    document.addEventListener('DOMContentLoaded', function  () {
        document.body.classList.add('admin-page');
    });
</script>
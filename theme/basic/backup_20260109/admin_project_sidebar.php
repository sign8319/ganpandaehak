<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

// 현재 페이지 파악을 위한 로직
$current_file = basename($_SERVER['PHP_SELF']);
$bo_table = isset($board['bo_table']) ? $board['bo_table'] : (isset($_REQUEST['bo_table']) ? $_REQUEST['bo_table'] : '');

// Active 클래스 헬퍼 함수
function get_sidebar_active_class($is_active)
{
    if ($is_active) {
        return 'bg-orange-50 text-orange-600 border-orange-100';
    } else {
        return 'text-gray-600 hover:bg-gray-50 border-transparent';
    }
}
?>
<!-- Sidebar -->
<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto"
    style="height: 100vh; position: sticky; top: 0;">
    <div class="p-6">
        <!-- Title / Brand -->
        <div class="mb-8">
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">관리자 페이지</h2>
            <p class="text-xs text-gray-400 mt-1">간판대학 통합 관리</p>
        </div>

        <!-- Top Actions (Grid) -->
        <div class="grid grid-cols-2 gap-2 mb-6">
            <!-- Schedule Button (App Mode Popup) -->
            <button
                onclick="window.open('<?php echo G5_ADMIN_URL; ?>/admin_schedule.php?mode=app', 'schedule_app', 'width=1200,height=900,scrollbars=yes');"
                class="flex flex-col items-center justify-center p-3 bg-gray-900 text-white rounded-xl hover:bg-black transition shadow-sm group">
                <i class="fas fa-calendar-alt text-lg mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-bold">일정관리</span>
            </button>

            <!-- Settings Button (Modal) -->
            <button onclick="document.getElementById('biz_config_modal').showModal()"
                class="flex flex-col items-center justify-center p-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition shadow-sm group">
                <i class="fas fa-cog text-lg mb-1 group-hover:rotate-90 transition-transform duration-500"></i>
                <span class="text-xs font-bold">설정</span>
            </button>
        </div>

        <!-- Menu List -->
        <nav class="space-y-1">
            <!-- Customer Management -->
            <?php
            $is_active = ($current_file == 'admin_quote.php' || $current_file == 'admin_customer.php');
            $w_param = isset($_GET['w']) ? $_GET['w'] : '';
            if ($current_file == 'admin_quote.php' && $w_param == 'form')
                $is_active = false; // "견적작성"은 별도 메뉴로 칠 수도 있으나, 여기선 고객관리시스템 메인으로 봄? 아니면 견적신청? 
            // 사용자의 요청은 "왼쪽 메뉴가 안나와" -> 통일성.
            // admin_quote.php (리스트) -> 고객관리시스템 Active
            // admin_quote.php?w=form -> ? (기존 코드는 고객관리시스템 Active였음, 근데 견적작성 서브메뉴가 또 있었음. 일단 메인 유지를 위해..)
            // 정확히는 기존 코드에서 $w != 'form' 일때만 Active 였음.
            $is_customer_system = ($current_file == 'admin_quote.php' && $w_param != 'form');
            ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_quote.php"
                class="<?php echo get_sidebar_active_class($is_customer_system); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors">
                <i class="fas fa-user-friends w-5 text-center"></i>
                고객관리시스템
            </a>

            <!-- Estimate Request -->
            <?php $is_consult = ($bo_table == 'consult'); ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=consult"
                class="<?php echo get_sidebar_active_class($is_consult); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-file-invoice w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                견적신청
            </a>

            <!-- Payment Center -->
            <?php $is_payment = ($bo_table == 'payment'); ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=payment"
                class="<?php echo get_sidebar_active_class($is_payment); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-credit-card w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                결제센터
            </a>

            <!-- Image Assets -->
            <?php $is_assets = ($current_file == 'admin_assets.php'); ?>
            <a href="<?php echo G5_THEME_URL; ?>/admin_assets.php"
                class="<?php echo get_sidebar_active_class($is_assets); ?> flex items-center gap-3 px-4 py-3 text-sm font-bold border rounded-xl transition-colors group">
                <i class="fas fa-images w-5 text-center group-hover:text-orange-500 transition-colors"></i>
                이미지자산
            </a>
        </nav>
    </div>
</aside>
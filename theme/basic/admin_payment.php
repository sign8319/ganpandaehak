<?php
// [Refactor] bo_table은 URL 파라미터로 전달됨 (?bo_table=payment)
// common.php가 자동으로 $_REQUEST['bo_table']을 읽어서 처리함

// [Wrappter Signal] 스킨에서 이 상수를 체크하여 링크를 admin wrapper로 변경함
define('IN_ADMIN_WRAPPER', true);

include_once('./_common.php');

// URL 파라미터가 없으면 강제 할당 (하위 호환성)
if (!isset($_REQUEST['bo_table']) || !$_REQUEST['bo_table']) {
    $_GET['bo_table'] = 'payment';
    $_REQUEST['bo_table'] = 'payment';
    $bo_table = 'payment';
}

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

include_once(G5_THEME_PATH . '/head.php');

// $write_href는 common.php에서 자동 생성되지 않거나, board.php 내부 로직에 의해 재정의될 수 있음.
// 안전하게 명시적 생성
$write_href = G5_BBS_URL . '/write.php?bo_table=' . $bo_table;
?>

<!-- Sidebar Layout Wrapper -->
<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <!-- Sidebar -->
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>

    <div class="flex-1 min-w-0 flex flex-col">
        <div class="px-6 py-8 flex-1 flex flex-col">
            <div
                class="max-w-[1600px] mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6 w-full flex-1 flex flex-col relative flow-root">
                <!-- Board Title -->
                <div class="mb-6 border-b pb-4 border-gray-100 flex justify-between items-center flex-none">
                    <h2 class="text-2xl font-bold text-gray-800">
                        결제센터 관리
                        <span class="text-sm font-normal text-gray-500 ml-2">결제 요청 및 내역 관리</span>
                    </h2>
                    <a href="<?php echo $write_href; ?>"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 transition">
                        <i class="fas fa-pen mr-2"></i>글쓰기
                    </a>
                </div>

                <!-- Board Include -->
                <div class="board-wrapper flex-1 relative">
                    <?php
                    // 게시판 Core 로드
                    include(G5_BBS_PATH . '/board.php');
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
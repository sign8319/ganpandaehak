<?php
if (!defined("_GNUBOARD_"))
    exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH . '/thumbnail.lib.php');

// Tailwind CSS & FontAwesome
echo '<script src="https://cdn.tailwindcss.com"></script>';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';

// 연동된 견적 데이터 조회 (g5_quote)
// wr_id가 g5_quote에 매핑되어 있거나 (wr_id 컬럼), 아니면 보드 wr_id와 연동
// write_update.skin.php에서 g5_quote에 wr_id를 저장했으므로 이를 기준으로 조회
$linked_quote = sql_fetch(" SELECT * FROM g5_quote WHERE wr_id = '{$view['wr_id']}' ");

// 데이터 매핑 (Linked Quote 우선, 없으면 Board View 데이터)
$subject = $linked_quote ? $linked_quote['qa_subject'] : $view['wr_subject'];
$name = $linked_quote ? $linked_quote['qa_client_name'] : $view['wr_name'];
$phone = $linked_quote ? $linked_quote['qa_client_hp'] : $view['wr_1'];
$email = $linked_quote ? $linked_quote['qa_client_email'] : $view['wr_email'];
$addr1 = $linked_quote ? $linked_quote['qa_client_addr'] : $view['wr_6'];
$addr2 = $linked_quote ? $linked_quote['qa_client_addr2'] : $view['wr_7'];
$status = $linked_quote ? $linked_quote['qa_status'] : '신규문의';
$memo = $linked_quote ? $linked_quote['qa_memo_user'] : $view['wr_5'];
$company = $linked_quote ? $linked_quote['qa_tax_company_name'] : $view['wr_name']; // 상호가 없으면 일단 이름으로
$code = $linked_quote ? $linked_quote['qa_code'] : '';

// 게시판 추가 정보 매핑 (Board View 데이터)
$sign_type = isset($view['wr_8']) ? $view['wr_8'] : '';
$design_file = isset($view['wr_2']) ? $view['wr_2'] : '';
$budget = isset($view['wr_3']) ? $view['wr_3'] : '';
$open_date = isset($view['wr_4']) ? $view['wr_4'] : '';

// [Admin Wrapper Link Fix] 관리자 권한이면 목록 링크를 관리자 래퍼로 강제 변경
// (상수 체크 대신 is_admin 체크 - 상세 페이지 직접 접근 시에도 동작하도록)
if ($is_admin && $bo_table == 'consult') {
    $list_href = G5_THEME_URL . '/admin_consult.php?bo_table=consult';
}
?>

<style>
    .compact-input {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        background-color: #f9fafb;
        /* Readonly style */
        cursor: not-allowed;
    }

    textarea.readonly-memo {
        background-color: #f9fafb;
        cursor: not-allowed;
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-6xl">

    <!-- Header / Nav -->
    <div class="flex justify-between items-center mb-6 border-b pb-4 border-gray-200">
        <div class="flex flex-col">
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight leading-none mb-1">견적 문의 상세</h1>
            <p class="text-gray-500 text-xs">게시판 ID: <?php echo $view['wr_id']; ?> | 작성일:
                <?php echo $view['wr_datetime']; ?>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo $list_href ?>"
                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-bold shadow-sm transition">
                <i class="fas fa-list mr-1"></i> 목록으로
            </a>
            <?php if ($update_href): ?>
                <a href="<?php echo $update_href ?>"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm transition">
                    <i class="fas fa-edit mr-1"></i> 수정
                </a>
            <?php endif; ?>
            <?php if ($delete_href): ?>
                <a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-bold shadow-sm transition">
                    <i class="fas fa-trash mr-1"></i> 삭제
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content (Admin Quote Style) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border border-gray-200">

        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 border-b pb-4 gap-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-orange-600"></i>
                기본 정보
            </h2>
            <div class="flex flex-wrap items-center gap-3">
                <?php if ($code): ?>
                    <span class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-500 font-bold">No.
                        <?php echo $code; ?></span>
                <?php endif; ?>

                <!-- Status Badge -->
                <div class="flex items-center gap-2 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">
                    <label class="text-xs font-bold text-orange-700">상태</label>
                    <span class="text-sm font-bold text-orange-800"><?php echo $status; ?></span>
                </div>
            </div>
        </div>

        <!-- Form Grid -->
        <div class="grid grid-cols-12 gap-4">

            <!-- Quote Subject -->
            <div class="col-span-12">
                <label class="block text-xs font-bold text-gray-600 mb-1">견적명 (제목)</label>
                <div class="w-full p-2.5 border border-gray-300 rounded-lg bg-gray-50 text-sm font-bold text-gray-800">
                    <?php echo $subject; ?>
                </div>
            </div>

            <!-- Company Name -->
            <div class="col-span-12 lg:col-span-5">
                <label class="block text-xs font-bold text-gray-600 mb-1">상호 (업체명)</label>
                <input type="text" value="<?php echo $company; ?>" readonly
                    class="w-full compact-input border border-gray-300 rounded-lg text-sm">
            </div>

            <!-- Client Name -->
            <div class="col-span-12 lg:col-span-3">
                <label class="block text-xs font-bold text-gray-600 mb-1">고객명 (담당자)</label>
                <input type="text" value="<?php echo $name; ?>" readonly
                    class="w-full compact-input border border-gray-300 rounded-lg text-sm">
            </div>

            <!-- Phone -->
            <div class="col-span-12 lg:col-span-4">
                <label class="block text-xs font-bold text-gray-600 mb-1">연락처(HP)</label>
                <input type="text" value="<?php echo $phone; ?>" readonly
                    class="w-full compact-input border border-gray-300 rounded-lg text-sm">
            </div>

            <!-- Address -->
            <div class="col-span-12 lg:col-span-8">
                <label class="block text-xs font-bold text-gray-600 mb-1">주소</label>
                <div class="flex gap-2">
                    <input type="text" value="<?php echo $addr1; ?>" readonly
                        class="w-full compact-input border border-gray-300 rounded-lg text-sm">
                    <input type="text" value="<?php echo $addr2; ?>" readonly
                        class="w-1/3 compact-input border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <!-- Email -->
            <div class="col-span-12 lg:col-span-4">
                <label class="block text-xs font-bold text-gray-400 mb-1">이메일</label>
                <input type="text" value="<?php echo $email; ?>" readonly
                    class="w-full compact-input border border-gray-300 rounded-lg text-xs py-2">
            </div>

            <!-- Extra Info Row (From Consult Board) -->
            <div class="col-span-12 mt-4 pt-4 border-t border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 mb-3">📋 추가 상담 정보</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <label class="block text-xs text-gray-500 mb-1">간판 종류</label>
                        <div class="text-sm font-bold text-gray-800"><?php echo $sign_type ? $sign_type : '-'; ?></div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <label class="block text-xs text-gray-500 mb-1">디자인 파일</label>
                        <div class="text-sm font-bold text-gray-800"><?php echo $design_file ? $design_file : '-'; ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <label class="block text-xs text-gray-500 mb-1">예산대</label>
                        <div class="text-sm font-bold text-gray-800"><?php echo $budget ? $budget : '-'; ?></div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <label class="block text-xs text-gray-500 mb-1">오픈 예정일</label>
                        <div class="text-sm font-bold text-gray-800"><?php echo $open_date ? $open_date : '-'; ?></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Content / Memo Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
            📢 문의 내용 / 메모
        </h3>
        <textarea readonly
            class="w-full h-48 p-4 border border-gray-200 rounded-lg readonly-memo resize-none text-sm text-gray-700 leading-relaxed focus:outline-none"><?php echo $memo ? $memo : strip_tags($view['content']); ?></textarea>
    </div>

    <!-- Attachments -->
    <?php if ($view['file']['count']): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-bold text-gray-700 mb-3">📎 첨부파일</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php for ($i = 0; $i < count($view['file']); $i++):
                    if (isset($view['file'][$i]['source']) && $view['file'][$i]['source']): ?>
                        <div class="border rounded-lg p-3 flex items-start gap-3 bg-gray-50">
                            <?php if ($view['file'][$i]['view']): ?>
                                <div class="w-16 h-16 rounded overflow-hidden flex-shrink-0 bg-gray-200">
                                    <!-- 썸네일 생성 -->
                                    <?php echo get_file_thumbnail($view['file'][$i]); ?>
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 rounded flex items-center justify-center bg-gray-200 text-gray-500">
                                    <i class="fas fa-file"></i>
                                </div>
                            <?php endif; ?>
                            <div class="overflow-hidden">
                                <a href="<?php echo $view['file'][$i]['href']; ?>"
                                    class="block text-sm font-medium text-gray-900 truncate hover:text-blue-600">
                                    <?php echo $view['file'][$i]['source']; ?>
                                </a>
                                <span class="text-xs text-gray-500"><?php echo $view['file'][$i]['size']; ?></span>
                            </div>
                        </div>
                    <?php endif; endfor; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Future Action Placeholder (Admin Only) -->
    <div class="flex justify-end mt-6">
        <!-- 나중에 추가 예정 -->
    </div>

</div>

<script>
    // 이미지보기를 위한 기존 스크립트 연결
    $(function () {
        $("a.view_image").click(function () {
            window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
            return false;
        });
    });
</script>
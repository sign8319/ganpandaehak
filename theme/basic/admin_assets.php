<?php
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// Config
// 이미지 기본 URL 설정 (기본값: G5_DATA_URL)
// 서버 이전 시 경로가 꼬인다면 이 값을 '/data' 등으로 직접 지정할 수 있습니다.
$assets_base_url = G5_DATA_URL;
$assets_dir = G5_DATA_PATH . '/assets';
$assets_url = $assets_base_url . '/assets';
$allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

// Ensure Directory Exists
if (!is_dir($assets_dir)) {
    @mkdir($assets_dir, G5_DIR_PERMISSION, true);
    @chmod($assets_dir, G5_DIR_PERMISSION);
}

// -----------------------------------------------------------------------------
// Action: Upload
// -----------------------------------------------------------------------------
if (isset($_FILES['asset_file']) && $_FILES['asset_file']['name']) {
    $file = $_FILES['asset_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        alert('허용되지 않는 파일 형식입니다. (Allowed: jpg, jpeg, png, webp, gif)');
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        alert('파일 용량은 5MB 이하만 가능합니다.');
    }

    // Rename strategy: asset_{timestamp}_{random}.{ext}
    $new_name = 'asset_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
    $dest = $assets_dir . '/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        @chmod($dest, G5_FILE_PERMISSION);
        goto_url('./admin_assets.php');
    } else {
        alert('파일 업로드에 실패했습니다.');
    }
}

// -----------------------------------------------------------------------------
// Action: Delete
// -----------------------------------------------------------------------------
if (isset($_GET['w']) && $_GET['w'] == 'd' && isset($_GET['file'])) {
    $file_to_delete = basename($_GET['file']); // Prevent directory traversal
    $file_path = $assets_dir . '/' . $file_to_delete;

    if (file_exists($file_path)) {
        @unlink($file_path);
    }
    goto_url('./admin_assets.php');
}

// -----------------------------------------------------------------------------
// View Data
// -----------------------------------------------------------------------------
$files = [];
if (is_dir($assets_dir)) {
    $scandir = scandir($assets_dir);
    foreach ($scandir as $f) {
        if ($f == '.' || $f == '..')
            continue;
        if (is_file($assets_dir . '/' . $f)) {
            // Use parse_url to get root-relative path (e.g., /data/assets/file.png)
            // This ensures it works on both localhost and production regardless of domain.
            $full_url = $assets_url . '/' . $f;
            $relative_path = parse_url($full_url, PHP_URL_PATH);

            $files[] = [
                'name' => $f,
                'url' => $relative_path,
                'time' => filemtime($assets_dir . '/' . $f)
            ];
        }
    }
}
// Sort by newest first
usort($files, function ($a, $b) {
    return $b['time'] - $a['time'];
});

$page_title = '이미지 자산 관리';
include_once(G5_THEME_PATH . '/head.php');
?>

<!-- Sidebar Layout Wrapper -->
<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <!-- Sidebar -->
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>

    <div class="flex-1 min-w-0">
        <div class="px-6 py-8">
            <div class="max-w-[1600px] mx-auto">

                <!-- Header (Consistent with admin_quote.php) -->
                <div
                    class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 border-b pb-4 border-gray-200 gap-4">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-extrabold text-gray-900 tracking-tight">
                            이미지 자산 관리
                            <span class="text-orange-600 text-sm font-medium ml-2 hidden md:inline">Image Asset
                                Manager</span>
                        </h1>
                        <p class="text-gray-500 text-xs mt-1">견적서 및 게시글 등에 사용할 이미지를 업로드하고 URL을 복사하여 사용하세요.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="location.href='./admin_quote.php'"
                            class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 transition">
                            ◀ 관리자 메인
                        </button>
                    </div>
                </div>

                <!-- Layout grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    <!-- Upload Panel -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                            <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                                <span class="bg-orange-100 text-orange-600 p-1.5 rounded-lg">📤</span> 새 이미지 업로드
                            </h3>

                            <form action="./admin_assets.php" method="post" enctype="multipart/form-data"
                                class="space-y-4">
                                <div class="w-full">
                                    <label class="block mb-2 text-sm font-bold text-gray-700">파일 선택</label>
                                    <input type="file" name="asset_file" accept=".jpg,.jpeg,.png,.webp,.gif" required
                                        class="w-full text-sm text-gray-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-orange-50 file:text-orange-700
                                hover:file:bg-orange-100
                                cursor-pointer border border-gray-200 rounded-lg bg-gray-50
                                ">
                                    <p class="mt-2 text-xs text-gray-400">
                                        * 지원: JPG, PNG, WEBP, GIF<br>
                                        * 최대 5MB<br>
                                        * 투명 배경 유지됨 (변환 없음)
                                    </p>
                                </div>
                                <button type="submit"
                                    class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-lg shadow transition flex justify-center items-center gap-2">
                                    <span>⬆️</span> 업로드 하기
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Gallery Grid -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                                    <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg">🖼️</span> 보유 이미지 목록
                                    <span
                                        class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full ml-2"><?php echo count($files); ?>개</span>
                                </h3>
                            </div>

                            <?php if (empty($files)): ?>
                                <div class="text-center py-20 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                    <div class="text-4xl mb-3">📂</div>
                                    <p class="text-gray-500 font-bold">등록된 이미지가 없습니다.</p>
                                    <p class="text-xs text-gray-400 mt-1">좌측 패널에서 이미지를 업로드해주세요.</p>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <?php foreach ($files as $file): ?>
                                        <div
                                            class="group relative bg-gray-50 border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col">
                                            <!-- Image Thumbnail -->
                                            <div
                                                class="aspect-square bg-white relative overflow-hidden flex items-center justify-center p-2 checkerboard-bg">
                                                <img src="<?php echo $file['url']; ?>" alt="<?php echo $file['name']; ?>"
                                                    class="max-w-full max-h-full object-contain transition transform group-hover:scale-105">
                                            </div>

                                            <!-- Info & Actions -->
                                            <div class="p-3 bg-white border-t border-gray-100 flex flex-col gap-2">
                                                <div class="text-xs font-bold text-gray-700 truncate"
                                                    title="<?php echo $file['name']; ?>">
                                                    <?php echo $file['name']; ?>
                                                </div>
                                                <div class="flex gap-1.5">
                                                    <button onclick="copyToClipboard('<?php echo $file['url']; ?>')"
                                                        class="flex-1 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 rounded py-1.5 text-xs font-bold transition flex items-center justify-center gap-1"
                                                        title="URL 복사">
                                                        <span>🔗</span> URL
                                                    </button>
                                                    <button onclick="deleteAsset('<?php echo $file['name']; ?>')"
                                                        class="flex-none bg-white text-red-500 hover:bg-red-50 border border-red-200 rounded px-2 py-1.5 text-xs font-bold transition"
                                                        title="삭제">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* CSS pattern for transparency check */
            .checkerboard-bg {
                background-image:
                    linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
                    linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
                    linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
                    linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
                background-size: 20px 20px;
                background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            }
        </style>

        <script>
            function copyToClipboard(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        alert('이미지 주소가 복사되었습니다!\n' + text);
                    }).catch(err => {
                        prompt('아래 주소를 복사하세요:', text);
                    });
                } else {
                    prompt('아래 주소를 복사하세요:', text);
                }
            }

            function deleteAsset(filename) {
                if (confirm('정말로 이 이미지를 삭제하시겠습니까?\n삭제 후에는 복구할 수 없습니다.')) {
                    location.href = './admin_assets.php?w=d&file=' + encodeURIComponent(filename);
                }
            }
        </script>

    </div><!-- End .px-6 py-8 -->
</div><!-- End .flex-1 min-w-0 -->
</div><!-- End Sidebar Layout Wrapper -->

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>
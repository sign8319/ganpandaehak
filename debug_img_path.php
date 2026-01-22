<?php
// CLI 환경에서 웹 환경 시뮬레이션
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/ganpandaehak/debug_img_path.php';
$_SERVER['SCRIPT_NAME'] = '/ganpandaehak/debug_img_path.php';
$_SERVER['PHP_SELF'] = '/ganpandaehak/debug_img_path.php';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SERVER_PORT'] = '80';

// config.php 등에서 DOCUMENT_ROOT를 사용할 수 있으므로 설정
$_SERVER['DOCUMENT_ROOT'] = 'c:/xampp/htdocs';

include_once('./_common.php');

echo "--------------------------------------------------\n";
echo "G5_URL: " . G5_URL . "\n";
echo "G5_IMG_URL: " . G5_IMG_URL . "\n";
echo "G5_DATA_URL: " . G5_DATA_URL . "\n";
echo "--------------------------------------------------\n";

// no_img.png 확인
$no_img_path = G5_IMG_DIR . '/no_img.png';
// G5_IMG_DIR은 'img' 문자열일 뿐
$real_no_img_path = G5_PATH . '/' . G5_IMG_DIR . '/no_img.png';

echo "Real No Img Path: " . $real_no_img_path . "\n";
if (file_exists($real_no_img_path)) {
    echo "No Img File Exists: YES\n";
} else {
    echo "No Img File Exists: NO\n";
}

// Gallery 폴더 다시 확인
$gallery_dir = G5_DATA_PATH . '/file/gallery';
echo "Gallery Dir path: " . $gallery_dir . "\n";
$files = scandir($gallery_dir);
$file_count = count($files) - 2; // .과 .. 제외
echo "Actual files in gallery: " . $file_count . "\n";
?>
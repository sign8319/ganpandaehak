<?php
include_once('./common.php');

echo "<h1>권한 변경 및 폴더 생성 도구</h1>";

// 확인 및 변경할 게시판 ID 목록 (필요한 경우 여기에 추가)
$boards = array('ca_portfolio', 'portfolio_v2');

$data_file_dir = G5_DATA_PATH . '/file';

// 1. data/file 폴더 권한 시도
echo "<h2>1. data/file 폴더 확인</h2>";
if (is_writable($data_file_dir)) {
    echo "data/file 폴더에 쓰기 권한이 있습니다.<br>";
} else {
    echo "<strong style='color:red;'>data/file 폴더에 쓰기 권한이 없습니다. chmod 755 또는 777 시도 중...</strong><br>";
    @chmod($data_file_dir, G5_DIR_PERMISSION);
}

// 2. 게시판별 폴더 생성 및 권한 변경
echo "<h2>2. 게시판별 폴더 확인</h2>";
foreach ($boards as $board_id) {
    $target_dir = $data_file_dir . '/' . $board_id;

    echo "<hr><strong>확인 대상: " . $target_dir . "</strong><br>";

    if (!is_dir($target_dir)) {
        echo "폴더가 존재하지 않습니다. 생성을 시도합니다...<br>";
        if (@mkdir($target_dir, G5_DIR_PERMISSION, true)) {
            echo "<span style='color:blue;'>폴더 생성 성공</span><br>";
        } else {
            echo "<span style='color:red;'>폴더 생성 실패 (상위 폴더 권한 문제일 수 있음)</span><br>";
        }
    } else {
        echo "폴더가 이미 존재합니다.<br>";
    }

    if (is_dir($target_dir)) {
        // 권한 변경 시도 (777)
        if (@chmod($target_dir, 0777)) { // 0777은 8진수
            echo "권한 변경 성공 (0777)<br>";
        } else {
            // 실패 시 755 시도
            if (@chmod($target_dir, 0755)) {
                echo "권한 변경 성공 (0755)<br>";
            } else {
                echo "<span style='color:red;'>권한 변경 실패</span><br>";
            }
        }

        echo "현재 권한: " . substr(sprintf('%o', fileperms($target_dir)), -4) . "<br>";
    }
}

echo "<br><br>완료되었습니다. 이 파일을 삭제해주세요.";
?>
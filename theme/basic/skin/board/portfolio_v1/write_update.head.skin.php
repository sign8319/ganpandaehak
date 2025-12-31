<?php
/**
 * 포트폴리오 게시판 전용 - 파일 순서 재정렬
 * 이 파일은 write_update.php가 업로드를 처리하기 '전에' 실행됩니다.
 * 목적: 사용자가 선택한 썸네일을 0번 인덱스(첫 번째 파일)로 이동시켜,
 *       그누보드가 해당 파일을 썸네일로 인식하고 업로드하도록 함.
 */

if (!defined('_GNUBOARD_'))
    exit;

// POST로 넘어온 thumbnail_index 확인
$thumbnail_index = isset($_POST['thumbnail_index']) ? (int) $_POST['thumbnail_index'] : 0;

// 썸네일 인덱스가 0보다 크고 (첫 번째가 아님), 파일이 존재할 때만 재정렬 수행
if ($thumbnail_index > 0 && isset($_FILES['bf_file']) && is_array($_FILES['bf_file']['name'])) {

    // 임시로 저장된 파일 정보 가져오기
    $files_temp = array();
    $file_count = count($_FILES['bf_file']['name']);

    for ($i = 0; $i < $file_count; $i++) {
        // 파일이 있든 없든 배열 구조 유지를 위해 모두 가져옴
        $files_temp[] = array(
            'name' => $_FILES['bf_file']['name'][$i],
            'type' => $_FILES['bf_file']['type'][$i],
            'tmp_name' => $_FILES['bf_file']['tmp_name'][$i],
            'error' => $_FILES['bf_file']['error'][$i],
            'size' => $_FILES['bf_file']['size'][$i]
        );
    }

    // 썸네일로 선택된 파일이 존재하는지 확인 (선택한 인덱스에 파일이 있어야 함)
    if (isset($files_temp[$thumbnail_index]) && $files_temp[$thumbnail_index]['name']) {
        // 선택된 파일을 맨 앞으로 이동 (배열 조작)
        $selected_file = $files_temp[$thumbnail_index];

        // 해당 인덱스 제거
        unset($files_temp[$thumbnail_index]);

        // 맨 앞에 추가
        array_unshift($files_temp, $selected_file);

        // $_FILES 배열 재구성
        $reordered_files = array(
            'name' => array(),
            'type' => array(),
            'tmp_name' => array(),
            'error' => array(),
            'size' => array()
        );

        // key 재정렬 (0, 1, 2...)
        $files_temp = array_values($files_temp);

        foreach ($files_temp as $file) {
            $reordered_files['name'][] = $file['name'];
            $reordered_files['type'][] = $file['type'];
            $reordered_files['tmp_name'][] = $file['tmp_name'];
            $reordered_files['error'][] = $file['error'];
            $reordered_files['size'][] = $file['size'];
        }

        // $_FILES['bf_file'] 덮어쓰기
        $_FILES['bf_file'] = $reordered_files;

        // bf_content(파일 설명)도 파일과 동일하게 순서 변경
        if (isset($_POST['bf_content']) && is_array($_POST['bf_content'])) {
            $content_temp = $_POST['bf_content'];
            $selected_content = isset($content_temp[$thumbnail_index]) ? $content_temp[$thumbnail_index] : '';

            unset($content_temp[$thumbnail_index]);
            array_unshift($content_temp, $selected_content);

            // key 재정렬
            $_POST['bf_content'] = array_values($content_temp);
        }
    }
}
?>
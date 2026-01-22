<?php
/**
 * 포트폴리오 게시판 전용 - 크롭 이미지 저장
 * 이 파일은 write_update.php가 업로드를 모두 마친 '후'에 실행됩니다.
 * 목적: 업로드가 완료된 '원본 썸네일' 파일을 사용자가 편집한 '크롭 이미지'로 덮어씌웁니다.
 */

if (!defined('_GNUBOARD_'))
    exit;

// 크롭된 이미지가 있는지 확인
$cropped_image = isset($_POST['cropped_image']) ? $_POST['cropped_image'] : '';

if ($cropped_image && strpos($cropped_image, 'data:image') === 0 && $wr_id) {
    // 1. 현재 글($wr_id)의 첫 번째 파일(썸네일, bf_no=0) 정보를 DB에서 가져옴
    $sql = " select bf_file from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = 0 ";
    $row = sql_fetch($sql);

    if ($row && $row['bf_file']) {
        // 2. 실제 파일 경로 확인
        $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $row['bf_file'];

        if (file_exists($file_path)) {
            // 3. Base64 데이터 디코딩
            list($type, $data) = explode(';', $cropped_image);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);

            if ($data) {
                // 4. 원본 파일 덮어쓰기 (크롭된 이미지로 교체)
                // file_put_contents는 서버 권한으로 쓰므로 is_uploaded_file 체크와 무관하게 작동함
                file_put_contents($file_path, $data);

                // (선택사항) 썸네일은 새로 생성되도록 기존 썸네일 삭제 로직이 필요할 수 있으나, 
                // 그누보드는 뷰 페이지 접속 시 썸네일을 자동 생성하므로 원본만 바꾸면 충분함.
            }
        }
    }
}
?>
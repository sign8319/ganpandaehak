<?php
if (!defined('_GNUBOARD_'))
    exit;

// ====================================================================================
// 1. 추가 필드 (wr_11 이상) 저장 로직
// 기본 write_update.php는 wr_10까지만 자동 저장하므로, wr_11은 직접 업데이트해야 합니다.
// ====================================================================================
if (isset($_POST['wr_11'])) {
    $wr_11 = clean_xss_tags(trim($_POST['wr_11']));
    sql_query(" update {$write_table} set wr_11 = '{$wr_11}' where wr_id = '{$wr_id}' ");
}

// ====================================================================================
// 2. 크롭된 이미지 (Base64) 처리 로직
// ====================================================================================

// 파일 저장 디렉토리 (없으면 생성)
$file_dir = G5_DATA_PATH . '/file/' . $bo_table;
@mkdir($file_dir, G5_DIR_PERMISSION);
@chmod($file_dir, G5_DIR_PERMISSION);

// 크롭 이미지 처리 함수
function save_cropped_image($base64_data, $bo_table, $wr_id, $bf_no, $file_dir)
{
    global $g5, $now;

    if (empty($base64_data))
        return;

    // Base64 헤더 제거 (data:image/png;base64, ... 형태)
    $data_pieces = explode(',', $base64_data);
    $encoded_image = count($data_pieces) > 1 ? $data_pieces[1] : $base64_data;
    $decoded_image = base64_decode($encoded_image);

    // 파일명 생성 (충돌 방지)
    $chars_array = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    shuffle($chars_array);
    $shuffle = implode('', array_slice($chars_array, 0, 8));
    $filename = abs(ip2long($_SERVER['REMOTE_ADDR'])) . '_' . substr(microtime(), 2, 9) . $shuffle . '.png'; // PNG로 저장
    $filesize = strlen($decoded_image);

    // 파일 저장
    $dest_path = $file_dir . '/' . $filename;
    file_put_contents($dest_path, $decoded_image);
    chmod($dest_path, G5_FILE_PERMISSION);

    // 기존 파일 정보 확인
    $row = sql_fetch(" select bf_file from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$bf_no' ");

    // 기존 파일이 있으면 삭제
    if ($row['bf_file']) {
        @unlink($file_dir . '/' . $row['bf_file']);
    }

    // DB Insert/Update
    if ($row['bf_file']) {
        // Update
        $sql = " update {$g5['board_file_table']}
                    set bf_source = 'cropped_image.png',
                        bf_file = '{$filename}',
                        bf_download = 0,
                        bf_content = '',
                        bf_filesize = '{$filesize}',
                        bf_width = 0,
                        bf_height = 0,
                        bf_type = 2,
                        bf_datetime = '{$now}'
                  where bo_table = '$bo_table'
                    and wr_id = '$wr_id'
                    and bf_no = '$bf_no' ";
        sql_query($sql);
    } else {
        // Insert
        $sql = " insert into {$g5['board_file_table']}
                    set bo_table = '$bo_table',
                        wr_id = '$wr_id',
                        bf_no = '$bf_no',
                        bf_source = 'cropped_image.png',
                        bf_file = '{$filename}',
                        bf_download = 0,
                        bf_content = '',
                        bf_filesize = '{$filesize}',
                        bf_width = 0,
                        bf_height = 0,
                        bf_type = 2,
                        bf_datetime = '{$now}' ";
        sql_query($sql);
    }

    // 게시판 테이블(g5_write_...)의 파일 개수 업데이트
    // 전체 파일 개수를 다시 셉니다.
    $cnt = sql_fetch(" select count(*) as cnt from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' ");
    sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_file = '{$cnt['cnt']}' where wr_id = '$wr_id' ");
}

// ----------------------------------------------------
// (A) 고정형 썸네일 (fixed) -> bf_no = 0
// ----------------------------------------------------
if (isset($_POST['wr_10']) && $_POST['wr_10'] == 'fixed') {
    if (!empty($_POST['cropped_image_fixed'])) {
        save_cropped_image($_POST['cropped_image_fixed'], $bo_table, $wr_id, 0, $file_dir);
    }
}

// ----------------------------------------------------
// (B) Before & After
// Before -> bf_no = 1
// After  -> bf_no = 0
// ----------------------------------------------------
if (isset($_POST['wr_10']) && $_POST['wr_10'] == 'beforeafter') {
    // After 이미지 (bf_no 0)
    if (!empty($_POST['cropped_image_after'])) {
        save_cropped_image($_POST['cropped_image_after'], $bo_table, $wr_id, 0, $file_dir);
    }
    // Before 이미지 (bf_no 1)
    if (!empty($_POST['cropped_image_before'])) {
        save_cropped_image($_POST['cropped_image_before'], $bo_table, $wr_id, 1, $file_dir);
    }
}

// 썸네일 새로 생성 (목록 출력용)
// 기존 썸네일 삭제 후 자동 생성되게 하거나 직접 생성
// 여기서는 글 수정 시 썸네일 갱신을 위해 간단히 처리
delete_board_thumbnail($bo_table, $wr_id);
?>
<?php
if (!defined('_GNUBOARD_'))
    exit;

// ====================================================================================
// 1. 추가 필드 (wr_10, wr_11) 저장 로직
// ====================================================================================

// wr_10 (썸네일 타입: fixed / beforeafter)
if (isset($_POST['wr_10'])) {
    $wr_10 = clean_xss_tags(trim($_POST['wr_10']));
    sql_query(" update {$write_table} set wr_10 = '{$wr_10}' where wr_id = '{$wr_id}' ");
}

// wr_11 (상세페이지 표시 방식: before / after / auto)
if (isset($_POST['wr_11'])) {
    $wr_11 = clean_xss_tags(trim($_POST['wr_11']));
    sql_query(" update {$write_table} set wr_11 = '{$wr_11}' where wr_id = '{$wr_id}' ");
}

// ====================================================================================
// 2. 파일 권한 및 폴더 자동 생성 (안전장치)
// ====================================================================================
$file_dir = G5_DATA_PATH . '/file/' . $bo_table;

// 폴더가 없으면 생성
if (!is_dir($file_dir)) {
    @mkdir($file_dir, G5_DIR_PERMISSION, true);
    @chmod($file_dir, G5_DIR_PERMISSION);
}

// 폴더 권한이 755/707 등이 아니면 시도 (필요시)
if (is_dir($file_dir) && !is_writable($file_dir)) {
    @chmod($file_dir, G5_DIR_PERMISSION);
}

// ====================================================================================
// 3. 썸네일 갱신
// ====================================================================================
// 글 수정 시 기존 썸네일을 삭제하여 새로 생성되도록 유도
delete_board_thumbnail($bo_table, $wr_id);
?>
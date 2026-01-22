<?php
if (!defined('_GNUBOARD_'))
    exit;

// [강제 삭제 패치]
// 데이터 꼬임(wr_id != wr_parent 등)으로 인해 표준 로직에서 삭제되지 않는 게시물을
// 관리자 권한으로 강제 삭제합니다.
if ($is_admin && $write['wr_id']) {
    // 1. 게시물 Row 강제 삭제
    sql_query(" delete from $write_table where wr_id = '{$write['wr_id']}' ");

    // 2. 파일 테이블 정리 (이미 삭제되었을 수도 있지만 안전장치)
    // 파일 삭제까지는 복잡도를 높이므로, 우선 DB에서 안 보이게 처리하는 것을 우선합니다.
    // 필요 시 파일 unlink 로직 추가 가능
    sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$write['wr_id']}' ");
}
?>
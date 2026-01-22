<?php
if (!defined('_GNUBOARD_'))
    exit;

// [강제 삭제 기능]
// 데이터 꼬임으로 표준 로직에서 삭제되지 않는 게시물을
// '선택삭제' 시 관리자 권한으로 강제 삭제합니다.

if ($is_admin && count($tmp_array) > 0) {
    foreach ($tmp_array as $wr_id) {
        $wr_id = (int) $wr_id;
        if (!$wr_id)
            continue;

        // 원글 정보 확인 (표준 로직이 실패할 경우를 대비)
        $write = sql_fetch(" select * from $write_table where wr_id = '$wr_id' ");

        // 게시물이 존재하는데 표준 로직(delete_all.php)에서 삭제되지 않았을 경우를 대비해
        // 여기서 미리 강제 삭제를 시도할 수도 있지만,
        // delete_all.php는 '상위 권한' 체크 등으로 인해 skip 하는 경우가 많음.
        // 따라서 여기서는 '관리자가 선택한 것은 무조건 삭제'라는 정책으로
        // 강제 삭제 쿼리를 날립니다.

        // 1. 게시물 Row 삭제
        sql_query(" delete from $write_table where wr_id = '$wr_id' ");

        // 2. 파일 정리 (DB)
        sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' ");

        // 3. 새글/스크랩 등 정리
        sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_parent = '$wr_id' ");
        sql_query(" delete from {$g5['scrap_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' ");
    }
}
?>
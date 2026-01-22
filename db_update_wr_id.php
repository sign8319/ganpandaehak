<?php
include_once('./_common.php');

// quotes 테이블에 wr_id 컬럼 추가
$row = sql_fetch(" SHOW COLUMNS FROM quotes LIKE 'wr_id' ");
if (!$row) {
    sql_query(" ALTER TABLE quotes ADD `wr_id` int(11) NOT NULL DEFAULT 0 AFTER `id` ");
    echo "Added wr_id column to quotes table.<br>";
} else {
    echo "wr_id column already exists.<br>";
}

// 기존 데이터 동기화 등은 필요한 경우 여기서 수행
?>
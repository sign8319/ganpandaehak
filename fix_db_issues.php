<?php
include_once('./_common.php');

// Security: Only Admin
if (!$is_admin) {
    die("관리자만 실행 가능합니다.");
}

echo "<style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; } h2 { border-bottom: 2px solid #ddd; padding-bottom: 10px; } .log { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; margin-bottom: 20px; }</style>";
echo "<h1>DB Issue Fixer</h1>";

// 0. Set SQL Mode
sql_query("SET SESSION sql_mode = ''");

// =========================================================
// PART 1: g5_write_consult Table Repair
// =========================================================
echo "<h2>1. '문의폼(consult)' 게시판 복구</h2>";
echo "<div class='log'>";

$target_table = $g5['write_prefix'] . 'consult';
$row = sql_fetch(" SHOW TABLES LIKE '{$target_table}' ");

if ($row) {
    echo "<div>- 테이블 '{$target_table}'이 이미 존재합니다.</div>";
} else {
    echo "<div>- 테이블 '{$target_table}'이 없습니다. 생성을 시작합니다...</div>";
    $sql = "CREATE TABLE `{$target_table}` (
      `wr_id` int(11) NOT NULL AUTO_INCREMENT,
      `wr_num` int(11) NOT NULL DEFAULT 0,
      `wr_reply` varchar(10) NOT NULL,
      `wr_parent` int(11) NOT NULL DEFAULT 0,
      `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
      `wr_comment` int(11) NOT NULL DEFAULT 0,
      `wr_comment_reply` varchar(5) NOT NULL,
      `ca_name` varchar(255) NOT NULL,
      `wr_option` set('html1','html2','secret','mail') NOT NULL,
      `wr_subject` varchar(255) NOT NULL,
      `wr_content` text NOT NULL,
      `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
      `wr_link1` text NOT NULL,
      `wr_link2` text NOT NULL,
      `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
      `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
      `wr_hit` int(11) NOT NULL DEFAULT 0,
      `wr_good` int(11) NOT NULL DEFAULT 0,
      `wr_nogood` int(11) NOT NULL DEFAULT 0,
      `mb_id` varchar(20) NOT NULL,
      `wr_password` varchar(255) NOT NULL,
      `wr_name` varchar(255) NOT NULL,
      `wr_email` varchar(255) NOT NULL,
      `wr_homepage` varchar(255) NOT NULL,
      `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      `wr_file` tinyint(4) NOT NULL DEFAULT 0,
      `wr_last` varchar(19) NOT NULL,
      `wr_ip` varchar(255) NOT NULL,
      `wr_facebook_user` varchar(255) NOT NULL,
      `wr_twitter_user` varchar(255) NOT NULL,
      `wr_1` varchar(255) NOT NULL,
      `wr_2` varchar(255) NOT NULL,
      `wr_3` varchar(255) NOT NULL,
      `wr_4` varchar(255) NOT NULL,
      `wr_5` varchar(255) NOT NULL,
      `wr_6` varchar(255) NOT NULL,
      `wr_7` varchar(255) NOT NULL,
      `wr_8` varchar(255) NOT NULL,
      `wr_9` varchar(255) NOT NULL,
      `wr_10` varchar(255) NOT NULL,
      PRIMARY KEY (`wr_id`),
      KEY `wr_seo_title` (`wr_seo_title`),
      KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
      KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

    if (sql_query($sql)) {
        echo "<div>- <b>성공:</b> 테이블을 생성했습니다.</div>";
    } else {
        echo "<div>- <b>실패:</b> 테이블 생성 중 오류 발생. (" . sql_error_info() . ")</div>";
    }
}

// Data Migration
$sql = " SELECT * FROM g5_quote WHERE qa_status != '삭제' ORDER BY qa_datetime ASC ";
$result = sql_query($sql);
$migrated = 0;
while ($row = sql_fetch_array($result)) {
    $wr_subject = addslashes($row['qa_subject']);
    $wr_datetime = $row['qa_datetime'];

    // Check exist
    $chk = sql_fetch(" SELECT count(*) as cnt FROM {$target_table} WHERE wr_subject = '{$wr_subject}' AND wr_datetime = '{$wr_datetime}' ");
    if ($chk['cnt'] > 0)
        continue;

    $row_num = sql_fetch(" SELECT min(wr_num) as min_wr_num FROM {$target_table} ");
    $wr_num = (int) ($row_num['min_wr_num'] ?? 0) - 1;

    $wr_content = addslashes($row['qa_memo_user']);
    $wr_name = addslashes($row['qa_client_name']);
    $wr_email = addslashes($row['qa_client_email']);

    $insert_sql = " INSERT INTO {$target_table}
                    SET wr_num = '{$wr_num}',
                        wr_reply = '',
                        wr_comment = 0,
                        ca_name = '',
                        wr_option = '',
                        wr_subject = '{$wr_subject}',
                        wr_content = '{$wr_content}',
                        wr_hit = 0,
                        wr_name = '{$wr_name}',
                        wr_email = '{$wr_email}',
                        wr_datetime = '{$wr_datetime}',
                        wr_last = '{$wr_datetime}',
                        wr_ip = '{$_SERVER['REMOTE_ADDR']}' "; // use simple fields

    if (sql_query($insert_sql)) {
        $wr_id = sql_insert_id();
        sql_query(" UPDATE {$target_table} SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ");
        echo "<div>- 데이터 복구: {$row['qa_subject']} (ID: {$wr_id})</div>";

        // Sync board_new
        $bn_sql = " SELECT bn_id FROM {$g5['board_new_table']} 
                    WHERE bo_table = 'consult' 
                    AND wr_id = 0 
                    AND ABS(TIMESTAMPDIFF(SECOND, bn_datetime, '{$wr_datetime}')) < 60 ";
        $bn_row = sql_fetch($bn_sql);

        if ($bn_row && isset($bn_row['bn_id'])) {
            sql_query(" UPDATE {$g5['board_new_table']} SET wr_id = '{$wr_id}', wr_parent = '{$wr_id}' WHERE bn_id = '{$bn_row['bn_id']}' ");
        } else {
            sql_query(" INSERT INTO {$g5['board_new_table']} SET bo_table = 'consult', wr_id = '{$wr_id}', wr_parent = '{$wr_id}', bn_datetime = '{$wr_datetime}', mb_id = '{$member['mb_id']}' ");
        }
        $migrated++;
    }
}
if ($migrated == 0)
    echo "<div>- 추가로 복구할 데이터가 없습니다.</div>";
else
    echo "<div>- 총 {$migrated}건 복구 완료.</div>";

// Update count
$row = sql_fetch(" SELECT count(*) as cnt FROM {$target_table} ");
$cnt = $row['cnt'];
sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = '{$cnt}' WHERE bo_table = 'consult' ");

echo "</div>";

// =========================================================
// PART 2: Clean up Ghost Records (board_new)
// =========================================================
echo "<h2>2. '삭제된 게시물' (Ghost Records) 정리</h2>";
echo "<div class='log'>";

$boards = array();
$sql = " SELECT bo_table FROM {$g5['board_table']} ";
$res = sql_query($sql);
while ($row = sql_fetch_array($res)) {
    $boards[] = $row['bo_table'];
}

$board_new_table = $g5['board_new_table'];
$cleaned = 0;

// Scan board_new
// Limit scan to recent 200 items to be safe and fast, or scan all if needed.
// 'Consult' and 'BeforeAfter' are main targets.
$sql = " SELECT * FROM {$board_new_table} ORDER BY bn_id DESC LIMIT 500 ";
$res = sql_query($sql);

while ($row = sql_fetch_array($res)) {
    $bo_table = $row['bo_table'];
    $wr_id = $row['wr_id'];
    $bn_id = $row['bn_id'];

    if (!in_array($bo_table, $boards)) {
        // Board not exists
        echo "<div>- 게시판 없음: {$bo_table} (bn_id: {$bn_id}) -> 삭제</div>";
        sql_query(" DELETE FROM {$board_new_table} WHERE bn_id = '{$bn_id}' ");
        $cleaned++;
        continue;
    }

    $write_table = $g5['write_prefix'] . $bo_table;
    $chk = sql_fetch(" SHOW TABLES LIKE '{$write_table}' ");
    if (!$chk) {
        // Table not exists (Should fix consult first, but if still not exists, clean it)
        echo "<div>- 테이블 없음: {$write_table} (bn_id: {$bn_id}) -> 삭제</div>";
        sql_query(" DELETE FROM {$board_new_table} WHERE bn_id = '{$bn_id}' ");
        $cleaned++;
        continue;
    }

    // Check record
    $chk_row = sql_fetch(" SELECT count(*) as cnt FROM {$write_table} WHERE wr_id = '{$wr_id}' ");
    if (!$chk_row || $chk_row['cnt'] == 0) {
        // Ghost record
        echo "<div>- 유령 레코드: {$bo_table} (wr_id: {$wr_id}, bn_id: {$bn_id}) -> 삭제</div>";
        sql_query(" DELETE FROM {$board_new_table} WHERE bn_id = '{$bn_id}' ");
        $cleaned++;
    }
}

if ($cleaned == 0)
    echo "<div>- 정리할 잘못된 레코드가 발견되지 않았습니다.</div>";
else
    echo "<div>- 총 {$cleaned}건의 잘못된 레코드를 삭제했습니다.</div>";

echo "</div>";
echo "<h3>모든 작업이 완료되었습니다. 관리자 메인으로 돌아가 확인해주세요.</h3>";
?>
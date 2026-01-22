<?php
include_once('./_common.php');

echo "<h1>Fix Consult Table & Data</h1>\n";

$source_table = $g5['write_prefix'] . 'notice';
$target_table = $g5['write_prefix'] . 'consult';

// 1. Create Table if not exists
$row = sql_fetch(" SHOW TABLES LIKE '{$target_table}' ");
if ($row) {
    echo "<div>Table '{$target_table}' already exists. Skipping creation.</div>\n";
} else {
    echo "<div>Creating table '{$target_table}' based on '{$source_table}'...</div>\n";
    $sql = " CREATE TABLE {$target_table} LIKE {$source_table} ";
    sql_query($sql);
    echo "<div>Table created.</div>\n";
}

// 2. Migrate Data from g5_quote
echo "<h2>Migrating Data from g5_quote</h2>\n";
$sql = " SELECT * FROM g5_quote WHERE qa_status != '삭제' ORDER BY qa_datetime ASC ";
$result = sql_query($sql);

$cnt = 0;
while ($row = sql_fetch_array($result)) {
    $wr_subject = $row['qa_subject'];
    $wr_name = $row['qa_client_name'];
    $wr_content = $row['qa_memo_user'];
    $wr_datetime = $row['qa_datetime'];
    $wr_email = $row['qa_client_email'];

    // Check duplication
    $check_sql = " SELECT count(*) as cnt FROM {$target_table} 
                   WHERE wr_subject = '" . addslashes($wr_subject) . "' 
                   AND wr_datetime = '{$wr_datetime}' ";
    $check_row = sql_fetch($check_sql);

    if ($check_row['cnt'] > 0) {
        // 이미 존재하면 마이그레이션 스킵
        echo "<div>Skipping duplicate: {$wr_subject} ({$wr_datetime})</div>\n";
        continue;
    }

    // wr_num 계산
    $row_num = sql_fetch(" SELECT min(wr_num) as min_wr_num FROM {$target_table} ");
    $wr_num = (int) ($row_num['min_wr_num'] ?? 0) - 1;

    $insert_sql = " INSERT INTO {$target_table}
                    SET wr_num = '{$wr_num}',
                        wr_reply = '',
                        wr_comment = 0,
                        ca_name = '',
                        wr_option = '',
                        wr_subject = '" . addslashes($wr_subject) . "',
                        wr_content = '" . addslashes($wr_content) . "',
                        wr_link1 = '',
                        wr_link2 = '',
                        wr_link1_hit = 0,
                        wr_link2_hit = 0,
                        wr_hit = 0,
                        wr_good = 0,
                        wr_nogood = 0,
                        mb_id = '',
                        wr_password = '',
                        wr_name = '" . addslashes($wr_name) . "',
                        wr_email = '" . addslashes($wr_email) . "',
                        wr_homepage = '',
                        wr_datetime = '{$wr_datetime}',
                        wr_last = '{$wr_datetime}',
                        wr_ip = '127.0.0.1',
                        wr_1 = '',
                        wr_2 = '',
                        wr_3 = '',
                        wr_4 = '',
                        wr_5 = '',
                        wr_6 = '',
                        wr_7 = '',
                        wr_8 = '',
                        wr_9 = '',
                        wr_10= '' ";
    sql_query($insert_sql);
    $wr_id = sql_insert_id();

    // Update wr_parent
    sql_query(" UPDATE {$target_table} SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ");

    echo "<div>Inserted: {$wr_subject} (ID: {$wr_id})</div>\n";

    // 3. Fix g5_board_new
    $bn_sql = " SELECT bn_id FROM {$g5['board_new_table']} 
                WHERE bo_table = 'consult' 
                AND wr_id = 0 
                AND ABS(TIMESTAMPDIFF(SECOND, bn_datetime, '{$wr_datetime}')) < 5 ";
    $bn_row = sql_fetch($bn_sql);

    if ($bn_row && isset($bn_row['bn_id'])) {
        $update_bn = " UPDATE {$g5['board_new_table']} 
                       SET wr_id = '{$wr_id}', wr_parent = '{$wr_id}' 
                       WHERE bn_id = '{$bn_row['bn_id']}' ";
        sql_query($update_bn);
        echo "<div> -> Fixed g5_board_new (bn_id: {$bn_row['bn_id']})</div>\n";
    } else {
        $insert_bn = " INSERT INTO {$g5['board_new_table']}
                       SET bo_table = 'consult',
                           wr_id = '{$wr_id}',
                           wr_parent = '{$wr_id}',
                           bn_datetime = '{$wr_datetime}',
                           mb_id = '' ";
        sql_query($insert_bn);
        echo "<div> -> Inserted into g5_board_new</div>\n";
    }

    $cnt++;
}

echo "<h3>Total {$cnt} records migrated.</h3>\n";

// 4. Clean up remaining broken entries in g5_board_new
echo "<h2>Cleaning up broken entries</h2>\n";
$sql = " DELETE FROM {$g5['board_new_table']} WHERE bo_table = 'consult' AND wr_id = 0 ";
sql_query($sql);
// G5 doesn't seem to expose sql_affected_rows globally in common.php or it depends on driver.
// Just ignore the count output or check connection.
global $g5_connect;
$affected = 0;
if (function_exists('mysqli_affected_rows') && isset($g5_connect)) {
    $affected = mysqli_affected_rows($g5_connect);
}
echo "<div>Deleted broken records from g5_board_new.</div>\n";

// Update board count
$row = sql_fetch(" SELECT count(*) as cnt FROM {$target_table} ");
$bo_count_write = $row['cnt'];
sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = '{$bo_count_write}' WHERE bo_table = 'consult' ");
echo "<div>Updated board write count to {$bo_count_write}.</div>\n";

echo "<h1>Done.</h1>";
?>
<?php
include_once('./_common.php');

if ($is_admin != 'super') {
    echo json_encode(['error' => '권한이 없습니다.']);
    exit;
}

$mode = $_POST['mode'];
$table_name = G5_TABLE_PREFIX . 'schedule';

// DB Check & Fix (Self-healing)
$row = sql_fetch(" SHOW COLUMNS FROM {$table_name} LIKE 'is_done' ");
if (!$row) {
    sql_query(" ALTER TABLE {$table_name} ADD `is_done` tinyint(4) NOT NULL DEFAULT '0' AFTER `color` ");
    sql_query(" ALTER TABLE {$table_name} ADD `alarm_minutes` int(11) NOT NULL DEFAULT '0' AFTER `is_done` ");
}

if ($mode == 'bg_insert' || $mode == 'update') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $memo = isset($_POST['memo']) ? trim($_POST['memo']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '#3788d8';
    $is_done = isset($_POST['is_done']) ? (int) $_POST['is_done'] : 0;
    $alarm_minutes = isset($_POST['alarm_minutes']) ? (int) $_POST['alarm_minutes'] : 0;

    // Convert to proper datetime format
    $start_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
    $end_datetime = date('Y-m-d H:i:s', strtotime($end_datetime));

    if (!$title) {
        echo json_encode(['error' => '제목을 입력해주세요.']);
        exit;
    }
}

if ($mode == 'bg_insert') {
    $sql = " INSERT INTO {$table_name}
             SET title = '{$title}',
                 start_datetime = '{$start_datetime}',
                 end_datetime = '{$end_datetime}',
                 memo = '{$memo}',
                 color = '{$color}',
                 is_done = '{$is_done}',
                 alarm_minutes = '{$alarm_minutes}',
                 created_at = NOW(),
                 updated_at = NOW() ";
    sql_query($sql);
    echo json_encode(['status' => 'success', 'id' => sql_insert_id()]);
} else if ($mode == 'update') {
    $id = (int) $_POST['id'];
    $sql = " UPDATE {$table_name}
             SET title = '{$title}',
                 start_datetime = '{$start_datetime}',
                 end_datetime = '{$end_datetime}',
                 memo = '{$memo}',
                 color = '{$color}',
                 is_done = '{$is_done}',
                 alarm_minutes = '{$alarm_minutes}',
                 updated_at = NOW()
             WHERE id = '{$id}' ";
    sql_query($sql);
    echo json_encode(['status' => 'success']);
} else if ($mode == 'delete') {
    $id = (int) $_POST['id'];
    $sql = " DELETE FROM {$table_name} WHERE id = '{$id}' ";
    sql_query($sql);
    echo json_encode(['status' => 'success']);
} else if ($mode == 'update_date') { // Drag and drop update
    $id = (int) $_POST['id'];
    $start_datetime = date('Y-m-d H:i:s', strtotime($_POST['start_datetime']));
    $end_datetime = date('Y-m-d H:i:s', strtotime($_POST['end_datetime']));

    $sql = " UPDATE {$table_name}
             SET start_datetime = '{$start_datetime}',
                 end_datetime = '{$end_datetime}',
                 updated_at = NOW()
             WHERE id = '{$id}' ";
    sql_query($sql);
    echo json_encode(['status' => 'success']);
} else if ($mode == 'toggle_done') {
    $id = (int) $_POST['id'];
    $is_done = (int) $_POST['is_done'];
    $sql = " UPDATE {$table_name} SET is_done = '{$is_done}', updated_at = NOW() WHERE id = '{$id}' ";
    sql_query($sql);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['error' => '잘못된 요청입니다.']);
}
?>
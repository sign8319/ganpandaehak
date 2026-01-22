<?php
include_once('./_common.php');

if ($is_admin != 'super')
    die(json_encode(['error' => 'Unauthorized']));

$start = $_GET['start'];
$end = $_GET['end'];

// Format dates for DB query
$start_date = date('Y-m-d H:i:s', strtotime($start));
$end_date = date('Y-m-d H:i:s', strtotime($end));

$sql = " SELECT * FROM " . G5_TABLE_PREFIX . "schedule 
         WHERE start_datetime >= '{$start_date}' AND end_datetime <= '{$end_date}' "; // This might miss events spanning across? 
// Better logic for overlaps: (StartA <= EndB) and (EndA >= StartB)
$sql = " SELECT * FROM " . G5_TABLE_PREFIX . "schedule 
         WHERE end_datetime >= '{$start_date}' AND start_datetime <= '{$end_date}' ";

$result = sql_query($sql);

$events = array();

while ($row = sql_fetch_array($result)) {
    $className = $row['is_done'] ? 'done-task' : '';

    $events[] = array(
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start_datetime'],
        'end' => $row['end_datetime'],
        'backgroundColor' => $row['color'],
        'borderColor' => $row['color'],
        'className' => $className,
        'extendedProps' => array(
            'memo' => $row['memo'],
            'is_done' => $row['is_done'],
            'alarm_minutes' => $row['alarm_minutes'],
            'related_type' => $row['related_type'],
            'related_id' => $row['related_id']
        )
    );
}

echo json_encode($events);
?>
<?php
include_once('./common.php');

echo "<h2>데이터베이스 파일 정보:</h2>";

$sql = "SELECT bo_table, wr_id, bf_file FROM g5_board_file WHERE bo_table='ca_portfolio' ORDER BY wr_id DESC LIMIT 6";
$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
    echo "wr_id: {$row['wr_id']}<br>";
    echo "파일명: {$row['bf_file']}<br>";
    
    $full_path = G5_DATA_PATH . '/file/ca_portfolio/' . $row['bf_file'];
    $url_path = G5_DATA_URL . '/file/ca_portfolio/' . $row['bf_file'];
    
    echo "물리경로: {$full_path}<br>";
    echo "URL경로: {$url_path}<br>";
    echo "파일존재: " . (file_exists($full_path) ? '✅ 예' : '❌ 아니오') . "<br>";
    echo "<img src='{$url_path}' style='max-width:200px;'><br>";
    echo "<hr>";
}

echo "<h3>G5 상수:</h3>";
echo "G5_DATA_PATH: " . G5_DATA_PATH . "<br>";
echo "G5_DATA_URL: " . G5_DATA_URL . "<br>";
echo "G5_URL: " . G5_URL . "<br>";
?>
```

저장 후:
```
http://localhost/ganpandaehak/test_db.php
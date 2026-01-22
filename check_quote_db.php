<?php
include_once('./_common.php');

// if (!$is_admin) {
//    echo "관리자만 접근 가능합니다.";
//    exit;
// }

echo "<h1>최근 견적 데이터 확인 (g5_quote)</h1>";

// 1. 테이블 존재 여부 및 컬럼 확인
$tbl = sql_fetch("SHOW TABLES LIKE 'g5_quote'");
if (!$tbl) {
    echo "<p style='color:red'>g5_quote 테이블이 존재하지 않습니다.</p>";
} else {
    echo "<p>g5_quote 테이블 존재함.</p>";
    $cols = sql_query("SHOW COLUMNS FROM g5_quote");
    echo "<ul>";
    while ($col = sql_fetch_array($cols)) {
        echo "<li>{$col['Field']} ({$col['Type']})</li>";
    }
    echo "</ul>";
}

// 2. 최근 데이터 5건 조회
echo "<h2>최근 데이터 (Limit 5)</h2>";
$sql = " SELECT * FROM g5_quote ORDER BY qa_id DESC LIMIT 5 ";
$result = sql_query($sql);

echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#f1f1f1'>
        <th>qa_id</th>
        <th>mb_id</th>
        <th>qa_status</th>
        <th>qa_subject</th>
        <th>qa_datetime</th>
        <th>qa_client_name</th>
      </tr>";

while ($row = sql_fetch_array($result)) {
    echo "<tr>";
    echo "<td>{$row['qa_id']}</td>";
    echo "<td>" . ($row['mb_id'] ? $row['mb_id'] : '<span style="color:red">NULL/Empty</span>') . "</td>";
    echo "<td>{$row['qa_status']}</td>";
    echo "<td>{$row['qa_subject']}</td>";
    echo "<td>{$row['qa_datetime']}</td>";
    echo "<td>{$row['qa_client_name']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>현재 로그인 세션 정보</h2>";
echo "<p>Member ID: {$member['mb_id']}</p>";
?>
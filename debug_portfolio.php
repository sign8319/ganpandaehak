<?php
// 그누보드 초기화
include_once('./_common.php');

// 게시판 테이블명
$bo_table = 'ca_portfolio';

// 페이지 설정
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

echo "<h2>포트폴리오 게시판 디버깅</h2>";
echo "<hr>";

// 1. 게시판 설정 확인
$sql = "SELECT * FROM {$g5['board_table']} WHERE bo_table = '$bo_table'";
$board = sql_fetch($sql);

if ($board) {
    echo "<h3>✅ 게시판 설정 발견</h3>";
    echo "게시판 이름: " . $board['bo_subject'] . "<br>";
    echo "PC 스킨: " . $board['bo_skin'] . "<br>";
    echo "모바일 스킨: " . $board['bo_mobile_skin'] . "<br>";
} else {
    echo "<h3>❌ 게시판 설정 없음!</h3>";
    exit;
}

echo "<hr>";

// 2. 전체 글 개수 확인
$sql = "SELECT COUNT(*) as cnt FROM {$g5['write_prefix']}{$bo_table}";
$result = sql_fetch($sql);
echo "<h3>전체 글 개수: " . $result['cnt'] . "개</h3>";

echo "<hr>";

// 3. 최근 글 목록 (list.php처럼)
$sql = "SELECT * FROM {$g5['write_prefix']}{$bo_table} ORDER BY wr_num LIMIT 0, 10";
$result = sql_query($sql);

$list_count = 0;
echo "<h3>최근 글 10개:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>번호</th><th>제목</th><th>작성일</th><th>조회수</th></tr>";

while ($row = sql_fetch_array($result)) {
    $list_count++;
    echo "<tr>";
    echo "<td>" . $row['wr_id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['wr_subject']) . "</td>";
    echo "<td>" . $row['wr_datetime'] . "</td>";
    echo "<td>" . $row['wr_hit'] . "</td>";
    echo "</tr>";
}

echo "</table>";

if ($list_count == 0) {
    echo "<p style='color:red;'>❌ SQL 쿼리는 실행되지만 결과가 0개입니다!</p>";
} else {
    echo "<p style='color:green;'>✅ SQL 쿼리 정상 작동! {$list_count}개 발견</p>";
}

echo "<hr>";

// 4. 그누보드 list 변수 시뮬레이션
echo "<h3>그누보드 내장 함수 테스트:</h3>";

// common.php의 get_list() 함수 사용
$write_table = $g5['write_prefix'] . $bo_table;

$sql = " select * from $write_table where wr_is_comment = 0 order by wr_num limit 0, 10 ";
$result = sql_query($sql);
$list = array();
for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $list[$i] = get_list($row, $board, G5_BBS_URL . "/board.php?bo_table=$bo_table", "");
}

echo "내장 함수로 불러온 list 배열 개수: " . count($list) . "개<br>";

if (count($list) > 0) {
    echo "<p style='color:green;'>✅ 그누보드 list 배열 정상!</p>";
    echo "<pre>";
    print_r($list[0]); // 첫 번째 항목 출력
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ 그누보드 list 배열이 비어있습니다!</p>";
}

?>
<?php
/**
 * Customer to Work Migration Script (FIXED VERSION)
 * g5_customer에만 있고 g5_work에 없는 고객 데이터를 마이그레이션
 */

include_once('./_common.php');

// 에러 표시 활성화
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 관리자만 실행 가능
if (!$is_admin) {
    die('관리자만 실행할 수 있습니다.');
}

echo "<h2>고객 데이터 마이그레이션</h2>";
echo "<p>g5_customer → g5_work 마이그레이션을 시작합니다...</p>";

// 1. g5_work 테이블 구조 확인
echo "<h3>1. g5_work 테이블 구조</h3>";
$result_desc = sql_query("DESCRIBE g5_work");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
while ($row = sql_fetch_array($result_desc)) {
    echo "<tr>";
    echo "<td><strong>{$row['Field']}</strong></td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. g5_work에 없는 고객 찾기
echo "<h3>2. 마이그레이션 대상 고객</h3>";
$sql_check = "
    SELECT c.* 
    FROM g5_customer c
    WHERE c.customer_id NOT IN (
        SELECT DISTINCT customer_id 
        FROM g5_work 
        WHERE customer_id IS NOT NULL AND customer_id > 0
    )
    ORDER BY c.customer_id ASC
";

$result = sql_query($sql_check);
$total_customers = sql_num_rows($result);

echo "<p><strong>마이그레이션 대상: {$total_customers}명</strong></p>";

if ($total_customers == 0) {
    echo "<p style='color: green;'>✅ 모든 고객이 이미 g5_work에 존재합니다.</p>";
    echo "<p><a href='./admin_quote.php'>관리자 페이지로 돌아가기</a></p>";
    exit;
}

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th><th>상호/업체명</th><th>고객명</th><th>전화번호</th><th>등록일</th>";
echo "</tr>";

$customers = [];
while ($row = sql_fetch_array($result)) {
    $customers[] = $row;
    $company = !empty($row['customer_company']) ? $row['customer_company'] : $row['customer_name'];
    echo "<tr>";
    echo "<td>{$row['customer_id']}</td>";
    echo "<td><strong>{$company}</strong></td>";
    echo "<td>{$row['customer_name']}</td>";
    echo "<td>{$row['customer_hp']}</td>";
    echo "<td>{$row['customer_datetime']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. 사용자 확인
if (!isset($_GET['confirm']) || $_GET['confirm'] != 'yes') {
    echo "<p style='color: orange;'>⚠️ 위 고객들을 g5_work 테이블로 마이그레이션하시겠습니까?</p>";
    echo "<p><strong>참고:</strong></p>";
    echo "<ul>";
    echo "<li>work_subject: 상호/업체명 (없으면 고객명)</li>";
    echo "<li>work_status: '고객등록'</li>";
    echo "<li>customer_id: 고객 ID 연결</li>";
    echo "</ul>";
    echo "<p><a href='?confirm=yes' style='display: inline-block; padding: 10px 20px; background: #f97316; color: white; text-decoration: none; border-radius: 5px;'>마이그레이션 실행</a></p>";
    echo "<p><a href='./admin_quote.php'>취소하고 돌아가기</a></p>";
    exit;
}

// 4. 마이그레이션 실행
echo "<h3>3. 마이그레이션 진행</h3>";

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($customers as $customer) {
    echo "<hr style='margin: 15px 0;'>";

    // work_subject 결정 (상호/업체명 우선, 없으면 고객명)
    $work_subject = !empty($customer['customer_company'])
        ? $customer['customer_company']
        : $customer['customer_name'];

    $work_subject = sql_real_escape_string($work_subject);

    echo "<h4>고객: {$customer['customer_name']} (ID: {$customer['customer_id']})</h4>";
    echo "<p><strong>work_subject:</strong> {$work_subject}</p>";

    // 올바른 INSERT 쿼리
    $sql_insert = "
        INSERT INTO g5_work SET
            work_subject = '{$work_subject}',
            work_status = '고객등록',
            qa_id = 0,
            customer_id = {$customer['customer_id']},
            created_at = '" . ($customer['customer_datetime'] ?: date('Y-m-d H:i:s')) . "',
            updated_at = NOW()
    ";

    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; font-size: 12px;'>" . htmlspecialchars($sql_insert) . "</pre>";

    $result_insert = sql_query($sql_insert, false);

    if ($result_insert) {
        $success_count++;
        $insert_id = sql_insert_id();
        echo "<p style='color: green; font-weight: bold;'>✅ 성공! (work_id: {$insert_id})</p>";
    } else {
        $error_count++;

        $error_msg = 'Unknown error';
        if (function_exists('sql_error')) {
            $error_msg = sql_error();
        } elseif (function_exists('mysqli_error')) {
            global $connect_db;
            if (isset($connect_db)) {
                $error_msg = mysqli_error($connect_db);
            }
        }

        $errors[] = "{$customer['customer_name']} (ID: {$customer['customer_id']}): {$error_msg}";

        echo "<p style='color: red; font-weight: bold;'>❌ 실패!</p>";
        echo "<p style='background: #fee; padding: 10px; border: 1px solid #fcc;'><strong>에러:</strong> " . htmlspecialchars($error_msg) . "</p>";
    }
}

// 5. 결과 요약
echo "<hr style='margin: 30px 0; border: 2px solid #333;'>";
echo "<h3>마이그레이션 완료</h3>";
echo "<p><strong>총 대상:</strong> {$total_customers}명</p>";
echo "<p style='color: green; font-size: 18px;'><strong>✅ 성공:</strong> {$success_count}명</p>";

if ($error_count > 0) {
    echo "<p style='color: red; font-size: 18px;'><strong>❌ 실패:</strong> {$error_count}명</p>";
    echo "<h4>에러 상세:</h4>";
    echo "<ul style='background: #fee; padding: 20px; border: 1px solid #fcc;'>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

// 6. 마이그레이션 후 확인
if ($success_count > 0) {
    echo "<hr>";
    echo "<h3>마이그레이션된 고객 확인</h3>";

    $sql_verify = "
        SELECT w.work_id, w.work_subject, w.work_status, w.customer_id, 
               c.customer_name, c.customer_hp, w.created_at
        FROM g5_work w
        LEFT JOIN g5_customer c ON w.customer_id = c.customer_id
        WHERE w.work_status = '고객등록'
        ORDER BY w.work_id DESC
        LIMIT {$success_count}
    ";

    $result_verify = sql_query($sql_verify);

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>work_id</th><th>work_subject</th><th>work_status</th><th>customer_id</th><th>고객명</th><th>전화번호</th><th>등록일</th>";
    echo "</tr>";

    while ($row = sql_fetch_array($result_verify)) {
        echo "<tr>";
        echo "<td>{$row['work_id']}</td>";
        echo "<td><strong>{$row['work_subject']}</strong></td>";
        echo "<td>{$row['work_status']}</td>";
        echo "<td>{$row['customer_id']}</td>";
        echo "<td>{$row['customer_name']}</td>";
        echo "<td>{$row['customer_hp']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<p style='margin-top: 30px;'>";
echo "<a href='./admin_quote.php' style='display: inline-block; padding: 10px 20px; background: #1f2937; color: white; text-decoration: none; border-radius: 5px;'>✅ 관리자 페이지로 돌아가기</a> ";
echo "<a href='./migrate_customer_to_work.php' style='display: inline-block; padding: 10px 20px; background: #6b7280; color: white; text-decoration: none; border-radius: 5px;'>🔄 다시 실행</a>";
echo "</p>";

// 7. 로그 기록
$log_file = G5_DATA_PATH . '/migration_customer_to_work.log';
$log_content = date('Y-m-d H:i:s') . " - Customer to Work Migration\n";
$log_content .= "Total: {$total_customers}, Success: {$success_count}, Failed: {$error_count}\n";
if ($error_count > 0) {
    $log_content .= "Errors:\n" . implode("\n", $errors) . "\n";
}
$log_content .= "---\n";
file_put_contents($log_file, $log_content, FILE_APPEND);

echo "<p style='margin-top: 20px; padding: 10px; background: #f0f0f0; border: 1px solid #ddd;'>";
echo "<strong>📄 로그 파일:</strong> " . $log_file;
echo "</p>";

?>
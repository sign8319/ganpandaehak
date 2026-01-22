<?php
// ============================================================================
// 홈택스 세금계산서 엑셀 생성 (PhpSpreadsheet 사용 / .xlsx 출력)
// ============================================================================

// 1. 출력 버퍼 정리 (파일 오작동 방지)
while (ob_get_level()) {
    ob_end_clean();
}

// 2. 에러 출력 (디버깅용 - 배포 시 주석 처리)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

include_once('./_common.php');

// 3. Composer Autoload (PhpSpreadsheet)
$vendor_autoload = G5_PATH . '/vendor/autoload.php';
if (!file_exists($vendor_autoload)) {
    // 혹시 상위 폴더에 있을 경우 대비
    $vendor_autoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
}

if (!file_exists($vendor_autoload)) {
    alert('Composer Autoload 파일을 찾을 수 없습니다. 관리자에게 문의하세요.');
}
require $vendor_autoload;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// 4. 권한 체크
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

// 5. 파라미터 확인
$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;
if (!$qa_id) {
    alert('견적서를 선택해주세요.', './admin_customer.php');
}

$quote = sql_fetch(" SELECT * FROM g5_quote WHERE qa_id = '$qa_id' ");
if (!$quote) {
    alert('견적서를 찾을 수 없습니다.', './admin_customer.php');
}

// ============================================================================
// 데이터 준비
// ============================================================================

// 설정 로드
$config_file = G5_DATA_PATH . '/quote_config.json';
$biz_info = [];
if (file_exists($config_file)) {
    $biz_info = json_decode(file_get_contents($config_file), true);
}

// 공급자 (User)
$supplier_biz_num = isset($biz_info['biz_no']) ? str_replace('-', '', $biz_info['biz_no']) : '';
$supplier_company = isset($biz_info['biz_name']) ? $biz_info['biz_name'] : '';
$supplier_ceo = isset($biz_info['biz_ceo']) ? $biz_info['biz_ceo'] : '';
$supplier_addr = isset($biz_info['biz_addr']) ? $biz_info['biz_addr'] : '';
$supplier_type = isset($biz_info['biz_type']) ? $biz_info['biz_type'] : '';
$supplier_item = isset($biz_info['biz_class']) ? $biz_info['biz_class'] : '';
$supplier_email = isset($biz_info['biz_email']) ? $biz_info['biz_email'] : '';

// 공급받는자 (Client)
$buyer_biz_num = str_replace('-', '', $quote['qa_tax_biz_num']);
$buyer_company = $quote['qa_tax_company_name'];
$buyer_ceo = $quote['qa_tax_ceo_name'];
$buyer_addr = $quote['qa_tax_addr'];
$buyer_type = ''; // 입력받지 않음
$buyer_item = ''; // 입력받지 않음
$buyer_email1 = $quote['qa_tax_email'];
$buyer_email2 = '';

// 계산서 정보
$tax_type = $quote['qa_tax_type'] ? $quote['qa_tax_type'] : '01'; // 01:일반
$tax_date = $quote['qa_tax_date'] ? date('Ymd', strtotime($quote['qa_tax_date'])) : date('Ymd');
$supply_price = $quote['qa_tax_supply_price'] ? (int) $quote['qa_tax_supply_price'] : 0;
$vat_price = $quote['qa_tax_vat_price'] ? (int) $quote['qa_tax_vat_price'] : 0;
// $total_price = $supply_price + $vat_price; // 홈택스 엑셀에는 합계 필드가 없음 (공급가, 세액만 있음)
$claim_type = $quote['qa_tax_claim_type'] ? $quote['qa_tax_claim_type'] : '01'; // 01:영수, 02:청구
$memo = '';

// 품목 정보 (단일 품목으로 처리)
$item_date = date('d', strtotime($tax_date)); // 일자 (일)
$item_name = $quote['qa_tax_item_name'] ? $quote['qa_tax_item_name'] : '간판제작';
$item_spec = '';
$item_qty = '1';
$item_price = $supply_price;
$item_supply = $supply_price;
$item_vat = $vat_price;
$item_memo = '';


// 데이터 배열 구성 (인덱스 0 ~ 58) 
// * 홈택스 엑셀 양식 기준 *
// 0: 종류
// 1: 작성일자
// 2: 공급자 등록번호
// 3: 공급자 종사업장번호
// 4: 공급자 상호
// 5: 공급자 성명
// 6: 공급자 사업장주소
// 7: 공급자 업태
// 8: 공급자 종목
// 9: 공급자 이메일
// 10: 받는자 등록번호
// 11: 받는자 종사업장번호
// 12: 받는자 상호
// 13: 받는자 성명
// 14: 받는자 사업장주소
// 15: 받는자 업태
// 16: 받는자 종목
// 17: 받는자 이메일1
// 18: 받는자 이메일2
// 19: 공급가액 합계
// 20: 세액 합계
// 21: 비고
// 22~29: 일자1, 품목1, 규격1, 수량1, 단가1, 공급가액1, 세액1, 비고1
// ... (반복) ...
// 58: 영수/청구 구분

$row_data = [
    $tax_type,                  // 0
    $tax_date,                  // 1
    $supplier_biz_num,          // 2
    '',                         // 3 (종사업장)
    $supplier_company,          // 4
    $supplier_ceo,              // 5
    $supplier_addr,             // 6
    $supplier_type,             // 7
    $supplier_item,             // 8
    $supplier_email,            // 9
    $buyer_biz_num,             // 10
    '',                         // 11 (종사업장)
    $buyer_company,             // 12
    $buyer_ceo,                 // 13
    $buyer_addr,                // 14
    '',                         // 15 (업태)
    '',                         // 16 (종목)
    $buyer_email1,              // 17
    $buyer_email2,              // 18
    $supply_price,              // 19
    $vat_price,                 // 20
    $memo,                      // 21

    // 품목 1
    $item_date,                 // 22
    $item_name,                 // 23
    $item_spec,                 // 24
    $item_qty,                  // 25
    $item_price,                // 26
    $item_supply,               // 27
    $item_vat,                  // 28
    $item_memo                  // 29
];

// 나머지 품목 2,3,4 (각 8개 칼럼) 빈값 채우기 (30 ~ 53)
for ($i = 0; $i < 24; $i++) {
    $row_data[] = '';
}

// 현금/수표/어음/외상함 (54~57) - 빈값
for ($i = 0; $i < 4; $i++) {
    $row_data[] = '';
}

// 58: 영수/청구
$row_data[] = $claim_type;


// ============================================================================
// 엑셀 생성 (PhpSpreadsheet)
// ============================================================================

try {
    // 1. 템플릿 로드 (없으면 빈 파일 생성)
    // 가능한 템플릿 파일명 목록
    $possible_templates = [
        G5_DATA_PATH . '/세금계산서등록양식_일반_.xls',
        G5_DATA_PATH . '/세금계산서등록양식_일반_.xlsx',
        G5_DATA_PATH . '/세금계산서등록양식(일반) (100건 이하).xls'
    ];

    $template_path = '';
    foreach ($possible_templates as $t) {
        if (file_exists($t)) {
            $template_path = $t;
            break;
        }
    }

    if ($template_path) {
        // 템플릿 로드 (구형 .xls도 로드 가능)
        $spreadsheet = IOFactory::load($template_path);
    } else {
        // 템플릿 없으면 새로 생성
        $spreadsheet = new Spreadsheet();
    }

    $sheet = $spreadsheet->getActiveSheet();

    // 2. 데이터 입력 (7행부터 시작 - 인덱스 1부터 시작하므로 Row 7)
    $target_row = 7;
    $col_index = 1; // A열 = 1

    foreach ($row_data as $val) {
        // 명시적으로 문자열로 입력 (숫자 변형 방지)
        // getCellByColumnAndRow 대신 좌표 변환 사용 (최신 버전 호환성)
        $colString = Coordinate::stringFromColumnIndex($col_index);
        $sheet->getCell($colString . $target_row)->setValueExplicit($val, DataType::TYPE_STRING);
        $col_index++;
    }

    // 3. 파일 출력 설정 (.xlsx)
    $filename = '세금계산서_' . preg_replace('/[^가-힣a-zA-Z0-9]/', '', $buyer_company) . '_' . date('Ymd') . '.xlsx';

    // 출력 버퍼 최후 정리
    while (ob_get_level()) {
        ob_end_clean();
    }

    // 헤더 설정
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . urlencode($filename) . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    alert('파일 생성 중 오류가 발생했습니다: ' . $e->getMessage());
}

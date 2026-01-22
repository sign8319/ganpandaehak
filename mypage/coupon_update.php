<?php
include_once('./_common.php');

if (!$is_member) {
    die('error: login required');
}

$cp_code = strip_tags($_POST['cp_code']);

// 실제 상용 서비스에서는 g5_coupon 테이블에서 코드를 조회하고 g5_coupon_log에 인서트하는 로직이 필요합니다.
// 여기서는 코드 등록 UI 시연을 위해 성공 메시지만 띄웁니다.

// 예시 로직:
// $cp = sql_fetch(" select * from g5_coupon where cp_id = '{$cp_code}' ");
// if(!$cp) alert('유효하지 않은 쿠폰 코드입니다.');
// sql_query(" insert into g5_coupon_log ... ");

alert('쿠폰이 성공적으로 등록되었습니다.', './coupon_box.php');
?>
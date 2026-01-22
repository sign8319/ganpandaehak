<?php
include_once('./_common.php'); // 그누보드 환경 설정 불러오기

$client_id = "639f97a24765411c8a65368faaaf7fd4"; // REST API 키
$client_secret = "zZ2TYCFIGxMSYx2xVO1SJNoQuZKyZFrM"; // 클라이언트 시크릿
$redirect_uri = urlencode("https://간판대학.com/kakao_callback.php");
$code = $_GET["code"];

// 1. 액세스 토큰 요청
$token_url = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=" . $client_id . "&redirect_uri=" . $redirect_uri . "&code=" . $code . "&client_secret=" . $client_secret;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    // 디버깅을 위해 응답 출력
    echo "카카오 토큰 발급 실패<br>";
    echo "Response: " . $response;
    exit;
}
$access_token = $token_data['access_token'];

// 2. 사용자 정보 조회
$user_info_url = "https://kapi.kakao.com/v2/user/me";
$header = array("Authorization: Bearer " . $access_token);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);

if (!isset($user_data['id'])) {
    echo "카카오 유저 정보 조회 실패<br>";
    echo "Response: " . $user_response;
    exit;
}
$kakao_id = $user_data['id']; // 카카오 고유 식별 번호

// 3. 로그인 및 자동 회원가입 처리 (mb_social_id 이용)
// 사용자 정의: mb_social_id 컬럼을 통해 고유 ID 관리

// 먼저 mb_social_id로 회원이 있는지 조회
$mb = sql_fetch(" select * from {$g5['member_table']} where mb_social_id = '{$kakao_id}' ");

if (!$mb['mb_id']) {
    // 신규 회원이면 자동 가입 처리

    // mb_id 생성: 그누보드는 mb_id가 20자 제한이므로, 고유성을 보장하면서 짧게 생성해야 합니다.
    // 조회는 mb_social_id로 하므로 mb_id는 식별용으로만 유니크하면 됩니다.
    $mb_id = 'k' . substr(md5($kakao_id), 0, 18); // k + 해시값 18자

    // 혹시 모를 mb_id 중복 체크
    $check = sql_fetch(" select mb_id from {$g5['member_table']} where mb_id = '{$mb_id}' ");
    if ($check['mb_id'])
        $mb_id = 'k' . substr(md5($kakao_id . time()), 0, 18);

    $mb_name = isset($user_data['properties']['nickname']) ? $user_data['properties']['nickname'] : '카카오사용자';
    $mb_nick = $mb_name;
    $mb_email = isset($user_data['kakao_account']['email']) ? $user_data['kakao_account']['email'] : '';
    $mb_level = isset($config['cf_register_level']) ? $config['cf_register_level'] : 2;

    // 닉네임 중복 시 처리
    $cnt = 0;
    while (1) {
        $check_nick = $cnt ? $mb_nick . $cnt : $mb_nick;
        $row = sql_fetch(" select count(*) as cnt from {$g5['member_table']} where mb_nick = '{$check_nick}' ");
        if (!$row['cnt']) {
            $mb_nick = $check_nick;
            break;
        }
        $cnt++;
        if ($cnt > 10)
            $mb_nick .= rand(100, 999);
    }

    $sql = " insert into {$g5['member_table']}
                set mb_id = '{$mb_id}',
                     mb_password = '',
                     mb_name = '{$mb_name}',
                     mb_nick = '{$mb_nick}',
                     mb_nick_date = '" . G5_TIME_YMDHIS . "',
                     mb_email = '{$mb_email}',
                     mb_level = '{$mb_level}',
                     mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_today_login = '" . G5_TIME_YMDHIS . "',
                     mb_datetime = '" . G5_TIME_YMDHIS . "',
                     mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_email_certify = '" . G5_TIME_YMDHIS . "',
                     mb_social_id = '{$kakao_id}',
                     mb_hp = '',
                     mb_tel = '',
                     mb_zip1 = '',
                     mb_zip2 = '',
                     mb_addr1 = '',
                     mb_addr2 = '',
                     mb_addr3 = '',
                     mb_addr_jibeon = '',
                     mb_sms = '0',
                     mb_open = '0',
                     mb_profile = '',
                     mb_memo = '',
                     mb_signature = '',
                     mb_recommend = '',
                     mb_point = '0',
                     mb_homepage = '',
                     mb_sex = '',
                     mb_birth = '',
                     mb_certify = '',
                     mb_adult = '0',
                     mb_dupinfo = '',
                     mb_lost_certify = '',
                     mb_1 = '',
                     mb_2 = '',
                     mb_3 = '',
                     mb_4 = '',
                     mb_5 = '',
                     mb_6 = '',
                     mb_7 = '',
                     mb_8 = '',
                     mb_9 = '',
                     mb_10 = '' ";

    // 쿼리 실행 (에러 발생 시 즉시 출력하고 멈춤)
    $result = sql_query($sql, TRUE);
    if (!$result) {
        echo "회원가입 DB 입력 실패!<br>";
        echo "SQL: " . $sql . "<br>";
        // sql_error() 함수로 명확한 원인 파악
        echo "Error: " . sql_error();
        exit;
    }

    // 회원가입 후 정보 다시 조회
    $mb = get_member($mb_id);
}

if (!$mb['mb_id']) {
    alert("로그인 처리 중 회원정보를 가져오지 못했습니다.");
}

// 로그인 세션 및 쿠키 생성
set_session('ss_mb_id', $mb['mb_id']);

// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함
if (function_exists('generate_mb_key'))
    generate_mb_key($mb);

// 회원의 토큰키를 세션에 저장한다. /common.php 에서 해당 회원의 토큰값을 검사한다.
if (function_exists('update_auth_session_token'))
    update_auth_session_token($mb['mb_datetime']);

// 자동로그인 및 아이디 저장
set_cookie('ck_mb_id', $mb['mb_id'], 86400 * 31);
$key = md5($_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_SOFTWARE'] . $_SERVER['HTTP_USER_AGENT'] . $mb['mb_password']);
set_cookie('ck_auto', $key, 86400 * 31);

// 메인으로 이동 (로그인 확인 메시지 출력)
alert($mb['mb_nick'] . "님 반갑습니다!", G5_URL . '/login_dispatch.php');
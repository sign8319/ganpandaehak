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
$kakao_id = $user_data['id']; // 카카오 고유 식별 번호

// 3. 로그인 처리 (그누보드 방식 예시)
// DB에서 kakao_id로 회원 확인 후 세션 생성 로직이 추가되어야 합니다.
alert("카카오 로그인 성공! ID: " . $kakao_id, G5_URL);
?>
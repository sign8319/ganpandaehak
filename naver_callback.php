<?php
include_once('./_common.php');

$client_id = "zr5sckoxHr_mSrE1979d"; // 네이버 Client ID
$client_secret = "BCTZkDsvVe"; // 네이버 Client Secret
$code = $_GET["code"];
$state = $_GET["state"];

// 1. 액세스 토큰 요청
$token_url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=" . $client_id . "&client_secret=" . $client_secret . "&code=" . $code . "&state=" . $state;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);
$access_token = $token_data['access_token'];

// 2. 사용자 정보 조회
$user_info_url = "https://openapi.naver.com/v1/nid/me";
$header = array("Authorization: Bearer " . $access_token);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_info_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);
$naver_id = $user_data['response']['id']; // 네이버 고유 식별 번호

// 3. 로그인 처리
alert("네이버 로그인 성공! ID: " . $naver_id, G5_URL);
?>
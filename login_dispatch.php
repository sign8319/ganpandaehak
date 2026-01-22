<?php
include_once('./_common.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_URL);
}

// 관리자 레벨 체크 (사용자 요청: level >= 5)
if ($member['mb_level'] >= 5) {
    // 관리자 -> 페이지 선택 화면으로
    goto_url(G5_URL . '/select_page.php');
} else {
    // 일반 회원 -> 메인 페이지로
    goto_url(G5_URL);
}
?>
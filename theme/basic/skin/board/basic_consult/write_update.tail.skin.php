<?php
if (!defined('_GNUBOARD_'))
    exit;

// 상담 신청 완료 후 완료 페이지로 리다이렉트
// 새 글 작성(w='')인 경우에만 리다이렉트
if ($w == '') {
    // 회원/비회원 모두 완료 페이지로 (페이지 내에서 조건부 버튼 표시)
    goto_url(G5_THEME_URL . "/quote_complete.php");
}

// 그 외의 경우(수정, 답글 등)는 기본 동작 유지

?>
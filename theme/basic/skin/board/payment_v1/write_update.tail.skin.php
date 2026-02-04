<?php
if (!defined('_GNUBOARD_'))
    exit;

// [ADMIN] 관리자가 작성/수정 시 관리자 페이지로 리다이렉트
if ($is_admin) {
    goto_url(G5_THEME_URL . "/admin_payment.php?bo_table=" . $bo_table . "&wr_id=" . $wr_id . $qstr);
}

// write_update.tail.skin.php
// This file is included at the end of bbs/write_update.php

// 1. Check if custom_password is set
if (isset($_POST['custom_password']) && $_POST['custom_password']) {
    $custom_pw = trim($_POST['custom_password']);

    // 2. Encrypt the password using Gnuboard's standard function
    // function get_encrypt_string($str) is usually available or sql_password()
    // but in newer Gnuboard versions, consistent hash is used.

    // We can rely on the fact that write_update.php uses specific logic.
    // However, since we are in the tail, we can update the DB directly.

    if (function_exists('get_encrypt_string')) {
        $wr_password = get_encrypt_string($custom_pw);
    } else if (function_exists('sql_password')) {
        $wr_password = sql_password($custom_pw);
    } else {
        // Fallback or error? Assuming get_encrypt_string exists as it's standard G5
        $wr_password = password_hash($custom_pw, PASSWORD_DEFAULT);
    }

    // 3. Update the write table
    $write_table = $g5['write_prefix'] . $bo_table;
    $sql = " update {$write_table} set wr_password = '{$wr_password}' where wr_id = '{$wr_id}' ";
    sql_query($sql);
}
?>
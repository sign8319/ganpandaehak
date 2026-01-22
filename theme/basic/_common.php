<?php
// 1) 그누보드 루트 common.php (절대 먼저)
include_once(__DIR__ . '/../../common.php');

// 2) common.php 로딩 이후에만 상수 사용
if (defined('G5_PATH') && defined('QUERIES_DIR')) {
    $qfile = G5_PATH . QUERIES_DIR . '/queries_common.php';
    if (is_file($qfile)) {
        include_once($qfile);
    }
}

// 3) Quote system functions
$quote_functions_file = __DIR__ . '/includes/quote_functions.php';
if (is_file($quote_functions_file)) {
    include_once($quote_functions_file);
}

if (!function_exists('clean_sql')) {
    function clean_sql($str)
    {
        if (is_array($str))
            return $str;
        $str = trim($str);
        return sql_escape_string($str);
    }
}

if (!function_exists('e')) {
    function e($str)
    {
        if ($str === null)
            return '';
        return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
    }
}

<?php
include_once('./_common.php');

$bo_table = 'consult';
$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '$bo_table' ");

// Logic from common.php or similar to determine path
// Usually GnuBoard sets these in common.php if bo_table is passed?
// But write_update.php usually gets bo_table from POST.

if (function_exists('get_skin_path')) {
    $board_skin_path = get_skin_path('board', $board['bo_skin']);
    $board_skin_url = get_skin_url('board', $board['bo_skin']);
} else {
    echo "get_skin_path function not found!\n";
}

echo "G5_PATH: " . G5_PATH . "\n";
echo "G5_THEME_PATH: " . G5_THEME_PATH . "\n";
echo "bo_skin: " . $board['bo_skin'] . "\n";
echo "Real board_skin_path: " . $board_skin_path . "\n";

$target_file = $board_skin_path . '/write_update.skin.php';
echo "Target File: " . $target_file . "\n";
echo "File Exists? " . (file_exists($target_file) ? "YES" : "NO") . "\n";

?>
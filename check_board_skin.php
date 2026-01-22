<?php
include_once('./_common.php');
$board = sql_fetch("select bo_skin, bo_theme from g5_board where bo_table='consult'");
echo "Legacy Skin Path: " . $board['bo_skin'] . "\n";
echo "Theme Skin Path: " . $board['bo_theme'] . "\n";
?>
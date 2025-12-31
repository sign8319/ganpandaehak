<?php
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE); // Suppress warnings for CLI
define('G5_DISPLAY_SQL_ERROR', false);
include_once('../common.php');

$search_skin = 'portfolio';
$sql = " select bo_table, bo_skin, bo_mobile_skin from {$g5['board_table']} where bo_skin like '%{$search_skin}%' or bo_table like '%{$search_skin}%' ";
$result = sql_query($sql);

echo "--------------------------------------------------\n";
echo "Found boards matching '{$search_skin}':\n";
echo "--------------------------------------------------\n";
while ($row = sql_fetch_array($result)) {
    echo "Board ID: " . $row['bo_table'] . "\n";
    echo "PC Skin : " . $row['bo_skin'] . "\n";
    echo "Mo Skin : " . $row['bo_mobile_skin'] . "\n";
    echo "--------------------------------------------------\n";
}
?>
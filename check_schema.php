<?php
include_once('./_common.php');
$result = sql_query(" DESCRIBE g5_quote ");
echo "<pre>";
while ($row = sql_fetch_array($result)) {
    print_r($row);
}
echo "</pre>";
?>
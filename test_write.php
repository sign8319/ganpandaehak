<?php
include_once('./_common.php');
echo "G5_DATA_PATH: " . G5_DATA_PATH . "\n";
$test_file = G5_DATA_PATH . '/test_write.log';
$result = file_put_contents($test_file, "Hello World - " . date('Y-m-d H:i:s'));

if ($result === false) {
    echo "Write Failed!\n";
    echo "Last Error: ";
    print_r(error_get_last());
} else {
    echo "Write Success! Bytes: $result\n";
    echo "File content: " . file_get_contents($test_file) . "\n";
}
?>
<?php
// 오류 표시 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('./_common.php');

echo "<!-- DEBUG: _common.php loaded -->\n";

// ============================================================================
// Load Common Libraries
// ============================================================================
if (file_exists('./includes/quote_functions.php')) {
    include_once('./includes/quote_functions.php');
    echo "<!-- DEBUG: quote_functions.php loaded -->\n";
} else {
    die("ERROR: quote_functions.php not found!");
}

if (file_exists('./includes/quote_db_schema.php')) {
    include_once('./includes/quote_db_schema.php');
    echo "<!-- DEBUG: quote_db_schema.php loaded -->\n";
} else {
    die("ERROR: quote_db_schema.php not found!");
}

echo "<!-- DEBUG: All includes loaded successfully -->\n";

// 나머지 코드는 그대로...
?>
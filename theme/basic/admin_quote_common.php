<?php
if (!defined('_GNUBOARD_'))
    exit;

// Ensure necessary columns exist in g5_quote
$cols = [
    'qa_tax_company_name' => "varchar(100) NOT NULL DEFAULT '' AFTER `qa_client_name`"
];

foreach ($cols as $col => $def) {
    if (!sql_query("SHOW COLUMNS FROM g5_quote LIKE '$col'", false)) {
        sql_query("ALTER TABLE g5_quote ADD `$col` $def");
    }
}
?>
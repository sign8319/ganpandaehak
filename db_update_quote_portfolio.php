<?php
include_once('./_common.php');

// Add qa_portfolio_ids column to g5_quote table
$sql = " ALTER TABLE g5_quote ADD `qa_portfolio_ids` varchar(255) NOT NULL DEFAULT '' AFTER `qa_related_url` ";
$result = sql_query($sql, false);

if ($result) {
    echo "Successfully added qa_portfolio_ids column to g5_quote table.";
} else {
    echo "Column might already exist or error: " . sql_error_info();
}
?>
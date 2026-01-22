<?php
include_once('./_common.php');

echo "=== DIAGNOSTIC START ===\n";
echo "Checking board: beforeafter\n";

// 1. Check Table Exists & Row Count
$table_name = $g5['write_prefix'] . 'beforeafter';
$sql = "SELECT count(*) as cnt FROM {$table_name}";
$row = sql_fetch($sql);
echo "Total posts in {$table_name}: " . $row['cnt'] . "\n";

// 2. Fetch Latest Post
$sql = "SELECT * FROM {$table_name} ORDER BY wr_id DESC LIMIT 1";
$post = sql_fetch($sql);

if ($post) {
    echo "Latest Post ID: " . $post['wr_id'] . "\n";
    echo "Subject: " . $post['wr_subject'] . "\n";
    echo "Is Comment: " . $post['wr_is_comment'] . "\n";
    echo "Secret: " . (strstr($post['wr_option'], 'secret') ? 'YES' : 'NO') . "\n";

    // 3. Check Attached Files
    $sql_file = "SELECT * FROM {$g5['board_file_table']} WHERE bo_table = 'beforeafter' AND wr_id = '{$post['wr_id']}'";
    $result = sql_query($sql_file);
    echo "attached files count: " . sql_num_rows($result) . "\n";
    while ($file = sql_fetch_array($result)) {
        echo " - File #{$file['bf_no']}: {$file['bf_file']} (Size: {$file['bf_filesize']})\n";
    }
} else {
    echo "No posts found in table.\n";
}

echo "=== DIAGNOSTIC END ===\n";
?>
<?php
header('Content-Type: text/html; charset=utf-8');

// Test cases based on user screenshot/report
$inputs = [
    "동대문구간판 | LED ... 신이문 약국, 신뢰를 더하는 간판 #간판대학#감성간판# 예쁜간판#간판디자인",
    "<div>Some content</div>#Hashtag1 #Hashtag2",
    "Text&nbsp;#HashtagWithNBSP",
    "Text with #ConcatenatedHashtags like #One#Two",
    "Multi-line\n#Hashtag\n#Another",
    "Mixed content #해시태그1#해시태그2 text",
];

function clean_content($input)
{
    echo "Original: [" . $input . "]<br>";

    // 1. HTML Entity Decode
    $content = html_entity_decode($input);

    // 2. Strip Tags
    $content = strip_tags($content);

    // 3. Normalized spaces
    $content = str_replace(array('&nbsp;', '&amp;nbsp;'), ' ', $content);

    // 4. Regex
    // Current regex
    $cleaned = preg_replace('/#[^\s#]+/', '', $content);

    echo "Cleaned (Current): [" . $cleaned . "]<br>";

    // Improved Regex test
    $better = preg_replace('/#\S+/', '', $content);
    echo "Cleaned (Better?): [" . $better . "]<br>";

    echo "<hr>";
}

foreach ($inputs as $input) {
    clean_content($input);
}
?>
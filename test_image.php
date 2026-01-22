<?php
$image_path = __DIR__ . '/data/file/ca_portfolio/d1749e68c573832413bc927543293a4a_VR5CjGvq_e6e8e2545fe662f0762f0f1268444b3b4a54d6ae.jpg';

echo "파일 경로: " . $image_path . "<br>";
echo "파일 존재: " . (file_exists($image_path) ? '예' : '아니오') . "<br>";
echo "읽기 권한: " . (is_readable($image_path) ? '예' : '아니오') . "<br>";

if (file_exists($image_path)) {
    echo "<img src='/ganpandaehak/data/file/ca_portfolio/d1749e68c573832413bc927543293a4a_VR5CjGvq_e6e8e2545fe662f0762f0f1268444b3b4a54d6ae.jpg' style='max-width:500px;'>";
}
?>



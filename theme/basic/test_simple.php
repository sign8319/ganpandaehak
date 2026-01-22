<?php
echo "✅ PHP is working in theme/basic/ directory<br><br>";
echo "Current file: " . __FILE__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "PHP version: " . phpversion() . "<br>";
echo "<br>If you see this message, PHP execution is allowed in this directory.";
?>
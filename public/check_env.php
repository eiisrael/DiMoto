<?php
echo "<pre>";
echo "PHP test running...\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Loaded modules:\n";
print_r(apache_get_modules());
echo "\nDocument Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "\nScript filename: " . __FILE__ . "\n";
echo "</pre>";

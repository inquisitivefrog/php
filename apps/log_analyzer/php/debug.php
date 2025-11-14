<?php
header('Content-Type: text/plain');
echo "SAPI: " . php_sapi_name() . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'MISSING') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'MISSING') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'MISSING') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'MISSING') . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'MISSING') . "\n";
echo "\n";
echo "File exists: " . (file_exists($_SERVER['SCRIPT_FILENAME'] ?? '') ? 'YES' : 'NO') . "\n";

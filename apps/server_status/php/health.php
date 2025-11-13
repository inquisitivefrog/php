<?php
// php/health.php
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'OK',
    'php' => phpversion(),
    'time' => date('c')
], JSON_PRETTY_PRINT);

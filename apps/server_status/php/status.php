<?php
// php/status.php
header('Content-Type: text/html; charset=utf-8');

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>
<!DOCTYPE html>
<html><head><title>PHP Server Status</title>
<style>
  body {font-family:Arial;margin:2rem;background:#f9f9f9;}
  table {border-collapse:collapse;width:100%;}
  th,td {border:1px solid #ddd;padding:0.6rem;}
  th {background:#ecf0f1;}
</style>
</head><body>
<h1>PHP Server Status</h1>
<table>
<tr><th>Item</th><th>Value</th></tr>
<tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
<tr><td>SAPI</td><td><?= php_sapi_name() ?></td></tr>
<tr><td>Time</td><td><?= date('Y-m-d H:i:s T') ?></td></tr>
<tr><td>Memory</td><td><?= formatBytes(memory_get_peak_usage(true)) ?></td></tr>
</table>
<p><a href="/cgi/status.php">CGI Version</a> | <a href="/health">Health</a></p>
</body></html>

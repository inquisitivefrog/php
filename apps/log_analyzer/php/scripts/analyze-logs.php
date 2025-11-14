#!/usr/bin/env php
<?php
$defaultLogFile = '/var/log/nginx/access.log';
$logFile = $defaultLogFile;
$jsonOutput = false;

foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    if ($arg === '--json') {
        $jsonOutput = true;
    } elseif (file_exists($arg)) {
        $logFile = $arg;
    }
}

if ($logFile === $defaultLogFile && !file_exists($logFile)) {
    echo "Error: Default log file not found: $logFile\n";
    exit(1);
}

if (!file_exists($logFile)) {
    echo "Error: Log file not found: $logFile\n";
    exit(1);
}
if (filesize($logFile) === 0) {
    echo "Error: Log file is empty: $logFile\n";
    exit(1);
}

$stats = [
    'ips' => [],
    'status_404' => [],
    'user_agents' => [],
    'parsed_lines' => 0
];

$handle = fopen($logFile, 'r');
stream_set_timeout($handle, 5);

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '2025/')) continue;

    if (preg_match('/^(\S+).*?\[.*?\].*?"([^"]+)".*?(\d{3}).*?"([^"]+)"$/', $line, $m)) {
        $ip = $m[1];
        $request = $m[2];
        $status = $m[3];
        $userAgent = $m[4] === '-' ? 'Unknown' : $m[4];

        $stats['ips'][$ip] = ($stats['ips'][$ip] ?? 0) + 1;

        if ($status == '404') {
            $url = explode(' ', $request)[1] ?? '';
            $stats['status_404'][$url] = ($stats['status_404'][$url] ?? 0) + 1;
        }

        $stats['user_agents'][$userAgent] = ($stats['user_agents'][$userAgent] ?? 0) + 1;
        $stats['parsed_lines']++;
    }
}
fclose($handle);

function top($array, $limit = 10) {
    arsort($array);
    return array_slice($array, 0, $limit, true);
}

if ($jsonOutput) {
    $output = [
        'summary' => [
            'file' => $logFile,
            'size_bytes' => filesize($logFile),
            'parsed_lines' => $stats['parsed_lines']
        ],
        'top_ips' => top($stats['ips']),
        'top_404_urls' => top($stats['status_404']),
        'top_user_agents' => top($stats['user_agents'])
    ];
    echo json_encode($output, JSON_PRETTY_PRINT) . "\n";
    exit(0);
}

echo "Analyzing: $logFile (" . number_format(filesize($logFile)) . " bytes)\n";
echo str_repeat("=", 60) . "\n";
echo "Parsed {$stats['parsed_lines']} access log lines\n";

echo "\nTop 10 IPs:\n";
if (empty($stats['ips'])) {
    echo "  (none)\n";
} else {
    foreach (top($stats['ips']) as $ip => $count) {
        echo "  $count → $ip\n";
    }
}

echo "\nTop 404 URLs:\n";
if (empty($stats['status_404'])) {
    echo "  (none)\n";
} else {
    foreach (top($stats['status_404']) as $url => $count) {
        echo "  $count → $url\n";
    }
}

echo "\nTop User Agents:\n";
if (empty($stats['user_agents'])) {
    echo "  (none)\n";
} else {
    foreach (top($stats['user_agents']) as $agent => $count) {
        $short = substr($agent, 0, 60);
        echo "  $count → $short" . (strlen($agent) > 60 ? '...' : '') . "\n";
    }
}

echo "\nDone.\n";

<?php
$json = file_get_contents('version.json');
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON Error: ' . json_last_error_msg() . PHP_EOL;
    echo 'Raw content: ' . substr($json, 0, 100) . PHP_EOL;
} else {
    echo 'JSON is valid!' . PHP_EOL;
    echo 'Version: ' . $data['latest_version'] . PHP_EOL;
    print_r($data);
}

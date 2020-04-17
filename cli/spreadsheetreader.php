<?php

require_once __DIR__ . '/index.php';

$reader = new SpreadsheetReader($argv[1]);
$reader->ChangeSheet($argv[2] ?? 0);
while ($reader->valid()) {
    $current = $reader->current();
    $cl = [
        'sku' => $current[0],
        'count' => $current[1],
    ];
    echo $reader->key(), ':', $cl['sku'], PHP_EOL;
    $reader->next();
}

echo 'Finish', PHP_EOL;

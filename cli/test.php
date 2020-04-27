<?php

use app\material\Threshold;

require_once dirname(__DIR__) . '/bootstrap.php';

echo time(), PHP_EOL;
print_r((new Threshold())->current());
echo time(), PHP_EOL;
print_r((new Threshold())->current());
echo time(), PHP_EOL;

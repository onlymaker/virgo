<?php

define('ROOT', __DIR__);
define('RUNTIME', ROOT . '/runtime');

require_once ROOT . '/vendor/autoload.php';

$f3 = Base::instance();

$f3->mset([
    //system code
    'AUTOLOAD' => ROOT . '/src/',
    'UI' => ROOT . '/tpl/',
    //runtime data
    'LOGS' => RUNTIME . '/logs/',
    'TEMP' => RUNTIME . '/temp/',
    'UPLOADS' => RUNTIME . '/uploads/',
    //cache ['/path/to/cache','memcache=localhost:11211','redis=localhost:6379']
    'CACHE' => true,
    //debug ranging from 0 (stack trace suppressed) to 3 (most verbose)
    'DEBUG' => 3,
]);

$f3->config([
    ROOT . '/cfg/map.ini',
    ROOT . '/cfg/route.ini',
    RUNTIME . '/config.ini',
]);

$f3->set('LOGGER', new Log(date('Y-m-d.\l\o\g')));

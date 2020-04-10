<?php

namespace app;

use app\common\AppBase;

class Index extends AppBase
{
    function get()
    {
        echo \Template::instance()->render('index.html');
    }
}

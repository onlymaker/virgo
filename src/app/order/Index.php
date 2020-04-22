<?php

namespace app\order;

use app\common\AppBase;

class Index extends AppBase
{
    function get(\Base $f3)
    {
        echo \Template::instance()->render('order/index.html');
    }
}

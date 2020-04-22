<?php

namespace app\common;

class OrderStatus extends \Prefab
{
    const INITIAL = 0;
    const PREPARING = 1;
    const PREPARED = 2;
    const ALLOCATED = 3;
    const UPPER = 4;
    const SOLE = 5;
    const FINISH = 6;
    const CANCEL = 7;

    function get()
    {
        $class = new \ReflectionClass('app\common\OrderStatus');
        return $class->getConstants();
    }
}
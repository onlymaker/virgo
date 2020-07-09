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
    const WAITING = 8;

    function get()
    {
        $class = new \ReflectionClass('app\common\OrderStatus');
        return $class->getConstants();
    }

    function next($status)
    {
        switch ($status) {
            case self::PREPARED:
                return self::ALLOCATED;
            case self::ALLOCATED:
                return self::UPPER;
            case self::UPPER:
                return self::SOLE;
            case self::SOLE:
                return self::FINISH;
        }
        return $status;
    }

    function name()
    {
        return [
            self::INITIAL => '新建',
            self::PREPARING => '缺料',
            self::PREPARED => '材料备齐',
            self::ALLOCATED => '下料',
            self::UPPER => '面部',
            self::SOLE => '底部',
            self::FINISH => '出货',
            self::CANCEL => '取消',
            self::WAITING => '材料计算中',
        ];
    }
}
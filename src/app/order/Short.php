<?php

namespace app\order;

use app\common\Code;
use app\common\OrderStatus;
use db\Mysql;
use db\SqlMapper;

class Short extends Index
{
    function get(\Base $f3)
    {
        $number = $f3->get('GET.number');
        $order = new SqlMapper('virgo_order');
        $order->load(['order_number=?', $number], ['limit' => 1]);
        if($order->dry()) {
            $f3->error(404, "Order not found $number");
        } else {
            $f3->set('order', $order->cast());
            $f3->set('fields', Code::PRODUCT_MATERIAL);
            $f3->set('short', json_decode($order['short'], true));
            echo \Template::instance()->render('order/short.html');
        }
    }
}

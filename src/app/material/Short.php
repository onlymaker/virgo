<?php

namespace app\material;

use app\common\Code;
use app\common\OrderStatus;
use db\SqlMapper;

class Short extends Index
{
    function get(\Base $f3)
    {
        $data = [];
        $order = new SqlMapper('virgo_order');
        $material = new SqlMapper('virgo_material');
        if ($f3->get('REQUEST.number') ?? false) {
            $numbers = implode("','", explode(',', $f3->get('REQUEST.number')));
            $filter = ['status=' . OrderStatus::PREPARING . " and order_number in ('$numbers')"];
        } else {
            $filter = ['status=' . OrderStatus::PREPARING];
        }
        $order->load($filter);
        while (!$order->dry()) {
            $short = json_decode($order['short'], true);
            foreach ($short as $key => $line) {
                if ($data[$key] && $data[$key]['id']) {
                    $data[$key]['quantity'] += $line['quantity'];
                } else {
                    $material->load(['id=?', $line['id']]);
                    if ($material->dry()) {
                        if ($data[$key]) {
                            $data[$key]['quantity'] += $line['quantity'];
                        } else {
                            $data[$key] = $line;
                        }
                    } else {
                        if ($data[$key]) {
                            $line['quantity'] += $data[$key]['quantity'];
                        }
                        $data[$key] = array_merge($material->cast(), $line);
                    }
                }
            }
            $order->next();
        }
        $f3->set('keys', Code::PRODUCT_MATERIAL);
        $f3->set('data', $data);
        echo \Template::instance()->render('material/short.html');
    }

    function post(\Base $f3)
    {
        $this->get($f3);
    }
}

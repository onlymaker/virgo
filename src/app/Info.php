<?php

namespace app;

use db\Mysql;
use db\SqlMapper;

class Info
{
    function qrcode(\Base $f3)
    {
        $code = $f3->get('REQUEST.code');
        $db = Mysql::instance()->get();
        list($count) = $db->exec('select sku,sum(quantity) quantity from virgo_order where order_number=? group by sku', [$code]);
        if ($count['quantity']) {
            $product = new SqlMapper('virgo_product');
            $product->load(['sku=?', $count['sku']]);
            if ($product->dry()) {
                $f3->error(404, "找不到产品 {$count['sku']}");
            } else {
                $f3->set('info', [
                    'order' => $code,
                    'sku' => $count['sku'],
                    'quantity' => $count['quantity'],
                    'images' => explode(',', $product['images'] ?: []),
                    'upper' => $product['process_1'],
                    'sole' => $product['process_2'],
                    'last' => $product['last'],
                ]);
                echo \Template::instance()->render('info.html');
            }
        } else {
            $f3->error(404, "找不到订单 $code");
        }
    }
}

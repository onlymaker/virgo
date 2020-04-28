<?php

namespace app;

use db\Mysql;
use db\SqlMapper;

class Info
{
    function background(\Base $f3)
    {
        $dir = RUNTIME . '/bg/';
        if (is_dir($dir)) {
            $files = glob($dir . '/*[0-9].log');
            \Web::instance()->send(end($files));
        } else {
            $f3->error(404, 'No background dir');
        }
    }

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

    function summary(\Base $f3)
    {
        $number = $f3->get('REQUEST.number');
        $db = Mysql::instance()->get();
        list($summary) = $db->exec('select sku,image,sum(quantity) quantity from virgo_order where order_number=? group by sku,image', [$number]);
        echo json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

<?php

namespace app;

use db\Mysql;

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
        $query = $db->exec('select o.sku,o.size,o.quantity,p.images,p.process_1,p.process_2 from virgo_order o,virgo_product p where o.order_number=? and o.sku=p.sku and o.size=p.size order by size', [$code]);
        if ($query) {
            $count = 0;
            $data = [];
            foreach ($query as $line) {
                $count += $line['quantity'];
                $data[] = [
                    'size' => $line['size'],
                    'quantity' => $line['quantity'],
                    'process_1' => $line['process_1'],
                    'process_2' => $line['process_2'],
                ];
            }
            $f3->set('order', $code);
            $f3->set('sku', $query[0]['sku']);
            $f3->set('count', $count);
            $f3->set('data', $data);
            $f3->set('images', explode(',', $query[0]['images'] ?: []));
            echo \Template::instance()->render('info.html');
        } else {
            $f3->error(404, $code);
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

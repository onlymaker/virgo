<?php

namespace app\product;

use app\common\Code;
use db\Mysql;
use db\SqlMapper;

class Update extends Index
{
    function info(\Base $f3)
    {
        $fields = Code::PRODUCT_SCHEMA;
        foreach ($fields as &$field) {
            $field = iconv('UTF-8', 'GBK', $field);
        }
        $detail = '/tmp/product_' . time() . '.csv';
        $file = fopen($detail, 'w');
        fputcsv($file, $fields);
        $sku = $f3->get('GET.sku');
        $product = new SqlMapper('virgo_product');
        $product->load(['sku=?', $sku], ['order' => 'size']);
        while (!$product->dry()) {
            $keys = array_keys($fields);
            $line = [];
            foreach ($keys as $key) {
                $line[$key] = $product[$key];
            }
            fputcsv($file, $line);
            $product->next();
        }
        fclose($file);
        \Web::instance()->send($detail);
    }

    function image(\Base $f3)
    {
        $sku = $f3->get('POST.sku');
        $prototype = new SqlMapper('prototype');
        $prototype->load(['sku=?', $sku]);
        if (!$prototype->dry() && $prototype['thumb']) {
            Mysql::instance()->get()->exec('update virgo_product set image=? where sku=?', [$prototype['thumb'], $sku]);
        }
        echo 'success';
    }

    function delete(\Base $f3)
    {
        $sku = $f3->get('POST.sku');
        $size = $f3->get('POST.size');
        $product = new SqlMapper('virgo_product');
        if ($size) {
            $product->erase(['sku=? and size=?', $sku, $size]);
        } else {
            $product->erase(['sku=?', $sku]);
        }
        echo 'success';
    }
}

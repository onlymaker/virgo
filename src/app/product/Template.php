<?php

namespace app\product;

use app\common\Code;

class Template extends Index
{
    function get(\Base $f3)
    {
        $fields = Code::PRODUCT_SCHEMA;
        foreach ($fields as &$field) {
            $field = iconv('UTF-8', 'GBK', $field);
        }
        $template = '/tmp/product.csv';
        $file = fopen($template, 'w');
        fputcsv($file, $fields);
        fclose($file);
        \Web::instance()->send($template);
    }
}

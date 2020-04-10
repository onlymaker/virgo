<?php

namespace app\product;

use app\common\Code;
use db\Mysql;
use db\SqlMapper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Upload extends Index
{
    const SIZE_LIMIT = 1024 * 1024 * 1;

    function get(\Base $f3)
    {
        $f3->set('refer', $_SERVER['HTTP_REFERER'] ?? '');
        echo \Template::instance()->render("product/upload.html");
    }

    function post()
    {
        if ($_FILES['upload']['size'] > self::SIZE_LIMIT) {
            die('File is too large');
        }
        try {
            $sheet = IOFactory::load($_FILES['upload']['tmp_name'])->getSheet(0);
            $data = $sheet->toArray();
            $head = array_shift($data);
            $name = array_keys(Code::PRODUCT_SCHEMA);
            if (count($head) === count($name)) {
                $sku = [];
                $product = new SqlMapper('virgo_product');
                foreach ($data as $item) {
                    $parse = [];
                    foreach ($name as $k => $v) {
                        if ($v == 'size') {
                            $parse[$v] = $item[$k];
                        } else {
                            $parse[$v] = $item[$k];
                        }
                    }
                    $product->load(['sku=? and size=?', $parse['sku'], $parse['size']]);
                    $product->copyfrom($parse);
                    $product->save();
                    if (!$product['image']) {
                        $sku[] = $parse['sku'];
                    }
                }
                $sku = array_unique($sku);
                if ($sku) {
                    $filter = implode("','", $sku);
                    Mysql::instance()->get()->exec(<<<SQL
update virgo_product v inner join (select sku,thumb from prototype) p on v.sku in ('$filter') and v.sku=p.sku set v.image=p.thumb
SQL
                    );
                }
                echo 'success';
            } else {
                die('File format is invalid');
            }
        } catch (\Exception $e) {
            echo $e->getTraceAsString();
        }
    }
}

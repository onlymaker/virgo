<?php

namespace app\product;

use app\common\Code;
use app\common\ShoeSize;
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
            $shoeSize = ShoeSize::instance();
            $sheet = IOFactory::load($_FILES['upload']['tmp_name'])->getSheet(0);
            $column = $sheet->getHighestColumn(1);
            $name = array_keys(Code::PRODUCT_SCHEMA);
            if ((ord($column) - ord('A') + 1) === count($name)) {
                $sku = [];
                $product = new SqlMapper('virgo_product');
                $i = $sheet->getRowIterator(2);
                while ($i->valid() && $sheet->getCell('A' . $i->key())) {
                    $parse = [];
                    foreach ($name as $k => $v) {
                        $cell = chr(ord('A') + $k) . $i->key();
                        $value = trim($sheet->getCell($cell)->getFormattedValue());
                        if ($v == 'size') {
                            $euSize = $shoeSize->convert($value, ShoeSize::EU);
                            if (in_array($euSize, $shoeSize->available()['EU'])) {
                                $parse[$v] = $euSize;
                            } else {
                                die($cell . ' Unknown size: ' . $value);
                            }
                        } else {
                            $parse[$v] = $value;
                        }
                    }
                    $product->load(['sku=? and size=?', $parse['sku'], $parse['size']]);
                    $product->copyfrom($parse);
                    $product->save();
                    if (!$product['image']) {
                        $sku[] = $parse['sku'];
                    }
                    $i->next();
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

<?php

namespace app\material;

use app\common\Code;
use db\SqlMapper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Upload extends Index
{
    const SIZE_LIMIT = 1024 * 1024 * 1;

    function get(\Base $f3)
    {
        $f3->set('refer', $_SERVER['HTTP_REFERER'] ?? '');
        echo \Template::instance()->render("material/upload.html");
    }

    function post()
    {
        if ($_FILES['upload']['size'] > self::SIZE_LIMIT) {
            die('File is too large');
        }
        try {
            $sheet = IOFactory::load($_FILES['upload']['tmp_name'])->getSheet(0);
            $column = $sheet->getHighestColumn(1);
            $name = array_keys(Code::MATERIAL_SCHEMA);
            if ((ord($column) - ord('A') + 1) === count($name)) {
                $event = new Event();
                $material = new SqlMapper('virgo_material');
                $i = $sheet->getRowIterator(2);
                while ($i->valid() && $sheet->getCell('A' . $i->key())) {
                    $parse = [];
                    foreach ($name as $k => $v) {
                        $parse[$v] = $sheet->getCell(chr(ord('A') + $k) . $i->key())->getFormattedValue();
                    }
                    $parse['price'] = intval(floatval($parse['price']) * 100);
                    $material->load(['name=? and type=?', $parse['name'], $parse['type']]);
                    if ($material->dry()) {
                        $material->copyfrom($parse);
                        $material->save();
                        $event->upload($material->cast());
                    } else {
                        foreach ($parse as $k => $v) {
                            if ($material[$k] != $v) {
                                $prev = $material->cast();
                                $material->copyfrom($parse);
                                $material->save();
                                $event->update($prev, $material->cast());
                                break;
                            }
                        }
                    }
                    $i->next();
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

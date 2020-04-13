<?php

namespace app\material;

use app\common\Code;
use db\SqlMapper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Update extends Index
{
    function info(\Base $f3)
    {
        $fields = Code::MATERIAL_SCHEMA;
        $data = [$fields];
        $material = new SqlMapper('virgo_material');
        $material->load(['id in (' . $f3->get('POST.id') . ')']);
        while (!$material->dry()) {
            $line = [];
            $keys = array_keys($fields);
            foreach ($keys as $key) {
                $line[] = $material[$key];
            }
            $data[] = $line;
            $material->next();
        }
        $detail = '/tmp/material_' . time() . '.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);
        $sheet->fromArray($data);
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($detail);
        \Web::instance()->send($detail);
    }

    function delete(\Base $f3)
    {
        $material = new SqlMapper('virgo_material');
        $material->load(['id in (' . $f3->get('POST.id') . ')']);
        while (!$material->dry()) {
            $previous = $material->cast();
            $material->erase();
            $event = new Event();
            $event->delete($previous);
            $material->next();
        }
        echo 'success';
    }
}

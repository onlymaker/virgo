<?php

namespace app\product;

use app\common\Code;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Template extends Index
{
    function get(\Base $f3)
    {
        $template = '/tmp/product.xlsx';
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);
        $sheet->fromArray([Code::PRODUCT_SCHEMA]);
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($template);
        \Web::instance()->send($template);
    }
}

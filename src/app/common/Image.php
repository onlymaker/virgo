<?php

namespace app\common;

use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Picqer\Barcode\BarcodeGeneratorJPG;

class Image extends \Prefab
{
    function qrcode($order)
    {
        $dir = ROOT . '/html/qrcode/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $image = $dir . $sku . '.png';
        $code = new QrCode('https://oms.onlymaker.cn/model/ImageView?sku=' . $sku);
        $code->setWriterByName('png');
        $code->setMargin(5);
        $code->setEncoding('UTF-8');
        $code->setSize(100);
        $code->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $code->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $code->setLabel($sku, 10, ROOT . '/html/asset/font/SourceHanSansSC-Light.otf', LabelAlignment::CENTER());
        //$code->setLogoPath(ROOT . '/html/asset/img/onlymaker.png');
        //$code->setLogoSize(100, 30);
        $code->setRoundBlockSize(true);
        $code->setValidateResult(false);
        $code->writeFile($image);
    }

    function barcode($order)
    {
        $dir = ROOT . '/html/barcode/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents(
            $dir . $order . '.jpg',
            (new BarcodeGeneratorJPG())->getBarcode(
                $order,
                BarcodeGeneratorJPG::TYPE_CODE_128_A,
                1.5,
                65
            )
        );
    }
}
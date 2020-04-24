<?php

namespace app\common;

use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Picqer\Barcode\BarcodeGeneratorJPG;

class Image extends \Prefab
{
    function qrcode($order, $rewrite = false)
    {
        $dir = ROOT . '/html/qrcode/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $domain = \Base::instance()->get('DOMAIN');
        $image = $dir . $order . '.png';
        if ($rewrite || !is_file($image)) {
            $code = new QrCode($domain . '/info/qrcode?code=' . $order);
            $code->setWriterByName('png');
            $code->setMargin(5);
            $code->setEncoding('UTF-8');
            $code->setSize(150);
            $code->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
            $code->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
            $code->setLabel($order, 12, ROOT . '/html/font/SourceHanSansSC-Light.otf', LabelAlignment::CENTER());
            $code->setRoundBlockSize(true);
            $code->setValidateResult(false);
            $code->writeFile($image);
        }
        return [
            'path' => $image,
            'url' => $domain . '/qrcode/' . $order . '.png',
        ];
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
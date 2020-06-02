<?php

namespace app\order;

use app\common\Code;
use app\common\Image;
use app\common\OrderStatus;
use db\Mysql;
use db\SqlMapper;
use helper\WebHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Alloc extends Index
{
    function get(\Base $f3)
    {
        if (isset($_GET['pageNo'])) {
            $pageNo = $_GET['pageNo'];
            unset($_GET['pageNo']);
        } else {
            $pageNo = 1;
        }
        $pageSize = 25;
        $params = [];
        $filter = [];
        foreach ($_GET as $name => $value) {
            $filter[] = "`$name`=?";
            $params[] = $value;
            $f3->set($name, $value);
        }
        if ($filter) {
            $where = 'where' . implode(' and ', $filter);
        } else {
            $where = 'where status=' . OrderStatus::ALLOCATED;
        }
        $db = Mysql::instance()->get();
        $db->exec("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        list($count) = $db->exec("select count(distinct order_number) total from virgo_order $where");
        $offset = ($pageNo - 1) * $pageSize;
        $data = $db->exec("select sku,image,order_number,order_type,status,sum(quantity) quantity from virgo_order $where group by order_number order by sku,id desc limit $pageSize offset $offset", $params) ?: [];
        $helper = Image::instance();
        foreach ($data as &$value) {
            $value['barcode'] = $helper->barcode($value['order_number'])['url'];
            $value['qrcode'] = $helper->qrcode($value['order_number'])['url'];
        }
        $f3->set('pageNo', $pageNo);
        $f3->set('pageCount', ceil($count['total'] / $pageSize));
        $f3->set('data', $data);
        $f3->set('statusName', OrderStatus::instance()->name());
        echo \Template::instance()->render('order/alloc.html');
    }

    function post(\Base $f3)
    {
        $action = $f3->get('POST.action');
        $number = explode(',', $f3->get('POST.number'));
        $this->{$action}($number);
    }

    function export($number)
    {
        //image,order_number,template,quantity,materials
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);
        $row = 1;
        $sheet->setCellValue("A$row", '图片')
            ->setCellValue("B$row", '订单')
            ->setCellValue("C$row", '样板')
            ->setCellValue("D$row", '数量');
        $column = 'E';
        foreach (Code::ALLOC_MATERIAL as $field) {
            $sheet->setCellValue("$column$row", $field);
            $column = chr(ord($column) + 1);
            $sheet->setCellValue("$column$row", '位置');
            $column = chr(ord($column) + 1);
        }
        $row++;
        $imgPadding = 5;
        $imgWidth = 100;
        $order = new SqlMapper('virgo_order');
        $product = new SqlMapper('virgo_product');
        $material = new SqlMapper('virgo_material');
        foreach ($number as $value) {
            $order->load(['order_number=?', $value], ['order' => 'size']);
            if ($order->dry()) {
                $sheet->setCellValue("B$row", $value);
                $row++;
            } else {
                $image = $order['image'];
                $drawing = new Drawing();
                $drawing->setPath(WebHelper::instance()->fetchImageThumbnail($image))
                    ->setWorksheet($sheet)
                    ->setCoordinates("A$row")
                    ->setOffsetX($imgPadding)
                    ->setOffsetY($imgPadding);
                $sheet->getRowDimension($row)->setRowHeight(($imgWidth + 2 * $imgPadding) * 0.75);
                while (!$order->dry()) {
                    $product->load(['sku=? and size=?', $order['sku'], $order['size']]);
                    if ($product->dry()) {
                        //TODO: product data missing
                    } else {
                        $sheet->setCellValue("B$row", $value)
                            ->setCellValue("C$row", $product['template'])
                            ->setCellValue("D$row", $order['quantity']);
                        $column = 'E';
                        $keys = array_keys(Code::ALLOC_MATERIAL);
                        foreach ($keys as $key) {
                            $name = $product[$key];
                            $sheet->setCellValue("$column$row", $name);
                            $column = chr(ord($column) + 1);
                            if ($name) {
                                $material->load(['name=?', $name]);
                                $position = $material->dry() ? '' : $material['location'];
                                $sheet->setCellValue("$column$row", $position);
                            }
                            $column = chr(ord($column) + 1);
                        }
                    }
                    $order->next();
                    $row++;
                }
            }
        };
        $sheet->getColumnDimension('A')
            ->setWidth(SharedDrawing::pixelsToCellDimension($imgWidth + 2 * $imgPadding, new Font()));
        $path = '/tmp/alloc_' . date('Ymd') . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($path);
        \Web::instance()->send($path);
    }

    function label($number)
    {
        $data = [];
        $helper = Image::instance();
        $db = Mysql::instance()->get();
        $order = new SqlMapper('virgo_order');
        foreach ($number as $value) {
            $order->load(['order_number=?', $value]);
            if (!$order->dry()) {
                if ($order['order_type']) {
                    $data[] = [
                        'order' => $order['order_number'],
                        'sku' => $order['sku'],
                        'size' => '',
                        'type' => $order['order_type'],
                        'express' => '',
                        'barcode' => $helper->barcode($value)['url'],
                        'qrcode' => $helper->qrcode($value)['url'],
                    ];
                } else {
                    $query = $db->exec('select distribution_channel from order_item i,distribution d where i.trace_id=? and i.distribution_id=d.id', [$value]);
                    if ($query) {
                        preg_match('/\((?<express>\\w+)\)/', $query[0]['distribution_channel'], $match);
                        $data[] = [
                            'order' => $order['order_number'],
                            'sku' => $order['sku'],
                            'size' => $order['size'],
                            'type' => $order['order_type'],
                            'express' => $match['express'],
                            'barcode' => $helper->barcode($value)['url'],
                            'qrcode' => $helper->qrcode($value)['url'],
                        ];
                    }
                }
            }
        }
        \Base::instance()->set('data', $data);
        echo \Template::instance()->render('order/alloc_export.html');
    }

    function next($number)
    {
        Next::instance()->move($number, OrderStatus::ALLOCATED, OrderStatus::UPPER, '订单发往面部');
        echo 'success';
    }
}

<?php

namespace app\material;

use db\SqlMapper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Order extends Index
{
    const PAGE_SIZE = 50;

    function get(\Base $f3)
    {
        $filter = [];
        $params = [];
        $serial = $f3->get('GET.serial') ?? '';
        if ($serial) {
            $filter[] = "serial rlike '^$serial'";
        }
        $filter = array_merge([implode(' and ', $filter)], $params);
        $pageNo = $f3->get('GET.pageNo') ?? 1;
        $mapper = new SqlMapper('virgo_material_order');
        $page = $mapper->paginate($pageNo - 1, self::PAGE_SIZE, $filter, ['order' => 'create_time desc']);
        $f3->set('serial', $serial);
        $f3->set('pageNo', $page['pos'] + 1);
        $f3->set('pageCount', $page['count']);
        $f3->set('data', $page['subset']);
        echo \Template::instance()->render('material/order.html');
    }

    function finish(\Base $f3)
    {
        $event = new Event();
        $material = new SqlMapper('virgo_material');
        $order = new SqlMapper('virgo_material_order');
        $order->load(['id in (' . $f3->get('POST.id') . ')']);
        while (!$order->dry()) {
            $material->load(['name=? and type=?', $order['name'], $order['type']]);
            if ($material->dry()) {
                die("{$order['name']} {$order['type']} not found");
            } else {
                $prev = $material->cast();
                $material['price'] = $order['final_price'];
                $material['quantity'] += $order['final_quantity'];
                $material['supplier'] = $order['final_supplier'];
                $material->save();
                $current = $material->cast();
                $order['status'] = 1;
                $order->save();
                $event->purchase($order->cast(), $prev, $current);
                $order->next();
            }
        }
        echo 'success';
    }

    function delete(\Base $f3)
    {
        $order = new SqlMapper('virgo_material_order');
        $order->erase(['id in (' . $f3->get('POST.id') . ')']);
        echo 'success';
    }

    function export(\Base $f3)
    {
        $order = new SqlMapper('virgo_material_order');
        $order->load(['id in (' . $f3->get('POST.id') . ')'], ['']);
        $data = [[
            '供应商', '日期', '采购编号', '类别', '型号', '数量', '单价', '总价'
        ]];
        while (!$order->dry()) {
            $data[] = [
                $order['plan_supplier'],
                substr($order['create_time'], 0, 10),
                $order['serial'],
                $order['type'],
                $order['name'],
                $order['plan_quantity'],
                $order['plan_price'] / 100,
                $order['plan_price'] * $order['plan_quantity'] / 100,
            ];
            $order->next();
        }
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getSheet(0);
        $sheet->fromArray($data);
        $path = '/tmp/material_order_' . date('Ymd') . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
        \Web::instance()->send($path);
    }
}

<?php

namespace app\order;

use app\common\Code;
use app\common\Image;
use db\Mysql;
use db\SqlMapper;

class Pick extends Index
{
    function get(\Base $f3)
    {
        echo \Template::instance()->render('order/pick.html');
    }

    function post(\Base $f3)
    {
        $number = explode(',', $f3->get("POST.number"));
        $format = implode("','", $number);
        $db = Mysql::instance()->get();
        $order = $db->exec("select * from virgo_order where order_number in ('$format')");
        if ($order) {
            $type = array_unique(array_column($order, 'order_type'));
            if (count($type) > 1) {
                $f3->error(500, "不能同时选择散单和小批量单: {$f3['POST']['number']}");
            } else {
                if ($type[0]) {
                    if (count($number) > 1) {
                        $f3->error(500, "小批量单每次只处理1个: {$f3['POST']['number']}");
                    } else {
                        $this->processVolume($order);
                    }
                } else {
                    if (count($order) > 6) {
                        $f3->error(500, "散单每次不能超过6个: {$f3['POST']['number']}");
                    } else {
                        $data = array_flip($number);
                        $extract = array_flip(array_column($order, 'order_number'));
                        foreach ($data as $k => &$v) {
                            $v= $order[$extract[$k]];
                        }
                        $this->processSingle($data);
                    }
                }
            }
        } else {
            $f3->error(404, "找不到订单 {$f3['POST']['number']}");
        }
    }

    function processSingle($order)
    {
        $i = 1;
        $materials = [];
        $helper = Image::instance();
        $product = new SqlMapper('virgo_product');
        $material = new SqlMapper('virgo_material');
        foreach ($order as $k => &$v) {
            $product->load(['sku=? and size=?', $v['sku'], $v['size']]);
            if (!$product->dry()) {
                $v['template'] = $product['template'];
                $v['qrcode'] = $helper->qrcode($v['order_number'])['url'];
                foreach (Code::PICK_MATERIAL as $field => $name) {
                    if ($product[$field]) {
                        $material->load(['name=?', $product[$field]], ['limit' => 1]);
                        if ($material->dry() && $field == 'midsole') {
                            $length = strrpos($product[$field], '-');
                            if ($length) {
                                $material->load(['name rlike ?', '^' . substr($product[$field], 0, $length)], ['limit' => 1]);
                            }
                        }
                        if ($material->dry()) {
                            $materials[] = [
                                'i' => $i,
                                'order' => $k,
                                'name' => $name,
                                'value' => $product[$field],
                                'location' => 0,
                                'location_format' => 0
                            ];
                        } else {
                            $location = $material['location'];
                            $fragment = explode('-', $location);
                            foreach ($fragment as &$f) {
                                if (is_numeric($f)) {
                                    $f = sprintf('%04d', $f);
                                }
                            }
                            $materials[] = [
                                'i' => $i,
                                'order' => $k,
                                'name' => $name,
                                'value' => $product[$field],
                                'location' => $location,
                                'location_format' => implode('-', $fragment)
                            ];
                        }
                    }
                }
            }
            $i++;
        }
        $locations = array_column($materials, 'location_format');
        array_multisort($locations, SORT_ASC, $materials);
        \Base::instance()->set('materials', $materials);
        \Base::instance()->set('orders', $order);
        echo \Template::instance()->render('order/pick_single.html');
    }

    function processVolume($order)
    {
        $box = [
            'fabric_1' => '1',
            'fabric_2' => '1',
            'fabric_3' => '1',
            'lining_1' => '1',
            'lining_2' => '1',
            'lining_3' => '1',
            'platform' => '5',
            'heel' => '4',
            'surround' => '4',
            'midsole' => '2',
            'outsole' => '3',
            'insole' => '2',
            'lace' => '6',
            'accessory_1' => '6',
            'accessory_2' => '6',
        ];
        $i = 0;
        $matched = [];
        $materials = [];
        $helper = Image::instance();
        $product = new SqlMapper('virgo_product');
        $material = new SqlMapper('virgo_material');
        foreach ($order as &$line) {
            $sku = $line['sku'];
            $size = $line['size'];
            $quantity = $line['quantity'];
            $product->load(['sku=? and size=?', $sku, $size]);
            if (!$product->dry()) {
                $line['template'] = $product['template'];
                $line['qrcode'] = $helper->qrcode($line['order_number'])['url'];
                foreach (Code::PICK_MATERIAL as $field => $name) {
                    if ($product[$field]) {
                        if ($match = $matched[$product[$field]]) {
                            $materials[$match]['quantity'] += $quantity;
                        } else {
                            $material->load(['name=?', $product[$field]], ['limit' => 1]);
                            if ($material->dry() && $field == 'midsole') {
                                $length = strrpos($product[$field], '-');
                                if ($length) {
                                    $material->load(['name rlike ?', '^' . substr($product[$field], 0, $length)], ['limit' => 1]);
                                }
                            }
                            if ($material->dry()) {
                                $materials[$i] = [
                                    'i' => $box[$field],
                                    'order' => $order[0]['order_number'],
                                    'name' => $name,
                                    'value' => $product[$field],
                                    'quantity' => $quantity,
                                    'location' => 0,
                                    'location_format' => 0
                                ];
                            } else {
                                $location = $material['location'];
                                $fragment = explode('-', $location);
                                foreach ($fragment as &$f) {
                                    if (is_numeric($f)) {
                                        $f = sprintf('%04d', $f);
                                    }
                                }
                                $materials[$i] = [
                                    'i' => $box[$field],
                                    'order' => $order[0]['order_number'],
                                    'name' => $name,
                                    'value' => $product[$field],
                                    'location' => $location,
                                    'location_format' => implode('-', $fragment)
                                ];
                            }
                            $matched[$product[$field]] = $i;
                            $i++;
                        }
                    }
                }
            }
        }
        $locations = array_column($materials, 'location_format');
        array_multisort($locations, SORT_ASC, $materials);
        \Base::instance()->set('materials', $materials);
        \Base::instance()->set('orders', $order);
        echo \Template::instance()->render('order/pick_volume.html');
    }
}

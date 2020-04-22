<?php

namespace app\order;

use app\common\OrderStatus;
use app\common\ShoeSize;
use db\Mysql;
use db\SqlMapper;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Create extends Index
{
    function get(\Base $f3)
    {
        echo \Template::instance()->render('order/create.html');
    }

    function post(\Base $f3)
    {
        $failure = [];
        $success = [];
        if ($f3->get('POST.order_number')) {
            $orders = [$f3->get('POST.order_number')];
        } else {
            $orders = [];
            $file = $_FILES['upload']['tmp_name'];
            if (is_file($file)) {
                $sheet = IOFactory::load($file)->getSheet(0);
                $i = $sheet->getRowIterator();
                while ($i->valid() && $orderNumber = $sheet->getCell('A' . $i->key())) {
                    $orders[] = trim($orderNumber);
                    $i->next();
                }
            }
        }
        if ($orders) {
            $shoeSize = new ShoeSize();
            $db = Mysql::instance()->get();
            $volume = new SqlMapper('volume_order');
            $product = new SqlMapper('virgo_product');
            foreach ($orders as $order) {
                $query = $db->exec('select o.trace_id,o.size,o.channel,p.sku from order_item o,prototype p where o.trace_id=? and o.prototype_id=p.id', [$order]);
                if ($query) {
                    $sku = $query[0]['sku'];
                    $size = $shoeSize->convert($query[0]['size'], ShoeSize::EU);
                    $product->load(['sku=? and size=?', $sku, $size]);
                    if ($product->dry()) {
                        $failure[] = "$order: 找不到产品信息 $sku $size";
                    } else {
                        $this->create($query[0], $product->cast());
                        $success[] = "$order: 导入成功 $sku $size 1";
                    }
                } else {
                    $volume->load(['volume_serial=?', $order]);
                    if ($volume->dry()) {
                        $failure[] = "$order: 找不到订单";
                    } else {
                        while (!$volume->dry()) {
                            $sku = $volume['sku'];
                            $size = $volume['eu_size'];
                            $product->load(['sku=? and size=?', $sku, $size]);
                            if ($product->dry()) {
                                $failure[] = "$order: 找不到产品信息 $sku $size";
                            } else {
                                $this->create($volume->cast(), $product->cast());
                                $success[] = "$order: 导入成功 $sku $size {$volume['quantity']}";
                            }
                            $volume->next();
                        }
                    }
                }
            }
        } else {
            $failure[] = "未填写订单号";
        }
        $f3->set('failure', $failure);
        $f3->set('success', $success);
        echo \Template::instance()->render('order/create_done.html');
    }

    function create($order, $product)
    {
        unset($order['id']);
        unset($product['id']);
        if ($order['trace_id'] ?? false) {
            $data = [
                'quantity' => 1,
                'order_type' => 0,
                'order_sponsor' => '',
                'order_channel' => $order['channel'],
                'order_number' => $order['trace_id'],
                'status' => OrderStatus::INITIAL,
            ];
        } else {
            $data = [
                'quantity' => $order['quantity'],
                'order_type' => 1,
                'order_sponsor' => $order['sponsor'],
                'order_channel' => $order['market'],
                'order_number' => $order['volume_serial'],
                'status' => OrderStatus::INITIAL,
            ];
        }
        $data = array_merge($data, $product);
        $mapper = new SqlMapper('virgo_order');
        $history = new SqlMapper('virgo_order_history');
        $mapper->load(['order_number=? and sku=? and size=?', $data['order_number'], $data['sku'], $data['size']]);
        if ($mapper->dry()) {
            $history['description'] = '创建订单';
        } else {
            $history['description'] = '重复提交订单';
        }
        $mapper->copyfrom($data);
        $mapper->save();
        $cast = $mapper->cast();
        unset($cast['id']);
        unset($cast['create_time']);
        $history->copyfrom($cast);
        $history->save();
    }
}

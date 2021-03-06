<?php

use app\common\Code;
use app\common\OrderStatus;
use app\common\OrderType;
use app\material\Event;
use app\order\Next;
use db\Mysql;
use db\SqlMapper;

require_once __DIR__ . '/index.php';

$f3 = Base::instance();
$f3->set('LOGS', RUNTIME . '/bg/');
$f3->set('BG_LOG', new Log(date('Y-m-d.\l\o\g')));

logging('Starting background task ...');

while (true) {
    try {
        $db = Mysql::instance()->get();
        $sniff = $db->exec('select * from virgo_order where status=' . OrderStatus::WAITING . ' limit 1');
        if ($sniff) {
            $type = $sniff[0]['order_type'];
            $orderNumber = $sniff[0]['order_number'];
            if ($type) {
                $orders = $db->exec('select * from virgo_order where order_number=?', [$orderNumber]);
            } else {
                $orders = [$sniff[0]];
            }
            $usage = calcMaterial($orders);
            $db->begin();
            /**
             * short:
             *  - id: 材料id
             *  - key: 材料类别
             *  - value: 材料名称
             *  - message
             *  - stock: 库存数量
             *  - quantity: 订单需求
             */
            $short = [];
            $material = new SqlMapper('virgo_material');
            foreach ($usage as $key => $line) {
                if ($line['quantity']) {
                    $material->load(['name=?', $line['value']], ['limit' => 1]);
                    if ($material->dry() || $material['quantity'] == 0) {
                            if ($type == OrderType::SINGLE && $line['key'] == 'midsole') {
                            $length = strrpos($line['value'], '-');
                            if ($length) {
                                $line['value'] = substr($line['value'], 0, $length);
                                $material->load(['name=?', $line['value']], ['limit' => 1]);
                            }
                        }
                    }
                    if ($material->dry()) {
                        logging("material not found: {$orderNumber}, {$line['key']}, {$line['value']}");
                        $short[$key] = array_merge($line, [
                            'id' => 0,
                            'message' => 'material not found',
                            'stock' => 0,
                        ]);
                    } else {
                        if ($material['quantity'] < $line['quantity']) {
                            logging("material not enough: {$orderNumber}, {$line['key']}, {$line['value']}, required: {$line['quantity']}, contains: {$material['quantity']}");
                            $short[$key] = array_merge($line, [
                                'id' => $material['id'],
                                'message' => 'material not enough',
                                'stock' => $material['quantity'],
                            ]);
                        } else {
                            (Event::instance())->usage($material->cast(), $orderNumber, $line['quantity']);
                            $material['quantity'] -= $line['quantity'];
                            $material->save();
                        }
                    }
                } else {
                    $short[$key] = array_merge($line, [
                        'id' => 0,
                        'message' => 'product not found',
                        'stock' => 0,
                    ]);
                }
            }
            if ($short) {
                $db->rollback();
                $db->exec('update virgo_order set short=? where order_number=?', [json_encode($short, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $orderNumber]);
                Next::instance()->move($orderNumber, OrderStatus::WAITING, OrderStatus::PREPARING, '材料不齐');
                logging("order material not ready: $orderNumber");
            } else {
                $db->commit();
                Next::instance()->move($orderNumber, OrderStatus::WAITING, OrderStatus::PREPARED, '材料齐备');
                logging("order material ready: $orderNumber");
            }
        }
        sleep(60);
    } catch (Exception $e) {
        logging($e->getTraceAsString());
    }
}

function calcMaterial(array $orders)
{
    $usage = [];
    $db = Mysql::instance()->get();
    $fields = implode(',', array_flip(Code::PRODUCT_MATERIAL));
    foreach ($orders as $order) {
        $query = $db->exec("select $fields from virgo_product where sku=? and size=? limit 1", [$order['sku'], $order['size']]);
        if ($query) {
            $product = $query[0];
            foreach ($product as $key => $value) {
                if ($value) {
                    $hash = md5($value);
                    if ($usage[$hash] ?? false) {
                        $usage[$hash]['quantity'] += $order['quantity'];
                    } else {
                        $usage[$hash] = [
                            'key' => $key,
                            'value' => $value,
                            'quantity' => $order['quantity'],
                        ];
                    }
                }
            }
        } else {
            $hash = $order['sku'] . '-' . $order['size'];
            $usage[md5($hash)] = [
                'key' => $hash,
                'value' => 'Product Not Found',
                'quantity' => 0,
            ];
        }
    }
    return $usage;
}

function logging($message)
{
    global $f3;
    echo $message, "\n";
    $f3->get('BG_LOG')->write($message);
}

logging('Exit background task');

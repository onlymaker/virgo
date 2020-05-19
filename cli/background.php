<?php

use app\common\Code;
use app\common\OrderStatus;
use app\material\Event;
use app\material\Threshold;
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
        $queryOrder = $db->exec('select * from virgo_order where status=' . OrderStatus::WAITING . ' limit 1');
        if ($queryOrder) {
            $ready = false;
            $order = $queryOrder[0];
            $number = $order['order_number'];
            $type = $order['order_type'];
            $sku = $order['sku'];
            $fields = implode(',', array_flip(Code::PRODUCT_MATERIAL));
            logging("\norder material check: $number, type: $type");
            $db->begin();
            if ($type) {
                $queryVolume = $db->exec("select $fields from virgo_product where sku='$sku' order by size");
                if ($queryVolume) {
                    $material = new SqlMapper('virgo_material');
                    $history = Event::instance();
                    foreach ($queryVolume as $product) {
                        if (!materialUsage($order, $product)) {
                            goto READY;
                        }
                    }
                    $ready = true;
                }
            } else {
                $queryProduct = $db->exec("select $fields from virgo_product where sku='$sku' limit 1");
                if ($queryProduct) {
                    $product = $queryProduct[0];
                    $ready = materialUsage($order, $product);
                }
            }
            READY:
            if ($ready) {
                $db->commit();
                Next::instance()->move($number, OrderStatus::WAITING, OrderStatus::PREPARED, '材料齐备');
                logging("order material ready: $number, type: $type");
            } else {
                $db->rollback();
                Next::instance()->move($number, OrderStatus::WAITING, OrderStatus::PREPARING, '材料不齐全');
                logging("order material not ready: $number, type: $type");
            }
        }
        sleep(60);
    } catch (Exception $e) {
        logging($e->getTraceAsString());
    }
}

function materialUsage(array $order, array $product)
{
    $history = Event::instance();
    $material = new SqlMapper('virgo_material');
    $threshold = (new Threshold())->current();
    foreach ($product as $field => $value) {
        if ($value) {
            $material->load(['name=?', $value]);
            if ($material->dry() && $field == 'midsole') {
                $length = strrpos($value, '-');
                if ($length) {
                    $material->load(['name rlike ?', '^' . substr($value, 0, $length)], ['limit' => 1]);
                }
            }
            if ($material->dry()) {
                logging("order material not found: {$order['order_number']}, $field, $value");
                return false;
            } else {
                if ($material['quantity'] > $threshold[$field]) {
                    $history->usage($material->cast(), $order['order_number'], $order['quantity']);
                    $material['quantity'] -= $order['quantity'];
                    $material->save();
                } else {
                    logging("order material not enough: {$order['order_number']}, $field, $value");
                    return false;
                }
            }
        }
    }
    return true;
}

function logging($message)
{
    global $f3;
    echo $message, "\n";
    $f3->get('BG_LOG')->write($message);
}

logging('Exit background task');

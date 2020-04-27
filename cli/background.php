<?php

use app\common\Code;
use app\common\OrderStatus;
use app\material\Event;
use app\order\Next;
use db\Mysql;
use db\SqlMapper;

require_once __DIR__ . '/index.php';

$f3 = Base::instance();
$logger = $f3->get('LOGGER');

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
            $logger->write("\norder material check: $number, type: $type");
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
                $logger->write("order material ready: $number, type: $type");
            } else {
                $db->rollback();
                Next::instance()->move($number, OrderStatus::WAITING, OrderStatus::PREPARING, '材料不齐全');
                $logger->write("order material not ready: $number, type: $type");
            }
        }
        sleep(60);
    } catch (Exception $e) {
        $logger->write($e->getTraceAsString());
    }
}

function materialUsage(array $order, array $product)
{
    $logger = Base::instance()->get('LOGGER');
    $history = Event::instance();
    $material = new SqlMapper('virgo_material');
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
                $logger->write("order material not found: {$order['order_number']}, $field, $value");
                return false;
            } else {
                //TODO: check the real threshold
                if ($material['quantity'] > $order['quantity']) {
                    $history->usage($material->cast(), $order['order_number'], $order['quantity']);
                    $material['quantity'] -= $order['quantity'];
                    $material->save();
                } else {
                    $logger->write("order material not enough: {$order['order_number']}, $field, $value");
                    return false;
                }
            }
        }
    }
    return true;
}

$logger->write('Schedule task ends');

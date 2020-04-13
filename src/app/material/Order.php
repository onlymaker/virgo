<?php

namespace app\material;

use db\SqlMapper;

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
            $event->purchase($material->cast(), $order->cast());
            $order['status'] = 1;
            $order->save();
            $order->next();
        }
        echo 'success';
    }

    function delete(\Base $f3)
    {
        $order = new SqlMapper('virgo_material_order');
        $order->erase(['id in (' . $f3->get('POST.id') . ')']);
        echo 'success';
    }
}

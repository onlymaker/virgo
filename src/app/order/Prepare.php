<?php

namespace app\order;

use app\common\OrderStatus;
use db\Mysql;
use db\SqlMapper;

class Prepare extends Index
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
            $where = 'where status in (' . implode(',', [OrderStatus::INITIAL, OrderStatus::PREPARING, OrderStatus::PREPARED, OrderStatus::WAITING]) . ')';
        }
        $db = Mysql::instance()->get();
        $mapper = new SqlMapper('virgo_order');
        $db->exec("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        list($count) = $db->exec("select count(distinct order_number) total from virgo_order $where", $params);
        $offset = ($pageNo - 1) * $pageSize;
        $data = $db->exec("select sku,image,order_number,order_type,status,sum(quantity) quantity from virgo_order $where group by order_number order by id desc limit $pageSize offset $offset", $params) ?: [];
        foreach ($data as &$value) {
            $mapper->load(['order_number=?', $value['order_number']], ['order' => 'sku,size']);
            $value['create_time'] = substr($mapper['create_time'], 0, 10);
            $value['volume'] = [];
            while (!$mapper->dry()) {
                $value['volume'][] = [
                    'size' => $mapper['size'],
                    'quantity' => $mapper['quantity'],
                ];
                $mapper->next();
            }
        }
        $f3->set('pageNo', $pageNo);
        $f3->set('pageCount', ceil($count['total'] / $pageSize));
        $f3->set('data', $data);
        $f3->set('statusName', OrderStatus::instance()->name());
        echo \Template::instance()->render('order/prepare.html');
    }

    function post(\Base $f3)
    {
        $action = $f3->get('POST.action');
        $number = explode(',', $f3->get('POST.number'));
        $this->{$action}($number);
    }

    function check($number)
    {
        Next::instance()->move($number, OrderStatus::INITIAL, OrderStatus::WAITING, '订单材料核算');
        echo 'success';
    }

    function next($number)
    {
        Next::instance()->move($number, OrderStatus::PREPARED, OrderStatus::ALLOCATED, '订单发往下料');
        echo 'success';
    }
}

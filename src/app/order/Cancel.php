<?php

namespace app\order;

use app\common\OrderStatus;
use db\Mysql;

class Cancel extends Index
{
    function get(\Base $f3)
    {
        if (isset($_GET['pageNo'])) {
            $pageNo = $_GET['pageNo'];
            unset($_GET['pageNo']);
        } else {
            $pageNo = 1;
        }
        $pageSize = 100;
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
            $where = 'where status=' . OrderStatus::CANCEL;
        }
        $db = Mysql::instance()->get();
        $db->exec("set sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        list($count) = $db->exec("select count(distinct order_number) total from virgo_order $where");
        $offset = ($pageNo - 1) * $pageSize;
        $data = $db->exec("select sku,image,order_number,order_type,status,create_time,update_time,sum(quantity) quantity from virgo_order $where group by order_number order by id desc limit $pageSize offset $offset", $params) ?: [];
        foreach ($data as &$value) {
            $value['create_time'] = substr($value['create_time'], 0, 10);
            $value['update_time'] = substr($value['update_time'], 0, 10);
        }
        $f3->set('pageNo', $pageNo);
        $f3->set('pageCount', ceil($count['total'] / $pageSize));
        $f3->set('data', $data);
        $f3->set('statusName', OrderStatus::instance()->name());
        echo \Template::instance()->render('order/cancel.html');
    }

    function post(\Base $f3)
    {
        $number = $f3->get('POST.number');
        $db = Mysql::instance()->get();
        $count = $db->exec('select id from virgo_order where order_number=? limit 1', [$number]);
        if ($count) {
            $db->exec([
                'insert into virgo_order_history (sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,status,description) select sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,?,? from virgo_order where order_number=?',
                'update virgo_order set status=? where order_number=?'
            ], [
                [OrderStatus::CANCEL, '订单被取消', $number],
                [OrderStatus::CANCEL, $number]
            ]);
            echo 'success';
        } else {
            echo "找不到订单 $number";
        }
    }
}

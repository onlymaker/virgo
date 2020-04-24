<?php

namespace app\order;

use app\common\Image;
use app\common\OrderStatus;
use db\Mysql;

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
        $data = $db->exec("select sku,image,order_number,order_type,status,sum(quantity) quantity from virgo_order $where group by order_number order by id desc limit $pageSize offset $offset", $params) ?: [];
        $helper = Image::instance();
        foreach ($data as &$value) {
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

    function barcode($number)
    {
        echo 'barcode';
        print_r($number);
    }

    function export($number)
    {
        echo 'export';
        print_r($number);
    }

    function next($number)
    {
        Next::instance()->move($number, OrderStatus::ALLOCATED, OrderStatus::UPPER, '订单发往面部');
        echo 'success';
    }
}

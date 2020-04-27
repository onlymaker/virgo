<?php

namespace app\order;

use db\Mysql;

class Next extends \Prefab
{
    function move($number, $current, $next, $message)
    {
        $format = is_array($number) ? implode("','", $number) : $number;
        $db = Mysql::instance()->get();
        $db->begin();
        $db->exec([
            <<<SQL
insert into virgo_order_history (sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,status,description)
select sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,?,? from virgo_order where status=? and order_number in ('$format')
SQL,
            <<<SQL
update virgo_order set status=? where status=? and order_number in ('$format')
SQL,
        ], [
            [$next, $message, $current],
            [$next, $current]
        ]);
        $db->commit();
    }
}

<?php

namespace app\order;

use app\common\OrderStatus;
use db\Mysql;

class Scan extends Index
{
    function get(\Base $f3)
    {
        echo \Template::instance()->render('order/scan.html');
    }

    function post(\Base $f3)
    {
        $number = explode(',', $f3->get("POST.number"));
        $db = Mysql::instance()->get();
        $numbers = implode("','", $number);
        $query = $db->exec("select distinct status from virgo_order where order_number in ('$numbers')");
        if ($query) {
            if (count($query) > 1) {
                die("订单存在多个状态，请修改后重新提交");
            }
            foreach ($query as $status) {
                $next = OrderStatus::instance()->next($status);
                if ($status != $next) {
                    Next::instance()->move($number, $status, $next, '扫码批量修改订单状态');
                }
            }
        }
        echo <<<HTML
<h1>Success</h1>
<p>即将跳回扫码页面 ...</p>
<script>
setTimeout(function() {
    location.replace(location.href)
}, 3000);
</script>
HTML;
    }
}

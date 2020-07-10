<?php

namespace app\order;

use app\common\OrderStatus;

class Scan extends Index
{
    function get(\Base $f3)
    {
        $status = $f3->get('GET.status');
        $os = OrderStatus::instance();
        $next = $os->next($status);
        if ($status != $next) {
            $name = $os->name();
            $f3->set('status', $status);
            $f3->set('next', $next);
            $f3->set('name', $name);
            echo \Template::instance()->render('order/scan.html');
        } else {
            die('Invalid order status ' . $status);
        }
    }

    function post(\Base $f3)
    {
        $number = explode(',', $f3->get("POST.number"));
        $status = $f3->get('POST.status');
        $next = $f3->get('POST.next');
        $os = OrderStatus::instance();
        $name = $os->name();
        if ($next == $os->next($status)) {
            Next::instance()->move($number, $status, $next, "扫码批量修改订单状态为: " . $name[$next]);
            echo <<<HTML
<h1>Success</h1>
<p>即将跳回扫码页面 ...</p>
<script>
setTimeout(function() {
    location.replace(location.href)
}, 3000);
</script>
HTML;
        } else {
            die('Invalid order status ' . $status);
        }
    }
}

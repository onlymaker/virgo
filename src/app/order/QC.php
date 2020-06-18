<?php

namespace app\order;

use app\common\OrderStatus;
use db\Mysql;
use db\SqlMapper;

class QC extends Index
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
        $qc = new SqlMapper('virgo_order_qc');
        $page = $qc->paginate($pageNo-1, $pageSize, null, ['order' => 'id desc']);
        $f3->set('pageNo', $page['pos'] + 1);
        $f3->set('pageCount', $page['count']);
        $f3->set('data', $page['subset']);
        echo \Template::instance()->render('order/qc.html');
    }

    function post(\Base $f3)
    {
        $number = $f3->get('POST.number');
        $rejected = intval($f3->get('POST.rejected'));
        $description = $f3->get('POST.description') ?? '';
        if ($rejected) {
            $qc = new SqlMapper('virgo_order_qc');
            $qc->load(["order_number=? and date_format(create_time, '%Y-%m-%d')=current_date", $number]);
            if ($qc->dry()) {
                $db = Mysql::instance()->get();
                $db->begin();
                $db->exec([
'insert into virgo_order_history (sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,status,description) select sku,size,image,quantity,order_type,order_sponsor,order_channel,order_number,status,? from virgo_order where order_number=? and status=?',
'insert into virgo_order_qc (sku,image,quantity,rejected,order_type,order_sponsor,order_channel,order_number,description) select sku,image,quantity,?,order_type,order_sponsor,order_channel,order_number,? from virgo_order where order_number=? and status=?',
                ], [
                    ['QC', $number, OrderStatus::SOLE],
                    [$rejected, $description, $number, OrderStatus::SOLE]
                ]);
                $db->commit();
            } else {
                $qc['rejected'] = $rejected;
                $qc['description'] = $description;
                $qc->save();
            }
        }
        $this->get($f3);
    }
}

<?php

namespace app\material;

use app\common\AppBase;
use app\common\Code;
use db\Mysql;
use db\SqlMapper;

class Alert extends AppBase
{
    const PAGE_SIZE = 50;

    function get(\Base $f3)
    {
        $tabs = Code::PRODUCT_MATERIAL;
        $tab = $f3->get('GET.tab' ?? false);
        if ($tab && in_array($tab, array_keys($tabs))) {
            $thresholds = (new Threshold())->current();
            $threshold = $thresholds[$tab];
            $pageNo = $f3->get('GET.pageNo') ?? 1;
            $mapper = new SqlMapper('virgo_material');
            $page = $mapper->paginate($pageNo - 1, self::PAGE_SIZE, ['type=? and quantity<' . intval($threshold), $tabs[$tab]], ['order' => 'length(name),name']);
            $f3->set('pageNo', $page['pos'] + 1);
            $f3->set('pageCount', $page['count']);
            $f3->set('data', $page['subset']);
            $f3->set('threshold', $threshold);
        } else {
            $f3->set('pageNo', 1);
            $f3->set('pageCount', 1);
            $f3->set('data', []);
            $f3->set('threshold', 0);
        }
        $f3->set('tabs', $tabs);
        $f3->set('tab', $tab ?: '');
        $f3->set('tabName', $tabs[$tab] ?: '');
        echo \Template::instance()->render('material/alert.html');
    }
}

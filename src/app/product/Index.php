<?php

namespace app\product;

use app\common\AppBase;
use app\common\Code;
use db\SqlMapper;

class Index extends AppBase
{
    const PAGE_SIZE = 50;

    function get(\Base $f3)
    {
        $filter = [];
        $params = [];
        $sku = $f3->get('GET.sku') ?? '';
        if ($sku) {
            $filter[] = "sku rlike '^$sku'";
        }
        $filter = array_merge([implode(' and ', $filter)], $params);
        $pageNo = $f3->get('GET.pageNo') ?? 1;
        $mapper = new SqlMapper('virgo_product');
        $page = $mapper->paginate($pageNo - 1, self::PAGE_SIZE, $filter, ['order' => 'update_date desc, sku, size']);
        $f3->set('sku', $sku);
        $f3->set('pageNo', $page['pos'] + 1);
        $f3->set('pageCount', $page['count']);
        $f3->set('data', $page['subset']);
        $f3->set('fields', Code::PRODUCT_SCHEMA);
        echo \Template::instance()->render('product/index.html');
    }
}

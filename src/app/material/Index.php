<?php

namespace app\material;

use app\common\AppBase;
use db\SqlMapper;

class Index extends AppBase
{
    const PAGE_SIZE = 50;

    function get(\Base $f3)
    {
        $filter = [];
        $params = [];
        $name = $f3->get('GET.name') ?? '';
        if ($name) {
            $filter[] = "name rlike '^$name'";
        }
        $filter = array_merge([implode(' and ', $filter)], $params);
        $pageNo = $f3->get('GET.pageNo') ?? 1;
        $mapper = new SqlMapper('virgo_material');
        $page = $mapper->paginate($pageNo - 1, self::PAGE_SIZE, $filter, ['order' => 'name']);
        $f3->set('name', $name);
        $f3->set('pageNo', $page['pos'] + 1);
        $f3->set('pageCount', $page['count']);
        $f3->set('data', $page['subset']);
        echo \Template::instance()->render('material/index.html');
    }
}

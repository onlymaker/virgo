<?php

namespace app\material;

use db\Mysql;

class Threshold extends Index
{
    const KEY = 'MATERIAL_THRESHOLD';

    function get(\Base $f3)
    {
        $db = Mysql::instance()->get();
        $data = $db->exec("select * from virgo_material_threshold");
        $f3->set('data', $data);
        echo \Template::instance()->render('material/threshold.html');
    }

    function post(\Base $f3)
    {
        $db = Mysql::instance()->get();
        $db->exec('update virgo_material_threshold set threshold=? where name=?', [$f3->get('POST.threshold'), $f3->get('POST.name')]);
        $f3->clear(self::KEY);
        $this->get($f3);
    }

    function current()
    {
        $f3 = \Base::instance();
        $threshold = $f3->get(self::KEY);
        if (!$threshold) {
            $query = Mysql::instance()->get()->exec("select * from virgo_material_threshold");
            $threshold = array_column($query, 'threshold', 'name');
            $f3->set(self::KEY, $threshold);
        }
        return $threshold;
    }
}

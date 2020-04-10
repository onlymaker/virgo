<?php

namespace app\common;

class Minify
{
    function css(\Base $f3)
    {
        $this->minify($f3, 'css');
    }

    function js(\Base $f3)
    {
        $this->minify($f3, 'js');
    }

    private function minify(\Base $f3, $type)
    {
        $f3->set('UI', ROOT . "/html/$type/");
        if (empty($_SERVER['QUERY_STRING'])) {
            $f3->error(404);
        } else {
            echo \Web::instance()->minify($_SERVER['QUERY_STRING']);
        }
    }
}

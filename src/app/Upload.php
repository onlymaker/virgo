<?php

namespace app;

use app\common\AppHelper;

class Upload extends \Web
{
    use AppHelper;

    private $fileName;

    function beforeRoute(\Base $f3)
    {
        if (!$this->auth($f3)) {
            $f3->reroute($this->url('/Login'));
        } else {
            if ($f3->get('POST.name')) {
                $this->fileName = $f3->get('POST.name');
            } else {
                $start = strpos($f3->get('URI'), '/upload/');
                if ($start !== false) {
                    $start += strlen('/upload/');
                    $end = strpos($f3->get('URI'), '?');
                    if ($end === false) {
                        $end = strlen($f3->get('URI'));
                    }
                    $this->fileName = urldecode(substr($f3->get('URI'), $start, $end));
                }
            }
        }
    }

    function get(\Base $f3)
    {
        if (empty($this->fileName)) {
            echo \Template::instance()->render("upload.html");
        } else {
            $file = $f3->get('UPLOADS') . $this->fileName;
            if (is_file($file)) {
                $this->send($f3->get('UPLOADS') . $this->fileName);
            } else {
                $f3->error(404);
            }
        }
    }

    function upload()
    {
        $receive = $this->receive(null, true, false);
        var_dump($receive);
    }
}

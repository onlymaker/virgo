<?php
/**
 * Created by IntelliJ IDEA.
 * User: jibo
 * Date: 2019-01-01
 * Time: 20:51
 */

namespace app;

use db\JigMapper;

class BasicAuth
{
    function get()
    {
        $user = new JigMapper('users');
        $auth = new \Auth($user, array('id'=>'name', 'pw'=>'password'));
        if ($auth->basic()) {
            echo 'HTTP basic authentication';
        }
    }
}

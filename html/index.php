<?php

require_once dirname(__DIR__) . '/bootstrap.php';

call_user_func([Base::instance(), 'run']);

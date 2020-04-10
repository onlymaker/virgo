<?php

namespace db;

use DB\Jig\Mapper;

class JigMapper extends Mapper
{
    function __construct($object)
    {
        $db = new Jig(RUNTIME . '/jig/',Jig::FORMAT_JSON);
        parent::__construct($db, $object);
    }
}

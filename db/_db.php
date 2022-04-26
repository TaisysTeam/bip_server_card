<?php

use Libs\sql\mySql;

$_db = new mySql(array(
    'sql_server' => $conf['DB_HOST'],
    'port' => $conf['DB_PORT'],
    'dbname' => $conf['DB_DATABASE'],
    'dbuser' => $conf['DB_USERNAME'],
    'dbpasswd' => $conf['DB_PASSWORD'],
    'charset'=> $conf['DB_CHARSET']
));

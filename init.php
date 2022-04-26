<?php
use Libs\Funcs;
use Libs\Map;
use Libs\AES;

ignore_user_abort(true);
set_time_limit(0);


$TAG = "BIP_SERVER_CARD";
$ver = "0.3";

$funcs = new Funcs();


$input = file_get_contents('php://input');
$redis = new Redis();

$Map = new Map();
$AES = new AES();

$global = (object)[];
$global -> tag = "BIP_SERVER_CARD";
$global -> ver = "0.3";
$global -> conf = $conf;
$global -> redis = $redis;
$global -> funcs = $funcs;
$global -> map = $Map;
$global -> aes = $AES;
$global -> input = $input;


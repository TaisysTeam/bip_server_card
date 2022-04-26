<?php

/**
 * 建立連接 Redis 函式
 *
 * @param $redis redis 物件
 * @return bool
 *
 * @version 1.0
 * @author Bear
 */
function redis_connect($redis, $redis_host, $auth){

    $redis->connect($redis_host, 6379);
    $redis->auth($auth);
    $pong = $redis->ping();

    if($pong == "+PONG" or $pong == 1){
        return true;
    }

    return false;
}



/**
 * Redis 連線檢查 函式
 *
 * @return bool
 *
 * @version 1.0
 * @author Bear
 */
function redis_isConnected($redis){
    $pong = $redis->ping();

    if($pong == "+PONG" or $pong == 1){
        return true;
    }

    return false;
}
<?php
namespace Libs\Config;
$conf = make_config();


function make_config(){
    $config = [];
    $file_path = search_conf();
    $conf_file = fopen($file_path, 'r');
    while(!feof($conf_file)){
        $line = trim(fgets($conf_file, 1024));
        if(!empty($line)){
            $parm = explode('=', $line);
            $parm[1] = trim($parm[1], "'");
            $config[$parm[0]] = trim($parm[1], "\"");
        }
    }
    fclose($conf_file);
    return $config;
}

function search_conf(){
    $dir = dirname(dirname(__FILE__));
    $f = glob($dir."/conf/default.conf");

    if($f){
        return $f[0];
    } else {
        return null;
    }
}


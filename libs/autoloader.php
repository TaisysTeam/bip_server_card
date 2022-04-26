<?php
ini_set("include_path", dirname(dirname(__FILE__)));
$exe_dir = dirname(dirname(__FILE__));
$dir = "{$exe_dir}/libs";

$globs = glob($dir."/*");

if(!empty($globs) and is_array($globs)){
    foreach($globs as $file){
        if(is_dir($file)){
            $sub_globs = glob($file."/*");
            if(!empty($sub_globs) and is_array($sub_globs)){
                foreach($sub_globs as $sub_file){
                    if(substr($sub_file,-4) == '.php'){
                        require_once($sub_file);
                    }
                }
            }
        } elseif(substr($file,-4) == '.php') {
            require_once($file);
        }
    }
}



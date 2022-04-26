<?php


namespace Libs;


class display_log
{
    private $debug = 0;

    public function echo_warning($string){
        if($this -> debug){
            printf("\033[0;31m%s\033[0m\n", $string);
        }
    }

    public function echo_green($string){
        if($this -> debug) {
            printf("\033[0;32m%s\033[0m\n", $string);
        }
    }

    public function echo_blue($string){
        if($this -> debug) {
            printf("\033[0;34m%s\033[0m\n", $string);
        }
    }

    public function setDebug($status){
        $this -> debug = $status;
    }
}

$Dlog = new display_log();

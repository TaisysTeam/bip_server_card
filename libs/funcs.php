<?php
namespace Libs;

class Funcs {

    
    public function Agent_Check($condition){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(!empty($user_agent)){
            $agent = explode("/", $user_agent);
            if($agent[0] != $condition){ 
                exit;
            }
        }
    }



    public function output($Instruction){
        if(!empty($Instruction) && is_array($Instruction)){
            foreach($Instruction as $byte){
              //echo dechex($byte)." ";
              echo chr($byte);
            }
        }
    }


}
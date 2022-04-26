<?php

namespace Libs;

class Map {

    function program_mapping($code){
        $p = array("slimduet", "Actoma");
        return $p[$code];
    }
    
    
    
    function cmd_map($key){
        $map = array(
            "device_info" => array(array(0xB0, 0x10, 0x00, 0x00, 0x00), "device_info_2"),
            "device_info_2" => array(array(0xB0, 0x10, 0x00, 0x00, 0x00), ""),

            //"verify_pin" => array(0xB0, 0x1D, 0x0Y, 0x00, 0xl),
    
        );
    
        return $map[$key];
    }

}






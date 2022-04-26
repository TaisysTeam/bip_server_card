<?php

namespace Libs\sql;
use \PDO;

class mySql {

    private $db;

    function __construct($options){
        $this -> connect($options);
    }

    private function connect($options){
        $dsn = "mysql:host={$options['sql_server']};port={$options['port']};dbname={$options['dbname']};charset={$options['charset']}";
        $this -> db = new PDO($dsn, $options['dbuser'], $options['dbpasswd']);
    }

    public function query($cmd){
        $rows = $this -> db -> query($cmd);

        if($rows){
            $rows -> setFetchMode(PDO::FETCH_ASSOC);
            return $rows -> fetchAll();
        }

        return $this -> db -> errorInfo()[2];
    }

    public function prepare($cmd){
        return $this -> db -> prepare($cmd);
    }

    public function execute($data){
        $rows = $this -> db -> execute($data);
    }

    public function pquery($cmd, $data = array()){
        $prepare = $this -> db -> prepare($cmd);
        $prepare -> execute($data);
        return $prepare  -> fetchAll(PDO::FETCH_ASSOC);
    }

}



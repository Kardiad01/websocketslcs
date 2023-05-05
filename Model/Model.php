<?php

namespace Model;
use PDO;
use PDOException;

class Model extends PDO{

    protected string $table;
    protected array $fields;
    protected array $fieldsType;
    protected PDO $db;

    public function __construct()
    {
        try{
            $this->db = new PDO("mysql:host=" . $_ENV['DBHOST'].";dbname=" . $_ENV['DBNAME'] . ";charset=utf8mb4", $_ENV['DBUSER'] , $_ENV['DBPASS'], [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE =>true,
                PDO::ERRMODE_EXCEPTION => true
            ]);
        }catch(PDOException $e){
            throw $e;
        }
        
    }

    public function queryExec($sql, array $params = []) {
        try{
            $stmt = $this->db->prepare($sql);
            for ($x = 0; $x < count($params); $x++) {
                $stmt->bindParam($x+1, $params[$x], $this->tipo($params[$x]));
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            if(!empty($result)){
                return $result;
            }else{
                return [];
            }
        }catch(PDOException $e){
            throw $e;
        }
    }
    
    private function tipo($param) {
        switch (gettype($param)) {
            case "string":
                return PDO::PARAM_STR;
            case "integer":
                return PDO::PARAM_INT;
            case "resource":
                return PDO::PARAM_LOB;
        }

    }

}

?>
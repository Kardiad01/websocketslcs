<?php

namespace Model;
use PDO;

abstract class Model extends PDO{

    protected string $table;
    protected array $fields;
    protected array $fieldsType;
    protected PDO $db;

    public function __construct()
    {
        $this->db = new PDO("mysql:host=" . HOST.";dbname=" . DBNAME . ";charset=utf8", USER, PASS);
    }

}

?>
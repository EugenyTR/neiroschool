<?php
define('DB_HOST','localhost');
define('DB_NAME','u0485785_edu');
define('DB_USER','u0485785_edu');
define('DB_PASS','9d8f6tvozdfjhLKJH');
define('DB_CHARSET','utf8mb4');

class Database{
    private static $instance=null;
    private $pdo;
    private function __construct(){
        $dsn="mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $opt=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
        $this->pdo=new PDO($dsn,DB_USER,DB_PASS,$opt);
    }
    public static function getInstance(){
        if(self::$instance===null)self::$instance=new self();
        return self::$instance;
    }
    public function getConnection(){return $this->pdo;}
}
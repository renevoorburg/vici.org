<?php

namespace Vici\DB;

class DBConnector
{
    private \PDO $pdo;

    public function __construct($database = 'MAIN')
    {
        $datahost = $_ENV['DB_HOST'] ?? '';
        $database = $_ENV['DB_' . $database] ?? '';
        $username = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$datahost};dbname={$database};charset=utf8";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $this->pdo = new \PDO($dsn, $username, $password, $options);
    }
    
    public function prepare($query)
    {
        return $this->pdo->prepare($query);
    }
    
    public function query($query)
    {
        return $this->pdo->query($query);
    }
    
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }
    
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
}
<?php

class DatabaseSingleton
{
    private static $instance = null;
    private $connection;

    private $host = "localhost";
    private $dbName = "al_habib_pharmacy";
    private $username = "root";
    private $password = "";

    private function __construct()
    {
        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbName,
                $this->username,
                $this->password
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseSingleton();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton object");
    }
}

?>
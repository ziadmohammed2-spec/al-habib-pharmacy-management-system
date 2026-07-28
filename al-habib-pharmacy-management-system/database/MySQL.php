<?php

require_once __DIR__ . "/Database.php";

class MySQL implements Database
{
    private $host = "localhost";
    private $dbname = "al_habib_pharmacy";
    private $username = "root";
    private $password = "";
    private $conn;

    public function connectToDb()
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $this->conn;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function getConnection()
    {
        if (!$this->conn instanceof PDO) {
            $this->connectToDb();
        }

        return $this->conn;
    }

    public function insert($sql, $params = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function update($sql, $params = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($sql, $params = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    public function select($sql, $params = [])
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function prepare($sql, $options = [])
    {
        return $this->getConnection()->prepare($sql, $options);
    }

    public function query($sql, ?int $fetchMode = null, ...$fetchModeArgs)
    {
        if ($fetchMode === null) {
            return $this->getConnection()->query($sql);
        }

        return $this->getConnection()->query($sql, $fetchMode, ...$fetchModeArgs);
    }

    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        return $this->getConnection()->commit();
    }

    public function rollBack()
    {
        return $this->getConnection()->rollBack();
    }

    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    public function lastInsertId($name = null)
    {
        return $this->getConnection()->lastInsertId($name);
    }

    public function disconnect()
    {
        $this->conn = null;
    }
}

?>

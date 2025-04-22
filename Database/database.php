<?php

namespace Database;

use PDO;
use PDOException;

class Database {
    private $host = "bzzdrenbhtc4h3xmjgby-mysql.services.clever-cloud.com";
    private $db_name = "bzzdrenbhtc4h3xmjgby";
    private $username = "uc8vw8am9mjwuqix";
    private $password = "fYgwbaxA56wtaKsncaMS";
    public $connection;

    public function getConnection() {
        $this->connection = null;

        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
            exit;
        }

        return $this->connection;
    }
}

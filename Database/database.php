<?php

namespace Database;

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $connection;

    public function __construct() {
        // Load database credentials from environment variables
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASS'];
    }

    public function getConnection() {
        try {
            // Create new PDO connection using loaded credentials
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );

            // Set PDO error and fetch modes
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Return the PDO connection object
            return $this->connection;

        } catch (PDOException $e) {
            // Catch and display connection error
            echo "Connection error: " . $e->getMessage();
            exit;
        }
    }
}

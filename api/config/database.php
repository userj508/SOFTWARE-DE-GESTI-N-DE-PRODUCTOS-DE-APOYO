<?php
// api/config/database.php

class Database {
    private $host = '127.0.0.1';
    private $db_name = 'support_products';
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Read credentials from env variables if available, otherwise use defaults for local testing
        $this->username = getenv('DB_USER') ?: 'admin';
        $this->password = getenv('DB_PASS') ?: 'admin';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
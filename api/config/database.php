<?php
// api/config/database.php

class Database {
    private $host = 'bbdd.aniasystem.org';
    private $db_name = 'ddb274045';
    private $username = 'ddb274045';
    private $password = 'Contraseña2026!';
    public $conn;

    public function __construct() {
        // Configuracion para DonDominio
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
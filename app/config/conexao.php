<?php
// app/config/conexao.php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $host = "localhost";
        $user = "root";
        $pass = ""; // ajuste
        $db   = "MyBeatDB";

        $this->conn = new mysqli($host, $user, $pass, $db);
        if ($this->conn->connect_error) {
            die("Erro na conexão: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
$conn = Database::getInstance()->getConnection();

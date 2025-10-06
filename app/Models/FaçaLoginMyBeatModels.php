<?php
require_once __DIR__ . '/../config/conector.php';

class LoginModel {
    private $conn;

    public function __construct()
    {

        global $conn;

        if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
            $this->conn = $conn;
            return;
        }

        foreach (['conn', 'con', 'mysqli'] as $var) {
            if (!empty($GLOBALS[$var]) && $GLOBALS[$var] instanceof mysqli) {
                $this->conn = $GLOBALS[$var];
                return;
            }
        }

        throw new Exception("Conexão com banco de dados não estabelecida. Verifique 'conector.php'.");
    }

    public function findUserByEmail(string $email): ?array
    {
        $sql = "SELECT id_usuario, nome_usuario, email, hash_senha, nome_exibicao
                FROM Usuarios
                WHERE email = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro SQL: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conector.php'; 

class LoginADMModels
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;

        if (!empty($conn) && $conn instanceof mysqli && !$conn->connect_error) {
            $this->conn = $conn;
            return;
        }

        foreach (['conn', 'con', 'mysqli'] as $var) {
            if (!empty($GLOBALS[$var]) && $GLOBALS[$var] instanceof mysqli && !$GLOBALS[$var]->connect_error) {
                $this->conn = $GLOBALS[$var];
                return;
            }
        }

        throw new Exception("Conexão com banco de dados não estabelecida. Verifique 'conector.php'.");
    }

    public function findAdminByEmail(string $email): ?array
    {
        $sql = "SELECT id_admin, nome_admin, email, hash_senha, administrador
                FROM Administradores
                WHERE email = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro ao preparar statement: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param('s', $email);

        if (!$stmt->execute()) {
            error_log("Erro ao executar statement: " . $stmt->error);
            $stmt->close();
            return null;
        }

        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();
            return $row ?: null;
        } else {
            $stmt->bind_result($id_admin, $nome_admin, $email_db, $hash_senha, $administrador);
            if ($stmt->fetch()) {
                $stmt->close();
                return [
                    'id_admin'      => $id_admin,
                    'nome_admin'    => $nome_admin,
                    'email'         => $email_db,
                    'hash_senha'    => $hash_senha,
                    'administrador' => $administrador
                ];
            } else {
                $stmt->close();
                return null;
            }
        }
    }
}

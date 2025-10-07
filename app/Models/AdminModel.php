<?php
// app/Models/AdminModel.php
// Model resiliente: aceita PDO ou mysqli e tenta detectar tabela/colunas comuns automaticamente.

class AdminModel {
    private $pdo = null;
    private $mysqli = null;

    public function __construct($db = null) {
        if ($db instanceof PDO) { $this->pdo = $db; return; }
        if ($db instanceof mysqli) { $this->mysqli = $db; return; }

        // tenta incluir conector padrão sem modificá-lo
        $candidates = [
            __DIR__ . '/../config/conector.php',
            __DIR__ . '/../../app/config/conector.php',
            __DIR__ . '/../../config/conector.php'
        ];
        foreach ($candidates as $f) {
            if (file_exists($f)) { require_once $f; break; }
        }

        if (isset($pdo) && $pdo instanceof PDO) { $this->pdo = $pdo; return; }
        if (isset($conn) && ($conn instanceof mysqli || get_resource_type($conn) === 'mysql link')) {
            $this->mysqli = $conn;
            return;
        }

        throw new Exception("AdminModel: não foi possível obter conexão. Verifique o conector (esperado \$pdo ou \$conn).");
    }

    /**
     * Tenta descobrir a tabela de usuários e as colunas corretas.
     * Retorna array com keys: table, cols (assoc colmap)
     */
    private function discoverUsersSchema(): array {
        // candidatos de nomes de tabela e combinações de colunas
        $tableCandidates = ['Usuarios', 'usuarios', 'Users', 'users', 'usuarios_tb', 'usuario', 'usuario_tb'];
        // colmap: required column keys we will map to internal names
        $colMaps = [
            ['id_usuario'=>'id_usuario','nome_exibicao'=>'nome_exibicao','nome_usuario'=>'nome_usuario','email'=>'email','foto_perfil_url'=>'foto_perfil_url','data_cadastro'=>'data_cadastro'],
            ['id_usuario'=>'id','nome_exibicao'=>'display_name','nome_usuario'=>'username','email'=>'email','foto_perfil_url'=>'foto','data_cadastro'=>'created_at'],
            ['id_usuario'=>'id','nome_exibicao'=>'nome','nome_usuario'=>'username','email'=>'email','foto_perfil_url'=>'avatar','data_cadastro'=>'created_at'],
            ['id_usuario'=>'id_usuario','nome_exibicao'=>'nome','nome_usuario'=>'nome_usuario','email'=>'email','foto_perfil_url'=>'foto_perfil_url','data_cadastro'=>'data_cadastro'],
        ];

        foreach ($tableCandidates as $table) {
            foreach ($colMaps as $colMap) {
                // monta select testando se a query funciona
                if ($this->pdo) {
                    $cols = array_values($colMap);
                    $sql = "SELECT " . implode(", ", $cols) . " FROM `{$table}` LIMIT 1";
                    try {
                        $stmt = $this->pdo->query($sql);
                        $r = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($r !== false) {
                            return ['table'=>$table,'cols'=>$colMap];
                        }
                    } catch (Throwable $e) {
                        // ignora e tenta próxima combinação
                    }
                } else {
                    // mysqli
                    $cols = array_values($colMap);
                    $sql = "SELECT " . implode(", ", $cols) . " FROM `{$table}` LIMIT 1";
                    $res = @$this->mysqli->query($sql);
                    if ($res && $res->num_rows >= 0) {
                        // ok
                        return ['table'=>$table,'cols'=>$colMap];
                    }
                }
            }
        }
        // fallback: assume table Usuarios with default columns (poderá falhar)
        return ['table'=>'Usuarios','cols'=>['id_usuario'=>'id_usuario','nome_exibicao'=>'nome_exibicao','nome_usuario'=>'nome_usuario','email'=>'email','foto_perfil_url'=>'foto_perfil_url','data_cadastro'=>'data_cadastro']];
    }

    /**
     * Retorna todos os usuários (array associativo)
     */
    public function allUsers(): array {
        $schema = $this->discoverUsersSchema();
        $table = $schema['table'];
        $cols = $schema['cols']; // mapping internal->actual

        // monta select com aliases internos
        $selectParts = [];
        foreach ($cols as $internal => $actual) {
            $selectParts[] = "`{$actual}` AS `{$internal}`";
        }
        $sql = "SELECT " . implode(", ", $selectParts) . " FROM `{$table}` ORDER BY `" . (isset($cols['nome_exibicao']) ? $cols['nome_exibicao'] : current($cols)) . "` ASC";

        if ($this->pdo) {
            try {
                $stmt = $this->pdo->query($sql);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e) {
                error_log("AdminModel::allUsers PDO error: " . $e->getMessage());
                return [];
            }
        } else {
            $res = $this->mysqli->query($sql);
            if (!$res) return [];
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
            return $rows ?: [];
        }
    }

    /**
     * Reviews by user (keeps previous implementation but tolerant)
     */
    public function reviewsByUser(int $userId): array {
        $sql = "SELECT a.id_avaliacao AS id, a.texto_review AS texto, a.nota, a.data_avaliacao, al.titulo AS album_title
                FROM Avaliacoes a
                LEFT JOIN Albuns al ON a.id_album = al.id_album
                WHERE a.id_usuario = :uid
                ORDER BY a.data_avaliacao DESC";
        if ($this->pdo) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $stmt = $this->mysqli->prepare("SELECT a.id_avaliacao AS id, a.texto_review AS texto, a.nota, a.data_avaliacao, al.titulo AS album_title
                FROM Avaliacoes a
                LEFT JOIN Albuns al ON a.id_album = al.id_album
                WHERE a.id_usuario = ? ORDER BY a.data_avaliacao DESC");
            if (!$stmt) return [];
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $rows ?: [];
        }
    }

    

    /**
     * Retorna todas as avaliações (com dados do usuário e álbum)
     */
    public function allReviews(): array {
        $sql = "SELECT a.id_avaliacao AS id, a.texto_review AS texto, a.nota, a.data_avaliacao,
                       u.id_usuario AS usuario_id, u.nome_usuario, u.nome_exibicao,
                       al.id_album, al.titulo AS album_title
                FROM Avaliacoes a
                LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario
                LEFT JOIN Albuns al ON a.id_album = al.id_album
                ORDER BY a.data_avaliacao DESC";
        if ($this->pdo) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $res = $this->mysqli->query($sql);
            if (!$res) return [];
            $rows = $res->fetch_all(MYSQLI_ASSOC);
            $res->free();
            return $rows ?: [];
        }
    }

public function deleteReview(int $reviewId): bool {
        $sql = "DELETE FROM Avaliacoes WHERE id_avaliacao = :id";
        if ($this->pdo) {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $reviewId]);
        } else {
            $stmt = $this->mysqli->prepare("DELETE FROM Avaliacoes WHERE id_avaliacao = ?");
            if (!$stmt) return false;
            $stmt->bind_param("i", $reviewId);
            $ok = $stmt->execute();
            $stmt->close();
            return (bool)$ok;
        }
    }

    public function banUser(int $userId): bool {
        if ($this->pdo) {
            try {
                $this->pdo->beginTransaction();
                $stmt = $this->pdo->prepare("DELETE FROM Avaliacoes WHERE id_usuario = :uid");
                $stmt->execute([':uid' => $userId]);
                $stmt2 = $this->pdo->prepare("DELETE FROM Usuarios WHERE id_usuario = :uid");
                $stmt2->execute([':uid' => $userId]);
                $this->pdo->commit();
                return true;
            } catch (Throwable $e) {
                if ($this->pdo->inTransaction()) $this->pdo->rollBack();
                error_log("AdminModel::banUser error: " . $e->getMessage());
                return false;
            }
        } else {
            try {
                $this->mysqli->begin_transaction();
                $stmt = $this->mysqli->prepare("DELETE FROM Avaliacoes WHERE id_usuario = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
                $stmt2 = $this->mysqli->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
                $stmt2->bind_param("i", $userId);
                $stmt2->execute();
                $stmt2->close();
                $this->mysqli->commit();
                return true;
            } catch (Throwable $e) {
                @$this->mysqli->rollback();
                error_log("AdminModel::banUser (mysqli) error: " . $e->getMessage());
                return false;
            }
        }
    }
}
?>

<?php
require_once __DIR__ . '/../config/conector.php';

class SeguidoresMyBeatModels {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    
    public function buscarUsuarios($termo) {
        $sql = "SELECT id_usuario, nome_usuario, nome_exibicao, foto_perfil_url 
                FROM Usuarios 
                WHERE nome_usuario LIKE ? OR nome_exibicao LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $like = "%{$termo}%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    
    public function seguirUsuario($idSeguidor, $idSeguido) {
        $sql = "INSERT INTO Seguidores (id_seguidor, id_seguido) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idSeguidor, $idSeguido);
        return $stmt->execute();
    }

    
    public function deixarDeSeguir($idSeguidor, $idSeguido) {
        $sql = "DELETE FROM Seguidores WHERE id_seguidor = ? AND id_seguido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idSeguidor, $idSeguido);
        return $stmt->execute();
    }

    public function jaSegue($idSeguidor, $idSeguido) {
        $sql = "SELECT 1 FROM Seguidores WHERE id_seguidor = ? AND id_seguido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idSeguidor, $idSeguido);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

   
    public function listarSeguidores($idUsuario) {
        $sql = "SELECT u.id_usuario, u.nome_usuario, u.nome_exibicao, u.foto_perfil_url
                FROM Seguidores s
                JOIN Usuarios u ON s.id_seguidor = u.id_usuario
                WHERE s.id_seguido = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

   
    public function listarSeguindo($idUsuario) {
        $sql = "SELECT u.id_usuario, u.nome_usuario, u.nome_exibicao, u.foto_perfil_url
                FROM Seguidores s
                JOIN Usuarios u ON s.id_seguido = u.id_usuario
                WHERE s.id_seguidor = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

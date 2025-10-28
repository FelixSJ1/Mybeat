<?php
class Chat {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Enviar mensagem
    public function enviarMensagem($id_grupo, $id_usuario, $mensagem) {
        $stmt = $this->conn->prepare("INSERT INTO Mensagens_Grupo (id_grupo, id_usuario, mensagem) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_grupo, $id_usuario, $mensagem);
        $sucesso = $stmt->execute();
        $id_mensagem = $stmt->insert_id;
        $stmt->close();
        
        return $sucesso ? $id_mensagem : false;
    }
    
    // Buscar mensagens do grupo (com paginação)
    public function buscarMensagens($id_grupo, $limite = 50, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.nome_usuario, u.nome_exibicao, u.foto_perfil_url
            FROM Mensagens_Grupo m
            INNER JOIN Usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_grupo = ?
            ORDER BY m.data_envio DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id_grupo, $limite, $offset);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Buscar mensagens após determinado ID (para atualização em tempo real)
    public function buscarMensagensAposId($id_grupo, $ultimo_id) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.nome_usuario, u.nome_exibicao, u.foto_perfil_url
            FROM Mensagens_Grupo m
            INNER JOIN Usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_grupo = ? AND m.id_mensagem > ?
            ORDER BY m.data_envio ASC
        ");
        $stmt->bind_param("ii", $id_grupo, $ultimo_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Editar mensagem
    public function editarMensagem($id_mensagem, $nova_mensagem, $id_usuario) {
        $stmt = $this->conn->prepare("UPDATE Mensagens_Grupo SET mensagem = ?, editada = TRUE WHERE id_mensagem = ? AND id_usuario = ?");
        $stmt->bind_param("sii", $nova_mensagem, $id_mensagem, $id_usuario);
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
    
    // Deletar mensagem
    public function deletarMensagem($id_mensagem, $id_usuario) {
        $stmt = $this->conn->prepare("DELETE FROM Mensagens_Grupo WHERE id_mensagem = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_mensagem, $id_usuario);
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
    
    // Contar mensagens do grupo
    public function contarMensagens($id_grupo) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM Mensagens_Grupo WHERE id_grupo = ?");
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
    
    // Buscar última mensagem do grupo
    public function buscarUltimaMensagem($id_grupo) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.nome_usuario, u.nome_exibicao
            FROM Mensagens_Grupo m
            INNER JOIN Usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_grupo = ?
            ORDER BY m.data_envio DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        $mensagem = $result->fetch_assoc();
        $stmt->close();
        return $mensagem;
    }
}
?>
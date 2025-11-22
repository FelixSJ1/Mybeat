<?php
class Grupo {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Criar novo grupo
    public function criar($nome_grupo, $descricao, $id_criador, $privado = false, $foto_grupo_url = null) {
       $foto = $foto_grupo_url ?? '../Views/grupos/images/grupos/default_grupo.png';
        
        $stmt = $this->conn->prepare("INSERT INTO Grupos (nome_grupo, descricao, id_criador, privado, foto_grupo_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $nome_grupo, $descricao, $id_criador, $privado, $foto);
        
        if ($stmt->execute()) {
            $id_grupo = $stmt->insert_id;
            $stmt->close();
            
            // Adicionar criador como admin do grupo
            $this->adicionarMembro($id_grupo, $id_criador, 'admin');
            
            return $id_grupo;
        }
        
        $stmt->close();
        return false;
    }
    
    // Buscar todos os grupos públicos
    public function buscarGruposPublicos($termo_busca = '') {
        if ($termo_busca !== '') {
            $termo = '%' . $termo_busca . '%';
            $stmt = $this->conn->prepare("
                SELECT g.*, u.nome_usuario as nome_criador, 
                       COUNT(DISTINCT m.id_usuario) as total_membros
                FROM Grupos g
                LEFT JOIN Usuarios u ON g.id_criador = u.id_usuario
                LEFT JOIN Membros_Grupo m ON g.id_grupo = m.id_grupo
                WHERE g.privado = FALSE AND (g.nome_grupo LIKE ? OR g.descricao LIKE ?)
                GROUP BY g.id_grupo
                ORDER BY g.data_criacao DESC
            ");
            $stmt->bind_param("ss", $termo, $termo);
        } else {
            $stmt = $this->conn->prepare("
                SELECT g.*, u.nome_usuario as nome_criador,
                       COUNT(DISTINCT m.id_usuario) as total_membros
                FROM Grupos g
                LEFT JOIN Usuarios u ON g.id_criador = u.id_usuario
                LEFT JOIN Membros_Grupo m ON g.id_grupo = m.id_grupo
                WHERE g.privado = FALSE
                GROUP BY g.id_grupo
                ORDER BY g.data_criacao DESC
            ");
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Buscar grupos do usuário
    public function buscarGruposDoUsuario($id_usuario) {
        $stmt = $this->conn->prepare("
            SELECT g.*, m.role, m.data_entrada,
                   COUNT(DISTINCT m2.id_usuario) as total_membros
            FROM Grupos g
            INNER JOIN Membros_Grupo m ON g.id_grupo = m.id_grupo
            LEFT JOIN Membros_Grupo m2 ON g.id_grupo = m2.id_grupo
            WHERE m.id_usuario = ?
            GROUP BY g.id_grupo
            ORDER BY m.data_entrada DESC
        ");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Buscar detalhes do grupo
    public function buscarPorId($id_grupo) {
        $stmt = $this->conn->prepare("
            SELECT g.*, u.nome_usuario as nome_criador,
                   COUNT(DISTINCT m.id_usuario) as total_membros
            FROM Grupos g
            LEFT JOIN Usuarios u ON g.id_criador = u.id_usuario
            LEFT JOIN Membros_Grupo m ON g.id_grupo = m.id_grupo
            WHERE g.id_grupo = ?
            GROUP BY g.id_grupo
        ");
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        $result = $stmt->get_result();
        $grupo = $result->fetch_assoc();
        $stmt->close();
        return $grupo;
    }
    
    // Verificar se usuário é membro
    public function ehMembro($id_grupo, $id_usuario) {
        $stmt = $this->conn->prepare("SELECT id_membro FROM Membros_Grupo WHERE id_grupo = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_grupo, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $ehMembro = $result->num_rows > 0;
        $stmt->close();
        return $ehMembro;
    }
    
    // Adicionar membro ao grupo
    public function adicionarMembro($id_grupo, $id_usuario, $role = 'membro') {
        $stmt = $this->conn->prepare("INSERT INTO Membros_Grupo (id_grupo, id_usuario, role) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_grupo, $id_usuario, $role);
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
    
    // Remover membro do grupo
    public function removerMembro($id_grupo, $id_usuario) {
        $stmt = $this->conn->prepare("DELETE FROM Membros_Grupo WHERE id_grupo = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_grupo, $id_usuario);
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
    
    // Buscar membros do grupo
    public function buscarMembros($id_grupo) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.nome_usuario, u.nome_exibicao, u.foto_perfil_url
            FROM Membros_Grupo m
            INNER JOIN Usuarios u ON m.id_usuario = u.id_usuario
            WHERE m.id_grupo = ?
            ORDER BY m.role DESC, m.data_entrada ASC
        ");
        $stmt->bind_param("i", $id_grupo);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Atualizar grupo
    public function atualizar($id_grupo, $nome_grupo, $descricao, $foto_grupo_url = null) {
        if ($foto_grupo_url) {
            $stmt = $this->conn->prepare("UPDATE Grupos SET nome_grupo = ?, descricao = ?, foto_grupo_url = ? WHERE id_grupo = ?");
            $stmt->bind_param("sssi", $nome_grupo, $descricao, $foto_grupo_url, $id_grupo);
        } else {
            $stmt = $this->conn->prepare("UPDATE Grupos SET nome_grupo = ?, descricao = ? WHERE id_grupo = ?");
            $stmt->bind_param("ssi", $nome_grupo, $descricao, $id_grupo);
        }
        
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
    
    // Deletar grupo
    public function deletar($id_grupo) {
        $stmt = $this->conn->prepare("DELETE FROM Grupos WHERE id_grupo = ?");
        $stmt->bind_param("i", $id_grupo);
        $sucesso = $stmt->execute();
        $stmt->close();
        return $sucesso;
    }
}
?>
<?php
require_once __DIR__ . '/../Models/Grupo.php';
require_once __DIR__ . '/../Models/Chat.php';

class GrupoController {
    private $grupoModel;
    private $testing = false; // 🔹 ligar e desligar o modo de teste

    public function __construct($db, $testing = false) {
        $this->db = $db;
        $this->grupoModel = new Grupo($db);
        $this->chatModel = new Chat($db);
    }
    
    // Criar novo grupo
    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../../Views/grupos/criar_grupo.php');
            exit();
        }
        
        $nome_grupo = trim($_POST['nome_grupo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $privado = isset($_POST['privado']) ? 1 : 0;
        $id_criador = $_SESSION['id_usuario'];
        
        // Validações
        if (empty($nome_grupo)) {
            $_SESSION['mensagem_erro'] = "O nome do grupo é obrigatório.";
            header('Location: ../../Views/grupos/criar_grupo.php');
            exit();
        }
        
        if (strlen($nome_grupo) > 100) {
            $_SESSION['mensagem_erro'] = "O nome do grupo deve ter no máximo 100 caracteres.";
            header('Location: ../../Views/grupos/criar_grupo.php');
            exit();
        }
        
        // Upload de foto (opcional)
        $foto_grupo_url = null;
        if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['foto_grupo'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = uniqid('grupo_') . '.' . $extensao;
                $caminho_destino = __DIR__ . 'Mybeat/app/Views/grupos/public/images/grupos/' . $nome_arquivo;
                
                if (!file_exists(__DIR__ . 'Mybeat/app/Views/grupos/public/images/grupos/')) {
                    mkdir(__DIR__ . 'Mybeat/app/Views/grupos/public/images/grupos/', 0777, true);
                }
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                    $foto_grupo_url = 'Mybeat/app/Views/grupos/public/images/grupos/' . $nome_arquivo;
                }
            }
        }
        
        $id_grupo = $this->grupoModel->criar($nome_grupo, $descricao, $id_criador, $privado, $foto_grupo_url);
        
        if ($id_grupo) {
            $_SESSION['mensagem_sucesso'] = "Grupo criado com sucesso!";
            header('Location: ../../Views/grupos/grupo_chat.php?id=' . $id_grupo);
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao criar grupo.";
            header('Location: ../../Views/grupos/criar_grupo.php');
        }
        exit();
    }
    
    // Entrar no grupo
    public function entrar() {
        $id_grupo = (int)($_POST['id_grupo'] ?? 0);
        $id_usuario = $_SESSION['id_usuario'];
        
        if ($id_grupo <= 0) {
            $_SESSION['mensagem_erro'] = "Grupo inválido.";
            header('Location: ../../Views/grupos/lista_grupos.php');
            exit();
        }
        
        // Verificar se já é membro
        if ($this->grupoModel->ehMembro($id_grupo, $id_usuario)) {
            header('Location: ../../Views/grupos/grupo_chat.php?id=' . $id_grupo);
            exit();
        }
        
        // Adicionar como membro
        if ($this->grupoModel->adicionarMembro($id_grupo, $id_usuario)) {
            $_SESSION['mensagem_sucesso'] = "Você entrou no grupo!";
            header('Location: ../../Views/grupos/grupo_chat.php?id=' . $id_grupo);
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao entrar no grupo.";
            header('Location: ../../Views/grupos/lista_grupos.php');
        }
        exit();
    }
    
    // Sair do grupo
    public function sair() {
        $id_grupo = (int)($_POST['id_grupo'] ?? 0);
        $id_usuario = $_SESSION['id_usuario'];
        
        if ($this->grupoModel->removerMembro($id_grupo, $id_usuario)) {
            $_SESSION['mensagem_sucesso'] = "Você saiu do grupo.";
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao sair do grupo.";
        }
        
        header('Location: ../../Views/grupos/lista_grupos.php');
        exit();
    }
    
    // Enviar mensagem no chat
    public function enviarMensagem() {
        header('Content-Type: application/json');
        
        $id_grupo = (int)($_POST['id_grupo'] ?? 0);
        $mensagem = trim($_POST['mensagem'] ?? '');
        $id_usuario = $_SESSION['id_usuario'];
        
        // Validações
        if (empty($mensagem)) {
            echo json_encode(['success' => false, 'message' => 'Mensagem vazia']);
            exit();
        }
        
        // Verificar se é membro
        if (!$this->grupoModel->ehMembro($id_grupo, $id_usuario)) {
            echo json_encode(['success' => false, 'message' => 'Você não é membro deste grupo']);
            exit();
        }
        
        $id_mensagem = $this->chatModel->enviarMensagem($id_grupo, $id_usuario, $mensagem);
        
        if ($id_mensagem) {
            echo json_encode(['success' => true, 'id_mensagem' => $id_mensagem]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
        }
        exit();
    }
    
    // Buscar novas mensagens (AJAX)
    public function buscarNovasMensagens() {
        header('Content-Type: application/json');
        
        $id_grupo = (int)($_GET['id_grupo'] ?? 0);
        $ultimo_id = (int)($_GET['ultimo_id'] ?? 0);
        $id_usuario = $_SESSION['id_usuario'];
        
        // Verificar se é membro
        if (!$this->grupoModel->ehMembro($id_grupo, $id_usuario)) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit();
        }
        
        $result = $this->chatModel->buscarMensagensAposId($id_grupo, $ultimo_id);
        $mensagens = [];
        
        while ($msg = $result->fetch_assoc()) {
            $mensagens[] = $msg;
        }
        
        echo json_encode(['success' => true, 'mensagens' => $mensagens]);
        exit();
    }
}
?>
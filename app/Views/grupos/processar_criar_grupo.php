<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../FaçaLoginMyBeat.php');
    exit();
}

// Verificar se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: criar_grupo.php');
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';

$grupoModel = new Grupo($conn);

// Capturar dados do formulário
$nome_grupo = trim($_POST['nome_grupo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$privado = isset($_POST['privado']) ? 1 : 0;
$id_criador = $_SESSION['id_usuario'];

// Validações
if (empty($nome_grupo)) {
    $_SESSION['mensagem_erro'] = "O nome do grupo é obrigatório.";
    header('Location: criar_grupo.php');
    exit();
}

if (strlen($nome_grupo) > 100) {
    $_SESSION['mensagem_erro'] = "O nome do grupo deve ter no máximo 100 caracteres.";
    header('Location: criar_grupo.php');
    exit();
}

if (strlen($descricao) > 500) {
    $_SESSION['mensagem_erro'] = "A descrição deve ter no máximo 500 caracteres.";
    header('Location: criar_grupo.php');
    exit();
}

// Upload da foto (opcional)
$foto_grupo_url = null;

if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === UPLOAD_ERR_OK) {
    $arquivo = $_FILES['foto_grupo'];
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($extensao, $extensoes_permitidas)) {
        // Verificar tamanho do arquivo (máximo 5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            $_SESSION['mensagem_erro'] = "A imagem deve ter no máximo 5MB.";
            header('Location: criar_grupo.php');
            exit();
        }
        
        $nome_arquivo = uniqid('grupo_') . '.' . $extensao;
        $diretorio_destino = __DIR__ . '/images/grupos/';
        $caminho_destino = $diretorio_destino . $nome_arquivo;
        
        // Criar diretório se não existir
        if (!file_exists($diretorio_destino)) {
            mkdir($diretorio_destino, 0777, true);
        }
        
        // Mover arquivo
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
            $foto_grupo_url = 'images/grupos/' . $nome_arquivo;
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao fazer upload da imagem.";
            header('Location: criar_grupo.php');
            exit();
        }
    } else {
        $_SESSION['mensagem_erro'] = "Formato de imagem não permitido. Use JPG, JPEG, PNG ou GIF.";
        header('Location: criar_grupo.php');
        exit();
    }
}

// Criar o grupo
try {
    $id_grupo = $grupoModel->criar($nome_grupo, $descricao, $id_criador, $privado, $foto_grupo_url);
    
    if ($id_grupo) {
        $_SESSION['mensagem_sucesso'] = "Grupo criado com sucesso!";
        header('Location: grupo_chat.php?id=' . $id_grupo);
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao criar grupo. Tente novamente.";
        header('Location: criar_grupo.php');
    }
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Erro ao criar grupo: " . $e->getMessage();
    header('Location: criar_grupo.php');
}

exit();
?>
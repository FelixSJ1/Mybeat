<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';
require_once __DIR__ . '/../../Models/Chat.php';

$grupoModel = new Grupo($conn);
$chatModel = new Chat($conn);

$id_grupo = (int)($_POST['id_grupo'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');
$id_usuario = $_SESSION['id_usuario'];

// Validações
if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Grupo inválido']);
    exit();
}

if (empty($mensagem)) {
    echo json_encode(['success' => false, 'message' => 'Mensagem vazia']);
    exit();
}

if (strlen($mensagem) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Mensagem muito longa (máximo 1000 caracteres)']);
    exit();
}

// Verificar se é membro do grupo
if (!$grupoModel->ehMembro($id_grupo, $id_usuario)) {
    echo json_encode(['success' => false, 'message' => 'Você não é membro deste grupo']);
    exit();
}

// Enviar mensagem
try {
    $id_mensagem = $chatModel->enviarMensagem($id_grupo, $id_usuario, $mensagem);
    
    if ($id_mensagem) {
        echo json_encode([
            'success' => true, 
            'id_mensagem' => $id_mensagem,
            'message' => 'Mensagem enviada com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

exit();
?>
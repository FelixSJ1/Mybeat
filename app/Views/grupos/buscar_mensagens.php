<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';
require_once __DIR__ . '/../../Models/Chat.php';

$grupoModel = new Grupo($conn);
$chatModel = new Chat($conn);

$id_grupo = (int)($_GET['id_grupo'] ?? 0);
$ultimo_id = (int)($_GET['ultimo_id'] ?? 0);
$id_usuario = $_SESSION['id_usuario'];

// Validações
if ($id_grupo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Grupo inválido']);
    exit();
}

// Verificar se é membro do grupo
if (!$grupoModel->ehMembro($id_grupo, $id_usuario)) {
    echo json_encode(['success' => false, 'message' => 'Você não é membro deste grupo']);
    exit();
}

// Buscar mensagens após o último ID
try {
    $result = $chatModel->buscarMensagensAposId($id_grupo, $ultimo_id);
    $mensagens = [];
    
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = $msg;
    }
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

exit();
?>
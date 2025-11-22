<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../FaçaLoginMyBeat.php');
    exit();
}

// Verificar se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lista_grupos.php');
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';

$grupoModel = new Grupo($conn);

$id_grupo = (int)($_POST['id_grupo'] ?? 0);
$id_usuario = $_SESSION['id_usuario'];

// Validar ID do grupo
if ($id_grupo <= 0) {
    $_SESSION['mensagem_erro'] = "Grupo inválido.";
    header('Location: lista_grupos.php');
    exit();
}

// Verificar se o grupo existe
$grupo = $grupoModel->buscarPorId($id_grupo);
if (!$grupo) {
    $_SESSION['mensagem_erro'] = "Grupo não encontrado.";
    header('Location: lista_grupos.php');
    exit();
}

// Verificar se já é membro
if ($grupoModel->ehMembro($id_grupo, $id_usuario)) {
    $_SESSION['mensagem_erro'] = "Você já é membro deste grupo.";
    header('Location: grupo_chat.php?id=' . $id_grupo);
    exit();
}

// Verificar se o grupo é privado
if ($grupo['privado']) {
    $_SESSION['mensagem_erro'] = "Este grupo é privado. Você precisa de um convite para entrar.";
    header('Location: lista_grupos.php');
    exit();
}

// Adicionar como membro
try {
    if ($grupoModel->adicionarMembro($id_grupo, $id_usuario, 'membro')) {
        $_SESSION['mensagem_sucesso'] = "Você entrou no grupo!";
        header('Location: grupo_chat.php?id=' . $id_grupo);
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao entrar no grupo. Tente novamente.";
        header('Location: lista_grupos.php');
    }
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Erro ao entrar no grupo: " . $e->getMessage();
    header('Location: lista_grupos.php');
}

exit();
?>
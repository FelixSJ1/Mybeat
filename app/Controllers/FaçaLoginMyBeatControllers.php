<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Models/FaçaLoginMyBeatModels.php';

function redirect_with_message($url, $key = 'error', $message = '')
{
    if ($message !== '') {
        $_SESSION[$key] = $message;
    }
    header("Location: $url");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'Método inválido.');
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW);

if (!empty($_POST['email'])) {
    $_SESSION['old_email'] = $_POST['email'];
}

if (!$email || !$senha) {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'Preencha e-mail e senha corretamente.');
}

try {
    $model = new LoginModel();
} catch (Exception $e) {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'Erro de conexão: ' . $e->getMessage());
}

$user = $model->findUserByEmail($email);

if (!$user) {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'E-mail ou senha inválidos.');
}

$hashFromDb = $user['hash_senha'] ?? null;

if (!$hashFromDb) {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'Erro no registro do usuário.');
}

if (password_verify($senha, $hashFromDb)) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id_usuario'];
    $_SESSION['user_name'] = $user['nome_exibicao'] ?? $user['nome_usuario'];
    $_SESSION['logged_in'] = true;

    unset($_SESSION['error'], $_SESSION['old_email']);

<<<<<<< Updated upstream
    header('Location: ../../app/Views/home_usuario.php');
=======

    header('Location: ../Views/home_usuario.php');
>>>>>>> Stashed changes
    exit;
} else {
    redirect_with_message('../../app/Views/FaçaLoginMyBeat.php', 'error', 'E-mail ou senha inválidos.');
}
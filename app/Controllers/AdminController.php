<?php
// app/Controllers/AdminController.php
if (session_status() === PHP_SESSION_NONE) session_start();

// --- INÍCIO: Checagem de Acesso: apenas administradores podem acessar este controller ---
if (session_status() === PHP_SESSION_NONE) session_start();

// Se não estiver logado como admin, redireciona para a tela de login de administrador
if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    // opcional: guarda destino para após login (se quiser usar)
    $_SESSION['after_admin_login_redirect'] = $_SERVER['REQUEST_URI'] ?? '/';

    // redireciona para a view de login ADM (mesmo arquivo que o projeto usa)
    header('Location: ../Views/FaçaLoginMyBeatADM.php');
    exit;
}
// --- FIM: Checagem de Acesso ---


require_once __DIR__ . '/../Models/AdminModel.php';

// tenta incluir conector (NÃO modifica o conector)
if (file_exists(__DIR__ . '/../config/conector.php')) {
    require_once __DIR__ . '/../config/conector.php';
}

// Decide qual objeto passar para o model (PDO preferred)
$dbObj = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $dbObj = $pdo;
} elseif (isset($conn) && ($conn instanceof mysqli || get_resource_type($conn) === 'mysql link')) {
    $dbObj = $conn;
}

try {
    $model = new AdminModel($dbObj);
} catch (Exception $e) {
    die("Erro inicializando AdminController: " . $e->getMessage());
}

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_review' && !empty($_POST['review_id'])) {
        $rid = (int)$_POST['review_id'];
        $ok = $model->deleteReview($rid);
        header('Location: ../Views/admin.php?msg=' . ($ok ? 'review_deleted' : 'error'));
        exit;
    }
    if ($action === 'ban_user' && !empty($_POST['user_id'])) {
        $uid = (int)$_POST['user_id'];
        $ok = $model->banUser($uid);
        header('Location: ../Views/admin.php?msg=' . ($ok ? 'user_banned' : 'error'));
        exit;
    }
}

// pega dados e carrega view
$users = $model->allUsers();

// a view espera encontrar $users
require_once __DIR__ . '/../Views/admin.php';

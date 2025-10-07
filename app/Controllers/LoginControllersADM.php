<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Models/LoginADMModels.php';

class LoginControllersADM
{
    private LoginADMModels $loginModel;
    
    private const REDIRECT_SUCCESS = '../../app/Views/admin.php';
    private const REDIRECT_FAILURE = '../../Views/FaçaLoginMyBeatADM.php'; 

    public function __construct()
    {
        try {
            $this->loginModel = new LoginADMModels();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Erro interno do servidor: Conexão com DB falhou.';
            $this->redirect(self::REDIRECT_FAILURE);
            exit;
        }
    }

    public function processaLogin(string $email, string $senha): void
    {
        unset($_SESSION['old_email_adm']);

        $admin = $this->loginModel->findAdminByEmail($email);

        
        if (!$admin) {
            $this->handleFailure('E-mail ou senha inválidos.');
            return;
        }


        if (!isset($admin['hash_senha']) || !password_verify($senha, $admin['hash_senha'])) {
            $this->handleFailure('E-mail ou senha inválidos.');
            return;
        }

    
        if (!isset($admin['administrador']) || (int)$admin['administrador'] !== 1) {
            $this->handleFailure('Acesso negado. Você não é administrador.');
            return;
        }


        $this->handleSuccess($admin);
    }

    private function handleSuccess(array $adminData): void
    {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_id'] = $adminData['id_admin'];
        $_SESSION['admin_nome'] = $adminData['nome_admin'];
        $_SESSION['admin_email'] = $adminData['email'];
        unset($_SESSION['error']);

        $this->redirect(self::REDIRECT_SUCCESS);
    }

    private function handleFailure(string $message): void
    {
        $_SESSION['error'] = $message;

        if (isset($_POST['email'])) {
            $_SESSION['old_email_adm'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        }

        error_log("Falha no login: $message | Email: {$_POST['email']}");

        $this->redirect(self::REDIRECT_FAILURE);
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos.';
        header('Location: ../../Views/FaçaLoginMyBeatADM.php'); 
        exit;
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha']; 

    $controller = new LoginControllersADM();
    $controller->processaLogin($email, $senha);
} else {
    $_SESSION['error'] = 'Acesso inválido.';
    header('Location: ../../Views/FaçaLoginMyBeatADM.php');
    exit;
}

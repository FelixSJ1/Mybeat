<?php
session_start();
require_once '../config/conector.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'] ?? $_POST['password']; 
       
    
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['mensagem_erro'] = "Preencha todos os campos obrigatórios.";
        header("location: ../views/CadastroAdmin.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['mensagem_erro'] = "As senhas não coincidem.";
        header("location: ../views/CadastroAdmin.php");
        exit;
    }

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    
    $sql = "INSERT INTO Administradores (nome_admin, email, hash_senha) VALUES (?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $param_nome, $param_email, $param_senha);
        $param_nome = $username;
        $param_email = $email;
        $param_senha = $hashed_password;

        try {
            mysqli_stmt_execute($stmt);
            $_SESSION['mensagem_sucesso'] = "Administrador cadastrado com sucesso!";
            header("location: ../views/FaçaLoginMyBeatADM.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $_SESSION['mensagem_erro'] = "Este e-mail já está em uso.";
            } else {
                $_SESSION['mensagem_erro'] = "Erro ao cadastrar administrador. Tente novamente.";
            }
            header("location: ../views/CadastroAdmin.php");
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

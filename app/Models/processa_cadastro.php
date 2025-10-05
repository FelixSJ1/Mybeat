<?php

session_start();


require_once '../config/conector.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']);

    
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['mensagem_erro'] = "Por favor, preencha todos os campos obrigatórios.";
        header("location: ../Views/cadastro.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['mensagem_erro'] = "As senhas não coincidem.";
        header("location: ../Views/cadastro.php");
        exit;
    }

    if (!$terms) {
        $_SESSION['mensagem_erro'] = "Você deve concordar com os Termos de Serviço.";
        header("location: ../Views/cadastro.php");
        exit;
    }

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   
    $sql = "INSERT INTO Usuarios (nome_usuario, email, hash_senha) VALUES (?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $sql)) {
       
        mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password);

      
        $param_username = $username;
        $param_email = $email;
        $param_password = $hashed_password;

       
        try {
            mysqli_stmt_execute($stmt);

            
            $_SESSION['mensagem_sucesso'] = "Conta criada com sucesso! Faça o login.";
            header("location: ../views/FaçaLoginMyBeat.php");
            exit;

        } catch (mysqli_sql_exception $e) {
            
            if ($e->getCode() == 1062) {
                $_SESSION['mensagem_erro'] = "Este nome de usuário ou e-mail já está em uso.";
            } else {
                $_SESSION['mensagem_erro'] = "Algo deu errado. Tente novamente mais tarde.";
                
            }
            
            header("location: ../Views/cadastro.php");
            exit;
        }
       

       
        mysqli_stmt_close($stmt);
    }

   
    mysqli_close($conn);
}
?>
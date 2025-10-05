<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Administrador - myBeat</title>
    <link rel="stylesheet" href="../../public/css/AdminCadastro.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="background-glow"></div>

    <div class="container">
        <div class="form-box">
            <div class="logo">
                <span>myBeat</span>
                <img src="../../public/images/LogoF.png" alt="LOGO">
            </div>
            <h2>Criar Conta de Administrador</h2>

            <?php
            if (isset($_SESSION['mensagem_erro'])) {
                echo '<div class="mensagem-erro">' . $_SESSION['mensagem_erro'] . '</div>';
                unset($_SESSION['mensagem_erro']);
            }

            if (isset($_SESSION['mensagem_sucesso'])) {
                echo '<div class="mensagem-sucesso">' . $_SESSION['mensagem_sucesso'] . '</div>';
                unset($_SESSION['mensagem_sucesso']);
            }
            ?>

            <form action="../models/processa_cadastro_admin.php" method="POST">
                <div class="input-group">
                    <input type="text" id="username" name="username" required>
                    <label for="username">Nome de usuário</label>
                </div>

                <div class="input-group">
                    <input type="email" id="email" name="email" required>
                    <label for="email">E-mail</label>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" required>
                    <label for="password">Senha</label>
                </div>

                <div class="input-group">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <label for="confirm_password">Confirmar senha</label>
                </div>

                <button type="submit" class="btn-register">Cadastrar Administrador</button>
            </form>

            <div class="login-link">
                <p>Já é um administrador? <a href="FaçaLoginMyBeat.php">Faça login</a></p>
                <p>Deseja criar uma conta de <a href="cadastro.php">usuário?</a></p>
            </div>
        </div>
    </div>

</body>
</html>

<?php
session_start();

// Configurações do Google OAuth
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', '');

// Gerar URL de autenticação do Google
$google_auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online'
]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - myBeat</title>
    <link rel="stylesheet" href="../../public/css/StyleCadastro.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="background-glow"></div>

    <div class="container">
        <div class="form-box">
            <div class="logo">
                <span>myBeat</span>
                <img src="../../public/images/LogoF.png" alt="LOGO">
            </div>
            <h2>Criar sua conta</h2>

            <?php
            if (isset($_SESSION['mensagem_erro'])) {
                echo '<div class="mensagem-erro">' . $_SESSION['mensagem_erro'] . '</div>';
                unset($_SESSION['mensagem_erro']); 
            }
            ?>

            <form action="../models/processa_cadastro.php" method="POST">
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

                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">Concordo com os <a href="#">Termos de Serviço</a> e <a href="#">Política de Privacidade</a></label>
                </div>

                <button type="submit" class="btn-register">Cadastrar</button>
            </form>

            <div class="login-link">
                <p>Já tem uma conta? <a href="FaçaLoginMyBeat.php">Faça login</a></p>
                <p>Deseja criar uma conta de <a href="CadastroAdmin.php">administrador?</a></p>
            </div>

            <div class="social-login">
                <a href="<?php echo htmlspecialchars($google_auth_url); ?>" class="social-icon"><i class="fab fa-google"></i></a>
            </div>
        </div>
    </div>

</body>
</html>
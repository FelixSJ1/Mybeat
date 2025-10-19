<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Google OAuth
define('GOOGLE_CLIENT_ID', '266253581613-frnaairlmq69n04ieqrdvs2gcpt63mvf.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Mybeat/app/Models/google_callback.php');

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
    <title>myBeat - Login</title>
    <link rel="stylesheet" href="../../public/css/FaçaLoginStyle.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="background-effects"></div>
    <div class="login-container">
        <div class="login-card">
            <header>
                <img src="../../public/images/LogoF.png" alt="Logo myBeat com ondas e estrelas" class="logo1">
                <h2>Faça login</h2>
            </header>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <form action="../../app/Controllers/FaçaLoginMyBeatControllers.php" method="POST" class="login-form">
                <input type="email" name="email" placeholder="E-mail" required value="<?php echo htmlspecialchars($_SESSION['old_email'] ?? '', ENT_QUOTES); ?>">
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit" class="login-button">Entrar</button>
            </form>
            
            <div class="extra-links">
                <p>Não tem conta ainda? <a href="../../app/Views/cadastro.php" class="btn-registrar">Criar conta</a></p>
            </div>
            <div class="extra-links">
                <p>Já é administrador? <a href="../../app/Views/FaçaLoginMyBeatADM.php" class="signup-link">Faça Login</a></p>
            </div>
            <div class="social-login">
                <a href="<?php echo htmlspecialchars($google_auth_url); ?>" class="social-icon google-login">
                    <i class="fab fa-google"></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
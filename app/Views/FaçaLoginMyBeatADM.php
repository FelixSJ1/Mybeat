<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$old_email_adm = $_SESSION['old_email_adm'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['old_email_adm'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>myBeat - Login Administrador</title>

    <link rel="stylesheet" href="../../public/css/FaçaLoginStyleADM.css?v=2">
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

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <div style="margin-top: 1.5rem; text-align: center; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="./face_login_admin.php" 
                   style="color: #EB8046; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center;">
                    <span style="font-size: 1.2rem;">🔐</span>
                    Entrar com Reconhecimento Facial
                </a>
            </div>
            <form action="../../app/Controllers/LoginControllersADM.php" method="POST" class="login-form">
                <input type="email" name="email" placeholder="E-mail" required
                       value="<?php echo htmlspecialchars($old_email_adm, ENT_QUOTES); ?>">
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit" class="login-button">Entrar</button>
            </form>

        </div>
    </div>
</body>
</html>

<?php
session_start();

// Processar logout se solicitado
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    // Destruir todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie da sessão se existir
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para index.php limpo (sem parâmetro logout)
    header('Location: index.php');
    exit();
}

// Verificar se o usuário está logado
$usuario_logado = isset($_SESSION['id_usuario']);
$nome_usuario = $usuario_logado ? ($_SESSION['nome_exibicao'] ?? $_SESSION['nome_usuario'] ?? 'Usuário') : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Beat</title>
    <link rel="stylesheet" href="../public/css/stylePI.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="../public/images/LogoF.png" alt="Mybeat Logo">
                <h1>MyBeat</h1>
            </div>
            <div class="cta-header">
                <?php if ($usuario_logado): ?>
                    <span class="user-welcome">Olá, <?php echo htmlspecialchars($nome_usuario); ?>!</span>
                    <a href="../app/Views/home_usuario.php" class="btn-home">Home</a>
                    <a href="index.php?logout=1" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="../app/Views/FaçaLoginMyBeat.php" class="btn-login">Login</a>
                    <a href="../app/Views/cadastro.php" class="btn-registrar">Criar conta</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Ama música? Você não está sozinho. No MyBeat, nossa missão é que você expresse seu amor da melhor maneira possível.</h2>
                <p>Avalie, registre e descubra novas músicas conosco.</p>
                
            </div>
        </section>

        <section id="como-funciona" class="how-it-works">
            <div class="container">
                <h2>Como Funciona</h2>
                <div class="steps">
                    <div class="step">
                        <img src="../public/images/registrar.png" alt="Ícone de Upload">
                        <h3>Crie sua conta</h3>
                        <p>Faça rapidamente sua conta. Ao apertar o botão "Criar conta"</p>
                    </div>
                    <div class="step2">
                        <img src="../public/images/pesquisar.png" alt="Ícone de Formulário" >
                        <h3>Pesquise seus artistas favoritos</h3>
                        <p>Utilize nossa barra de pesquisa para encontrar as músicas dos seus artistas favoritos.</p>
                    </div>
                    <div class="step">
                        <img src="../public/images/avaliar.png" alt="Ícone de Certificado">
                        <h3>Comece a avaliar!</h3>
                        <p>Escreva reviews, dê notas e converse sobre o que ama!</p>
                    </div>
                </div>
            </div>
        </section>

      
    </main>

    
</body>
</html>
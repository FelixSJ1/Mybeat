<?php
require_once __DIR__ . '/../Controllers/SeguidoresMyBeatControllers.php';
session_start();

$controller = new SeguidoresMyBeatControllers();
$usuarios = [];

if (isset($_GET['termo'])) {
    $usuarios = $controller->buscar();
}

$nomeUsuarioLogado = $_SESSION['nome_exibicao'] ?? 'Meu Perfil';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Usuários</title>
    <link rel="stylesheet" href="../../public/css/SeguidoresStyle.css?v=4">
</head>
<body>
    <nav class="back-to-home-nav">
        <div class="container-nav-back">
            <a href="home_usuario.php" class="btn-voltar-home" title="Voltar para a Página Principal">
                &#9664; Home
            </a>
        </div>
    </nav>
<div class="container">
    
    <div class="search-and-profile-header">
        
        <div class="header-busca">
            <h1>🔍 Buscar Usuários</h1>
            <p>Encontre e siga outros amantes de música</p>
        </div>

        <a href="ShowPerfil.php" class="btn-meu-perfil" title="Ver seu perfil e seguidores">
            Meu Perfil: @<?= htmlspecialchars($nomeUsuarioLogado) ?>
        </a>
    </div>
    <form class="search-box" method="get">
        <div class="search-input-wrapper">
            <span class="search-icon">🔎</span>
            <input type="text" name="termo" class="search-input" placeholder="Digite o nome ou @usuário..." value="<?= htmlspecialchars($_GET['termo'] ?? '') ?>">
            <button type="submit" class="search-btn">Buscar</button>
        </div>
    </form>

    <div class="results-container">
        <?php if (count($usuarios) > 0): ?>
            <?php foreach ($usuarios as $u): ?>
                <?php
                    
                    if ($u['id_usuario'] == $_SESSION['id_usuario']) continue;
                    $jaSegue = $controller->model->jaSegue($_SESSION['id_usuario'], $u['id_usuario']);
                ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <img src="<?= $u['foto_perfil_url'] ?: '../../public/img/default.png' ?>" width="60" height="60" style="border-radius:50%;">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($u['nome_exibicao']) ?></div>
                        <div class="user-username">@<?= htmlspecialchars($u['nome_usuario']) ?></div>
                    </div>
                    <div class="user-actions">
                        <form method="post" action="../Controllers/SeguidoresMyBeatControllers.php" style="display:inline;">
                            <input type="hidden" name="id_seguido" value="<?= $u['id_usuario'] ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            <button type="submit" class="btn <?= $jaSegue ? 'btn-following' : 'btn-follow' ?>">
                                <?= $jaSegue ? '✅ Seguindo' : '➕ Seguir' ?>
                            </button>
                        </form>
                        <a href="ShowPerfil.php?id=<?= $u['id_usuario'] ?>" class="btn btn-profile">👤 Ver Perfil</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum usuário encontrado.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
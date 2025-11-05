<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../Controllers/SeguidoresMyBeatControllers.php'; 

$idUsuarioLogado = $_SESSION['id_usuario'];
$controller = new SeguidoresMyBeatControllers();

$notificacoes = $controller->listarNotificacoesSimples($idUsuarioLogado);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/notificacoes_style.css"> 
    </head>
<body>
    
    <div class="container-notificacoes">
        
        <header class="header-notificacoes">
            <h1>🔔 Novidades</h1>
            <a href="home_usuario.php" class="btn-voltar-home" title="Voltar para a Página Principal">
                &#9664; Voltar para Home
            </a>
        </header>

        <main class="notifications-list">
            <?php if (count($notificacoes) > 0): ?>
                <?php foreach ($notificacoes as $n): ?>
                    
                    <div class="notification-item">
                        
                        <a href="ShowPerfil.php?id=<?= htmlspecialchars($n['id_usuario_acao']) ?>" class="notification-avatar-link">
                            <img src="<?= htmlspecialchars($n['foto_perfil_acao'] ?: '../../public/images/Perfil_Usuario.png') ?>" 
                                 alt="Avatar" class="notification-avatar">
                        </a>
                        
                        <div class="notification-body">
                            <p>
                                <a href="ShowPerfil.php?id=<?= htmlspecialchars($n['id_usuario_acao']) ?>" class="username-link">
                                    <strong><?= htmlspecialchars($n['nome_exibicao_acao']) ?></strong>
                                </a>
                                começou a te seguir.
                                </p>
                            <span class="notification-time">
                                Seguiu em: <?= date('H:i:s, d/m/Y', strtotime($n['data_hora'])) ?>
                            </span>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-message">Nenhuma conexão de seguidores encontrada. Comece a interagir!</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
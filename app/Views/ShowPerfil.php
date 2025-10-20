<?php
session_start();

require_once __DIR__ . '/../Controllers/SeguidoresMyBeatControllers.php';
$controller = new SeguidoresMyBeatControllers();

$idUsuario = $_GET['id'] ?? 0;
$seguidores = $controller->listarSeguidores($idUsuario);
$seguindo = $controller->listarSeguindo($idUsuario);

$nomeUsuario = !empty($_SESSION['nome_exibicao']) ? $_SESSION['nome_exibicao'] : 'Usuário';
$fotoUsuario = !empty($_SESSION['foto_perfil_url']) ? $_SESSION['foto_perfil_url'] : '../../public/img/avatar_padrao.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/PerfilStyle.css?v=4">
</head>
<body>
    <div class="container">
      
        <div class="profile-header">
            <div class="avatar-large">
                <img src="<?= htmlspecialchars($fotoUsuario) ?>" alt="Foto de perfil">
            </div>
            <h1><?= htmlspecialchars($nomeUsuario) ?></h1>
            <p class="username">@<?= htmlspecialchars($nomeUsuario) ?></p>
        </div>

        <div class="profile-content">
            <div class="coluna-lista">
                <h2>👥 Seguidores</h2>
                <ul class="lista-usuarios">
                    <?php if (!empty($seguidores)): ?>
                        <?php foreach ($seguidores as $s): ?>
                            <li>@<?= htmlspecialchars($s['nome_usuario']) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="no-results">Nenhum seguidor encontrado</li>
                    <?php endif; ?>
                </ul>

                <h2>➡️ Seguindo</h2>
                <ul class="lista-usuarios">
                    <?php if (!empty($seguindo)): ?>
                        <?php foreach ($seguindo as $s): ?>
                            <li>@<?= htmlspecialchars($s['nome_usuario']) ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="no-results">Não está seguindo ninguém</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>

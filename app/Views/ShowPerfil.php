<?php
session_start();
require_once __DIR__ . '/../Controllers/SeguidoresMyBeatControllers.php';

$controller = new SeguidoresMyBeatControllers();

$idUsuario = $_GET['id'] ?? ($_SESSION['id_usuario'] ?? 0);

if ($idUsuario == 0) {
    header('Location: ../Views/Login.php');
    exit;
}

$seguidores = $controller->listarSeguidores($idUsuario);
$seguindo = $controller->listarSeguindo($idUsuario);

$nomeUsuario = 'Usuário'; 
$fotoUsuario = '../../public/img/Perfil_Usuario.png'; 

$dadosPerfil = $controller->buscarDadosUsuarioPorId($idUsuario); 
if ($dadosPerfil) {
    $nomeUsuario = $dadosPerfil['nome_exibicao'] ?? $dadosPerfil['nome_usuario'] ?? $nomeUsuario;
    $fotoUsuario = $dadosPerfil['foto_perfil_url'] ?? $fotoUsuario;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/PerfilStyle.css?v=6">
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
                            <li class="usuario-item">
                                <div class="info-usuario">
                                    <img 
                                        src="<?= !empty($s['foto_perfil_url']) ? htmlspecialchars($s['foto_perfil_url']) : '../../public/img/Perfil_Usuario.png' ?>" 
                                        alt="Foto de <?= htmlspecialchars($s['nome_exibicao'] ?? $s['nome_usuario'] ?? 'usuário') ?>" 
                                        class="foto-mini">
                                    <a href="ShowPerfil.php?id=<?= htmlspecialchars($s['id_usuario'] ?? $s['id']) ?>" class="nome-link">
                                        @<?= htmlspecialchars($s['nome_usuario'] ?? $s['nome_exibicao'] ?? 'usuario') ?>
                                    </a>
                                </div>
                                <a href="ShowPerfil.php?id=<?= htmlspecialchars($s['id_usuario'] ?? $s['id']) ?>" class="botao-ver">
                                    Ver perfil
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="no-results">Nenhum seguidor encontrado</li>
                    <?php endif; ?>
                </ul>

                <h2>➡️ Seguindo</h2>
                <ul class="lista-usuarios">
                    <?php if (!empty($seguindo)): ?>
                        <?php foreach ($seguindo as $s): ?>
                            <li class="usuario-item">
                                <div class="info-usuario">
                                    <img 
                                        src="<?= !empty($s['foto_perfil_url']) ? htmlspecialchars($s['foto_perfil_url']) : '../../public/img/Perfil_Usuario.png' ?>" 
                                        alt="Foto de <?= htmlspecialchars($s['nome_exibicao'] ?? $s['nome_usuario'] ?? 'usuário') ?>" 
                                        class="foto-mini">
                                    <a href="ShowPerfil.php?id=<?= htmlspecialchars($s['id_usuario'] ?? $s['id']) ?>" class="nome-link">
                                        @<?= htmlspecialchars($s['nome_usuario'] ?? $s['nome_exibicao'] ?? 'usuario') ?>
                                    </a>
                                </div>
                                <a href="ShowPerfil.php?id=<?= htmlspecialchars($s['id_usuario'] ?? $s['id']) ?>" class="botao-ver">
                                    Ver perfil
                                </a>
                            </li>
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

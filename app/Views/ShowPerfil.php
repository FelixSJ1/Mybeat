<?php

require_once __DIR__ . '/../Controllers/SeguidoresMyBeatControllers.php';
$controller = new SeguidoresMyBeatControllers();

$idUsuario = $_GET['id'] ?? 0;
$seguidores = $controller->listarSeguidores($idUsuario);
$seguindo = $controller->listarSeguindo($idUsuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/PerfilStyle.css">
</head>
<body>
<h2>👥 Seguidores</h2>
<ul>
<?php foreach ($seguidores as $s): ?>
    <li>@<?= htmlspecialchars($s['nome_usuario']) ?></li>
<?php endforeach; ?>
</ul>

<h2>➡️ Seguindo</h2>
<ul>
<?php foreach ($seguindo as $s): ?>
    <li>@<?= htmlspecialchars($s['nome_usuario']) ?></li>
<?php endforeach; ?>
</ul>

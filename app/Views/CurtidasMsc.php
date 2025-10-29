<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php'); 
    exit();
}

$id_usuario_logado = $_SESSION['id_usuario'];
$nome_exibicao_logado = $_SESSION['nome_exibicao'] ?? 'Usuário';
$foto_perfil_logado = $_SESSION['foto_perfil'] ?? '/Mybeat/public/images/Perfil_Usuario.png';

$musicasCurtidas = [
    [
        'id_musica' => 101,
        'titulo_musica' => 'Blinding Lights',
        'nome_artista' => 'The Weeknd',
        'id_album' => 1,
        'titulo_album' => 'After Hours',
        'capa_album_url' => 'https://placehold.co/40x40/7c3aed/FFF?text=BL', 
        'duracao_segundos' => 200 
    ],
    [
        'id_musica' => 102,
        'titulo_musica' => 'Levitating',
        'nome_artista' => 'Dua Lipa',
        'id_album' => 2,
        'titulo_album' => 'Future Nostalgia',
        'capa_album_url' => 'https://placehold.co/40x40/f97316/FFF?text=LV', 
        'duracao_segundos' => 203 
    ],
    [
        'id_musica' => 103,
        'titulo_musica' => 'good 4 u',
        'nome_artista' => 'Olivia Rodrigo',
        'id_album' => 3,
        'titulo_album' => 'SOUR',
        'capa_album_url' => 'https://placehold.co/40x40/503E8A/FFF?text=G4U', 
        'duracao_segundos' => 178 
    ],
    [
        'id_musica' => 104,
        'titulo_musica' => 'As It Was',
        'nome_artista' => 'Harry Styles',
        'id_album' => 4,
        'titulo_album' => 'Harry\'s House',
        'capa_album_url' => 'https://placehold.co/40x40/F59E0B/FFF?text=AIW', 
        'duracao_segundos' => 167 
    ],
    [
        'id_musica' => 105,
        'titulo_musica' => 'Another Love',
        'nome_artista' => 'Tom Odell',
        'id_album' => 5,
        'titulo_album' => 'Long Way Down',
        'capa_album_url' => 'https://placehold.co/40x40/333/FFF?text=AL', 
        'duracao_segundos' => 244 
    ]
];

$totalMusicasCurtidas = count($musicasCurtidas);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Músicas Curtidas - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/CurtidasMsc.css?v=1"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="liked-songs-page">
        <header> 
            <div class="logo">
                <a href="home_usuario.php" class="logo-link" title="Ir para a Home">
                    <img src="../../public/images/LogoF.png" alt="MyBeat Logo"> 
                <span class="site-title">MyBeat</span> 
            </a>
        </div>

        <div class="user-circle" title="Meu Perfil">
            <a href="perfilUsuario.php" style="display: block; width: 100%; height: 100%;">
                <img src="<?= htmlspecialchars($foto_perfil_logado) ?>" alt="Foto de Perfil">
            </a>
        </div>
    </header>
        <header class="playlist-header">
            <div class="playlist-cover">
                <i class="fas fa-heart"></i> 
            </div>
            <div class="playlist-info">
                <span class="playlist-type">PLAYLIST</span>
                <h1>Músicas Curtidas</h1>
                <div class="playlist-user">
                    <img src="<?= htmlspecialchars($foto_perfil_logado) ?>" alt="Foto de Perfil" class="user-avatar-small">
                    <span><?= htmlspecialchars($nome_exibicao_logado) ?></span>
                    <span class="dot">•</span>
                    <span><?= $totalMusicasCurtidas ?> músicas</span>
                </div>
            </div>
        </header>

        <section class="songs-list-section">
            <?php if ($totalMusicasCurtidas > 0): ?>
                <table class="liked-songs-table">
                    <thead>
                        <tr>
                            <th class="col-index">#</th>
                            <th class="col-title">Título</th>
                            <th class="col-album">Álbum</th>
                            <th class="col-like"><i class="far fa-heart"></i></th> 
                            <th class="col-duration"><i class="far fa-clock"></i></th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($musicasCurtidas as $index => $musica): ?>
                            <tr class="song-row">
                                <td class="col-index"><?= $index + 1 ?></td>
                                <td class="col-title">
                                    <img src="<?= htmlspecialchars($musica['capa_album_url'] ?? '/Mybeat/public/images/LogoF.png') ?>" alt="Capa" class="song-cover-small">
                                    <div class="song-details">
                                        <span class="song-title"><?= htmlspecialchars($musica['titulo_musica']) ?></span>
                                        <span class="song-artist"><?= htmlspecialchars($musica['nome_artista'] ?? 'Artista Desconhecido') ?></span>
                                    </div>
                                </td>
                                <td class="col-album"><?= htmlspecialchars($musica['titulo_album'] ?? 'Álbum Desconhecido') ?></td>
                                <td class="col-like">
                                    <form method="POST" action="../Controllers/CurtidasMusicaController.php" class="like-form">
                                        <input type="hidden" name="id_musica" value="<?= $musica['id_musica'] ?>">
                                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                        <button type="submit" name="action" value="toggle" class="like-button liked" title="Remover dos Curtidos">
                                             ❤️ 
                                        </button>
                                    </form>
                                </td>
                                <td class="col-duration">
                                     <?= !empty($musica['duracao_segundos']) ? gmdate("i:s", (int)$musica['duracao_segundos']) : '--:--' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-heart-crack empty-icon"></i>
                    <h2>Nenhuma música curtida ainda</h2>
                    <p>Clique no coração (♡) ao lado das suas músicas favoritas para adicioná-las aqui.</p>
                    <a href="home_usuario.php" class="btn-explorar">Explorar Músicas</a>
                </div>
            <?php endif; ?>
        </section>

    </div> 
</body>
</html>


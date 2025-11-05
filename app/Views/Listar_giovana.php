
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FRONT CONTROLLER embutido no listar_giovana.php

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Controllers/ControllersG.php';
require_once __DIR__ . '/../Models/ModelsG.php';
require_once __DIR__ . '/../Controllers/painelmusiccontroller.php';
require_once __DIR__ . '/../Controllers/playlistC.php';
require_once __DIR__ . '/../Models/playlistM.php';
require_once __DIR__ . '/../Controllers/AvaliacaoUController.php';//aquiiiiiiiiiiiiiiiiii

$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action'] ?? 'index';

switch ($controller) {
    case 'home':     $c = new HomeController($conn); break;
    case 'album':    $c = new AlbumController($conn); break;
    case 'musica':   $c = new MusicaController($conn); break;
    case 'avaliacao':$c = new AvaliacaoController($conn); break;
    case 'avaliacaoUsuario': $c = new AvaliacaoUController($conn); break;
    case 'painelmusic': $c = new PainelMusicController($conn); break;
    case 'playlist': $c = new PlaylistController($conn); break; // <-- adicionado
    default: die("Controller inválido");
}

// home/index → renderiza view embutida
if ($controller === 'home' && $action === 'index') {
    if (!isset($_SESSION['id_usuario'])) { 
        header('Location: FaçaLoginMyBeat.php'); 
        exit();
    }
    $id_usuario_logado = (int)$_SESSION['id_usuario'];
    $playlistModel = new PlaylistModel($conn); 
    
    // Pega o ID da playlist "Músicas Curtidas" (ou cria uma se não existir)
    $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario_logado);
    $q        = $_GET['q'] ?? '';
    $albuns   = $c->getAlbums($q);
    $musicas  = $c->getMusicas($q);
?>

    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>MyBeat - Início</title>
        <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../../public/css/PaginaListarGiovana.css">
    </head>
    <body>
    <header>
        <div class="logo">
            <a href="listar_giovana.php"><img src="../../public/images/LogoF.png" alt="Logo MyBeat"></a>
        </div>
        <div class="search-bar">
            <form method="GET" action="listar_giovana.php">
                <input type="hidden" name="controller" value="home">
                <input type="hidden" name="action" value="index">
                <input type="text" name="q" placeholder="Buscar músicas ou álbuns..." value="<?php echo htmlspecialchars($q); ?>">
            </form>
        </div>
    </header>

    <main>
        <section class="singles">
            <h2>Músicas</h2>
            <ul>
                <?php if ($musicas && $musicas->num_rows > 0): ?>
                    <?php while ($row = $musicas->fetch_assoc()): ?>
                        <li>
                            <div class="cover">
                                <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo $row['id_musica']; ?>">
                                    <?php if (!empty($row['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['capa_album_url']); ?>" alt="Capa do álbum">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="info">
                                <p>
                                    <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo $row['id_musica']; ?>" class="titulo-musica">
                                        <?php echo htmlspecialchars($row['titulo_musica']); ?>
                                    </a>
                                </p>
                                <p><strong>Álbum:</strong>
                                    <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo $row['id_album']; ?>" class="titulo-album">
                                        <?php echo htmlspecialchars($row['titulo_album']); ?>
                                    </a>
                                </p>
                                <p><strong>Artista:</strong> <?php echo htmlspecialchars($row['nome_artista']); ?></p>
                                <p><strong>Duração:</strong> <?php echo gmdate("i:s", (int)$row['duracao_segundos']); ?></p>
                                <p><strong>Faixa nº:</strong> <?php echo htmlspecialchars($row['numero_faixa']); ?></p>
                            </div>

                            <div class="like-action">
                                <?php
                                if (isset($row['id_musica']) && isset($playlistModel) && isset($likedPlaylistId)):
                                    // 2. Verifica se a música JÁ ESTÁ na playlist
                                    $curtido = $playlistModel->isTrackInPlaylist($likedPlaylistId, (int)$row['id_musica']);

                                    $action = $curtido ? 'unlike_track' : 'like_track';
                                ?>
                                    <!-- 4. O Formulário aponta para o CurtidaC.php (LikeController) -->
                                    <form method="POST" action="../Controllers/CurtidaC.php" class="like-form">
                                        <input type="hidden" name="id_musica" value="<?= $row['id_musica'] ?>">
                                        <input type="hidden" name="action" value="<?= $action ?>">
                                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                
                                        <button type="submit" class="like-button <?= $curtido ? 'liked' : '' ?>" title="<?= $curtido ? 'Descurtir' : 'Curtir' ?>">
                                            <?= $curtido ? '❤️' : '♡' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>Nenhuma música encontrada.</li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="albuns">
            <h2>Álbuns</h2>
            <ul>
                <?php if ($albuns && $albuns->num_rows > 0): ?>
                    <?php while ($row = $albuns->fetch_assoc()): ?>
                        <li>
                            <div class="cover">
                                <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo $row['id_album']; ?>">
                                    <?php if (!empty($row['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['capa_album_url']); ?>" alt="Capa do álbum">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="info">
                                <p>
                                    <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo $row['id_album']; ?>" class="titulo-album">
                                        <?php echo htmlspecialchars($row['titulo']); ?>
                                    </a>
                                </p>
                                <p><strong>Artista:</strong> <?php echo htmlspecialchars($row['nome_artista']); ?></p>
                                <p><strong>Lançamento:</strong> <?php echo htmlspecialchars($row['data_lancamento']); ?></p>
                                <p><strong>Gênero:</strong> <?php echo htmlspecialchars($row['genero']); ?></p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($row['tipo']); ?></p>
                            </div>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>Nenhum álbum encontrado.</li>
                <?php endif; ?>
            </ul>
        </section>
    </main>
    </body>
    </html>
    <?php
    exit;
}

// outras ações
if (method_exists($c, $action)) {
    $c->$action();
} else {
    die("Ação inválida");
}
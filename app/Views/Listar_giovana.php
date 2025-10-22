<?php
// listar_giovana.php - Front controller refatorado

// configurações / conexões
require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Controllers/ControllersG.php';
require_once __DIR__ . '/../Models/ModelsG.php';
require_once __DIR__ . '/../Controllers/playlistC.php';
require_once __DIR__ . '/../Models/playlistM.php';
// rota (controller / action) via GET
$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action'] ?? 'index';

switch ($controller) {
    case 'home':     $c = new HomeController($conn); break;
    case 'album':    $c = new AlbumController($conn); break;
    case 'musica':   $c = new MusicaController($conn); break;
    case 'avaliacao':$c = new AvaliacaoController($conn); break;
    case 'avaliacaoUsuario': $c = new AvaliacaoUsuarioController($conn); break;
    case 'playlist': $c = new PlaylistController($conn); break; // <-- adicionado
    default:
        http_response_code(404);
        die("Controller inválido");
}

// Se for a rota home/index renderizamos diretamente a view de listagem (início)
if ($controller === 'home' && $action === 'index') {
    $q = trim($_GET['q'] ?? '');
    // supondo que os métodos abaixo existem no HomeController e retornam mysqli_result
    $albuns  = $c->getAlbums($q);
    $musicas = $c->getMusicas($q);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <input type="text" name="q" placeholder="Buscar músicas ou álbuns..." value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>">
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
                                <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo (int)$row['id_musica']; ?>">
                                    <?php if (!empty($row['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['capa_album_url'], ENT_QUOTES); ?>" alt="Capa do álbum">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="info">
                                <p>
                                    <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo (int)$row['id_musica']; ?>" class="titulo-musica">
                                        <?php echo htmlspecialchars($row['titulo_musica'], ENT_QUOTES); ?>
                                    </a>
                                </p>
                                <p><strong>Álbum:</strong>
                                    <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo (int)$row['id_album']; ?>" class="titulo-album">
                                        <?php echo htmlspecialchars($row['titulo_album'], ENT_QUOTES); ?>
                                    </a>
                                </p>
                                <p><strong>Artista:</strong> <?php echo htmlspecialchars($row['nome_artista'], ENT_QUOTES); ?></p>
                                <p><strong>Duração:</strong> <?php echo gmdate("i:s", (int)$row['duracao_segundos']); ?></p>
                                <p><strong>Faixa nº:</strong> <?php echo htmlspecialchars($row['numero_faixa'], ENT_QUOTES); ?></p>
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
                                <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo (int)$row['id_album']; ?>">
                                    <?php if (!empty($row['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['capa_album_url'], ENT_QUOTES); ?>" alt="Capa do álbum">
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="info">
                                <p>
                                    <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo (int)$row['id_album']; ?>" class="titulo-album">
                                        <?php echo htmlspecialchars($row['titulo'], ENT_QUOTES); ?>
                                    </a>
                                </p>
                                <p><strong>Artista:</strong> <?php echo htmlspecialchars($row['nome_artista'], ENT_QUOTES); ?></p>
                                <p><strong>Lançamento:</strong> <?php echo htmlspecialchars($row['data_lancamento'], ENT_QUOTES); ?></p>
                                <p><strong>Gênero:</strong> <?php echo htmlspecialchars($row['genero'], ENT_QUOTES); ?></p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($row['tipo'], ENT_QUOTES); ?></p>
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

// Para outras rotas, chamamos o método do controller (se existir)
if (method_exists($c, $action)) {
    // se os métodos renderizam views por conta própria, eles farão echo/require das views
    $c->$action();
} else {
    http_response_code(404);
    die("Ação inválida");
}

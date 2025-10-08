<?php

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Controllers/ControllersG.php';
require_once __DIR__ . '/../Models/ModelsG.php';

$albumModel = class_exists('Album') ? new Album($conn) : null;
$musicaModel = class_exists('Musica') ? new Musica($conn) : null;
$homeController = class_exists('HomeController') ? new HomeController($conn) : null;

$q = $_GET['q'] ?? '';
$genre = $_GET['genre'] ?? '';

$generos = [];
if ($albumModel) {
    $resG = $albumModel->getGeneros();
    if ($resG) {
        while ($g = $resG->fetch_assoc()) {
            $generos[] = $g['genero'];
        }
        $resG->free();
    }
}

$albums = [];
if ($genre !== '') {
    if ($albumModel) {
        $resA = $albumModel->getByGenero($genre, $q);
        if ($resA) {
            while ($a = $resA->fetch_assoc()) {
                $albums[] = $a;
            }
            $resA->free();
        }
    }
} else {
    if ($homeController) {
        $resA = $homeController->getAlbums($q);
    } elseif ($albumModel) {
        $resA = $albumModel->getAll($q);
    } else {
        $resA = false;
    }
    if ($resA) {
        while ($a = $resA->fetch_assoc()) {
            $albums[] = $a;
        }
        $resA->free();
    }
}

$musicas = [];
if ($genre !== '') {
    if ($albumModel) {
        foreach ($albums as $al) {
            $resM = $albumModel->getMusicas((int)$al['id_album']);
            if ($resM) {
                while ($m = $resM->fetch_assoc()) {
                    $m['titulo_album'] = $al['titulo'];
                    $m['capa_album_url'] = $al['capa_album_url'];
                    $m['artista'] = $al['nome_artista'] ?? '';
                    $musicas[] = $m;
                }
                $resM->free();
            }
        }
    }
} else {
    if ($homeController) {
        $resM = $homeController->getMusicas($q);
    } elseif ($musicaModel) {
        $resM = $musicaModel->getAll($q);
    } else {
        $resM = false;
    }
    if ($resM) {
        while ($m = $resM->fetch_assoc()) {
            if (!isset($m['artista']) && isset($m['nome_artista'])) $m['artista'] = $m['nome_artista'];
            $musicas[] = $m;
        }
        $resM->free();
    }
}

function build_search_query($q) {
    return $q !== '' ? '&q=' . urlencode($q) : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>MyBeat - Home do Usuário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
   <link href="/Mybeat/public/css/home_usuario.css" rel="stylesheet"> 
</head>
<body>
<header>
    <div class="logo">
        <a href="home_usuario.php" class="logo-link">
            <img src="../../public/images/LogoF.png" alt="MyBeat" class="logo-img">
            <span class="site-title">My Beat</span>
        </a>
    </div>

    <div class="search-bar">
        <form id="searchForm" method="GET" action="home_usuario.php">
            <input type="text" name="q" placeholder="Buscar músicas, álbuns ou artistas..." value="<?php echo htmlspecialchars($q); ?>">
            <input type="hidden" name="genre" id="hiddenGenre" value="<?php echo htmlspecialchars($genre); ?>">
        </form>
    </div>

    <div class="user-circle" title="Meu Perfil">
        <img src="../../public/images/Perfil_Usuario.png" alt="Usuário">
    </div>

</header>

<div class="filter-bar">
    <div class="filter-item">
        <label for="genreSelect">Gênero:</label>

        <select id="genreSelect" name="genre">
            
            <option value="" <?php echo ($genre === '') ? 'selected' : ''; ?>>Todos</option>
            <?php foreach ($generos as $g): ?>
                <option value="<?php echo htmlspecialchars($g); ?>" <?php echo ($g === $genre) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="spacer"></div>

    <div class="filter-info">
        <?php if ($genre !== ''): ?>
            <span>Filtro: <strong><?php echo htmlspecialchars($genre); ?></strong></span>
        <?php endif; ?>
    </div>
</div>

<main>
    <section class="carrossel-section">
        <h2>Álbuns</h2>
        <div class="carousel-wrap centered">
            <button class="carousel-btn left" id="prevBtn" aria-label="Anterior">&lt;</button>

            <div class="carousel" id="carousel">
                <?php if (count($albums) === 0): ?>
                    <div class="empty">Nenhum álbum encontrado.</div>
                <?php else: ?>
                    <?php foreach ($albums as $al): ?>
                        <div class="album-card card">
                            <a href="/Mybeat/app/Views/listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$al['id_album']; ?>" class="cover-link">
                                <div class="cover">
                                    <?php if (!empty($al['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($al['capa_album_url']); ?>" alt="<?php echo htmlspecialchars($al['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="no-cover">Sem capa</div>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <div class="album-info">
                                <h3>
                                    <a href="../../avaliacao.php?id_album=<?php echo (int)$al['id_album']; ?>">
                                        <?php echo htmlspecialchars($al['titulo']); ?>
                                    </a>
                                </h3>
                                <p class="small">por <?php echo htmlspecialchars($al['nome_artista'] ?? '—'); ?></p>
                                <p class="meta"><?php echo htmlspecialchars($al['genero'] ?? '—'); ?> · <?php echo htmlspecialchars($al['data_lancamento'] ?? '—'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button class="carousel-btn right" id="nextBtn" aria-label="Próximo">&gt;</button>
        </div>
    </section>

    <section class="musicas-section">
        <h2>Músicas</h2>
        <div class="musicas">
            <ul>
                <?php if (count($musicas) === 0): ?>
                    <li>Nenhuma música encontrada.</li>
                <?php else: ?>
                    <?php foreach ($musicas as $m): ?>
                        <li class="musica-item">
                            <div class="cover small-cover">
                                <a href="/Mybeat/index.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>">
                                    <?php if (!empty($m['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['capa_album_url']); ?>" alt="<?php echo htmlspecialchars($m['titulo'] ?? $m['titulo_musica']); ?>">
                                    <?php else: ?>
                                        <div class="no-cover">Sem capa</div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="info">
                                <p>
                                    <a href="/Mybeat/index.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>" class="titulo-musica">
                                        <?php echo htmlspecialchars($m['titulo'] ?? $m['titulo_musica']); ?>
                                    </a>
                                </p>
                                <p><strong>Álbum:</strong>
                                    <a href="/Mybeat/index.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>" class="titulo-musica">
                                        <?php echo htmlspecialchars($m['titulo_album'] ?? '—'); ?>
                                    </a>
                                </p>
                                <p><strong>Artista:</strong> <?php echo htmlspecialchars($m['artista'] ?? $m['nome_artista'] ?? '—'); ?></p>
                                <p class="meta">
                                    <span class="faixa-numero">Faixa: <?php echo htmlspecialchars($m['numero_faixa'] ?? '—'); ?></span>
                                    <?php if (!empty($m['duracao_segundos'])): ?>
                                        <span class="duracao"><?php echo gmdate("i:s", (int)$m['duracao_segundos']); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </section>
</main>

<script>
document.getElementById('genreSelect').addEventListener('change', function() {
    document.getElementById('hiddenGenre').value = this.value;
    document.getElementById('searchForm').submit();
});

(function() {
    const carousel = document.getElementById('carousel');
    const prev = document.getElementById('prevBtn');
    const next = document.getElementById('nextBtn');
    if (!carousel) return;

    prev.addEventListener('click', function() {
        carousel.scrollBy({ left: -carousel.clientWidth * 0.7, behavior: 'smooth' });
    });
    next.addEventListener('click', function() {
        carousel.scrollBy({ left: carousel.clientWidth * 0.7, behavior: 'smooth' });
    });

    window.addEventListener('load', function() {
        setTimeout(function() { carousel.scrollLeft = 0; }, 80);
    });
})();
</script>
</body>
</html>

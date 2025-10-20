<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Controllers/ControllersG.php';
require_once __DIR__ . '/../Models/ModelsG.php';

// Verificar se é o primeiro acesso (biografia vazia)
try {
    $stmt = $conn->prepare("SELECT biografia, foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $_SESSION['id_usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    // Se biografia estiver vazia, é primeiro acesso
    if ($usuario && (empty($usuario['biografia']) || $usuario['biografia'] === null)) {
        header('Location: perfilUsuario.php?primeiro_acesso=1');
        exit();
    }
    
    // Armazenar foto do perfil na sessão para usar no header
    $foto_perfil = $usuario['foto_perfil_url'] ?? '../../public/images/Perfil_Usuario.png';
    
} catch (Exception $e) {
    // Em caso de erro, continua normalmente
    $foto_perfil = '../../public/images/Perfil_Usuario.png';
}

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
    <link href="../../public/css/home_usuario.css" rel="stylesheet">
</head>
<body>

<?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
    <div style="position: fixed; top: 20px; right: 20px; background: rgba(138, 43, 226, 0.9); color: white; padding: 15px 20px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
        <?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); ?>
    </div>
    <?php unset($_SESSION['mensagem_sucesso']); ?>
<?php endif; ?>

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

    <?php 
    // Verifica se o tipo de login da session é admin
    if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): 
    ?>
        <a href="admin.php" class="admin-link-btn" title="Acessar Painel Administrativo">
            Painel Admin
        </a>
    <?php endif; ?>

    <?php 
    // Verifica o tipo de login da session, se for user normal mostra o botão de minhas avaliações
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true): 
    ?>
        <a href="historico_avaliacoes.php" class="minhas-avaliacoes-btn">Minhas Avaliações</a>
    <?php endif; ?>
    <a href="logout.php" class="logout-btn">Sair</a>

    <div class="user-circle" title="Meu Perfil">
        <a href="perfilUsuario.php" style="display: block; width: 100%; height: 100%;">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Usuário">
        </a>
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
                            <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$al['id_album']; ?>" class="cover-link">
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
                                    <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$al['id_album']; ?>">
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
                                <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>">
                                    <?php if (!empty($m['capa_album_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($m['capa_album_url']); ?>" alt="<?php echo htmlspecialchars($m['titulo'] ?? $m['titulo_musica']); ?>">
                                    <?php else: ?>
                                        <div class="no-cover">Sem capa</div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="info">
                                <p>
                                    <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>" class="titulo-musica">
                                        <?php echo htmlspecialchars($m['titulo'] ?? $m['titulo_musica']); ?>
                                    </a>
                                </p>
                                <p><strong>Álbum:</strong>
                                    <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$m['id_album']; ?>" class="titulo-musica">
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
    
    
    setTimeout(function() {
        const msg = document.querySelector('[style*="position: fixed"]');
        if (msg) {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s';
            setTimeout(function() { msg.remove(); }, 500);
        }
    }, 5000);
})();
</script>
</body>
</html>
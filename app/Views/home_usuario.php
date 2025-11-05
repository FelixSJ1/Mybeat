<?php
session_start();

$has_temp_notifications = isset($_SESSION['notificacoes_temporarias']) && count($_SESSION['notificacoes_temporarias']) > 0;
// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Controllers/ControllersG.php';
require_once __DIR__ . '/../Models/ModelsG.php';
require_once __DIR__ . '/../Models/playlistM.php';
require_once __DIR__ . '/../Models/HomeExtrasModel.php';

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

$homeController = class_exists('HomeController') ? new HomeController($conn) : null;

$id_usuario_logado = (int)$_SESSION['id_usuario'];
$playlistModel = new PlaylistModel($conn);

$likedPlaylistId = null;
if ($playlistModel) {
    $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario_logado);
}

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
    
    <link rel="stylesheet" href="/Mybeat/public/css/acessibilidade.css">
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
            <div style="position: relative;">
                <input type="text" name="q" id="searchInput" placeholder="Buscar" value="<?php echo htmlspecialchars($q); ?>">
                
                </div>
            <input type="hidden" name="genre" id="hiddenGenre" value="<?php echo htmlspecialchars($genre); ?>">
        </form>
    </div>

    <?php 
    // Verifica se o tipo de login da session é admin
    if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): 
    ?>
        <a href="admin.php" class="minhas-avaliacoes-btn" title="Acessar Painel Administrativo">
            Painel Admin
        </a>
    <?php endif; ?>

    <?php 
    // Verifica o tipo de login da session, se for user normal mostra o botão de minhas avaliações
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true): 
    ?>
        
        
    <?php endif; ?>
    

    <div class="interaction-icons-container"> 
        
        <a href="notificacoes_seguidores.php" class="notification-button" title="Notificações de Seguidores">
            🔔
        </a>

    </div>
    
    <div class="user-circle" title="Meu Perfil">
        <a id="profileMenuToggle"  href="#"  style="display: block; width: 100%; height: 100%;">
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

    
<?php
$extras = isset($extras) ? $extras : new HomeExtras($conn);
$popularesSemana = $extras->getPopularesSemana(12);
?>
<?php if (!empty($popularesSemana) && count($popularesSemana) > 0): ?>
<section class="section">
    <h2 class="section-title">Populares da semana</h2>
    <div class="carousel-wrap">
        <button class="carousel-btn left" aria-label="Anterior" id="prevWeekBtn">&lt;</button>
        <div class="carousel" role="list">
            <?php foreach ($popularesSemana as $al): ?>
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
        </div>
        <button class="carousel-btn right" id="nextWeekBtn" aria-label="Próximo">&gt;</button>
    </div>
</section>
<?php endif; ?>
<?php
$evaluationBlock = null;
if (isset($_SESSION['id_usuario'])) {
    $evaluationBlock = $extras->findEvaluationWithSimilar((int)$_SESSION['id_usuario'], 12, 1, 12);
}
?>
<?php if (!empty($evaluationBlock) && !empty($evaluationBlock['similar'])): ?>
    <?php $userLast = $evaluationBlock['evaluation']; $similarByGenre = $evaluationBlock['similar']; ?>
    <section class="section">
        <h2 class="section-title">Porque você avaliou <?php echo htmlspecialchars($userLast['titulo']); ?>:</h2>
        <div class="carousel-wrap">
            <button class="carousel-btn left" aria-label="Anterior" id="prevWhyBtn">&lt;</button>
            <div class="carousel" role="list">
                <?php foreach ($similarByGenre as $al): ?>
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
            </div>
            <button class="carousel-btn right" id="nextWhyBtn" aria-label="Próximo">&gt;</button>
        </div>
    </section>
<?php endif; ?>
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
                            <div class="like-action">
                                <?php
                                if (isset($m['id_musica']) && $playlistModel && $likedPlaylistId):
            
                                    // 2. Verifica se a música JÁ ESTÁ na playlist de curtidas
                                    $curtido = $playlistModel->isTrackInPlaylist($likedPlaylistId, (int)$m['id_musica']);

                                    $action = $curtido ? 'unlike_track' : 'like_track';
                                ?>
                                    <form method="POST" action="../Controllers/CurtidaC.php" class="like-form">
                                        <input type="hidden" name="id_musica" value="<?= $m['id_musica'] ?>">
                                        <input type="hidden" name="action" value="<?= $action ?>">
                                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                
                                        <button type="submit" class="like-button <?= $curtido ? 'liked' : '' ?>" title="<?= $curtido ? 'Descurtir' : 'Curtir' ?>">
                                            <?= $curtido ? '❤️' : '♡' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </section>
</main>

<script>
// Seleção de gênero
document.getElementById('genreSelect').addEventListener('change', function() {
    document.getElementById('hiddenGenre').value = this.value;
    document.getElementById('searchForm').submit();
});

// Carrossel
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
    
    // Remover mensagem de sucesso após 5 segundos
    setTimeout(function() {
        const msg = document.querySelector('[style*="position: fixed"]');
        if (msg) {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s';
            setTimeout(function() { msg.remove(); }, 500);
        }
    }, 5000);
})();

// ============================================
// MODIFICAÇÃO: Bloco "PESQUISA POR VOZ" removido
// ============================================

</script>

<?php
// Sidebar data (robust) - ensures variables match perfilUsuario.php expectations
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

// Ensure $conn available
if (!isset($conn) && file_exists(__DIR__ . '/../config/conector.php')) {
    require_once __DIR__ . '/../config/conector.php';
}

// If perfilUsuario.php defines individual variables, prefer them; otherwise fetch from DB and define both forms.
$fetched = array();
if (!isset($nome_exibicao) || !isset($nome_usuario) || !isset($foto_perfil_url)) {
    if (isset($_SESSION['id_usuario']) && isset($conn)) {
        $stmt = $conn->prepare("SELECT nome_usuario, nome_exibicao, email, foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['id_usuario']);
            $stmt->execute();
            $result = $stmt->get_result();
            $fetched = $result ? $result->fetch_assoc() : array();
            $stmt->close();
        }
    }
}

// Normalize variables: favor explicitly defined variables (from perfilUsuario), else use fetched values
if (!isset($nome_exibicao) || $nome_exibicao === '') {
    $nome_exibicao = isset($fetched['nome_exibicao']) ? $fetched['nome_exibicao'] : (isset($usuario['nome_exibicao']) ? $usuario['nome_exibicao'] : '');
}
if (!isset($nome_usuario) || $nome_usuario === '') {
    $nome_usuario = isset($fetched['nome_usuario']) ? $fetched['nome_usuario'] : (isset($usuario['nome_usuario']) ? $usuario['nome_usuario'] : '');
}
if (!isset($foto_perfil_url) || $foto_perfil_url === '') {
    $foto_perfil_url = isset($fetched['foto_perfil_url']) ? $fetched['foto_perfil_url'] : (isset($foto_perfil) ? $foto_perfil : '');
}
if (!isset($email) || $email === '') {
    $email = isset($fetched['email']) ? $fetched['email'] : (isset($usuario['email']) ? $usuario['email'] : '');
}

// Also populate $usuario array for backward compatibility
if (!isset($usuario) || !is_array($usuario) || empty($usuario)) {
    $usuario = array(
        'nome_usuario' => $nome_usuario ?? '',
        'nome_exibicao' => $nome_exibicao ?? '',
        'email' => $email ?? '',
        'foto_perfil_url' => $foto_perfil_url ?? ''
    );
}

// Sidebar display variables
$sidebar_nome = !empty($nome_exibicao) ? $nome_exibicao : (!empty($nome_usuario) ? $nome_usuario : 'Usuário');
$sidebar_handle = !empty($nome_usuario) ? (strpos($nome_usuario, '@') === 0 ? $nome_usuario : '@'.$nome_usuario) : '';
$sidebar_foto = !empty($foto_perfil_url) ? $foto_perfil_url : (isset($foto_perfil) && !empty($foto_perfil) ? $foto_perfil : '/Mybeat/public/images/Perfil_Usuario.png');
?>
<div id="profileSidebar" class="profile-sidebar" aria-hidden="true" style="display:none;">
  <div class="profile-sidebar-inner">
    <div class="profile-header">
      <img class="sidebar-profile-pic" src="<?php echo htmlspecialchars($sidebar_foto); ?>" alt="Foto de perfil">
      <div class="profile-meta">
        <div class="profile-name"><?php echo htmlspecialchars($sidebar_nome); ?></div>
        <div class="profile-handle"><?php echo htmlspecialchars($sidebar_handle); ?></div>
        <a href="perfilUsuario.php" class="edit-profile-btn">Editar perfil</a>
      </div>
    </div>
    <nav class="profile-links">
      <a class="profile-item" href="SeguidoresMyBeatViews.php"><img src="/Mybeat/public/images/buscar_usuarios.png" class="ico" alt="Buscar"> Buscar usuários</a>
      <a class="profile-item" href="grupos/lista_grupos.php"><img src="/Mybeat/public/images/grupos.png" class="ico" alt="Grupos"> Grupos</a>
      <a class="profile-item" href="historico_avaliacoes.php"><img src="/Mybeat/public/images/minhas_avaliacoes.png" class="ico" alt="Avaliações"> Minhas avaliações</a>
      <a class="profile-item" href="listar_giovana.php?controller=avaliacaoUsuario&action=mostrarAlbunsCurtidos"><img src="/Mybeat/public/images/heart.png" class="ico" alt="Curtidos"> Álbuns Curtidos</a>
      <a class="profile-item" href="Listar_giovana.php?controller=playlist&action=index"><img src="/Mybeat/public/images/minhas_playlist.png" class="ico" alt="Playlist"> Minhas playlist</a>
      <a class="profile-item" href="logout.php"><img src="/Mybeat/public/images/sair.png" class="ico" alt="Sair"> Sair</a>
    </nav>
    <button id="closeProfileSidebar" class="close-profile">Fechar</button>
  </div>
</div>
<div id="profileOverlay" class="profile-overlay" style="display:none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var toggle = document.getElementById('profileMenuToggle');
    var sidebar = document.getElementById('profileSidebar');
    var overlay = document.getElementById('profileOverlay');
    var closeBtn = document.getElementById('closeProfileSidebar');
    function openSidebar() {
        if (!sidebar) return;
        sidebar.style.display = 'block';
        sidebar.setAttribute('aria-hidden','false');
        if (overlay) overlay.style.display = 'block';
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.style.display = 'none';
        sidebar.setAttribute('aria-hidden','true');
        if (overlay) overlay.style.display = 'none';
    }
    if(toggle) toggle.addEventListener('click', function(e){ e.preventDefault(); openSidebar(); });
    if(overlay) overlay.addEventListener('click', closeSidebar);
    if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
});
</script>

<script src="/Mybeat/public/js/acessibilidade.js" defer></script>

</body>
</html>
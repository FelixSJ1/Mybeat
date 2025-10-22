<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php');
    exit;
}

$q = $q ?? '';
$addingMusicId = $addingMusicId ?? null;
$msg = $msg ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Minhas Playlists - MyBeat</title>

    <link rel="stylesheet" href="/Mybeat/public/css/playlist.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="playlist-page">
     <header>
        <div class="container">
            <div class="logo">
                <a href="/Mybeat/app/Views/home_usuario.php" class="logo-link">
                    <img src="/Mybeat/public/images/LogoF.png" alt="Mybeat Logo">
                    <h1>MyBeat</h1>
                </a>
            </div>
            <div class="cta-header">
                <div class="user-profile" title="Perfil"></div>
            </div>
        </div>
    </header>

  <main>
    <div class="container page-layout">

      <!-- BARRA DE BUSCA + BOTÃO NOVA PLAYLIST -->
      <div class="search-row">
        <form class="search-form" method="GET" action="listar_giovana.php" role="search" aria-label="Buscar playlists">
            <input type="hidden" name="controller" value="playlist">
            <input type="hidden" name="action" value="index">
            <input type="text" name="q" placeholder="Pesquisar playlists pelo nome..." value="<?= htmlspecialchars($q, ENT_QUOTES) ?>">
        </form>

        <div class="new-playlist-wrap">
            <a href="listar_giovana.php?controller=playlist&action=criar" class="btn-new-playlist" role="button">+ Nova Playlist</a>
        </div>
      </div>
      <!-- /BARRA DE BUSCA -->

      <?php if (!empty($addingMusicId)): ?>
          <div class="notice" style="margin:12px 0; color:#fff; background: rgba(166,74,201,0.06); padding:10px; border-radius:8px;">
              <p>Adicionando a música <strong><?= htmlspecialchars($addingMusicId, ENT_QUOTES) ?></strong>. Clique em uma playlist para adicioná-la.</p>
          </div>
      <?php endif; ?>

      <?php if (!empty($msg)): ?>
          <div class="msg-area" style="margin:12px 0;">
            <?php if ($msg === 'added'): ?>
                <div class="alert-success">✅ Música adicionada à playlist com sucesso!</div>
            <?php elseif ($msg === 'exists_or_error'): ?>
                <div class="alert-error">❌ A música já existe na playlist ou ocorreu um erro.</div>
            <?php elseif ($msg === 'error'): ?>
                <div class="alert-error">❌ Parâmetros inválidos.</div>
            <?php endif; ?>
          </div>
      <?php endif; ?>

      <section class="playlists-section">
        <h2>Minhas Playlists</h2>

        <?php if (!empty($playlists)): ?>
          <div class="playlists-grid">
            <?php foreach ($playlists as $pl): 
                $playlistId = (int)$pl['id_playlist'];
                if (!empty($addingMusicId)) {
                    $cardHref = "listar_giovana.php?controller=playlist&action=adicionar&playlist_id={$playlistId}&music_id=" . (int)$addingMusicId;
                } else {
                    $cardHref = "#"; // não funcional por enquanto
                }
            ?>
              <div class="playlist-card">
                <a href="<?= $cardHref ?>" class="playlist-cover-link" title="<?= htmlspecialchars($pl['nome_playlist'], ENT_QUOTES) ?>">
                  <div class="playlist-cover">
                    <img src="<?= htmlspecialchars($pl['capa_playlist_url'] ?: '/Mybeat/public/images/LogoF.png', ENT_QUOTES) ?>" alt="<?= htmlspecialchars($pl['nome_playlist'], ENT_QUOTES) ?>">
                  </div>
                </a>

                <a href="<?= $cardHref ?>" class="playlist-name-link">
                  <div class="playlist-name"><?= htmlspecialchars($pl['nome_playlist'], ENT_QUOTES) ?></div>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty">Você ainda não tem playlists. Crie uma clicando em "Nova Playlist".</div>
        <?php endif; ?>
      </section>

    </div>
  </main>

  <footer>
    <div class="container">
        <p>&copy; 2025 MyBeat. Todos os direitos reservados.</p>
    </div>
  </footer>
  </div>
</body>
</html>

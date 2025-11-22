<?php
// app/Views/playlist_detalhes.php
// VIEW PURO: espera as variáveis $playlist (array) e $musicas (array) fornecidas pelo controller.
// Não inclui lógica de model/controller.

if (session_status() === PHP_SESSION_NONE) session_start();

// helper de view (formatação)
function format_duracao($segundos) {
    if (!is_numeric($segundos)) return '-';
    $m = floor($segundos / 60);
    $s = $segundos % 60;
    return sprintf('%d:%02d', $m, $s);
}

// garantir variáveis
$playlist = $playlist ?? null;
$musicas = $musicas ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($playlist['nome_playlist'] ?? 'Playlist', ENT_QUOTES) ?> - MyBeat</title>

  <!-- CSS separado -->
  <link rel="stylesheet" href="/Mybeat/public/css/playlist_detalhes.css" />
</head>
<body>
  <header class="mb-header">
    <div class="container">
      <a class="logo-link" href="/Mybeat/app/Views/home_usuario.php">
        <img src="/Mybeat/public/images/LogoF.png" alt="Logo" class="logo-img">
        <h1 class="logo-title">MyBeat</h1>
      </a>
    </div>
  </header>

  <main>
    <div class="container page-layout">

      <?php if (!$playlist): ?>
        <section class="empty-playlist">
          <h2>Playlist não encontrada</h2>
          <p>Desculpe — a playlist solicitada não foi encontrada ou foi removida.</p>
        </section>
      <?php else: ?>
        <section class="playlist-hero">
          <div class="hero-left">
            <div class="cover-card">
              <?php $capa = !empty($playlist['capa_playlist_url']) ? $playlist['capa_playlist_url'] : '/Mybeat/public/images/LogoF.png'; ?>
              <img src="<?= htmlspecialchars($capa, ENT_QUOTES) ?>" alt="Capa da playlist">
            </div>
          </div>

          <div class="hero-right">
            <div class="playlist-meta">
              <div class="playlist-type"><?= ((int)($playlist['playlist_publica'] ?? 0)) ? 'Playlist pública' : 'Playlist privada' ?></div>
              <h2 class="playlist-title"><?= htmlspecialchars($playlist['nome_playlist'] ?? 'Playlist', ENT_QUOTES) ?></h2>

              <div class="playlist-info">
                <div class="owner"><strong>Usuário</strong> #<?= htmlspecialchars($playlist['id_usuario'] ?? 0, ENT_QUOTES) ?></div>
                <div class="meta-sep">•</div>
                <div class="tracks"><?= count($musicas) ?> músicas</div>
                <div class="meta-sep">•</div>
                <div class="duration"><?= htmlspecialchars($playlist['data_criacao'] ?? '-', ENT_QUOTES) ?></div>
              </div>

              <p class="playlist-desc"><?= nl2br(htmlspecialchars($playlist['descricao_playlist'] ?? '', ENT_QUOTES)) ?></p>

              <!-- BOTÕES REMOVIDOS: Tocar / Salvar como nova -->
            </div>
          </div>
        </section>

        <section class="tracks-section">
          <div class="tracks-header">
            <div>#</div>
            <div>Título</div>
            <div>Álbum</div>
            <div>Adicionada em</div>
            <div>⏱</div>
          </div>

          <div class="tracks-list">
            <?php if (empty($musicas)): ?>
              <div class="empty">Nenhuma música nesta playlist.</div>
            <?php else: ?>
              <?php $i = 1; foreach ($musicas as $m): ?>
                <a href="#" class="track-row" data-id="<?= (int)($m['id_musica'] ?? 0) ?>">
                  <div><?= htmlspecialchars($m['ordem_na_playlist'] ?? $i, ENT_QUOTES) ?></div>
                  <div style="display:flex;align-items:center;gap:12px;">
                    <div class="track-thumb">
                      <img src="<?= htmlspecialchars($m['capa_album_url'] ?? '/Mybeat/public/images/LogoF.png', ENT_QUOTES) ?>" alt="">
                    </div>
                    <div class="track-meta">
                      <div class="track-name"><?= htmlspecialchars($m['titulo'] ?? '-', ENT_QUOTES) ?></div>
                      <div class="track-artist"><?= htmlspecialchars($m['artista_nome'] ?? '-', ENT_QUOTES) ?></div>
                    </div>
                  </div>
                  <div><?= htmlspecialchars($m['album_titulo'] ?? '-', ENT_QUOTES) ?></div>
                  <div><?= htmlspecialchars($m['data_adicao'] ?? '-', ENT_QUOTES) ?></div>
                  <div><?= format_duracao($m['duracao_segundos'] ?? null) ?></div>
                </a>
              <?php $i++; endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
      <?php endif; ?>

    </div>
  </main>

  <footer>
    <div class="container">
      <p>&copy; <?= date('Y') ?> MyBeat. Todos os direitos reservados.</p>
    </div>
  </footer>

  <!-- Sem scripts específicos -->
</body>
</html>

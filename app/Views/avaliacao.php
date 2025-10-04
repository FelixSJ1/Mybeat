<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Álbum - MyBeat</title>

    <link rel="stylesheet" href="/Mybeat/public/css/stylePI.css">
    <link rel="stylesheet" href="/Mybeat/public/css/avaliacao.css">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/Mybeat/public/index.php" class="logo-link">
                  <img src="/Mybeat/public/images/LogoF.png" alt="Mybeat Logo">
                  <h1>MyBeat</h1>
                </a>
            </div>
            <div class="cta-header">
                <div class="user-profile"></div>
            </div>
        </div>
    </header>

    <form action="/Mybeat/salvar-avaliacao-album" method="POST">
        <main>
  <div class="container page-layout">
    
    <!-- Lado esquerdo: capa -->
    <aside class="left-panel">
      <img src="<?= htmlspecialchars($album['capa_album_url'] ?? '/Mybeat/public/images/LogoF.png') ?>" alt="Capa do álbum" class="album-cover">
    </aside>

    <!-- Centro: informações do álbum -->
    <section class="center-panel">
      <h1 class="music-title"><?= htmlspecialchars($album['titulo'] ?? 'Título do Álbum') ?></h1>
      <p class="artist-name">por <?= htmlspecialchars($album['artist_name'] ?? 'Artista Desconhecido') ?></p>
      <p class="album-info">
        do gênero "<?= htmlspecialchars($album['genero'] ?? 'Gênero') ?>" 
        (<?= htmlspecialchars(date('Y', strtotime($album['data_lancamento'] ?? 'now'))) ?>)
      </p>
      <div class="rating-display">
        ★★★★☆
        <span class="rating-text">4.0 / 5</span>
      </div>
    </section>

    <!-- Lado direito: ações -->
    <aside class="right-panel">
      <button type="button" class="action-btn">♡ Curtir</button>
      <button type="button" class="action-btn">+ Playlist</button>
      
      <div class="star-rating-box">
        <p>Sua Avaliação:</p>
        <div class="star-rating">
          <input type="radio" id="star5" name="nota" value="5"><label for="star5">★</label>
          <input type="radio" id="star4" name="nota" value="4"><label for="star4">★</label>
          <input type="radio" id="star3" name="nota" value="3"><label for="star3">★</label>
          <input type="radio" id="star2" name="nota" value="2"><label for="star2">★</label>
          <input type="radio" id="star1" name="nota" value="1"><label for="star1">★</label>
        </div>
      </div>
    </aside>
  </div>

  <!-- LISTA DE MÚSICAS DO ÁLBUM -->
  <section class="tracklist-section">
    <h2>Faixas do Álbum</h2>
    <ul class="tracklist">
      <?php if (!empty($musicas)): ?>
        <?php foreach ($musicas as $index => $musica): ?>
          <li>
            <span class="track-number"><?= $index + 1 ?>.</span>
            <span class="track-title"><?= htmlspecialchars($musica['titulo_musica']) ?></span>
            <span class="track-duration"><?= htmlspecialchars($musica['duracao'] ?? '') ?></span>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="no-tracks">Nenhuma música encontrada para este álbum.</li>
      <?php endif; ?>
    </ul>
  </section>

  <!-- CAMPO DE ADICIONAR COMENTÁRIO -->
  <section class="review-section">
    <h2>Adicione um comentário</h2>
    <textarea name="texto_review" rows="6" placeholder="O que você achou deste álbum?"></textarea>

    <input type="hidden" name="id_album" value="<?= htmlspecialchars($album['id_album'] ?? '0') ?>">

    <button type="submit" class="btn-submit">Salvar</button>
  </section>

  <!-- COMENTÁRIOS E AVALIAÇÕES EXISTENTES -->
  <section class="comments-section">
    <h2>Avaliações Recentes</h2>
    <?php if (!empty($avaliacoes)): ?>
      <?php foreach ($avaliacoes as $avaliacao): ?>
        <div class="comment-box">
          <div class="comment-header">
            <span class="user-name"><?= htmlspecialchars($avaliacao['nome_usuario']) ?></span>
            <span class="user-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <?= $i <= $avaliacao['nota'] ? '★' : '☆' ?>
              <?php endfor; ?>
            </span>
          </div>
          <p class="comment-text"><?= htmlspecialchars($avaliacao['texto_review']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-comments">Ainda não há comentários para este álbum.</p>
    <?php endif; ?>
  </section>
</main>
 </form> <footer>
        <div class="container">
            <p>&copy; 2025 MyBeat. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
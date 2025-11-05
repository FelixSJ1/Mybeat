<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Álbum - MyBeat</title>

    
    <link rel="stylesheet" href="/Mybeat/public/css/avaliacao.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="avaliacao-page">
     <header>
        <div class="container">
            <div class="logo">
                <a href="/Mybeat/app/Views/home_usuario.php" class="logo-link">
                    <img src="/Mybeat/public/images/LogoF.png" alt="Mybeat Logo">
                    <h1>MyBeat</h1>
                </a>
            </div>
            
        </div>
    </header>

        <main>
          <form action="listar_giovana.php?controller=avaliacaoUsuario&action=salvar" method="POST">
            <div class="container page-layout">

                <!-- Lado esquerdo: capa -->
                <aside class="left-panel">
                    <img src="<?= htmlspecialchars($album['capa_album_url'] ?? '/Mybeat/public/images/LogoF.png') ?>" alt="Capa do álbum" class="album-cover">
                </aside>

                <!-- Centro: informações do álbum -->
                <section class="center-panel">
                    <h1 class="music-title"><?= htmlspecialchars($album['titulo'] ?? 'Título do Álbum') ?></h1>
                    <p class="artist-name"> <?= htmlspecialchars($album['nome_artista'] ?? 'Artista Desconhecido') ?></p>
                    <p class="album-info">
                        <?= htmlspecialchars($album['genero'] ?? 'Gênero') ?>
                        (<?= htmlspecialchars(date('Y', strtotime($album['data_lancamento'] ?? 'now'))) ?>)
                    </p>
                    <div class="rating-display">
                        <?php
                        // Arredonda a média para o número inteiro mais próximo para desenhar as estrelas
                            $mediaArredondada = round($album['media_nota'] ?? 0);

                            // Desenha as estrelas preenchidas e vazias
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $mediaArredondada ? '★' : '☆';
                            }
                        ?>
                        <span class="rating-text">
                            <?= number_format($album['media_nota'] ?? 0, 1) ?> / 5
                            <span style="color: var(--text-dark); font-weight: normal;">
                                (<?= $album['total_avaliacoes'] ?? 0 ?> avaliações)
                            </span>
                        </span>
                    </div>
                    <!-- botão Ver Estatísticas (álbum) - fica abaixo da média/estrelas -->
                    <div class="stats-button-container" style="margin-top:10px; text-align:left;">
                        <a class="action-btn" href="listar_giovana.php?controller=painelmusic&action=show&id_album=<?= (int)($album['id_album'] ?? 0) ?>">
                            Ver estatísticas das avaliações
                        </a>
                    </div>

                </section>

                <!-- Lado direito: ações -->
                <aside class="right-panel">
                    <?php if (isset($isAlbumCurtido) && $isAlbumCurtido): ?>
                    
                        <button type="submit" 
                                class="action-btn" 
                                title="Remover álbum dos curtidos"
                                formaction="listar_giovana.php?controller=avaliacaoUsuario&action=curtirAlbum&id_album=<?= (int)($album['id_album'] ?? 0) ?>"
                                formmethod="POST"
                                style="color: #ff8b3d; border-color: #ff8b3d;"> ♥ Curtido
                        </button>
                
                    <?php else: ?>
                    
                        <button type="submit" 
                                class="action-btn" 
                                title="Adicionar álbum aos curtidos"
                                formaction="listar_giovana.php?controller=avaliacaoUsuario&action=curtirAlbum&id_album=<?= (int)($album['id_album'] ?? 0) ?>"
                                formmethod="POST">
                            ♡ Curtir Álbum
                        </button>

                    <?php endif; ?>
                   
                    <a href="listar_giovana.php?controller=playlist&action=index" class="action-btn album-playlist-btn" role="button" aria-label="Adicionar álbum à playlist">+ Playlist</a>
                    
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
                                <div class="track-left">
                                    <span class="track-number"><?= $index + 1 ?>.</span>
                                    <span class="track-title"><?= htmlspecialchars($musica['titulo']) ?></span>
                                </div>
                                <div class="track-right">
                                    <span class="track-duration"><?= htmlspecialchars($musica['duracao'] ?? '') ?></span>

                                    <!-- Botões menores (mesmo estilo dos do painel direito, porém reduzidos) -->
                                    <div class="track-actions">
                                        <a
                                            href="listar_giovana.php?controller=avaliacaoUsuario&action=curtirMusica&id_musica=<?= (int)($musica['id_musica'] ?? 0) ?>&id_album=<?= (int)($album['id_album'] ?? 0) ?>&from=avaliacao"
                                            class="track-action-btn track-like"
                                            title="Curtir faixa <?= htmlspecialchars($musica['titulo']) ?>"
                                        >♡</a>


                                        <!-- Alterado: agora é link para adicionar música às playlists (passa add_music_id) -->
                                        <a
                                            href="listar_giovana.php?controller=playlist&action=index&add_music_id=<?= (int)($musica['id_musica'] ?? 0) ?>"
                                            class="track-action-btn track-add"
                                            aria-label="Adicionar faixa <?= htmlspecialchars($musica['titulo']) ?> à playlist"
                                            title="Adicionar à playlist"
                                        >+</a>
                                    </div>
                                </div>
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
          </form>
           <?php
          
            if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])) {
                
                $toast_type = 'toast-info'; 
                if ($_SESSION['flash_message_type'] === 'alert-success') {
                    $toast_type = 'toast-success'; 
                }

                echo '<div class="toast-notification ' . $toast_type . '">' 
                   . htmlspecialchars($_SESSION['flash_message']) 
                   . '</div>';
                
                unset($_SESSION['flash_message']);
                unset($_SESSION['flash_message_type']);
            }
            ?>

            <!-- COMENTÁRIOS E AVALIAÇÕES EXISTENTES -->
            <section class="comments-section">
                <h2>Avaliações Recentes</h2>
                <?php if (!empty($avaliacoes)): ?>
                    <?php foreach ($avaliacoes as $avaliacao): ?>
                        <div class="comment-box">
                            <div class="comment-header">
                                <span class="user-name"><?= htmlspecialchars($avaliacao['nome_exibicao']) ?></span>
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
        
    <footer>
        <div class="container">
            <p>&copy; 2025 MyBeat. Todos os direitos reservados.</p>
        </div>
    </footer>
  </div>
<script>
(function() {
   
    setTimeout(function() {
        
        const msg = document.querySelector('.toast-notification');
        
        if (msg) {
            msg.style.opacity = '0';
            
            setTimeout(function() { 
                msg.remove(); 
            }, 500); 
        }
    }, 5000);
})();
</script>

</body>

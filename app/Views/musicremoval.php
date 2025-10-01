<?php
// app/Views/musicremoval.php
if (session_status() === PHP_SESSION_NONE) session_start();

/* fallback POST handling preserved (não alterei lógica de remoção) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
    require_once __DIR__ . '/../Models/music_removal_model.php';
    try {
        $modelFallback = new Music_Removal_Model();
        $action = $_POST['form_action'];
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            switch ($action) {
                case 'delete':
                    $ok = $modelFallback->deleteSong($id);
                    $msg = $ok ? 'Música removida com sucesso' : 'Falha ao remover música';
                    break;
                case 'delete_album':
                    $res = $modelFallback->deleteAlbum($id);
                    $msg = ($res === true) ? 'Álbum removido com sucesso' : $res;
                    break;
                case 'delete_album_force':
                    $res = $modelFallback->deleteAlbumForce($id);
                    $msg = ($res === true) ? 'Álbum e suas músicas removidos com sucesso' : $res;
                    break;
                case 'delete_artist':
                    $res = $modelFallback->deleteArtist($id);
                    $msg = ($res === true) ? 'Artista removido com sucesso' : $res;
                    break;
                case 'delete_artist_force':
                    $res = $modelFallback->deleteArtistForce($id);
                    $msg = ($res === true) ? 'Artista e dependências removidos com sucesso' : $res;
                    break;
                default:
                    $msg = 'Ação inválida';
            }
        } else {
            $msg = 'ID inválido';
        }
    } catch (Throwable $e) {
        $msg = 'Erro: ' . $e->getMessage();
    }
    $uri = $_SERVER['REQUEST_URI'];
    $sep = (strpos($uri, '?') === false) ? '?' : '&';
    header('Location: ' . $uri . $sep . 'msg=' . urlencode($msg));
    exit;
}

/* Carrega dados caso view aberta direto (fallback) */
if (!isset($songs) || !isset($albums) || !isset($artists)) {
    require_once __DIR__ . '/../Models/music_removal_model.php';
    try {
        $m = new Music_Removal_Model();
        $songs = $m->all();
        $albums = $m->allAlbums();
        $artists = $m->allArtists();
    } catch (Throwable $e) {
        $songs = $albums = $artists = [];
        $loadError = $e->getMessage();
    }
}

/* mensagem vinda do controller (ou do redirect fallback acima) */
$message = isset($_GET['msg']) ? trim($_GET['msg']) : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>MyBeat — Remoção</title>
  <link rel="stylesheet" href="../../public/css/musicremoval.css">
</head>
<body>
  <main class="page-main">
    <header class="site-header">
      <div class="brand">
        <!-- logo: se o nome do arquivo for diferente no seu projeto, ajuste apenas o src -->
        <img src="../../public/images/LogoF.png" alt="MyBeat" class="logo" />
        <div class="brand-texts">
          <h1 class="site-title">MyBeat</h1>
          <div class="site-sub">Remoção — Gerencie Artistas, Álbuns e Músicas</div>
        </div>
      </div>
    </header>

    <!-- mensagem de status (aparecerá se $message existir) -->
    <?php if (!empty($message)): ?>
      <div id="page-notice" class="notice"><?= htmlspecialchars($message) ?></div>
    <?php else: ?>
      <div id="page-notice" class="notice" style="display:none;"></div>
    <?php endif; ?>

    <section class="grid">
      <!-- Artistas -->
      <div class="card">
        <h2>Artistas</h2>
        <p class="muted">Lista de Artistas do Banco de Dados</p>

        <?php if (empty($artists)): ?>
          <div class="empty">Nenhum Artista Encontrado</div>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($artists as $a):
              $artistId = $a['id'] ?? ($a['id_artista'] ?? '');
              $artistName = $a['name'] ?? ($a['nome'] ?? '—');
              $albumsCount = $a['albums_count'] ?? 0;
              $songsCount = $a['songs_count'] ?? 0;
            ?>
              <li class="list-item">
                <div class="item-left">
                  <div class="item-title"><?= htmlspecialchars($artistName) ?></div>
                  <div class="item-meta">Álbuns: <?= $albumsCount ?> • Músicas: <?= $songsCount ?></div>
                </div>

                <div class="item-actions">
                  <form method="post" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="confirm-delete-form"
                        data-type="artist" data-albums="<?= $albumsCount ?>" data-songs="<?= $songsCount ?>">
                    <input type="hidden" name="form_action" value="delete_artist">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($artistId) ?>">
                    <button class="btn btn-danger" type="submit">Remover</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Albuns -->
      <div class="card">
        <h2>Álbuns</h2>
        <p class="muted">Lista de Álbuns do Banco de Dados</p>

        <?php if (empty($albums)): ?>
          <div class="empty">Nenhum Álbum Encontrado</div>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($albums as $al):
              $albumId = $al['id'] ?? ($al['id_album'] ?? '');
              $albumTitle = $al['title'] ?? ($al['titulo'] ?? '');
              $albumArtist = $al['artist_name'] ?? ($al['nome'] ?? '—');
              $songsCount = $al['songs_count'] ?? 0;
            ?>
              <li class="list-item">
                <div class="item-left">
                  <div class="item-title"><?= htmlspecialchars($albumTitle) ?></div>
                  <div class="item-meta"><?= htmlspecialchars($albumArtist) ?> • Músicas: <?= $songsCount ?></div>
                </div>

                <div class="item-actions">
                  <form method="post" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="confirm-delete-form"
                        data-type="album" data-songs="<?= $songsCount ?>">
                    <input type="hidden" name="form_action" value="delete_album">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($albumId) ?>">
                    <button class="btn btn-danger" type="submit">Remover</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Músicas -->
      <div class="card">
        <h2>Músicas</h2>
        <p class="muted">Lista de Músicas com Artista e Álbum</p>

        <?php if (empty($songs)): ?>
          <div class="empty">Nenhuma Música Encontrada</div>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($songs as $song):
              $songId = $song['id'] ?? ($song['id_musica'] ?? '');
              $songTitle = $song['title'] ?? ($song['titulo'] ?? '');
              $songArtist = $song['artist_name'] ?? ($song['nome'] ?? '—');
              $songAlbum = $song['album_title'] ?? ($song['titulo_album'] ?? '—');
            ?>
              <li class="list-item">
                <div class="item-left">
                  <div class="item-title"><?= htmlspecialchars($songTitle) ?></div>
                  <div class="item-meta"><?= htmlspecialchars($songArtist) ?> • <?= htmlspecialchars($songAlbum) ?></div>
                </div>

                <div class="item-actions">
                  <form method="post" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" class="confirm-delete-form"
                        data-type="song">
                    <input type="hidden" name="form_action" value="delete">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($songId) ?>">
                    <button class="btn btn-danger" type="submit">Remover</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </section>

    <footer class="footer muted" style="margin-top:16px">Remover irá apagar do banco — confirme com atenção.</footer>
  </main>

  <script>
    // Mantive o JS de confirmação (sem alteração lógica)
    document.addEventListener('DOMContentLoaded', function() {
      const forms = document.querySelectorAll('.confirm-delete-form');
      forms.forEach(form => {
        form.addEventListener('submit', function(ev) {
          ev.preventDefault();
          const type = form.dataset.type;
          let baseMsg = 'Tem certeza que deseja remover?';
          let confirmAction = form.querySelector('input[name="form_action"]').value;

          if (type === 'album') {
            const songs = parseInt(form.dataset.songs || 0, 10);
            if (songs > 0) {
              baseMsg = `Este álbum possui ${songs} música(s). Confirmar remoção do álbum e de todas as músicas deste álbum?`;
              confirmAction = 'delete_album_force';
            } else {
              baseMsg = 'Confirmar remoção deste álbum?';
              confirmAction = 'delete_album';
            }
          } else if (type === 'artist') {
            const albums = parseInt(form.dataset.albums || 0, 10);
            const songs = parseInt(form.dataset.songs || 0, 10);
            if (albums > 0 || songs > 0) {
              baseMsg = `Este artista possui ${albums} álbum(ens) e ${songs} música(s). Confirmar remoção do artista e de todas as dependências?`;
              confirmAction = 'delete_artist_force';
            } else {
              baseMsg = 'Confirmar remoção deste artista?';
              confirmAction = 'delete_artist';
            }
          } else if (type === 'song') {
            baseMsg = 'Confirmar remoção desta música?';
            confirmAction = 'delete';
          }

          if (confirm(baseMsg)) {
            form.querySelector('input[name="form_action"]').value = confirmAction;
            form.submit();
          }
        });
      });

      // --- mensagem: remover param ?msg=... da URL após mostrar (evita reaparecer) ---
      const notice = document.getElementById('page-notice');
      if (notice && notice.innerText.trim().length > 0) {
        // fade out after 5s
        setTimeout(() => {
          notice.style.transition = 'opacity 0.6s ease, max-height 0.6s ease';
          notice.style.opacity = '0';
          notice.style.maxHeight = '0';
        }, 5000);

        // remove query parameter 'msg' da URL sem recarregar (apenas para UI)
        try {
          const url = new URL(window.location.href);
          if (url.searchParams.has('msg')) {
            url.searchParams.delete('msg');
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
          }
        } catch (e) {
          // browsers antigos: nada — não é crítico
        }
      }
    });
  </script>
</body>
</html>

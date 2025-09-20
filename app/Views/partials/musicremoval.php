<?php
if (!isset($songs)) $songs = [];
if (!isset($message)) $message = '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>my_beat — Music Removal</title>

  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../../public/css/musicremoval.css">

</head>
<body>

  <div class="container">
    <header class="header">
      <div class="brand">
        <div class="logo">MY</div>
        <div>
          <h1 class="h1">MY_BEAT</h1>
          <div class="sub">Gerenciamento</div>
        </div>
      </div>

    </header>

    <?php if (!empty($message)): ?>
      <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <main>
      <table class="table" aria-labelledby="songs">
        <thead>
          <tr><th>#</th><th>Música</th><th>Artista</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach($songs as $s): ?>
            <tr>
              <td><?= $s['id'] ?></td>
              <td><div class="song-title"><?= htmlspecialchars($s['title']) ?></div></td>
              <td><div class="song-artist"><?= htmlspecialchars($s['artist']) ?></div></td>
              <td style="text-align:right">
                <a class="btn" href="music_removal.php?action=confirm&id=<?= $s['id'] ?>">Apagar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </main>
  </div>

  <?php if (isset($song) && $song): ?>
    <div class="modal-wrap" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal" role="document">
        <div class="title" id="modal-title">Confirmar exclusão</div>
        <div class="desc">Confirme para prosseguir com a remoção.</div>

        <div class="song">
          <div>
            <div class="song-title-strong"><?= htmlspecialchars($song['title']) ?></div>
            <div class="song-artist"><?= htmlspecialchars($song['artist']) ?></div>
          </div>
          <div class="song-meta">ID <?= $song['id'] ?></div>
        </div>

        <form method="post" action="music_removal.php?action=delete" class="modal-form" novalidate>
          <input type="hidden" name="id" value="<?= $song['id'] ?>">
          <div class="controls">
            <a class="cancel-link" href="music_removal.php">Cancelar</a>
            <button type="submit" class="form-button primary">Confirmar remoção</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

</body>
</html>

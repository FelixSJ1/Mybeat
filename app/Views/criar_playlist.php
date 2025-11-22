<?php
// app/Views/criar_playlist.php
if (session_status() === PHP_SESSION_NONE) session_start();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Criar Playlist - MyBeat</title>

  <!-- CSS separado para criar playlist -->
  <link rel="stylesheet" href="/Mybeat/public/css/criar_playlist.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body class="criar-playlist-page">
  <div class="playlist-page">

    <!-- CABEÇALHO -->
    <header>
      <div class="container">
        <div class="logo">
          <a href="listar_giovana.php?controller=home&action=index">
            <img src="/Mybeat/public/images/LogoF.png" alt="Logo">
          </a>
        </div>
      </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main>
      <div class="container">

        <h2>Criar Nova Playlist</h2>

        <!-- Mensagens -->
        <?php if ($msg === 'nome_required'): ?>
            <div class="alert-error">Por favor informe um nome para a playlist.</div>
        <?php elseif ($msg === 'error'): ?>
            <div class="alert-error">Erro ao criar a playlist. Tente novamente.</div>
        <?php elseif ($msg === 'created'): ?>
            <div class="alert-success">Playlist criada com sucesso!</div>
        <?php endif; ?>

        <!-- Formulário -->
        <form method="POST" action="listar_giovana.php?controller=playlist&action=salvar" enctype="multipart/form-data">
          <div>
            <label for="nome_playlist">Nome da playlist</label>
            <input id="nome_playlist" name="nome_playlist" type="text" required>
          </div>

          <div>
            <label for="descricao_playlist">Descrição (opcional)</label>
            <textarea id="descricao_playlist" name="descricao_playlist" rows="4"></textarea>
          </div>

          <div>
            <label for="capa_playlist">Capa (opcional)</label>
            <input id="capa_playlist" name="capa_playlist" type="file" accept="image/*">
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-new-playlist">Criar</button>
            <a href="listar_giovana.php?controller=playlist&action=index" class="btn-new-playlist">Cancelar</a>
          </div>
        </form>

      </div>
    </main>

    <!-- RODAPÉ -->
    <footer>
      <div class="container">
        <p>&copy; 2025 MyBeat. Todos os direitos reservados.</p>
      </div>
    </footer>

  </div>
</body>
</html>

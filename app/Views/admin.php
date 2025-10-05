<?php
// app/Views/admin.php
if (session_status() === PHP_SESSION_NONE) session_start();

// carrega CSS externo (path relativo à view)
$css_path = '../../public/css/admin.css';

// função para detectar logo (procura em lugares comuns)
function detect_logo_src() {
    $candidates = [
        __DIR__ . '/../../public/images/logo.png' => '../../public/images/logo.png',
        __DIR__ . '/../../public/images/F.png' => '../../public/images/F.png',
        __DIR__ . '/../../public/images/f.png' => '../../public/images/f.png',
        __DIR__ . '/../../public/images/logo.svg' => '../../public/images/logo.svg',
        __DIR__ . '/../../public/img/logo.png' => '../../public/img/logo.png',
        __DIR__ . '/../../public/assets/logo.png' => '../../public/assets/logo.png',
    ];
    foreach ($candidates as $fs => $url) {
        if (file_exists($fs)) return $url;
    }
    // fallback: scan public/images for filename with 'logo' or single-letter 'f' or 'F'
    $dir = __DIR__ . '/../../public/images';
    if (is_dir($dir)) {
        foreach (scandir($dir) as $f) {
            if (preg_match('/logo|^f(\\.|_)/i', $f) || preg_match('/^f\\.png$/i', $f) || preg_match('/^F\\.png$/', $f)) {
                return '../../public/images/' . $f;
            }
        }
    }
    return null;
}
$logo_src = detect_logo_src();

// decide se mostra tabela de usuários (apenas quando ?show=users)
$showUsers = (isset($_GET['show']) && $_GET['show'] === 'users');

// se for para mostrar usuários, carregamos o model aqui (não carregamos por padrão)
$users = [];
if ($showUsers) {
    // tenta incluir model e conector
    if (file_exists(__DIR__ . '/../Models/AdminModel.php')) require_once __DIR__ . '/../Models/AdminModel.php';
    $cands = [
        __DIR__ . '/../config/conector.php',
        __DIR__ . '/../../app/config/conector.php',
        __DIR__ . '/../../config/conector.php'
    ];
    foreach ($cands as $c) if (file_exists($c)) { require_once $c; break; }
    // escolhe db object se disponível
    $dbObj = null;
    if (isset($pdo) && $pdo instanceof PDO) $dbObj = $pdo;
    elseif (isset($conn) && ($conn instanceof mysqli || get_resource_type($conn) === 'mysql link')) $dbObj = $conn;
    try {
        $model = new AdminModel($dbObj);
        $users = $model->allUsers();
    } catch (Exception $e) {
        // registra e segue com users vazios
        error_log("admin view: " . $e->getMessage());
        $users = [];
    }
}
?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Painel Admin — MyBeat</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($css_path) ?>">
</head>
<body>
<main class="admin-root">
  <div class="header-row">
    <?php if ($logo_src): ?>
      <img class="site-logo" src="<?= htmlspecialchars($logo_src) ?>" alt="MyBeat logo">
    <?php endif; ?>
    <h1>Painel do Administrador</h1>
  </div>

  <section class="admin-actions">
    <a class="big-button" href="./juncao_kauelly.php">Administrar conteúdo (músicas / artistas / álbuns)</a>
    <!-- abre mesma view com ?show=users para carregar a tabela -->
    <?php if ($showUsers): ?>
      <a class="big-button" href="./admin.php">Voltar</a>
    <?php else: ?>
      <a class="big-button" href="./admin.php?show=users">Administrar usuários</a>
    <?php endif; ?>
  </section>

  <?php if ($showUsers): ?>
  <section id="users" class="users-section">
    <h2>Usuários (padrões)</h2>
    <?php if (!empty($_GET['msg'])): ?>
      <div class="flash"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table role="table" aria-label="Lista de usuários">
      <thead><tr><th>Foto</th><th>Nome de exibição</th><th>Usuário / Email</th><th>Cad.</th><th>Ações</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td class="user-photo">
            <?php if (!empty($u['foto_perfil_url'])): ?>
              <img src="<?= htmlspecialchars($u['foto_perfil_url']) ?>" alt="foto">
            <?php else: ?>
              <div class="avatar-placeholder">U</div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($u['nome_exibicao'] ?: $u['nome_usuario']) ?></td>
          <td>
            <div class="muted">@<?= htmlspecialchars($u['nome_usuario']) ?></div>
            <div><?= htmlspecialchars($u['email']) ?></div>
          </td>
          <td><?= htmlspecialchars($u['data_cadastro']) ?></td>
          <td>
            <details>
              <summary>Reviews</summary>
              <div class="reviews-list">
                <?php
                  // pega reviews
                  try {
                      if (isset($model)) {
                          $reviews = $model->reviewsByUser((int)$u['id_usuario']);
                      } else {
                          $reviews = [];
                      }
                  } catch (Throwable $e) { $reviews = []; }
                ?>
                <?php if (empty($reviews)): ?>
                  <div class="muted">Sem reviews</div>
                <?php else: ?>
                  <table class="reviews-table">
                    <thead><tr><th>Álbum</th><th>Nota</th><th>Texto</th><th>Data</th><th>Ação</th></tr></thead>
                    <tbody>
                      <?php foreach ($reviews as $r): ?>
                        <tr>
                          <td><?= htmlspecialchars($r['album_title'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($r['nota']) ?></td>
                          <td><?= nl2br(htmlspecialchars($r['texto'])) ?></td>
                          <td><?= htmlspecialchars($r['data_avaliacao']) ?></td>
                          <td>
                            <form method="post" action="../Controllers/AdminController.php">
                              <input type="hidden" name="action" value="delete_review">
                              <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                              <button type="submit" class="btn small">Excluir review</button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php endif; ?>
              </div>
            </details>

            <form method="post" action="../Controllers/AdminController.php" style="margin-top:8px;">
              <input type="hidden" name="action" value="ban_user">
              <input type="hidden" name="user_id" value="<?= (int)$u['id_usuario'] ?>">
              <button type="submit" class="btn danger">Banir usuário</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
  <?php endif; ?>
</main>
</body>
</html>

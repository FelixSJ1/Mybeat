<?php
// app/Views/admin.php
// Painel Admin — view atualizada com checagem de acesso e botão laranja "Voltar ao site"

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificação de acesso: somente administradores logados podem ver essa view.
// Se não for administrador, redireciona para a página de login de administradores.
if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    // opcional: salvar destino para depois do login
    $_SESSION['after_admin_login_redirect'] = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: ../Views/FaçaLoginMyBeatADM.php');
    exit;
}

/* caminho do css (mantive o seu padrão) */
$css_path = '../../public/css/admin.css';

/* função para detectar logo (procura alguns nomes possíveis) */
function detect_logo_src() {
    $candidates = [
        __DIR__ . '/../../public/images/logo.png' => '../../public/images/logo.png',
        __DIR__ . '/../../public/images/LogoF.png' => '../../public/images/LogoF.png',
        __DIR__ . '/../../public/images/F.png' => '../../public/images/F.png',
        __DIR__ . '/../../public/images/f.png' => '../../public/images/f.png',
        __DIR__ . '/../../public/images/logo.svg' => '../../public/images/logo.svg',
    ];
    foreach ($candidates as $file => $rel) {
        if (file_exists($file)) return $rel;
    }
    return null;
}

$logo_src = detect_logo_src();

/* Caso o controller não tenha populado $users/$reviews, tentamos carregar via conector (segurança: não remove nada do projeto). */
if (!isset($users) || !is_array($users) || !isset($reviews) || !is_array($reviews)) {
    $conector_path = __DIR__ . '/../config/conector.php';
    if (file_exists($conector_path)) {
        require_once $conector_path; // deve definir $conn (mysqli)
        if (isset($conn) && $conn instanceof mysqli) {
            // busca usuários
            $users = [];
            try {
                $sqlUsers = "SELECT id_usuario, nome_exibicao, nome_usuario, email, foto_perfil_url, data_cadastro
                             FROM Usuarios ORDER BY data_cadastro DESC";
                if ($res = $conn->query($sqlUsers)) {
                    $users = $res->fetch_all(MYSQLI_ASSOC);
                    $res->free();
                }
            } catch (Throwable $e) {
                error_log("admin.php: erro ao buscar usuarios: " . $e->getMessage());
                $users = [];
            }

            // busca avaliacoes
            $reviews = [];
            try {
                $sqlReviews = "SELECT a.id_avaliacao AS id, a.texto_review AS texto, a.nota, a.data_avaliacao,
                                      u.id_usuario AS usuario_id, u.nome_usuario, u.nome_exibicao,
                                      al.id_album, al.titulo AS album_title
                               FROM Avaliacoes a
                               LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario
                               LEFT JOIN Albuns al ON a.id_album = al.id_album
                               ORDER BY a.data_avaliacao DESC";
                if ($res2 = $conn->query($sqlReviews)) {
                    $reviews = $res2->fetch_all(MYSQLI_ASSOC);
                    $res2->free();
                }
            } catch (Throwable $e) {
                error_log("admin.php: erro ao buscar avaliacoes: " . $e->getMessage());
                $reviews = [];
            }
        }
    }
}

// Garantias (caso nada tenha sido populado)
if (!isset($users) || !is_array($users)) $users = [];
if (!isset($reviews) || !is_array($reviews)) $reviews = [];
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Painel Admin — MyBeat</title>
  <link rel="stylesheet" href="<?= htmlspecialchars($css_path) ?>">

  <!-- Pequeno estilo inline para garantir botão laranja (não sobrescreve classes existentes) -->
  <style>
    /* Garantir variante laranja sem depender do CSS existente */
    .big-button.orange {
      background: #EB8046 !important;
      border-color: #EB8046 !important;
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(235,128,70,0.12);
    }
    .header-row { display:flex; align-items:center; gap:1rem; }
    .header-actions { margin-left:auto; }
    .admin-table { width:100%; border-collapse:collapse; }
    .admin-table th, .admin-table td { padding:0.5rem 0.75rem; text-align:left; vertical-align:middle; }
    .avatar-placeholder { width:48px; height:48px; display:inline-flex; align-items:center; justify-content:center; background:#ddd; border-radius:6px; color:#555; }
    .review-text { max-width:420px; white-space:normal; }
  </style>
</head>
<body>
<main class="admin-root">
  <div class="header-row" style="padding:1rem 0;">
    <?php if ($logo_src): ?>
      <img class="site-logo" src="<?= htmlspecialchars($logo_src) ?>" alt="MyBeat logo" style="height:56px;">
    <?php endif; ?>

    <h1 style="margin:0;flex:1;">Painel do Administrador</h1>

    <!-- Botão laranja para voltar à Home Usuário -->
    <div class="header-actions" aria-hidden="false">
      <!-- link para a view Home Usuário (não para controller); a view fará checagem se necessário -->
      <a class="big-button orange" href="./home_usuario.php" title="Voltar para Home">Voltar ao site</a>
    </div>
  </div>

  <section class="admin-actions" style="margin-top:1rem;">
    <a class="big-button" href="./AdicaoDeDadosF.php">Adicionar Dados</a>
    <a class="big-button" href="./Listar_giovana.php">Listar Dados</a>
    <a class="big-button" href="./EditMyBeatViews.php">Editar Dados</a>
    <a class="big-button" href="./musicremoval.php">Remover Dados</a>
  </section>

  <!-- Tabela de Usuários (sempre visível) -->
  <section id="users" class="users-section" style="margin-top:1.25rem;">
    <h2>Usuários (padrões)</h2>

    <?php if (!empty($_GET['msg'])): ?>
      <div class="flash"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table role="table" aria-label="Lista de usuários" class="admin-table">
      <thead><tr>
        <th>Foto</th><th>Nome de exibição</th><th>Usuário / Email</th><th>Cad.</th><th>Ações</th>
      </tr></thead>
      <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $u): ?>
        <tr>
          <td class="user-photo">
            <?php if (!empty($u['foto_perfil_url'])): ?>
              <img src="<?= htmlspecialchars($u['foto_perfil_url']) ?>" alt="foto" style="height:48px;">
            <?php else: ?>
              <div class="avatar-placeholder">U</div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($u['nome_exibicao'] ?: $u['nome_usuario']) ?></td>
          <td>
            <div class="muted">@<?= htmlspecialchars($u['nome_usuario']) ?></div>
            <div><?= htmlspecialchars($u['email']) ?></div>
          </td>
          <td><?= htmlspecialchars($u['data_cadastro'] ?? '') ?></td>
          <td>
            <form method="post" action="../Controllers/AdminController.php" onsubmit="return confirm('Banir usuário? Esta ação removerá o usuário e dados relacionados.');" style="display:inline-block;">
              <input type="hidden" name="action" value="ban_user">
              <input type="hidden" name="user_id" value="<?= (int)$u['id_usuario'] ?>">
              <button type="submit" class="btn danger">Banir usuário</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="muted">Nenhum usuário encontrado.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </section>

  <!-- Nova seção: Avaliações -->
  <section id="reviews" class="reviews-section" style="margin-top:2rem;">
    <h2>Avaliações</h2>

    <?php if (!empty($reviews) && is_array($reviews)): ?>
      <table class="admin-table reviews-table" role="grid" aria-label="Avaliações">
        <thead>
          <tr>
            <th>ID</th><th>Usuário</th><th>Nome exib.</th><th>Álbum</th><th>Nota</th><th>Texto</th><th>Data</th><th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['nome_usuario'] ?? '---') ?></td>
            <td><?= htmlspecialchars($r['nome_exibicao'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['album_title'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['nota'] ?? '') ?></td>
            <td class="review-text"><?= nl2br(htmlspecialchars($r['texto'] ?? '')) ?></td>
            <td><?= htmlspecialchars($r['data_avaliacao'] ?? '') ?></td>
            <td>
              <form method="post" action="../Controllers/AdminController.php" onsubmit="return confirm('Remover avaliação #<?= (int)$r['id'] ?>? Esta ação é irreversível.');">
                <input type="hidden" name="action" value="delete_review">
                <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn danger">Remover</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="muted">Nenhuma avaliação registrada.</p>
    <?php endif; ?>
  </section>

</main>
</body>
</html>

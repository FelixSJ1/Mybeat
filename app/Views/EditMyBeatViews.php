<?php
declare(strict_types=1);


ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/databaseAA.php';
require_once __DIR__ . '/../Controllers/EditMyBeatControllers.php';

$controller = new EditMyBeatControllers($pdo);

$action = $_GET['action'] ?? 'home';
$type = $_GET['type'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$data = $controller->handleRequest($action, $type, $id);


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MyBeat</title>
    <style>
        body { background: #000; color: #fff; font-family: "Open Sans", sans-serif; padding: 20px; }
        h1 { font-family: "Lora", serif; color: #eb8046; margin-bottom: 12px; }
        .card { background: #111; border: 1px solid #2a2a2a; padding: 12px; border-radius: 8px; margin-bottom: 12px; }
        label { color: #eb8046; font-family: "Lora"; font-weight: bold; display:block; margin-top:8px; }
        textarea { width:100%; min-height:110px; background:#000; color:#fff; border-radius:10px; border:3px solid #eb8046; padding:8px; }
        input[type="text"], input[type="number"], select { width:100%; padding:8px; border-radius:8px; border:1px solid #eb8046; background:#5b3a92; color:#eb8046; }
        .btn { background:#eb8046; color:#000; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; margin-top:8px; }
        .small { font-size:0.9rem; color:#ccc; }
        a { color:#eb8046; text-decoration:none; }
        .user-icon { position: fixed; top: 20px; right: 20px; width: 36px; height: 36px; 
        border-radius: 50%; background-color: #eb8046; color: #000; font-size: 18px; 
        font-weight: bold; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="user-icon">U</div>

    <?php if (isset($_GET['success'])): ?>
        <?php if ($_GET['success'] === '1'): ?>
            <p style="color:lightgreen">Atualização realizada com sucesso!</p>
        <?php else: ?>
            <p style="color:red">Erro ao realizar a atualização.</p>
        <?php endif; ?>
    <?php endif; ?>

    <p>
        <a href="?action=list&type=artistas">Artistas</a> |
        <a href="?action=list&type=albuns">Álbuns</a> |
        <a href="?action=list&type=musicas">Músicas</a>
    </p>

    <hr>

    <?php if ($action === 'list' && $type === 'artistas'): ?>
        <h1>Lista de Artistas</h1>
        <?php if (!empty($data['artistas'])): ?>
            <?php foreach ($data['artistas'] as $a): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($a['nome'] ?? '') ?></h3>
                    <p class="small">País: <?= htmlspecialchars($a['pais_origem'] ?? '') ?></p>
                    <a href="?action=edit&type=artista&id=<?= (int)($a['id_artista'] ?? 0) ?>">Editar</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="small">Nenhum artista encontrado.</p>
        <?php endif; ?>


    <?php elseif ($action === 'list' && $type === 'albuns'): ?>
        <h1>Lista de Álbuns</h1>
        <?php if (!empty($data['albuns'])): ?>
            <?php foreach ($data['albuns'] as $al): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($al['titulo'] ?? '') ?></h3>
                    <p class="small">Data: <?= htmlspecialchars($al['data_lancamento'] ?? '') ?></p>
                    <a href="?action=edit&type=album&id=<?= (int)($al['id_album'] ?? 0) ?>">Editar</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="small">Nenhum álbum encontrado.</p>
        <?php endif; ?>


    <?php elseif ($action === 'list' && $type === 'musicas'): ?>
        <h1>Lista de Músicas</h1>
        <?php if (!empty($data['musicas'])): ?>
            <?php foreach ($data['musicas'] as $m): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($m['titulo'] ?? '') ?></h3>
                    <p class="small">Duração: <?= (int)($m['duracao_segundos'] ?? 0) ?>s</p>
                    <a href="?action=edit&type=musica&id=<?= (int)($m['id_musica'] ?? 0) ?>">Editar</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="small">Nenhuma música encontrada.</p>
        <?php endif; ?>

    <?php elseif ($action === 'edit' && $type === 'artista' && !empty($data['artista'])): 
        $artista = $data['artista'];
    ?>
        <h1>Editar Artista: <?= htmlspecialchars($artista['nome'] ?? '') ?></h1>
        <form action="?action=update&type=artista" method="post" class="card">
            <input type="hidden" name="id_artista" value="<?= (int)($artista['id_artista'] ?? 0) ?>">

            <label for="nome_artista">Nome</label>
            <input id="nome_artista" type="text" name="nome" value="<?= htmlspecialchars($artista['nome'] ?? '') ?>">

            <label for="biografia">Biografia</label>
            <textarea id="biografia" name="biografia"><?= htmlspecialchars($artista['biografia'] ?? '') ?></textarea>

            <label for="foto_url">URL da Foto</label>
            <input id="foto_url" type="text" name="foto_artista_url" value="<?= htmlspecialchars($artista['foto_artista_url'] ?? '') ?>">

            <label for="ano_inicio">Ano de Início</label>
            <input id="ano_inicio" type="number" name="ano_inicio_atividade" value="<?= htmlspecialchars((string)($artista['ano_inicio_atividade'] ?? '')) ?>">

            <label for="pais_origem">País de Origem</label>
            <input id="pais_origem" type="text" name="pais_origem" value="<?= htmlspecialchars($artista['pais_origem'] ?? '') ?>">

            <button type="submit" class="btn">Salvar alterações</button>
        </form>

    <?php elseif ($action === 'edit' && $type === 'album' && !empty($data['album'])): 
        $album = $data['album'];
    ?>
        <h1>Editar Álbum: <?= htmlspecialchars($album['titulo'] ?? '') ?></h1>
        <form action="?action=update&type=album" method="post" class="card">
            <input type="hidden" name="id_album" value="<?= (int)($album['id_album'] ?? 0) ?>">

            <label for="titulo_album">Título</label>
            <input id="titulo_album" type="text" name="titulo" value="<?= htmlspecialchars($album['titulo'] ?? '') ?>">

            <label for="data_lancamento">Data de Lançamento</label>
            <input id="data_lancamento" type="text" name="data_lancamento" value="<?= htmlspecialchars($album['data_lancamento'] ?? '') ?>">

            <label for="capa_url">URL da Capa</label>
            <input id="capa_url" type="text" name="capa_album_url" value="<?= htmlspecialchars($album['capa_album_url'] ?? '') ?>">

            <label for="genero">Gênero</label>
            <input id="genero" type="text" name="genero" value="<?= htmlspecialchars($album['genero'] ?? '') ?>">

            <label for="tipo_album">Tipo</label>
            <select id="tipo_album" name="tipo">
                <option value="Album" <?= (isset($album['tipo']) && $album['tipo'] === 'Album') ? 'selected' : '' ?>>Album</option>
                <option value="EP" <?= (isset($album['tipo']) && $album['tipo'] === 'EP') ? 'selected' : '' ?>>EP</option>
                <option value="Single" <?= (isset($album['tipo']) && $album['tipo'] === 'Single') ? 'selected' : '' ?>>Single</option>
                <option value="Coletânea" <?= (isset($album['tipo']) && $album['tipo'] === 'Coletânea') ? 'selected' : '' ?>>Coletânea</option>
            </select>

            <button type="submit" class="btn">Salvar alterações</button>
        </form>

    <?php elseif ($action === 'edit' && $type === 'musica' && !empty($data['musica'])):
        $musica = $data['musica'];
    ?>
        <h1>Editar Música: <?= htmlspecialchars($musica['titulo'] ?? '') ?></h1>
        <form action="?action=update&type=musica" method="post" class="card">
            <input type="hidden" name="id_musica" value="<?= (int)($musica['id_musica'] ?? 0) ?>">

            <label for="titulo_musica">Título</label>
            <input id="titulo_musica" type="text" name="titulo" value="<?= htmlspecialchars($musica['titulo'] ?? '') ?>">

            <label for="duracao_segundos">Duração (segundos)</label>
            <input id="duracao_segundos" type="number" name="duracao_segundos" value="<?= htmlspecialchars((string)($musica['duracao_segundos'] ?? '')) ?>">

            <label for="numero_faixa">Número da Faixa</label>
            <input id="numero_faixa" type="number" name="numero_faixa" value="<?= htmlspecialchars((string)($musica['numero_faixa'] ?? '')) ?>">

            <button type="submit" class="btn">Salvar alterações</button>
        </form>

    <?php else: ?>
        <h1>Área de Edição!</h1>
        <p class="small">Use o menu acima para listar e editar artistas, álbuns ou músicas.</p>
    <?php endif; ?>

</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($album['titulo']); ?> - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/PaginaListarGiovana.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="listar_giovana.php"><img src="../../public/images/LogoF.png" alt="Logo MyBeat"></a>
    </div>
</header>

<main>
    <section class="album-detalhes">
        <div class="cover">
            <?php if (!empty($album['capa_album_url'])): ?>
                <img src="<?php echo htmlspecialchars($album['capa_album_url']); ?>" alt="Capa do álbum">
            <?php endif; ?>
        </div>
        <div class="info">
            <h2><?php echo htmlspecialchars($album['titulo']); ?></h2>
            <p><strong>Artista:</strong> <?php echo htmlspecialchars($album['nome_artista']); ?></p>
            <p><strong>Lançamento:</strong> <?php echo htmlspecialchars($album['data_lancamento']); ?></p>
            <p><strong>Gênero:</strong> <?php echo htmlspecialchars($album['genero']); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($album['tipo']); ?></p>
        </div>
    </section>

    <section class="musicas">
        <h3>Faixas</h3>
        <ul>
            <?php if ($musicas && $musicas->num_rows > 0): ?>
                <?php while ($m = $musicas->fetch_assoc()): ?>
                    <li>
                        <span class="faixa-numero"><?php echo htmlspecialchars($m['numero_faixa']); ?></span>
                        <div class="info">
                            <p>
                                <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo $m['id_musica']; ?>" class="titulo-musica">
                                    <?php echo htmlspecialchars($m['titulo']); ?>
                                </a>
                            </p>
                            <p><strong>Duração:</strong> <?php echo gmdate("i:s", (int)$m['duracao_segundos']); ?></p>
                        </div>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>Nenhuma faixa cadastrada.</li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="avaliacoes">
        <h3>Avaliações</h3>
        <?php if ($avaliacoes && $avaliacoes->num_rows > 0): ?>
            <ul>
                <?php while ($av = $avaliacoes->fetch_assoc()): ?>
                    <li>
                        <p class="nota">Nota: <?php echo (float)$av['nota']; ?> ★</p>
                        <p><?php echo nl2br(htmlspecialchars($av['texto_review'])); ?></p>
                        <p class="data">por <?php echo htmlspecialchars($av['nome_exibicao']); ?> em <?php echo htmlspecialchars($av['data_avaliacao']); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma avaliação ainda.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>

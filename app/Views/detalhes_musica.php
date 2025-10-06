<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($musica['titulo']); ?> - MyBeat</title>
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
            <?php if (!empty($musica['capa_album_url'])): ?>
                <img src="<?php echo htmlspecialchars($musica['capa_album_url']); ?>" alt="Capa do álbum">
            <?php endif; ?>
        </div>
        <div class="info">
            <h2>
                <a href="listar_giovana.php?controller=musica&action=detalhes&id=<?php echo $musica['id_musica']; ?>" class="titulo-musica">
                    <?php echo htmlspecialchars($musica['titulo']); ?>
                </a>
            </h2>
            <p><strong>Artista:</strong> <?php echo htmlspecialchars($musica['nome_artista']); ?></p>
            <p><strong>Álbum:</strong>
                <a href="listar_giovana.php?controller=album&action=detalhes&id=<?php echo $musica['id_album']; ?>" class="titulo-album">
                    <?php echo htmlspecialchars($musica['titulo_album']); ?>
                </a>
            </p>
            <p><strong>Duração:</strong> <?php echo gmdate("i:s", (int)$musica['duracao_segundos']); ?></p>
            <p><strong>Faixa nº:</strong> <?php echo htmlspecialchars($musica['numero_faixa']); ?></p>
        </div>
    </section>
</main>
</body>
</html>

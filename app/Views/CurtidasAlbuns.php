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
    <title>Meus Álbuns Curtidos - MyBeat</title>
    
    <link rel="stylesheet" href="/Mybeat/public/css/avaliacao.css"> 
    
    <link rel="stylesheet" href="/Mybeat/public/css/home_usuario.css"> 
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        /* Estilo para a página de álbuns curtidos */
        .main-content {
            padding: 40px 0;
            min-height: 70vh; /* Garante que o rodapé fique embaixo */
        }
        .page-title {
            text-align: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 40px;
        }
        
        .no-results {
            text-align: center;
            font-size: 1.2rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="avaliacao-page"> <header>
            <div class="container">
                <div class="logo">
                    <a href="/Mybeat/app/Views/home_usuario.php" class="logo-link">
                        <img src="/Mybeat/public/images/LogoF.png" alt="Mybeat Logo">
                        <h1>MyBeat</h1>
                    </a>
                </div>
                </div>
        </header>

        <main class="main-content">
            <div class="container">
                <h1 class="page-title">Meus Álbuns Curtidos</h1>

                <?php if (isset($albuns) && $albuns->num_rows > 0): ?>
                    
                    <section class="album-grid">
                        <?php while ($album = $albuns->fetch_assoc()): ?>
                            <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?= (int)$album['id_album'] ?>" class="album-card">
                                <img src="<?= htmlspecialchars($album['capa_album_url'] ?? '/Mybeat/public/images/LogoF.png') ?>" alt="Capa do <?= htmlspecialchars($album['titulo']) ?>" class="album-cover">
                                <h3 class="album-title"><?= htmlspecialchars($album['titulo']) ?></h3>
                                <p class="artist-name"><?= htmlspecialchars($album['nome_artista']) ?></p>
                                <span class="album-year"><?= htmlspecialchars(date('Y', strtotime($album['data_lancamento'] ?? 'now'))) ?></span>
                            </a>
                        <?php endwhile; ?>
                    </section>

                <?php else: ?>
                    
                    <p class="no-results">Você ainda não curtiu nenhum álbum.</p>

                <?php endif; ?>

            </div>
        </main>

        <footer>
            <div class="container">
                <p>&copy; 2025 MyBeat. Todos os direitos reservados.</p>
            </div>
        </footer>
    </div>
</body>
</html>
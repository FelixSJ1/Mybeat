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

        /* ★★★ COLE ESTE BLOCO A PARTIR DAQUI ★★★ */

        /* Define a grade de álbuns */
        .album-grid { 
            display: grid;
            /* Cria colunas responsivas: no mínimo 180px, 
               e preenche o espaço */
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px; /* Espaço entre os álbuns */
        }

        /* Estilo do Card do Álbum (copiado da sua home) */
        .album-card {
            background-color: #1e1e1e; /* Fundo do card (do seu tema) */
            border-radius: 8px;
            overflow: hidden; /* Para a imagem não vazar */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            text-decoration: none;
            color: var(--text-light);
            transition: transform 0.2s ease;
            display: block; /* Garante que o link ocupe o espaço */
        }

        .album-card:hover {
            transform: translateY(-5px); /* Efeito "flutuar" */
        }

        /* Estilo da Capa do Álbum */
        .album-card .album-cover {
            width: 100%;
            aspect-ratio: 1 / 1; /* Garante que a capa seja quadrada */
            object-fit: cover;
        }

        /* Estilo do Título do Álbum */
        .album-card .album-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-light);
            margin: 10px 15px 5px 15px;
            /* Limita o texto para 2 linhas */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Estilo do Nome do Artista e Ano */
        .album-card .artist-name,
        .album-card .album-year {
            font-size: 0.9rem;
            color: var(--text-dark);
            margin: 0 15px 10px 15px;
            display: block; /* Garante que o <p> e <span> fiquem em linhas separadas */
        }

        .album-card .album-year {
            font-size: 0.8rem;
            margin-top: -5px; /* Puxa o ano para perto do nome */
            margin-bottom: 15px;
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
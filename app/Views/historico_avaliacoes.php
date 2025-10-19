<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Models/AvaliacaoModel.php';
require_once __DIR__ . '/../Controllers/AvaliacaoController.php';

$id_usuario = $_SESSION['id_usuario'];

try {
    // Buscar foto do perfil usando o conector.php
    $stmt = $conn->prepare("SELECT foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $foto_perfil = $usuario['foto_perfil_url'] ?? '../../public/images/Perfil_Usuario.png';
    $stmt->close();
    
} catch (Exception $e) {
    $foto_perfil = '../../public/images/Perfil_Usuario.png';
}

// Instanciar controller e buscar avaliações
$avaliacaoController = new AvaliacaoController($conn);
$avaliacoes = $avaliacaoController->getHistoricoUsuario($id_usuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>MyBeat - Histórico de Avaliações</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../public/css/historico_avaliacoes.css" rel="stylesheet">
    <style>
        .user-circle {
            cursor: pointer;
        }
        .user-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="home_usuario.php" class="logo-link">
            <img src="../../public/images/LogoF.png" alt="MyBeat" class="logo-img">
            <span class="site-title">My Beat</span>
        </a>
    </div>

    <div class="header-nav">
        <a href="home_usuario.php" class="nav-link">Home</a>
        <a href="historico_avaliacoes.php" class="nav-link active">Minhas Avaliações</a>
    </div>

    <div class="user-circle" title="Meu Perfil">
        <a href="perfilUsuario.php" style="display: block; width: 100%; height: 100%;">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Usuário">
        </a>
    </div>
</header>

<main>
    <section class="historico-section">
        <div class="section-header">
            <h1>Histórico de Avaliações</h1>
            <p class="subtitle">Todas as suas avaliações de álbuns</p>
        </div>

        <?php if (count($avaliacoes) === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">♪</div>
                <h3>Nenhuma avaliação ainda</h3>
                <p>Você ainda não avaliou nenhum álbum. Comece a explorar e compartilhe suas opiniões!</p>
                <a href="home_usuario.php" class="btn-explorar">Explorar Álbuns</a>
            </div>
        <?php else: ?>
            <div class="avaliacoes-grid">
                <?php foreach ($avaliacoes as $av): ?>
                    <div class="avaliacao-card">
                        <div class="card-header">
                            <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$av['id_album']; ?>" class="album-cover">
                                <?php if (!empty($av['capa_album_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($av['capa_album_url']); ?>" alt="<?php echo htmlspecialchars($av['titulo_album']); ?>">
                                <?php else: ?>
                                    <div class="no-cover">Sem capa</div>
                                <?php endif; ?>
                            </a>
                            
                            <div class="album-info">
                                <h3>
                                    <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$av['id_album']; ?>">
                                        <?php echo htmlspecialchars($av['titulo_album']); ?>
                                    </a>
                                </h3>
                                <p class="artista"><?php echo htmlspecialchars($av['nome_artista']); ?></p>
                                <p class="meta-info">
                                    <span class="genero"><?php echo htmlspecialchars($av['genero'] ?? '—'); ?></span>
                                    <?php if (!empty($av['data_lancamento'])): ?>
                                        <span class="separador">•</span>
                                        <span class="ano"><?php echo date('Y', strtotime($av['data_lancamento'])); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="avaliacao-info">
                                <div class="nota-container">
                                    <span class="nota-label">Sua nota:</span>
                                    <span class="nota-valor"><?php echo number_format($av['nota'], 1, ',', '.'); ?></span>
                                    <span class="nota-max">/5,0</span>
                                </div>
                                
                                <div class="data-avaliacao">
                                    <?php 
                                    $data = new DateTime($av['data_avaliacao']);
                                    echo $data->format('d/m/Y \à\s H:i');
                                    ?>
                                </div>
                            </div>

                            <?php if (!empty($av['texto_review'])): ?>
                                <div class="review-texto">
                                    <p><?php echo nl2br(htmlspecialchars($av['texto_review'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer">
                            <a href="listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=<?php echo (int)$av['id_album']; ?>" class="btn-ver-album">
                                Ver Álbum
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>

<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../config/conector.php';

// Buscar dados do usuário
try {
    $stmt = $conn->prepare("SELECT nome_usuario, nome_exibicao, biografia, foto_perfil_url, banner_url FROM Usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $_SESSION['id_usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    if (!$usuario) {
        header('Location: FaçaLoginMyBeat.php');
        exit();
    }
    
    $nome_usuario = $usuario['nome_usuario'] ?? '';
    $nome_exibicao = $usuario['nome_exibicao'] ?? $nome_usuario;
    $biografia = $usuario['biografia'] ?? '';
    $foto_perfil_url = $usuario['foto_perfil_url'] ?? '../../public/images/Perfil_Usuario.png';
    $banner_url = $usuario['banner_url'] ?? '../../public/images/default_banner.jpg';
    
} catch (Exception $e) {
    $mensagem_erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Buscar álbuns favoritos (máximo 5)
$albuns_favoritos = [];
try {
    $stmt = $conn->prepare("
        SELECT af.id_album, af.posicao, a.titulo, a.capa_album_url, art.nome as nome_artista
        FROM Albuns_Favoritos af
        INNER JOIN Albuns a ON af.id_album = a.id_album
        INNER JOIN Artistas art ON a.id_artista = art.id_artista
        WHERE af.id_usuario = ?
        ORDER BY af.posicao ASC
        LIMIT 5
    ");
    $stmt->bind_param("i", $_SESSION['id_usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $albuns_favoritos[$row['posicao']] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Silenciosamente falha se a tabela não existir ainda
}

// Preencher slots vazios
for ($i = 1; $i <= 5; $i++) {
    if (!isset($albuns_favoritos[$i])) {
        $albuns_favoritos[$i] = null;
    }
}
ksort($albuns_favoritos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nome_exibicao); ?> - MyBeat</title>
    <link href="../../public/css/perfil_completo.css" rel="stylesheet">
    <link rel="stylesheet" href="/Mybeat/public/css/acessibilidade.css">
</head>
<body>
    <div class="container">
        <!-- Banner -->
        <div class="profile-banner">
            <img src="<?php echo htmlspecialchars($banner_url); ?>" alt="Banner do perfil">
        </div>

        <!-- Área do perfil -->
        <div class="profile-header">
            <div class="profile-info-wrapper">
                <div class="avatar-container">
                    <img src="<?php echo htmlspecialchars($foto_perfil_url); ?>" alt="Foto de perfil" class="profile-avatar">
                </div>
                
                <div class="profile-actions">
                    <a href="home_usuario.php" class="btn-back">← Voltar</a>
                    <a href="perfilUsuario.php" class="btn-edit">Editar perfil</a>
                </div>
            </div>

            <div class="profile-details">
                <h1 class="profile-name"><?php echo htmlspecialchars($nome_exibicao); ?></h1>
                <p class="profile-username">@<?php echo htmlspecialchars($nome_usuario); ?></p>
                
                <?php if (!empty($biografia)): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($biografia)); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Álbuns Favoritos -->
        <div class="favorites-section">
            <h2 class="section-title">Álbuns Favoritos</h2>
            <div class="favorites-grid">
                <?php foreach ($albuns_favoritos as $posicao => $album): ?>
                    <div class="favorite-card <?php echo $posicao === 3 ? 'favorite-top' : ''; ?>" data-posicao="<?php echo $posicao; ?>">
                        <?php if ($posicao === 3): ?>
                            <div class="crown-icon">👑</div>
                        <?php endif; ?>
                        
                        <div class="card-header">
                            <span class="card-label">Álbuns favoritos</span>
                        </div>

                        <?php if ($album): ?>
                            <div class="album-content">
                                <div class="album-cover">
                                    <img src="<?php echo htmlspecialchars($album['capa_album_url']); ?>" alt="<?php echo htmlspecialchars($album['titulo']); ?>">
                                </div>
                                <div class="album-info">
                                    <h3 class="album-title"><?php echo htmlspecialchars($album['titulo']); ?></h3>
                                    <p class="album-artist"><?php echo htmlspecialchars($album['nome_artista']); ?></p>
                                </div>
                                <button class="btn-remove" onclick="removerAlbum(<?php echo $posicao; ?>)">×</button>
                            </div>
                        <?php else: ?>
                            <div class="empty-slot">
                                <button class="btn-add" onclick="abrirSeletorAlbum(<?php echo $posicao; ?>)">
                                    <span class="plus-icon">+</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal para selecionar álbum -->
    <div id="albumModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Escolha um álbum favorito</h3>
                <button class="modal-close" onclick="fecharModal()">×</button>
            </div>
            
            <div class="modal-body">
                <input type="text" id="searchAlbum" placeholder="Buscar álbum..." class="search-input">
                <div id="albumList" class="album-list">
                    <!-- Preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="/Mybeat/public/js/acessibilidade.js" defer></script>
    <script>
        let posicaoAtual = null;

        function abrirSeletorAlbum(posicao) {
            posicaoAtual = posicao;
            document.getElementById('albumModal').style.display = 'flex';
            carregarAlbuns();
        }

        function fecharModal() {
            document.getElementById('albumModal').style.display = 'none';
            posicaoAtual = null;
        }

        async function carregarAlbuns(query = '') {
            try {
                const response = await fetch(`../Controllers/AlbumController.php?action=buscar&q=${encodeURIComponent(query)}`);
                const albuns = await response.json();
                
                const albumList = document.getElementById('albumList');
                albumList.innerHTML = '';
                
                albuns.forEach(album => {
                    const div = document.createElement('div');
                    div.className = 'album-item';
                    div.innerHTML = `
                        <img src="${album.capa_album_url}" alt="${album.titulo}">
                        <div class="album-item-info">
                            <h4>${album.titulo}</h4>
                            <p>${album.nome_artista}</p>
                        </div>
                    `;
                    div.onclick = () => selecionarAlbum(album.id_album);
                    albumList.appendChild(div);
                });
            } catch (error) {
                console.error('Erro ao carregar álbuns:', error);
            }
        }

        async function selecionarAlbum(idAlbum) {
            try {
                const formData = new FormData();
                formData.append('action', 'adicionar');
                formData.append('id_album', idAlbum);
                formData.append('posicao', posicaoAtual);

                const response = await fetch('../Controllers/AlbumFavoritoController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Erro ao adicionar álbum favorito');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        async function removerAlbum(posicao) {
            if (!confirm('Deseja remover este álbum dos favoritos?')) return;

            try {
                const formData = new FormData();
                formData.append('action', 'remover');
                formData.append('posicao', posicao);

                const response = await fetch('../Controllers/AlbumFavoritoController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Erro ao remover álbum favorito');
                }
            } catch (error) {
                console.error('Erro:', error);
            }
        }

        // Busca de álbuns
        let searchTimeout;
        document.getElementById('searchAlbum')?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                carregarAlbuns(e.target.value);
            }, 300);
        });

        // Fechar modal ao clicar fora
        window.onclick = (event) => {
            const modal = document.getElementById('albumModal');
            if (event.target === modal) {
                fecharModal();
            }
        };
    </script>
</body>
</html>
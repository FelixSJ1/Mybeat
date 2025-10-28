<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';

$grupoModel = new Grupo($conn);
$termo_busca = $_GET['q'] ?? '';

// Buscar grupos públicos
$grupos_publicos = $grupoModel->buscarGruposPublicos($termo_busca);

// Buscar grupos do usuário
$meus_grupos = $grupoModel->buscarGruposDoUsuario($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos - MyBeat</title>
    <link href="../../../public/css/home_usuario.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <a href="../home_usuario.php" class="btn-voltar">← Voltar</a>

        <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
            <div class="mensagem mensagem-sucesso">
                <?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); unset($_SESSION['mensagem_sucesso']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['mensagem_erro'])): ?>
            <div class="mensagem mensagem-erro">
                <?php echo htmlspecialchars($_SESSION['mensagem_erro']); unset($_SESSION['mensagem_erro']); ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h1>Grupos Musicais</h1>
            <a href="criar_grupo.php" class="btn-criar">+ Criar Grupo</a>
        </div>

        <div class="search-section">
            <form method="GET" action="lista_grupos.php">
                <input 
                    type="text" 
                    name="q" 
                    class="search-box" 
                    placeholder="Pesquisar grupos..."
                    value="<?php echo htmlspecialchars($termo_busca); ?>"
                >
            </form>
        </div>

        <!-- MEUS GRUPOS -->
        <h2 class="section-title">Meus Grupos</h2>
        <div class="grupos-grid">
            <?php 
            $meus_grupos_array = [];
            while ($grupo = $meus_grupos->fetch_assoc()) {
                $meus_grupos_array[] = $grupo;
            }
            
            if (empty($meus_grupos_array)): 
            ?>
                <div class="empty-state">
                    <h3>Você ainda não entrou em nenhum grupo</h3>
                    <p>Explore os grupos públicos abaixo ou crie o seu próprio!</p>
                </div>
            <?php else: ?>
                <?php foreach ($meus_grupos_array as $grupo): ?>
                    <div class="grupo-card">
                        <div class="grupo-header">
                            <img src="<?php echo htmlspecialchars($grupo['foto_grupo_url']); ?>" alt="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" class="grupo-foto">
                            <div class="grupo-info">
                                <h3><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h3>
                                <p class="grupo-meta"><?php echo (int)$grupo['total_membros']; ?> membros</p>
                            </div>
                        </div>
                        <p class="grupo-descricao">
                            <?php echo htmlspecialchars(substr($grupo['descricao'] ?? 'Sem descrição', 0, 100)); ?>
                            <?php if (strlen($grupo['descricao'] ?? '') > 100) echo '...'; ?>
                        </p>
                        <div class="grupo-footer">
                            <span class="grupo-meta">Sua função: <strong><?php echo htmlspecialchars($grupo['role']); ?></strong></span>
                            <a href="grupo_chat.php?id=<?php echo (int)$grupo['id_grupo']; ?>" class="btn-acessar">Acessar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- GRUPOS PÚBLICOS -->
        <h2 class="section-title">Descobrir Grupos</h2>
        <div class="grupos-grid">
            <?php 
            $grupos_publicos_array = [];
            while ($grupo = $grupos_publicos->fetch_assoc()) {
                $grupos_publicos_array[] = $grupo;
            }
            
            if (empty($grupos_publicos_array)): 
            ?>
                <div class="empty-state">
                    <h3>Nenhum grupo encontrado</h3>
                    <p>Tente outra pesquisa ou seja o primeiro a criar um grupo!</p>
                </div>
            <?php else: ?>
                <?php foreach ($grupos_publicos_array as $grupo): ?>
                    <?php 
                    // Verificar se o usuário já é membro
                    $eh_membro = $grupoModel->ehMembro((int)$grupo['id_grupo'], $_SESSION['id_usuario']);
                    ?>
                    <div class="grupo-card">
                        <div class="grupo-header">
                            <img src="<?php echo htmlspecialchars($grupo['foto_grupo_url']); ?>" alt="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" class="grupo-foto">
                            <div class="grupo-info">
                                <h3><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h3>
                                <p class="grupo-meta">
                                    <?php echo (int)$grupo['total_membros']; ?> membros · 
                                    Criado por <?php echo htmlspecialchars($grupo['nome_criador']); ?>
                                </p>
                            </div>
                        </div>
                        <p class="grupo-descricao">
                            <?php echo htmlspecialchars(substr($grupo['descricao'] ?? 'Sem descrição', 0, 100)); ?>
                            <?php if (strlen($grupo['descricao'] ?? '') > 100) echo '...'; ?>
                        </p>
                        <div class="grupo-footer">
                            <span class="grupo-meta">Criado em <?php echo date('d/m/Y', strtotime($grupo['data_criacao'])); ?></span>
                            <?php if ($eh_membro): ?>
                                <a href="grupo_chat.php?id=<?php echo (int)$grupo['id_grupo']; ?>" class="btn-acessar">Acessar</a>
                            <?php else: ?>
                                <form method="POST" action="processar_entrar_grupo.php" style="margin: 0;">
                                    <input type="hidden" name="id_grupo" value="<?php echo (int)$grupo['id_grupo']; ?>">
                                    <button type="submit" class="btn-entrar">Entrar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
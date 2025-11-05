<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../config/conector.php';

$mensagem_sucesso = '';
$mensagem_erro = '';

// Buscar dados atuais do usuário
try {
    $stmt = $conn->prepare("SELECT nome_usuario, nome_exibicao, biografia, foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
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
    $nome_exibicao = $usuario['nome_exibicao'] ?? '';
    $biografia = $usuario['biografia'] ?? '';
    $foto_perfil_url = $usuario['foto_perfil_url'] ?? '';
    
} catch (Exception $e) {
    $mensagem_erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_nome_exibicao = trim($_POST['nome_exibicao'] ?? '');
    $nova_biografia = trim($_POST['biografia'] ?? '');
    
    // Validações
    if (strlen($novo_nome_exibicao) > 100) {
        $mensagem_erro = "O nome de exibição deve ter no máximo 100 caracteres.";
    } elseif (strlen($nova_biografia) > 500) {
        $mensagem_erro = "A biografia deve ter no máximo 500 caracteres.";
    } else {
        try {
            // Upload da foto se enviada
            $nova_foto_url = $foto_perfil_url;
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $_FILES['foto_perfil'];
                $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($extensao, $extensoes_permitidas)) {
                    $nome_arquivo = uniqid('perfil_') . '.' . $extensao;
                    $caminho_destino = __DIR__ . '/../../public/images/perfis/' . $nome_arquivo;
                    
                    // Criar diretório se não existir
                    if (!file_exists(__DIR__ . '/../../public/images/perfis/')) {
                        mkdir(__DIR__ . '/../../public/images/perfis/', 0777, true);
                    }
                    
                    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                        $nova_foto_url = '../../public/images/perfis/' . $nome_arquivo;
                    }
                } else {
                    $mensagem_erro = "Formato de imagem não permitido. Use JPG, JPEG, PNG ou GIF.";
                }
            }
            
            if (empty($mensagem_erro)) {
                // Atualizar banco de dados
                $stmt = $conn->prepare("UPDATE Usuarios SET nome_exibicao = ?, biografia = ?, foto_perfil_url = ? WHERE id_usuario = ?");
                $stmt->bind_param("sssi", $novo_nome_exibicao, $nova_biografia, $nova_foto_url, $_SESSION['id_usuario']);
                $stmt->execute();
                $stmt->close();
                
                $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
                header('Location: home_usuario.php');
                exit();
            }
        } catch (Exception $e) {
            $mensagem_erro = "Erro ao atualizar perfil: " . $e->getMessage();
        }
    }
}

// Calcular contadores
$contador_nome = strlen($nome_exibicao);
$contador_bio = strlen($biografia);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - myBeat</title>
    <link href="../../public/css/perfilUsuario.css" rel="stylesheet">
    <link rel="stylesheet" href="/Mybeat/public/css/acessibilidade.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Editar Perfil</h1>
            <p>Personalize suas informações</p>
        </div>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="mensagem mensagem-sucesso">
                <?php echo htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="mensagem mensagem-erro">
                <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="profile-photo-section">
                <div class="profile-photo-container <?php echo empty($foto_perfil_url) ? 'empty' : ''; ?>">
                    <?php if (!empty($foto_perfil_url)): ?>
                        <img src="<?php echo htmlspecialchars($foto_perfil_url); ?>" alt="Foto do perfil">
                    <?php endif; ?>
                </div>
                <label for="foto_perfil" class="photo-upload-label">
                    Alterar Foto
                </label>
                <input 
                    type="file" 
                    id="foto_perfil" 
                    name="foto_perfil" 
                    accept="image/*"
                    style="display: none;"
                >
            </div>

            <div class="form-group">
                <label for="nome_usuario">Nome de Usuário</label>
                <input 
                    type="text" 
                    id="nome_usuario" 
                    value="<?php echo htmlspecialchars($nome_usuario); ?>"
                    disabled
                    style="opacity: 0.6; cursor: not-allowed;"
                >
            </div>

            <div class="form-group">
                <label for="nome_exibicao">Nome de Exibição</label>
                <input 
                    type="text" 
                    id="nome_exibicao" 
                    name="nome_exibicao" 
                    placeholder="Como você quer ser chamado"
                    maxlength="100"
                    value="<?php echo htmlspecialchars($nome_exibicao); ?>"
                >
                <div class="char-count <?php echo $contador_nome > 90 ? 'warning' : ''; ?>">
                    <?php echo $contador_nome; ?>/100
                </div>
            </div>

            <div class="form-group">
                <label for="biografia">Biografia</label>
                <textarea 
                    id="biografia" 
                    name="biografia" 
                    placeholder="Conte um pouco sobre você..."
                    maxlength="500"
                ><?php echo htmlspecialchars($biografia); ?></textarea>
                <div class="char-count <?php echo $contador_bio > 450 ? 'warning' : ''; ?>">
                    <?php echo $contador_bio; ?>/500
                </div>
            </div>

            <div class="info-box">
                <strong>💡 Dica:</strong> Seu nome de usuário não pode ser alterado. Use o nome de exibição para personalizar como você aparece para outros usuários.
            </div>

            <div class="button-group">
                <a href="home_usuario.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
    <script src="/Mybeat/public/js/acessibilidade.js" defer></script>
    <script src="/Mybeat/public/js/perfilUsuario-voice.js" defer></script>
</body>
</html>
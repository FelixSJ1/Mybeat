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
    $nome_exibicao = $usuario['nome_exibicao'] ?? '';
    $biografia = $usuario['biografia'] ?? '';
    $foto_perfil_url = $usuario['foto_perfil_url'] ?? '';
    $banner_url = $usuario['banner_url'] ?? '';
    
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
            $nova_foto_url = $foto_perfil_url;
            $novo_banner_url = $banner_url;
            
            // Upload da foto de perfil
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $_FILES['foto_perfil'];
                $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($extensao, $extensoes_permitidas)) {
                    $nome_arquivo = uniqid('perfil_') . '.' . $extensao;
                    $caminho_destino = __DIR__ . '/../../public/images/perfis/' . $nome_arquivo;
                    
                    if (!file_exists(__DIR__ . '/../../public/images/perfis/')) {
                        mkdir(__DIR__ . '/../../public/images/perfis/', 0777, true);
                    }
                    
                    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                        $nova_foto_url = '../../public/images/perfis/' . $nome_arquivo;
                    }
                } else {
                    $mensagem_erro = "Formato de imagem não permitido para foto de perfil. Use JPG, JPEG, PNG ou GIF.";
                }
            }
            
            // Upload do banner
            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                $arquivo = $_FILES['banner'];
                $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($extensao, $extensoes_permitidas)) {
                    $nome_arquivo = uniqid('banner_') . '.' . $extensao;
                    $caminho_destino = __DIR__ . '/../../public/images/banners/' . $nome_arquivo;
                    
                    if (!file_exists(__DIR__ . '/../../public/images/banners/')) {
                        mkdir(__DIR__ . '/../../public/images/banners/', 0777, true);
                    }
                    
                    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                        $novo_banner_url = '../../public/images/banners/' . $nome_arquivo;
                    }
                } else {
                    $mensagem_erro = "Formato de imagem não permitido para banner. Use JPG, JPEG, PNG ou GIF.";
                }
            }
            
            if (empty($mensagem_erro)) {
                // Atualizar banco de dados
                $stmt = $conn->prepare("UPDATE Usuarios SET nome_exibicao = ?, biografia = ?, foto_perfil_url = ?, banner_url = ? WHERE id_usuario = ?");
                $stmt->bind_param("ssssi", $novo_nome_exibicao, $nova_biografia, $nova_foto_url, $novo_banner_url, $_SESSION['id_usuario']);
                $stmt->execute();
                $stmt->close();
                
                $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
                
                // Verificar se veio do Perfil_completo
                $redirect = $_GET['from'] === 'perfil_completo' ? 'Perfil_completo.php' : 'home_usuario.php';
                header("Location: {$redirect}");
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
    <style>
        .banner-section {
            margin-bottom: 30px;
        }
        
        .banner-preview {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #A64AC9, #EB8046);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }
        
        .banner-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .banner-preview.empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        .banner-upload-label {
            display: inline-block;
            background: linear-gradient(135deg, #A64AC9, #9333ea);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .banner-upload-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(166, 74, 201, 0.3);
        }
    </style>
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
            <!-- Banner -->
            <div class="banner-section">
                
                <div class="banner-preview <?php echo empty($banner_url) ? 'empty' : ''; ?>">
                    <?php if (!empty($banner_url)): ?>
                        <img src="<?php echo htmlspecialchars($banner_url); ?>" alt="Banner do perfil">
                    <?php else: ?>
                        <span>Nenhum banner selecionado</span>
                    <?php endif; ?>
                </div>
                <label for="banner" class="banner-upload-label">
                    Alterar Banner
                </label>
                <input 
                    type="file" 
                    id="banner" 
                    name="banner" 
                    accept="image/*"
                    style="display: none;"
                >
            </div>

            <!-- Foto de Perfil -->
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
                <a href="<?php echo isset($_GET['from']) && $_GET['from'] === 'perfil_completo' ? 'Perfil_completo.php' : 'home_usuario.php'; ?>" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
    
    <script>
        // Preview de imagens
        document.getElementById('foto_perfil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.querySelector('.profile-photo-container');
                    container.classList.remove('empty');
                    container.innerHTML = `<img src="${e.target.result}" alt="Foto do perfil">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        document.getElementById('banner').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.banner-preview');
                    preview.classList.remove('empty');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Banner do perfil">`;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Contadores de caracteres
        document.getElementById('nome_exibicao').addEventListener('input', function() {
            const count = this.value.length;
            const counter = this.nextElementSibling;
            counter.textContent = count + '/100';
            counter.classList.toggle('warning', count > 90);
        });
        
        document.getElementById('biografia').addEventListener('input', function() {
            const count = this.value.length;
            const counter = this.nextElementSibling;
            counter.textContent = count + '/500';
            counter.classList.toggle('warning', count > 450);
        });
    </script>
    
    <script src="/Mybeat/public/js/acessibilidade.js" defer></script>
    <script src="/Mybeat/public/js/perfilUsuario-voice.js" defer></script>
</body>
</html>
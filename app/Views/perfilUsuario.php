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
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=MyBeatDB", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT nome_usuario, nome_exibicao, biografia, foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['id_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        header('Location: FaçaLoginMyBeat.php');
        exit();
    }
    
    $nome_usuario = $usuario['nome_usuario'] ?? '';
    $nome_exibicao = $usuario['nome_exibicao'] ?? '';
    $biografia = $usuario['biografia'] ?? '';
    $foto_perfil_url = $usuario['foto_perfil_url'] ?? '';
    
} catch (PDOException $e) {
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
                $stmt = $pdo->prepare("UPDATE Usuarios SET nome_exibicao = ?, biografia = ?, foto_perfil_url = ? WHERE id_usuario = ?");
                $stmt->execute([$novo_nome_exibicao, $nova_biografia, $nova_foto_url, $_SESSION['id_usuario']]);
                
                $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
                header('Location: home_usuario.php');
                exit();
            }
        } catch (PDOException $e) {
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: #1a1a1a;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            border: 1px solid #2a2a2a;
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2a2a2a;
        }

        .header h1 {
            color: #ffffff;
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .header p {
            color: #888888;
            font-size: 14px;
        }

        .mensagem {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .mensagem-sucesso {
            background: rgba(138, 43, 226, 0.1);
            color: #a855f7;
            border-left: 4px solid #a855f7;
        }

        .mensagem-erro {
            background: rgba(255, 140, 66, 0.1);
            color: #ff8c42;
            border-left: 4px solid #ff8c42;
        }

        .profile-photo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-photo-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            background: #2a2a2a;
            border: 3px solid #3a3a3a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .profile-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-photo-container.empty::before {
            content: '👤';
            font-size: 60px;
            color: #555555;
        }

        .photo-upload-label {
            display: inline-block;
            background: linear-gradient(135deg, #a855f7 0%, #ff8c42 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .photo-upload-label:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #2a2a2a;
            border-radius: 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            background: #0a0a0a;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #a855f7;
            background: #141414;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            max-height: 200px;
        }

        .char-count {
            font-size: 12px;
            color: #666666;
            margin-top: 5px;
            text-align: right;
        }

        .char-count.warning {
            color: #ff8c42;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #a855f7 0%, #ff8c42 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #2a2a2a;
            color: #ffffff;
            border: 2px solid #3a3a3a;
        }

        .btn-secondary:hover {
            background: #3a3a3a;
            transform: translateY(-2px);
        }

        .info-box {
            background: #2a2a2a;
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #888888;
            border-left: 4px solid #ff8c42;
        }

        .info-box strong {
            color: #ff8c42;
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
</body>
</html>
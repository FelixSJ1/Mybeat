<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Grupo - MyBeat</title>
    <link href="../../public/css/home_usuario.css" rel="stylesheet">
    <style>
        body {
            background: #0b0b0b;
            color: #ffffff;
            font-family: 'Open Sans', Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #151517;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            margin: 20px;
            border: 1px solid rgba(166, 74, 201, 0.18);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 2px solid #222;
        }

        .header h1 {
            color: #A64AC9;
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
            font-family: 'Lora', serif;
        }

        .header p {
            color: #bbb;
            font-size: 14px;
        }

        .mensagem {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .mensagem-erro {
            background: rgba(235, 128, 70, 0.2);
            color: #EB8046;
            border-left: 4px solid #EB8046;
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
            background: #222;
            border: 3px solid #A64AC9;
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
            content: '👥';
            font-size: 60px;
            color: #555555;
        }

        .photo-upload-label {
            display: inline-block;
            background: linear-gradient(135deg, #a855f7 0%, #EB8046 100%);
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
            border: 2px solid #222;
            border-radius: 8px;
            font-family: 'Open Sans', Arial, Helvetica, sans-serif;
            font-size: 14px;
            background: #0b0b0b;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #A64AC9;
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
            color: #EB8046;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #A64AC9;
        }

        .checkbox-group label {
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
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
            background: linear-gradient(135deg, #a855f7 0%, #EB8046 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #222;
            color: #ffffff;
            border: 2px solid #333;
        }

        .btn-secondary:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .info-box {
            background: #222;
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #bbb;
            border-left: 4px solid #EB8046;
        }

        .info-box strong {
            color: #EB8046;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Criar Novo Grupo</h1>
            <p>Crie um espaço para discutir música</p>
        </div>

        <?php if (!empty($_SESSION['mensagem_erro'])): ?>
            <div class="mensagem mensagem-erro">
                <?php echo htmlspecialchars($_SESSION['mensagem_erro']); unset($_SESSION['mensagem_erro']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="processar_criar_grupo.php" enctype="multipart/form-data">
            <div class="profile-photo-section">
                <div class="profile-photo-container empty" id="previewContainer">
                    <img id="previewImage" style="display: none;">
                </div>
                <label for="foto_grupo" class="photo-upload-label">
                    Escolher Foto do Grupo
                </label>
                <input 
                    type="file" 
                    id="foto_grupo" 
                    name="foto_grupo" 
                    accept="image/*"
                    style="display: none;"
                >
            </div>

            <div class="form-group">
                <label for="nome_grupo">Nome do Grupo *</label>
                <input 
                    type="text" 
                    id="nome_grupo" 
                    name="nome_grupo" 
                    placeholder="Ex: Fãs de Rock Progressivo"
                    maxlength="100"
                    required
                >
                <div class="char-count" id="nomeCount">0/100</div>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea 
                    id="descricao" 
                    name="descricao" 
                    placeholder="Descreva o propósito do grupo..."
                    maxlength="500"
                ></textarea>
                <div class="char-count" id="descricaoCount">0/500</div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="privado" name="privado">
                <label for="privado">Tornar este grupo privado (apenas por convite)</label>
            </div>

            <div class="info-box">
                <strong>💡 Dica:</strong> Grupos públicos podem ser descobertos e qualquer usuário pode entrar. Grupos privados só aceitam membros por convite.
            </div>

            <div class="button-group">
                <a href="lista_grupos.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Criar Grupo
                </button>
            </div>
        </form>
    </div>

    <script>
        // Preview da foto
        document.getElementById('foto_grupo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('previewImage');
                    const container = document.getElementById('previewContainer');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    container.classList.remove('empty');
                };
                reader.readAsDataURL(file);
            }
        });

        // Contador de caracteres - Nome
        const nomeInput = document.getElementById('nome_grupo');
        const nomeCount = document.getElementById('nomeCount');
        nomeInput.addEventListener('input', function() {
            const length = this.value.length;
            nomeCount.textContent = length + '/100';
            if (length > 90) {
                nomeCount.classList.add('warning');
            } else {
                nomeCount.classList.remove('warning');
            }
        });

        // Contador de caracteres - Descrição
        const descricaoInput = document.getElementById('descricao');
        const descricaoCount = document.getElementById('descricaoCount');
        descricaoInput.addEventListener('input', function() {
            const length = this.value.length;
            descricaoCount.textContent = length + '/500';
            if (length > 450) {
                descricaoCount.classList.add('warning');
            } else {
                descricaoCount.classList.remove('warning');
            }
        });
    </script>
</body>
</html>
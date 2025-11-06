<?php
// app/Views/register_face_admin.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Verifica se é admin logado
if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: ./FaçaLoginMyBeatADM.php');
    exit;
}

$admin_id = $_SESSION['admin_id'] ?? null;
$admin_email = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Reconhecimento Facial - myBeat Admin</title>
    <link rel="stylesheet" href="../../public/css/FaçaLoginStyleADM.css">
    <style>
        .face-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            text-align: center;
        }
        #video {
            width: 100%;
            max-width: 480px;
            border-radius: 8px;
            margin: 1rem auto;
            display: block;
            background: #000;
        }
        .status-message {
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 500;
        }
        .status-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .status-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .status-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .face-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .face-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .face-btn-primary {
            background: #EB8046;
            color: white;
        }
        .face-btn-primary:hover {
            background: #d9703d;
        }
        .face-btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .face-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .face-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #canvas { display: none; }
    </style>
</head>
<body>
    <div class="background-effects"></div>
    
    <div class="login-container">
        <div class="face-container">
            <header>
                <img src="../../public/images/LogoF.png" alt="Logo myBeat" class="logo1" style="height: 60px;">
                <h2>Cadastrar Reconhecimento Facial</h2>
                <p style="opacity: 0.7; margin-top: 0.5rem;">Admin: <?= htmlspecialchars($admin_email) ?></p>
            </header>

            <div id="status" class="status-message status-info">
                Carregando modelos de reconhecimento facial...
            </div>

            <video id="video" autoplay muted playsinline></video>
            <canvas id="canvas"></canvas>

            <div class="face-buttons">
                <button id="captureBtn" class="face-btn face-btn-primary" disabled>
                    Capturar Rosto
                </button>
                <button id="removeFaceBtn" class="face-btn face-btn-secondary" style="background: #ef4444; display: none;">
                    Remover Reconhecimento
                </button>
                <button id="cancelBtn" class="face-btn face-btn-secondary" onclick="window.location.href='./admin.php'">
                    Cancelar
                </button>
            </div>

            <p style="margin-top: 2rem; opacity: 0.6; font-size: 0.9rem;">
                Posicione seu rosto no centro da câmera. O sistema detectará automaticamente suas características faciais.
            </p>
        </div>
    </div>

    <!-- Face-api.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('captureBtn');
        const removeFaceBtn = document.getElementById('removeFaceBtn');
        const statusDiv = document.getElementById('status');
        let modelsLoaded = false;
        let faceDetected = false;

        // Verifica se já tem face cadastrada
        async function checkFaceStatus() {
            try {
                const response = await fetch('../Controllers/FaceAdminController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'check_face_status' })
                });
                const result = await response.json();
                if (result.success && result.face_registered) {
                    removeFaceBtn.style.display = 'inline-block';
                    updateStatus('Você já possui reconhecimento facial cadastrado. Capture novamente para atualizar.', 'info');
                }
            } catch (error) {
                console.error('Erro ao verificar status:', error);
            }
        }

        function updateStatus(message, type = 'info') {
            statusDiv.className = `status-message status-${type}`;
            statusDiv.textContent = message;
        }

        async function loadModels() {
            try {
                const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
                
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                
                modelsLoaded = true;
                updateStatus('Modelos carregados! Iniciando câmera...', 'success');
                await checkFaceStatus();
                await startVideo();
            } catch (error) {
                console.error('Erro ao carregar modelos:', error);
                updateStatus('Erro ao carregar modelos de IA. Recarregue a página.', 'error');
            }
        }

        async function startVideo() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    } 
                });
                video.srcObject = stream;
                
                video.addEventListener('playing', () => {
                    detectFace();
                    updateStatus('Posicione seu rosto no centro da câmera', 'info');
                });
            } catch (error) {
                console.error('Erro ao acessar câmera:', error);
                updateStatus('Erro ao acessar câmera. Verifique as permissões.', 'error');
            }
        }

        async function detectFace() {
            if (!modelsLoaded) return;

            const detections = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (detections) {
                faceDetected = true;
                captureBtn.disabled = false;
                updateStatus('Rosto detectado! Clique em "Capturar Rosto"', 'success');
            } else {
                faceDetected = false;
                captureBtn.disabled = true;
                if (modelsLoaded) {
                    updateStatus('Nenhum rosto detectado. Posicione-se melhor.', 'info');
                }
            }

            requestAnimationFrame(detectFace);
        }

        captureBtn.addEventListener('click', async () => {
            if (!faceDetected) {
                updateStatus('Nenhum rosto detectado. Tente novamente.', 'error');
                return;
            }

            captureBtn.disabled = true;
            updateStatus('Capturando características faciais...', 'info');

            try {
                const detections = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detections) {
                    throw new Error('Não foi possível detectar o rosto no momento da captura');
                }

                const descriptor = Array.from(detections.descriptor);
                
                const response = await fetch('../Controllers/FaceAdminController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'register_face',
                        descriptor: descriptor
                    })
                });

                const result = await response.json();

                if (result.success) {
                    updateStatus('Reconhecimento facial cadastrado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = './admin.php';
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Erro ao cadastrar reconhecimento facial');
                }
            } catch (error) {
                console.error('Erro:', error);
                updateStatus('Erro: ' + error.message, 'error');
                captureBtn.disabled = false;
            }
        });

        removeFaceBtn.addEventListener('click', async () => {
            if (!confirm('Tem certeza que deseja remover seu reconhecimento facial? Você precisará usar email e senha para fazer login.')) {
                return;
            }

            removeFaceBtn.disabled = true;
            updateStatus('Removendo reconhecimento facial...', 'info');

            try {
                const response = await fetch('../Controllers/FaceAdminController.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'remove_face' })
                });

                const result = await response.json();

                if (result.success) {
                    updateStatus('Reconhecimento facial removido com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = './admin.php';
                    }, 2000);
                } else {
                    throw new Error(result.message || 'Erro ao remover');
                }
            } catch (error) {
                console.error('Erro:', error);
                updateStatus('Erro: ' + error.message, 'error');
                removeFaceBtn.disabled = false;
            }
        });

        window.addEventListener('load', loadModels);
    </script>
</body>
</html>
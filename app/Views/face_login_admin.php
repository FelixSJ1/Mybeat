<?php
// app/Views/face_login_admin.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Facial - myBeat Admin</title>
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
        .status-warning { background: rgba(251, 191, 36, 0.1); color: #fbbf24; }
        .back-link {
            margin-top: 1.5rem;
            display: block;
            color: #EB8046;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        #canvas { display: none; }
    </style>
</head>
<body>
    <div class="background-effects"></div>
    
    <div class="login-container">
        <div class="face-container">
            <header>
                <img src="../../public/images/LogoF.png" alt="Logo myBeat" class="logo1" style="height: 60px;">
                <h2>Login com Reconhecimento Facial</h2>
            </header>

            <div id="status" class="status-message status-info">
                Carregando modelos de reconhecimento facial...
            </div>

            <video id="video" autoplay muted playsinline></video>
            <canvas id="canvas"></canvas>

            <a href="./FaçaLoginMyBeatADM.php" class="back-link">← Voltar para login tradicional</a>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    
    <script>
        const video = document.getElementById('video');
        const statusDiv = document.getElementById('status');
        let modelsLoaded = false;
        let isProcessing = false;

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
                    updateStatus('Posicione seu rosto no centro da câmera', 'info');
                    detectAndAuthenticate();
                });
            } catch (error) {
                console.error('Erro ao acessar câmera:', error);
                updateStatus('Erro ao acessar câmera. Verifique as permissões.', 'error');
            }
        }

        async function detectAndAuthenticate() {
            if (!modelsLoaded || isProcessing) {
                requestAnimationFrame(detectAndAuthenticate);
                return;
            }

            try {
                const detections = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detections) {
                    isProcessing = true;
                    updateStatus('Rosto detectado! Autenticando...', 'warning');

                    const descriptor = Array.from(detections.descriptor);
                    
                    const response = await fetch('../Controllers/FaceAdminController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'authenticate_face',
                            descriptor: descriptor
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        updateStatus('Autenticação bem-sucedida! Redirecionando...', 'success');
                        setTimeout(() => {
                            window.location.href = './admin.php';
                        }, 1500);
                        return; // Para a detecção
                    } else {
                        updateStatus('Rosto não reconhecido. Tente novamente.', 'error');
                        isProcessing = false;
                    }
                }
            } catch (error) {
                console.error('Erro na autenticação:', error);
                isProcessing = false;
            }

            requestAnimationFrame(detectAndAuthenticate);
        }

        window.addEventListener('load', loadModels);
    </script>
</body>
</html>
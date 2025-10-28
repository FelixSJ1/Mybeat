<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../FaçaLoginMyBeat.php');
    exit();
}

require_once __DIR__ . '/../../config/conector.php';
require_once __DIR__ . '/../../Models/Grupo.php';
require_once __DIR__ . '/../../Models/Chat.php';

$id_grupo = (int)($_GET['id'] ?? 0);
$id_usuario = $_SESSION['id_usuario'];

if ($id_grupo <= 0) {
    $_SESSION['mensagem_erro'] = "Grupo inválido.";
    header('Location: lista_grupos.php');
    exit();
}

$grupoModel = new Grupo($conn);
$chatModel = new Chat($conn);

// Verificar se o grupo existe
$grupo = $grupoModel->buscarPorId($id_grupo);
if (!$grupo) {
    $_SESSION['mensagem_erro'] = "Grupo não encontrado.";
    header('Location: lista_grupos.php');
    exit();
}

// Verificar se é membro
if (!$grupoModel->ehMembro($id_grupo, $id_usuario)) {
    $_SESSION['mensagem_erro'] = "Você não é membro deste grupo.";
    header('Location: lista_grupos.php');
    exit();
}

// Buscar mensagens (últimas 50)
$mensagens_result = $chatModel->buscarMensagens($id_grupo, 50, 0);
$mensagens = [];
while ($msg = $mensagens_result->fetch_assoc()) {
    $mensagens[] = $msg;
}
$mensagens = array_reverse($mensagens); // Ordem cronológica

// Buscar membros
$membros_result = $grupoModel->buscarMembros($id_grupo);
$membros = [];
while ($membro = $membros_result->fetch_assoc()) {
    $membros[] = $membro;
}

// Buscar informações do usuário atual
$stmt = $conn->prepare("SELECT nome_usuario, foto_perfil_url FROM Usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario_atual = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($grupo['nome_grupo']); ?> - MyBeat</title>
    <link href="../../../public/css/chat.css" rel="stylesheet">
    
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="grupo-info">
                <img src="<?php echo htmlspecialchars($grupo['foto_grupo_url']); ?>" alt="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" class="grupo-foto-grande">
                <h2><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h2>
                <p><?php echo (int)$grupo['total_membros']; ?> membros</p>
            </div>

            <div class="membros-section">
                <h3>Membros</h3>
                <?php foreach ($membros as $membro): ?>
                    <div class="membro-item">
                        <img src="../../../public/images/perfis/<?php echo htmlspecialchars(basename($membro['foto_perfil_url'] ?? 'Perfil_Usuario.png')); ?>" alt="<?php echo htmlspecialchars($membro['nome_usuario']); ?>" class="membro-foto"> 
                        <div class="membro-info">
                            <div class="membro-nome"><?php echo htmlspecialchars($membro['nome_exibicao'] ?? $membro['nome_usuario']); ?></div>
                            <?php if ($membro['role'] !== 'membro'): ?>
                                <div class="membro-role"><?php echo htmlspecialchars($membro['role']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Chat -->
        <div class="chat-container">
            <div class="chat-header">
                <h1>Chat</h1>
                <a href="lista_grupos.php" class="btn-voltar">← Voltar aos Grupos</a>
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if (empty($mensagens)): ?>
                    <div class="empty-chat">
                        <h3>Nenhuma mensagem ainda</h3>
                        <p>Seja o primeiro a enviar uma mensagem!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mensagens as $msg): ?>
                        <div class="message <?php echo ($msg['id_usuario'] == $id_usuario) ? 'own' : ''; ?>">
                            <img src="../../../public/images/perfis/<?php echo htmlspecialchars(basename($msg['foto_perfil_url'] ?? 'Perfil_Usuario.png')); ?>" alt="<?php echo htmlspecialchars($msg['nome_usuario']); ?>" class="message-avatar">
                            <div class="message-content">
                                <div class="message-author"><?php echo htmlspecialchars($msg['nome_exibicao'] ?? $msg['nome_usuario']); ?></div>
                                <div class="message-text"><?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?></div>
                                <div class="message-time">
                                    <?php 
                                    $timestamp = strtotime($msg['data_envio']);
                                    $hoje = date('Y-m-d');
                                    $data_msg = date('Y-m-d', $timestamp);
                                    
                                    if ($data_msg === $hoje) {
                                        echo date('H:i', $timestamp);
                                    } else {
                                        echo date('d/m/Y H:i', $timestamp);
                                    }
                                    
                                    if ($msg['editada']) {
                                        echo ' (editada)';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input-container">
                <form class="chat-input-form" id="chatForm">
                    <input 
                        type="text" 
                        id="messageInput" 
                        class="chat-input" 
                        placeholder="Digite sua mensagem..."
                        maxlength="1000"
                        autocomplete="off"
                        required
                    >
                    <button type="submit" class="btn-send" id="sendBtn">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const idGrupo = <?php echo $id_grupo; ?>;
        const idUsuario = <?php echo $id_usuario; ?>;
        let ultimoIdMensagem = <?php echo empty($mensagens) ? 0 : max(array_column($mensagens, 'id_mensagem')); ?>;

        // Scroll para o final ao carregar
        scrollToBottom();

        // Enviar mensagem
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const mensagem = messageInput.value.trim();
            if (!mensagem) return;

            sendBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('id_grupo', idGrupo);
                formData.append('mensagem', mensagem);

                const response = await fetch('enviar_mensagem.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    messageInput.value = '';
                    // Buscar novas mensagens imediatamente
                    await buscarNovasMensagens();
                } else {
                    alert(data.message || 'Erro ao enviar mensagem');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem');
            } finally {
                sendBtn.disabled = false;
                messageInput.focus();
            }
        });

        // Buscar novas mensagens a cada 2 segundos
        setInterval(buscarNovasMensagens, 2000);

        async function buscarNovasMensagens() {
            try {
                const response = await fetch(`buscar_mensagens.php?id_grupo=${idGrupo}&ultimo_id=${ultimoIdMensagem}`);
                const data = await response.json();

                if (data.success && data.mensagens.length > 0) {
                    data.mensagens.forEach(msg => {
                        adicionarMensagem(msg);
                        ultimoIdMensagem = Math.max(ultimoIdMensagem, parseInt(msg.id_mensagem));
                    });
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Erro ao buscar mensagens:', error);
            }
        }

        function adicionarMensagem(msg) {
            // Remover mensagem vazia se existir
            const emptyChat = chatMessages.querySelector('.empty-chat');
            if (emptyChat) {
                emptyChat.remove();
            }

            const isOwn = parseInt(msg.id_usuario) === idUsuario;
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message' + (isOwn ? ' own' : '');

            const timestamp = new Date(msg.data_envio);
            const hoje = new Date().toDateString();
            const dataMsg = timestamp.toDateString();
            let timeStr;

            if (dataMsg === hoje) {
                timeStr = timestamp.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            } else {
                timeStr = timestamp.toLocaleString('pt-BR', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }

            if (msg.editada) {
                timeStr += ' (editada)';
            }

            messageDiv.innerHTML = `
                <img src="../../../public/images/perfis/${escapeHtml(basename(msg.foto_perfil_url) || 'Perfil_Usuario.png')}" alt="${escapeHtml(msg.nome_usuario)}" class="message-avatar">
                <div class="message-content">
                    <div class="message-author">${escapeHtml(msg.nome_exibicao || msg.nome_usuario)}</div>
                    <div class="message-text">${escapeHtml(msg.mensagem).replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${timeStr}</div>
                </div>
            `;

            chatMessages.appendChild(messageDiv);
        }

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function basename(path) {
            if (!path) return 'Perfil_Usuario.png';
            return path.split('/').pop().split('\\').pop();
        }
    </script>
</body>
</html>
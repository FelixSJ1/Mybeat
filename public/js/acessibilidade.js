// public/js/acessibilidade.js

// 1. Crie um registro global para módulos de comando específicos da página
window.voiceCommandModules = window.voiceCommandModules || [];

// 2. Crie uma função para que as páginas registrem seus comandos
window.registerVoiceModule = (module) => {
    // Um módulo deve ter uma função process(transcript, feedback)
    if (typeof module.process === 'function') {
        window.voiceCommandModules.push(module);
    } else {
        console.error('Módulo de voz inválido. Falta a função "process".');
    }
};


document.addEventListener('DOMContentLoaded', () => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (!SpeechRecognition) {
        console.warn("Seu navegador não suporta a Web Speech API. Acessibilidade por voz está desativada.");
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'pt-BR';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    // --- (Nenhuma mudança no feedback, botão ou áudio guia) ---
    
    const feedback = document.createElement('div');
    feedback.setAttribute('aria-live', 'polite');
    feedback.setAttribute('aria-atomic', 'true');
    feedback.className = 'acessibilidade-feedback';
    document.body.appendChild(feedback);

    const voiceButton = document.createElement('button');
    voiceButton.id = 'voice-command-button';
    voiceButton.innerHTML = '🎤';
    voiceButton.setAttribute('aria-label', 'Ativar comandos de voz (Pressione Ctrl + Espaço)');
    voiceButton.title = 'Ativar comandos de voz (Pressione Ctrl + Espaço)';
    document.body.appendChild(voiceButton);

    const audioGuia = new Audio('/Mybeat/public/js/guia.mp3');

    // 3. Definir os comandos de voz (GLOBAIS)
    const globalComandos = {
        'ir para home': () => window.location.href = '/Mybeat/app/Views/home_usuario.php',
        'abrir perfil': () => window.location.href = '/Mybeat/app/Views/perfilUsuario.php',
        'editar perfil': () => window.location.href = '/Mybeat/app/Views/perfilUsuario.php',
        'minhas avaliações': () => window.location.href = '/Mybeat/app/Views/historico_avaliacoes.php',
        'minhas playlists': () => window.location.href = '/Mybeat/app/Views/Listar_giovana.php?controller=playlist&action=index',
        'meus álbuns curtidos': () => window.location.href = '/Mybeat/app/Views/listar_giovana.php?controller=avaliacaoUsuario&action=mostrarAlbunsCurtidos',
        'buscar usuários': () => window.location.href = '/Mybeat/app/Views/SeguidoresMyBeatViews.php',
        'ver notificações': () => window.location.href = '/Mybeat/app/Views/notificacoes_seguidores.php',
        'meus grupos': () => window.location.href = '/Mybeat/app/Views/grupos/lista_grupos.php',
        'criar grupo': () => window.location.href = '/Mybeat/app/Views/grupos/criar_grupo.php',
        'sair': () => window.location.href = '/Mybeat/app/Views/logout.php',
        'ajuda': () => {
              // Fornece feedback de áudio
            feedback.textContent = 'Tocando o guia de áudio do MyBeat.';
             
             // Toca o arquivo guia.mp3
             audioGuia.play().catch(e => {
                console.error("Erro ao tocar o áudio:", e);
                feedback.textContent = "Não foi possível tocar o guia de áudio.";
             });
        }
    };

    // --- (Nenhuma mudança em recognition.onstart, onend, onerror) ---
    
    recognition.onstart = () => {
        feedback.textContent = 'Ouvindo...';
        voiceButton.classList.add('recording');
        voiceButton.innerHTML = '...';
    };
    
    recognition.onend = () => {
        feedback.textContent = '';
        voiceButton.classList.remove('recording');
        voiceButton.innerHTML = '🎤';
    };

    recognition.onerror = (event) => {
        if (event.error === 'no-speech') {
            feedback.textContent = 'Nenhuma fala detectada. Tente novamente.';
        } else {
            feedback.textContent = 'Erro no reconhecimento: ' + event.error;
        }
    };
    
    // 4. Lógica do Reconhecimento (Atualizada)
    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript.toLowerCase().trim();
        console.log('Comando recebido:', transcript);
        processarComando(transcript);
    };

    function processarComando(transcript) {
        let comandoEncontrado = false;

        // --- MODIFICAÇÃO INÍCIO ---
        // 4.1. Verifica primeiro os comandos específicos da página
        for (const module of window.voiceCommandModules) {
            // A função process do módulo deve retornar 'true' se ela lidou com o comando
            if (module.process(transcript, feedback)) {
                comandoEncontrado = true;
                break; // Para a execução, pois o comando foi encontrado
            }
        }

        // 4.2. Se um comando específico da página foi executado, não continue
        if (comandoEncontrado) {
            return;
        }
        // --- MODIFICAÇÃO FIM ---

        // 4.3. Se não, verifica os comandos globais
        
        // Comando global com parâmetro: "buscar por [termo]"
        if (transcript.startsWith('buscar por ')) {
            const termo = transcript.substring('buscar por '.length);
            feedback.textContent = `Buscando por: ${termo}`;
            
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            
            if (searchInput && searchForm) {
                searchInput.value = termo;
                searchForm.submit();
            } else {
                window.location.href = `/Mybeat/app/Views/home_usuario.php?q=${encodeURIComponent(termo)}`;
            }
            comandoEncontrado = true;
        } 
        // Comandos globais simples (sem parâmetro)
        else if (globalComandos[transcript]) {
            feedback.textContent = `Executando: ${transcript}`;
            globalComandos[transcript](); // Executa a função do comando
            comandoEncontrado = true;
        }

        if (!comandoEncontrado) {
            feedback.textContent = `Comando não reconhecido: ${transcript}. Diga "ajuda" para ver a lista de comandos.`;
        }
    }

    // 5. Ativação do reconhecimento (Nenhuma mudança)
    function ativarReconhecimento() {
        try {
            recognition.start();
        } catch (e) {
            console.error("Erro ao iniciar reconhecimento:", e);
        }
    }
    
    voiceButton.addEventListener('click', ativarReconhecimento);

    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.code === 'Space') {
            e.preventDefault();
            ativarReconhecimento();
        }
    });
});
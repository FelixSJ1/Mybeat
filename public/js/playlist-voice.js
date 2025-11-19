// public/js/playlist-voice.js

// 1. Cria o módulo de comandos para esta página
const playlistModule = {
    
    process: (transcript, feedback) => {
        
        // Comando: "adicionar a playlist [NOME DA PLAYLIST]"
        if (transcript.startsWith('adicionar a playlist ')) {
            // Pega o nome da playlist que foi falado
            const nomePlaylist = transcript.substring('adicionar a playlist '.length).trim();

            if (!nomePlaylist) {
                feedback.textContent = 'Por favor, diga o nome da playlist.';
                return true; // Comando reconhecido, mas incompleto
            }

            // Tenta encontrar a playlist na página
            const allPlaylistNames = document.querySelectorAll('.playlist-name');
            let found = false;

            for (const nameElement of allPlaylistNames) {
                // Compara o nome falado (em minúsculas) com o texto do elemento
                const elementText = nameElement.textContent.trim().toLowerCase();
                
                if (elementText === nomePlaylist) {
                    
                    // Encontramos! Agora, vamos encontrar o link clicável.
                    // No seu HTML (playlist.php), o link <a> é o pai do <div class="playlist-name">
                    const link = nameElement.closest('a.playlist-name-link');
                    
                    if (link && link.href) {
                        feedback.textContent = `Adicionando à playlist ${nameElement.textContent}...`;
                        link.click(); // Clica no link para adicionar a música
                        found = true;
                        break; // Para a busca
                    }
                }
            }

            if (!found) {
                feedback.textContent = `Não encontrei a playlist chamada "${nomePlaylist}". Tente novamente.`;
            }

            return true; // O comando foi processado (mesmo que não tenha encontrado)
        }

        // 3. Se nenhum comando corresponder, retorna 'false'
        return false;
    }
};

// 4. Registra este módulo no sistema de voz global
if (window.registerVoiceModule) {
    window.registerVoiceModule(playlistModule);
} else {
    // Garante que funcione mesmo se carregar fora de ordem
    window.addEventListener('load', () => {
        if (window.registerVoiceVodule) {
            window.registerVoiceModule(playlistModule);
        }
    });
}
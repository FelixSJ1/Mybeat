// public/js/avaliacao-voice.js

// 1. Cria o módulo de comandos para a página de avaliação
const avaliacaoModule = {
    
    // Função helper para converter texto em número de nota
    parseNota: (transcript) => {
        const parts = transcript.split(' ');
        const ultimoValor = parts[parts.length - 1];

        switch (ultimoValor) {
            case 'um':
            case '1':
                return 1;
            case 'dois':
            case '2':
                return 2;
            case 'três':
            case '3':
                return 3;
            case 'quatro':
            case '4':
                return 4;
            case 'cinco':
            case '5':
                return 5;
            case 'zero':
            case '0':
                return 0; // Para limpar a nota
            default:
                return null; // Não reconhecido
        }
    },

    // 2. A função 'process' será chamada pelo acessibilidade.js global
    process: (transcript, feedback) => {

        // Comando: "curtir álbum" ou "descurtir álbum"
        if (transcript === 'curtir álbum' || transcript === 'curtir o álbum' || transcript === 'descurtir álbum') {
            console.log('VOZ: Comando "curtir álbum" detectado.');
            const btnCurtir = document.querySelector('button[formaction*="curtirAlbum"]');
            if (!btnCurtir) {
                feedback.textContent = 'Erro: Não encontrei o botão "Curtir".';
                return true;
            }
            
            const acao = btnCurtir.textContent.includes('Curtido') ? 'Descurtindo' : 'Curtindo';
            feedback.textContent = `${acao} o álbum...`;
            btnCurtir.click();
            return true; // Comando processado
        }

        // --- ESTA É A PARTE IMPORTANTE ---
        
        // Comando: "adicionar a playlist"
        if (transcript === 'adicionar a playlist' || transcript === 'adicionar na playlist') {
            
            console.log('VOZ: Comando "adicionar a playlist" detectado (versão correta do script).');

            // Procura o botão "+" da PRIMEIRA FAIXA na lista
            const btnTrackPlaylist = document.querySelector('a.track-action-btn.track-add'); 
            
            if (!btnTrackPlaylist) {
                console.warn('VOZ: Botão da faixa (track-add) não encontrado.');
                const noTracks = document.querySelector('.no-tracks');
                if (noTracks) {
                    feedback.textContent = 'Não há faixas neste álbum para adicionar.';
                } else {
                    feedback.textContent = 'Erro: Não encontrei o botão "+" de adicionar faixa.';
                }
                return true;
            }

            console.log('VOZ: Botão da faixa encontrado. Clicando:', btnTrackPlaylist.href);
            feedback.textContent = 'Abrindo a seleção de playlists para a primeira faixa...';
            btnTrackPlaylist.click(); // Clica no link da primeira faixa
            return true; // Comando processado
        }
        
        // --- FIM DA PARTE IMPORTANTE ---
        
        // Comando: "salvar" ou "salvar avaliação"
        if (transcript === 'salvar' || transcript === 'salvar avaliação') {
            console.log('VOZ: Comando "salvar" detectado.');
            const btnSalvar = document.querySelector('button.btn-submit');
            if (!btnSalvar) {
                feedback.textContent = 'Erro: Não encontrei o botão "Salvar".';
                return true;
            }
            feedback.textContent = 'Salvando avaliação...';
            btnSalvar.click(); // Envia o formulário
            return true; // Comando processado
        }

        // Comando: "adicionar nota [NÚMERO]"
        if (transcript.startsWith('adicionar nota ') || transcript.startsWith('nota ') || transcript.startsWith('dar nota ')) {
            const notaNum = avaliacaoModule.parseNota(transcript);
            
            if (notaNum === null) {
                feedback.textContent = 'Nota não reconhecida. Diga um número de 1 a 5.';
                return true;
            }

            if (notaNum > 0) {
                const radioStar = document.getElementById(`star${notaNum}`);
                if (!radioStar) {
                    feedback.textContent = `Erro: Não encontrei a estrela ${notaNum}.`;
                    return true;
                }
                radioStar.checked = true;
                feedback.textContent = `Nota ${notaNum} estrelas selecionada.`;
            } else {
                document.querySelectorAll('input[name="nota"]').forEach(radio => radio.checked = false);
                feedback.textContent = 'Nota removida.';
            }
            console.log(`VOZ: Nota ${notaNum} aplicada.`);
            return true; // Comando processado
        }

        // Comando: "adicionar comentário [TEXTO]"
        if (transcript.startsWith('adicionar comentário ')) {
            const comentario = transcript.substring('adicionar comentário '.length);
            const textArea = document.querySelector('textarea[name="texto_review"]');
            
            if (!textArea) {
                feedback.textContent = 'Erro: Não encontrei o campo de comentário.';
                return true;
            }
            
            textArea.value = comentario;
            textArea.focus();
            feedback.textContent = 'Comentário adicionado.';
            console.log('VOZ: Comentário adicionado.');
            return true; // Comando processado
        }

        // Se nenhum comando corresponder, retorna 'false'
        return false;
    }
};

// 4. Registra este módulo no sistema de voz global
if (window.registerVoiceModule) {
    window.registerVoiceModule(avaliacaoModule);
} else {
    window.addEventListener('load', () => {
        if (window.registerVoiceModule) {
            window.registerVoiceModule(avaliacaoModule);
        }
    });
}
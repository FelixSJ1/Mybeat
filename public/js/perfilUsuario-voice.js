// public/js/perfilUsuario-voice.js

// 1. Cria o módulo de comandos para esta página
const perfilUsuarioModule = {
    
    // 2. A função 'process' será chamada pelo acessibilidade.js global
    process: (transcript, feedback) => {
        
        // Tenta encontrar os elementos da página de perfil
        const inputNome = document.getElementById('nome_exibicao');
        const inputBio = document.getElementById('biografia');
        const btnSalvar = document.querySelector('button.btn-primary[type="submit"]');
        const btnCancelar = document.querySelector('a.btn-secondary');

        // Comando: "alterar nome de exibição para [NOVO NOME]"
        if (transcript.startsWith('alterar nome de exibição para ')) {
            if (!inputNome) {
                feedback.textContent = 'Erro: Não encontrei o campo "Nome de Exibição".';
                return true; // Comando reconhecido, mas falhou
            }
            const novoNome = transcript.substring('alterar nome de exibição para '.length);
            inputNome.value = novoNome;
            feedback.textContent = `Nome de exibição alterado para: ${novoNome}`;
            return true; // Sucesso: O comando foi processado
        }

        // Comando: "alterar biografia para [NOVA BIO]"
        if (transcript.startsWith('alterar biografia para ')) {
            if (!inputBio) {
                feedback.textContent = 'Erro: Não encontrei o campo "Biografia".';
                return true;
            }
            const novaBio = transcript.substring('alterar biografia para '.length);
            inputBio.value = novaBio;
            feedback.textContent = 'Biografia alterada.';
            return true; // Sucesso: O comando foi processado
        }

        // Comando: "salvar" ou "salvar alterações"
        if (transcript === 'salvar' || transcript === 'salvar alterações') {
            if (!btnSalvar) {
                feedback.textContent = 'Erro: Não encontrei o botão "Salvar".';
                return true;
            }
            feedback.textContent = 'Salvando alterações...';
            btnSalvar.click(); // Simula o clique no botão de salvar
            return true; // Sucesso: O comando foi processado
        }

        // Comando: "cancelar"
        if (transcript === 'cancelar') {
            if (!btnCancelar) {
                feedback.textContent = 'Erro: Não encontrei o botão "Cancelar".';
                return true;
            }
            feedback.textContent = 'Cancelando e voltando para a home...';
            btnCancelar.click(); // Simula o clique no link "Cancelar"
            return true; // Sucesso: O comando foi processado
        }

        // 3. Se nenhum comando desta página corresponder, retorna 'false'
        // Isso permite que o acessibilidade.js global tente seus próprios comandos (como 'ir para home')
        return false;
    }
};

// 4. Registra este módulo no sistema de voz global
// Verifica se a função de registro existe (criada no passo 1)
if (window.registerVoiceModule) {
    window.registerVoiceModule(perfilUsuarioModule);
} else {
    // Caso este script carregue antes do global (improvável com 'defer')
    window.addEventListener('load', () => {
        if (window.registerVoiceModule) {
            window.registerVoiceModule(perfilUsuarioModule);
        }
    });
}
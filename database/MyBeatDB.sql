CREATE DATABASE IF NOT EXISTS MyBeatDB;
USE MyBeatDB;


CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    hash_senha VARCHAR(255) NOT NULL,
    nome_exibicao VARCHAR(100),
    biografia TEXT,
    foto_perfil_url VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE Artistas (
    id_artista INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    biografia TEXT,
    foto_artista_url VARCHAR(255),
    ano_inicio_atividade YEAR,      
    pais_origem VARCHAR(100)         
);


CREATE TABLE Albuns (
    id_album INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    id_artista INT NOT NULL,
    data_lancamento DATE,
    capa_album_url VARCHAR(255),
    genero VARCHAR(100),
    tipo ENUM('Álbum', 'EP', 'Single', 'Coletânea') NOT NULL DEFAULT 'Álbum',
    FOREIGN KEY (id_artista) REFERENCES Artistas(id_artista)
);


CREATE TABLE Musicas (
    id_musica INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    id_album INT NOT NULL,
    id_artista INT NOT NULL,
    duracao_segundos INT,           
    numero_faixa INT,                
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album),
    FOREIGN KEY (id_artista) REFERENCES Artistas(id_artista)
);


CREATE TABLE Avaliacoes (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_album INT NOT NULL,
    nota DECIMAL(3, 1),
    texto_review TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album),
    UNIQUE KEY `review_unica_por_usuario_album` (id_usuario, id_album)
);      

CREATE TABLE Administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nome_admin VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    hash_senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    administrador BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE Seguidores (
    id_seguidor INT NOT NULL,
    id_seguido INT NOT NULL,
    data_seguimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_seguido) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);


-- Tabela de Playlists criadas pelos usuários
CREATE TABLE Playlists (
    id_playlist INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome_playlist VARCHAR(255) NOT NULL,
    descricao_playlist TEXT,
    capa_playlist_url VARCHAR(255),
    playlist_publica BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabela de relacionamento entre Playlists e Músicas
CREATE TABLE Musicas_Playlist (
    id_musica_playlist INT AUTO_INCREMENT PRIMARY KEY,
    id_playlist INT NOT NULL,
    id_musica INT NOT NULL,
    ordem_na_playlist INT,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_playlist) REFERENCES Playlists(id_playlist) ON DELETE CASCADE,
    FOREIGN KEY (id_musica) REFERENCES Musicas(id_musica) ON DELETE CASCADE,
    UNIQUE KEY musica_unica_por_playlist (id_playlist, id_musica)
);

-- Tabela de Músicas curtidas pelos usuários
CREATE TABLE Musicas_Curtidas (
    id_curtida_musica INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_musica INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_musica) REFERENCES Musicas(id_musica) ON DELETE CASCADE,
    UNIQUE KEY curtida_unica_musica (id_usuario, id_musica)
);

-- Tabela de Álbuns curtidos pelos usuários
CREATE TABLE Albuns_Curtidos (
    id_curtida_album INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_album INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album) ON DELETE CASCADE,
    UNIQUE KEY curtida_unica_album (id_usuario, id_album)
);



-- Tabela de Grupos
CREATE TABLE IF NOT EXISTS Grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nome_grupo VARCHAR(100) NOT NULL,
    descricao TEXT,
    foto_grupo_url VARCHAR(255) DEFAULT '../../public/images/grupo_default.png',
    id_criador INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    privado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_criador) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_nome_grupo (nome_grupo),
    INDEX idx_criador (id_criador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Membros dos Grupos
CREATE TABLE IF NOT EXISTS Membros_Grupo (
    id_membro INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_usuario INT NOT NULL,
    data_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin', 'moderador', 'membro') DEFAULT 'membro',
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY unique_membro (id_grupo, id_usuario),
    INDEX idx_grupo (id_grupo),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Mensagens do Chat
CREATE TABLE IF NOT EXISTS Mensagens_Grupo (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_usuario INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    editada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_grupo_data (id_grupo, data_envio),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Convites para Grupos Privados 
CREATE TABLE IF NOT EXISTS Convites_Grupo (
    id_convite INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_usuario_convidado INT NOT NULL,
    id_usuario_convidador INT NOT NULL,
    status ENUM('pendente', 'aceito', 'recusado') DEFAULT 'pendente',
    data_convite TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_convidado) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_convidador) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario_convidado (id_usuario_convidado),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
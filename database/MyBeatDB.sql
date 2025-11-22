-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS MyBeatDB;
USE MyBeatDB;

-- =====================================================
-- TABELA: Usuarios
-- Armazena informações dos usuários do sistema
-- =====================================================
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    hash_senha VARCHAR(255) NOT NULL,
    nome_exibicao VARCHAR(100),
    biografia TEXT,
    foto_perfil_url VARCHAR(255),
    banner_url VARCHAR(255) DEFAULT '../../public/images/default_banner.jpg',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nome_usuario (nome_usuario),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Artistas
-- Armazena informações sobre artistas musicais
-- =====================================================
CREATE TABLE Artistas (
    id_artista INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    biografia TEXT,
    foto_artista_url VARCHAR(255),
    ano_inicio_atividade YEAR,
    pais_origem VARCHAR(100),
    INDEX idx_nome_artista (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Albuns
-- Armazena informações sobre álbuns musicais
-- =====================================================
CREATE TABLE Albuns (
    id_album INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    id_artista INT NOT NULL,
    data_lancamento DATE,
    capa_album_url VARCHAR(255),
    genero VARCHAR(100),
    tipo ENUM('Álbum', 'EP', 'Single', 'Coletânea') NOT NULL DEFAULT 'Álbum',
    FOREIGN KEY (id_artista) REFERENCES Artistas(id_artista) ON DELETE CASCADE,
    INDEX idx_titulo (titulo),
    INDEX idx_genero (genero),
    INDEX idx_data_lancamento (data_lancamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Musicas
-- Armazena informações sobre músicas individuais
-- =====================================================
CREATE TABLE Musicas (
    id_musica INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    id_album INT NOT NULL,
    id_artista INT NOT NULL,
    duracao_segundos INT,
    numero_faixa INT,
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album) ON DELETE CASCADE,
    FOREIGN KEY (id_artista) REFERENCES Artistas(id_artista) ON DELETE CASCADE,
    INDEX idx_titulo_musica (titulo),
    INDEX idx_album (id_album)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Avaliacoes
-- Armazena avaliações de álbuns feitas pelos usuários
-- =====================================================
CREATE TABLE Avaliacoes (
    id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_album INT NOT NULL,
    nota DECIMAL(3, 1),
    texto_review TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album) ON DELETE CASCADE,
    UNIQUE KEY review_unica_por_usuario_album (id_usuario, id_album),
    INDEX idx_usuario_avaliacao (id_usuario),
    INDEX idx_album_avaliacao (id_album),
    INDEX idx_data_avaliacao (data_avaliacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Administradores
-- Armazena informações dos administradores do sistema
-- =====================================================
CREATE TABLE Administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nome_admin VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    hash_senha VARCHAR(255) NOT NULL,
    face_descriptor TEXT,
    face_registered BOOLEAN DEFAULT FALSE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    administrador BOOLEAN NOT NULL DEFAULT TRUE,
    INDEX idx_email_admin (email),
    INDEX idx_face_registered (face_registered)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Seguidores
-- Relacionamento entre usuários (quem segue quem)
-- =====================================================
CREATE TABLE Seguidores (
    id_seguidor INT NOT NULL,
    id_seguido INT NOT NULL,
    data_seguimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_seguidor, id_seguido),
    FOREIGN KEY (id_seguidor) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_seguido) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_seguidor (id_seguidor),
    INDEX idx_seguido (id_seguido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Playlists
-- Playlists criadas pelos usuários
-- =====================================================
CREATE TABLE Playlists (
    id_playlist INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome_playlist VARCHAR(255) NOT NULL,
    descricao_playlist TEXT,
    capa_playlist_url VARCHAR(255),
    playlist_publica BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario_playlist (id_usuario),
    INDEX idx_publica (playlist_publica)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Musicas_Playlist
-- Relacionamento entre playlists e músicas
-- =====================================================
CREATE TABLE Musicas_Playlist (
    id_musica_playlist INT AUTO_INCREMENT PRIMARY KEY,
    id_playlist INT NOT NULL,
    id_musica INT NOT NULL,
    ordem_na_playlist INT,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_playlist) REFERENCES Playlists(id_playlist) ON DELETE CASCADE,
    FOREIGN KEY (id_musica) REFERENCES Musicas(id_musica) ON DELETE CASCADE,
    UNIQUE KEY musica_unica_por_playlist (id_playlist, id_musica),
    INDEX idx_playlist (id_playlist)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Musicas_Curtidas
-- Músicas curtidas pelos usuários
-- =====================================================
CREATE TABLE Musicas_Curtidas (
    id_curtida_musica INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_musica INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_musica) REFERENCES Musicas(id_musica) ON DELETE CASCADE,
    UNIQUE KEY curtida_unica_musica (id_usuario, id_musica),
    INDEX idx_usuario_curtida (id_usuario),
    INDEX idx_musica_curtida (id_musica)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Albuns_Curtidos
-- Álbuns curtidos pelos usuários
-- =====================================================
CREATE TABLE Albuns_Curtidos (
    id_curtida_album INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_album INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album) ON DELETE CASCADE,
    UNIQUE KEY curtida_unica_album (id_usuario, id_album),
    INDEX idx_usuario_album_curtido (id_usuario),
    INDEX idx_album_curtido (id_album)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Albuns_Favoritos
-- Álbuns favoritos dos usuários (máximo 5, exibidos no perfil)
-- =====================================================
CREATE TABLE Albuns_Favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_album INT NOT NULL,
    posicao INT NOT NULL CHECK (posicao BETWEEN 1 AND 5),
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_album) REFERENCES Albuns(id_album) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_posicao (id_usuario, posicao),
    INDEX idx_usuario_favorito (id_usuario),
    INDEX idx_posicao (posicao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Grupos
-- Grupos de discussão criados pelos usuários
-- =====================================================
CREATE TABLE Grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nome_grupo VARCHAR(100) NOT NULL,
    descricao TEXT,
    foto_grupo_url VARCHAR(255) DEFAULT '../../public/images/grupo_default.png',
    id_criador INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    privado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_criador) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_nome_grupo (nome_grupo),
    INDEX idx_criador (id_criador),
    INDEX idx_privado (privado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Membros_Grupo
-- Membros dos grupos
-- =====================================================
CREATE TABLE Membros_Grupo (
    id_membro INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_usuario INT NOT NULL,
    data_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin', 'moderador', 'membro') DEFAULT 'membro',
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE KEY unique_membro (id_grupo, id_usuario),
    INDEX idx_grupo (id_grupo),
    INDEX idx_usuario_grupo (id_usuario),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Mensagens_Grupo
-- Mensagens do chat dos grupos
-- =====================================================
CREATE TABLE Mensagens_Grupo (
    id_mensagem INT AUTO_INCREMENT PRIMARY KEY,
    id_grupo INT NOT NULL,
    id_usuario INT NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    editada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_grupo) REFERENCES Grupos(id_grupo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_grupo_data (id_grupo, data_envio),
    INDEX idx_usuario_mensagem (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: Convites_Grupo
-- Convites para grupos privados
-- =====================================================
CREATE TABLE Convites_Grupo (
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
    INDEX idx_status (status),
    INDEX idx_grupo_convite (id_grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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



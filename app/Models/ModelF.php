<?php
class ModelF {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function adicionarArtista($nome, $biografia, $foto_url, $ano_inicio, $pais) {
        $sql = "INSERT INTO Artistas (nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem)
                VALUES ('$nome', '$biografia', '$foto_url', ".($ano_inicio ? $ano_inicio : "NULL").", '$pais')";
        return mysqli_query($this->conn, $sql);
    }

    public function adicionarAlbum($titulo, $nome_artista, $data, $capa, $genero, $tipo) {
        $result = mysqli_query($this->conn, "SELECT id_artista FROM Artistas WHERE nome = '$nome_artista' LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        $id_artista = $row['id_artista'] ?? null;

        if(!$id_artista) return false;

        $sql = "INSERT INTO Albuns (titulo, id_artista, data_lancamento,capa_album_url,genero,tipo) 
                VALUES ('$titulo', '$id_artista', '$data','$capa','$genero','$tipo')";
        return mysqli_query($this->conn, $sql);
    }

    public function adicionarMusica($titulo, $album, $artista, $duracao, $faixa) {
        $result_artista = mysqli_query($this->conn, "SELECT id_artista FROM Artistas WHERE nome = '$artista' LIMIT 1");
        $row_artista = mysqli_fetch_assoc($result_artista);
        $id_artista = $row_artista['id_artista'] ?? null;

        $id_album = null;
        if(strtolower(trim($album)) !== 'single') {
            $result_album = mysqli_query($this->conn, "SELECT id_album FROM Albuns WHERE titulo = '$album' LIMIT 1");
            $row_album = mysqli_fetch_assoc($result_album);
            $id_album = $row_album['id_album'] ?? null;
            if(!$id_album) return false;
        }

        if(!$id_artista) return false;

        $id_album_inserir = strtolower(trim($album)) === 'single' ? "NULL" : $id_album;

        $sql = "INSERT INTO Musicas (titulo, id_album, id_artista, duracao_segundos, numero_faixa) 
                VALUES ('$titulo', $id_album_inserir, '$id_artista', '$duracao', '$faixa')";
        return mysqli_query($this->conn, $sql);
    }
}

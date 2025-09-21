<?php
require_once __DIR__ . '/../config/conexao.php';

class Album {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn ?: Database::getInstance()->getConnection();
    }

    public function getAll($q = '') {
        if ($q !== '') {
            $sql = "SELECT a.*, ar.nome AS artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.titulo LIKE ? OR ar.nome LIKE ? OR a.genero LIKE ?
                    ORDER BY a.data_lancamento DESC";
            $stmt = $this->conn->prepare($sql);
            $like = "%$q%";
            $stmt->bind_param('sss', $like, $like, $like);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->conn->query("SELECT a.*, ar.nome AS artista
                                   FROM Albuns a
                                   JOIN Artistas ar ON a.id_artista = ar.id_artista
                                   ORDER BY a.data_lancamento DESC");
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT a.*, ar.nome AS artista
                                      FROM Albuns a
                                      JOIN Artistas ar ON a.id_artista = ar.id_artista
                                      WHERE a.id_album = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getMusicas($id_album) {
        $stmt = $this->conn->prepare("SELECT * FROM Musicas WHERE id_album = ? ORDER BY numero_faixa ASC");
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result();
    }
}

class Musica {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn ?: Database::getInstance()->getConnection();
    }

    public function getAll($q = '') {
    if ($q !== '') {
        $sql = "SELECT m.id_musica, m.titulo AS titulo_musica,
                       a.titulo AS titulo_album, a.id_album, a.capa_album_url,
                       ar.nome AS artista, m.duracao_segundos, m.numero_faixa
                FROM Musicas m
                JOIN Albuns a   ON m.id_album = a.id_album
                JOIN Artistas ar ON m.id_artista = ar.id_artista
                WHERE m.titulo LIKE ? OR ar.nome LIKE ? OR a.titulo LIKE ?
                ORDER BY a.id_album ASC, m.numero_faixa ASC";
        $stmt = $this->conn->prepare($sql);
        $like = "%{$q}%";
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT m.id_musica, m.titulo AS titulo_musica,
                       a.titulo AS titulo_album, a.id_album, a.capa_album_url,
                       ar.nome AS artista, m.duracao_segundos, m.numero_faixa
                FROM Musicas m
                JOIN Albuns a   ON m.id_album = a.id_album
                JOIN Artistas ar ON m.id_artista = ar.id_artista
                ORDER BY a.id_album ASC, m.numero_faixa ASC";
        return $this->conn->query($sql);
    }
}

    public function getById($id) {
    $stmt = $this->conn->prepare("SELECT m.*, 
                                         a.titulo AS titulo_album, 
                                         a.capa_album_url, 
                                         ar.nome AS artista
                                  FROM Musicas m
                                  JOIN Albuns a ON m.id_album = a.id_album
                                  JOIN Artistas ar ON m.id_artista = ar.id_artista
                                  WHERE m.id_musica = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

}

class Avaliacao {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn ?: Database::getInstance()->getConnection();
    }

    public function getByAlbum($id_album) {
        $stmt = $this->conn->prepare("SELECT av.*, u.nome_exibicao
                                      FROM Avaliacoes av
                                      JOIN Usuarios u ON av.id_usuario = u.id_usuario
                                      WHERE av.id_album = ?
                                      ORDER BY av.data_avaliacao DESC");
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function adicionar($id_usuario, $id_album, $nota, $texto_review) {
        $stmt = $this->conn->prepare("INSERT INTO Avaliacoes (id_usuario, id_album, nota, texto_review)
                                      VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $id_usuario, $id_album, $nota, $texto_review);
        return $stmt->execute();
    }
}
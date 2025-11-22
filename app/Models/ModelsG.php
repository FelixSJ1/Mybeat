<?php

require_once __DIR__ . '/../config/conector.php';

class Album {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn;
    }

    public function getAll($q = '') {
        if ($q !== '') {
            $sql = "SELECT a.*, ar.nome AS nome_artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.titulo LIKE ? OR ar.nome LIKE ? OR a.genero LIKE ?
                    ORDER BY a.data_lancamento DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $like = "%$q%";
            $stmt->bind_param('sss', $like, $like, $like);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->conn->query("SELECT a.*, ar.nome AS nome_artista
                                   FROM Albuns a
                                   JOIN Artistas ar ON a.id_artista = ar.id_artista
                                   ORDER BY a.data_lancamento DESC");
    }

    public function getById($id_album) {
        $stmt = $this->conn->prepare("SELECT a.*, ar.nome AS nome_artista
                                      FROM Albuns a
                                      JOIN Artistas ar ON a.id_artista = ar.id_artista
                                      WHERE a.id_album = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getMusicas($id_album) {
        $stmt = $this->conn->prepare("SELECT * FROM Musicas WHERE id_album = ? ORDER BY numero_faixa ASC");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getGeneros() {
        $sql = "SELECT DISTINCT genero
                FROM Albuns
                WHERE genero IS NOT NULL AND genero <> ''
                ORDER BY genero ASC";
        return $this->conn->query($sql);
    }

    public function getByGenero($genero, $q = '') {
        if ($q !== '') {
            $sql = "SELECT a.*, ar.nome AS nome_artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.genero = ? AND (a.titulo LIKE ? OR ar.nome LIKE ?)
                    ORDER BY a.data_lancamento DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $like = "%$q%";
            $stmt->bind_param('sss', $genero, $like, $like);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            $sql = "SELECT a.*, ar.nome AS nome_artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.genero = ?
                    ORDER BY a.data_lancamento DESC";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('s', $genero);
            $stmt->execute();
            return $stmt->get_result();
        }
    }

    public function getPopulares($limit = 12) {
        $limit = (int)$limit;
        $sql = "SELECT a.*, ar.nome AS nome_artista, AVG(av.nota) AS media_nota, COUNT(av.id_avaliacao) AS qtd_avaliacoes
                FROM Albuns a
                JOIN Artistas ar ON a.id_artista = ar.id_artista
                JOIN Avaliacoes av ON a.id_album = av.id_album
                GROUP BY a.id_album
                HAVING COUNT(av.id_avaliacao) > 0
                ORDER BY media_nota DESC, qtd_avaliacoes DESC
                LIMIT {$limit}";
        return $this->conn->query($sql);
    }
    public function getRatingStats($id_album) {
        $stmt = $this->conn->prepare(
            "SELECT AVG(nota) as media_nota, COUNT(id_avaliacao) as total_avaliacoes
            FROM Avaliacoes
            WHERE id_album = ?"
        );
        if (!$stmt) return null;
    
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /* FUNÇÕES DE ÁLBUNS CURTIDOS */
    public function isAlbumCurtido(int $id_usuario, int $id_album): bool
    {
        $sql = "SELECT COUNT(*) as total FROM albuns_curtidos WHERE id_usuario = ? AND id_album = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $id_album);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['total'] > 0;
    }
    public function curtirAlbum(int $id_usuario, int $id_album): bool
    {
        $sql = "INSERT IGNORE INTO albuns_curtidos (id_usuario, id_album, data_curtida) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $id_album);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    public function descurtirAlbum(int $id_usuario, int $id_album): bool
    {
        $sql = "DELETE FROM albuns_curtidos WHERE id_usuario = ? AND id_album = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $id_album);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /* FUNÇÕES DE ÁLBUNS CURTIDOS */
    public function getAlbunsCurtidosPorUsuario(int $id_usuario)
    {
        $sql = "SELECT 
                    a.*, 
                    ar.nome AS nome_artista,
                    ac.data_curtida
                FROM 
                    albuns_curtidos ac
                JOIN 
                    Albuns a ON ac.id_album = a.id_album
                JOIN 
                    Artistas ar ON a.id_artista = ar.id_artista
                WHERE 
                    ac.id_usuario = ?
                ORDER BY 
                    ac.data_curtida DESC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false; 
        }
        
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        return $stmt->get_result(); 
    }
}

class Musica {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn;
    }

    public function getAll($q = '') {
    if ($q !== '') {
        $sql = "SELECT m.id_musica, m.id_artista,
                       m.titulo AS titulo_musica,
                       a.titulo AS titulo_album, a.id_album, a.capa_album_url,
                       ar.nome AS nome_artista, m.duracao_segundos, m.numero_faixa
                FROM Musicas m
                JOIN Albuns a   ON m.id_album = a.id_album
                JOIN Artistas ar ON m.id_artista = ar.id_artista
                WHERE m.titulo LIKE ? OR ar.nome LIKE ? OR a.titulo LIKE ?
                ORDER BY a.id_album ASC, m.numero_faixa ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $like = "%$q%";
        $stmt->bind_param('sss', $like, $like, $like);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT m.id_musica, m.id_artista,
                       m.titulo AS titulo_musica,
                       a.titulo AS titulo_album, a.id_album, a.capa_album_url,
                       ar.nome AS nome_artista, m.duracao_segundos, m.numero_faixa
                FROM Musicas m
                JOIN Albuns a   ON m.id_album = a.id_album
                JOIN Artistas ar ON m.id_artista = ar.id_artista
                ORDER BY a.id_album ASC, m.numero_faixa ASC";
        return $this->conn->query($sql);
    }
}


    public function getById($id_musica) {
        $stmt = $this->conn->prepare("SELECT m.*, 
                                             a.titulo AS titulo_album, 
                                             a.capa_album_url, 
                                             ar.nome AS nome_artista
                                      FROM Musicas m
                                      JOIN Albuns a ON m.id_album = a.id_album
                                      JOIN Artistas ar ON m.id_artista = ar.id_artista
                                      WHERE m.id_musica = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id_musica);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

class Avaliacao {
    private $conn;
    public function __construct($conn = null) {
        $this->conn = $conn;
    }
    
    public function getByAlbum($id_album) {
        $stmt = $this->conn->prepare("SELECT av.*, COALESCE(u.nome_exibicao, u.nome_usuario) AS nome_exibicao
                                      FROM Avaliacoes av
                                      JOIN Usuarios u ON av.id_usuario = u.id_usuario
                                      WHERE av.id_album = ?
                                      ORDER BY av.data_avaliacao DESC");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function adicionar($id_usuario, $id_album, $nota, $texto_review) {
        $stmt = $this->conn->prepare("INSERT INTO Avaliacoes (id_usuario, id_album, nota, texto_review)
                                      VALUES (?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("iids", $id_usuario, $id_album, $nota, $texto_review);
        return $stmt->execute();
    }
}

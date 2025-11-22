<?php
// app/Models/music_removal_model.php
class Music_Removal_Model {
    private $conn = null;

    public function __construct($conn = null) {
        if ($conn instanceof mysqli) { $this->conn = $conn; return; }
        $candidate = __DIR__ . '/../config/conector.php';
        if (file_exists($candidate)) {
            require_once $candidate;
            if (isset($conn) && $conn instanceof mysqli) { $this->conn = $conn; return; }
            if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) { $this->conn = $GLOBALS['conn']; return; }
        }
        $db_server = getenv('DB_SERVER') ?: '127.0.0.1';
        $db_user = getenv('DB_USER') ?: 'root';
        $db_pass = getenv('DB_PASS') ?: '';
        $db_name = getenv('DB_NAME') ?: 'MyBeatDB';
        $db_port = getenv('DB_PORT') ?: 3306;
        $m = @new mysqli($db_server, $db_user, $db_pass, $db_name, $db_port);
        if ($m && !$m->connect_errno) { $m->set_charset('utf8'); $this->conn = $m; return; }
        throw new Exception("Falha ao obter conexão MySQLi no Music_Removal_Model. Verifique app/config/conector.php");
    }

    private function ensureConnection() { return ($this->conn instanceof mysqli) ? $this->conn : null; }

    public function all() {
        $conn = $this->ensureConnection(); if (!$conn) return array();
        $sql = "SELECT m.id_musica AS id, m.titulo AS title, m.id_artista AS artist_id, m.id_album AS album_id,
                       a.nome AS artist_name, al.titulo AS album_title
                FROM musicas m
                LEFT JOIN artistas a ON a.id_artista = m.id_artista
                LEFT JOIN albuns al ON al.id_album = m.id_album
                ORDER BY m.titulo ASC";
        $res = $conn->query($sql);
        if (!$res) return array();
        $out = array();
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }

    public function allAlbums() {
        $conn = $this->ensureConnection(); if (!$conn) return array();
        $sql = "SELECT al.id_album AS id, al.titulo AS title, al.id_artista AS artist_id,
                       a.nome AS artist_name, al.data_lancamento AS release_date,
                       (SELECT COUNT(*) FROM musicas m WHERE m.id_album = al.id_album) AS songs_count
                FROM albuns al
                LEFT JOIN artistas a ON al.id_artista = a.id_artista
                ORDER BY al.titulo ASC";
        $res = $conn->query($sql);
        $out = array(); if ($res) while ($r = $res->fetch_assoc()) $out[] = $r;
        return $out;
    }

    public function allArtists() {
        $conn = $this->ensureConnection(); if (!$conn) return array();
        $sql = "SELECT ar.id_artista AS id, ar.nome AS name, ar.biografia AS bio,
                       (SELECT COUNT(*) FROM albuns al WHERE al.id_artista = ar.id_artista) AS albums_count,
                       (SELECT COUNT(*) FROM musicas m WHERE m.id_artista = ar.id_artista) AS songs_count
                FROM artistas ar
                ORDER BY ar.nome ASC";
        $res = $conn->query($sql);
        $out = array(); if ($res) while ($r = $res->fetch_assoc()) $out[] = $r;
        return $out;
    }

    public function countSongsByAlbum($albumId) {
        $conn = $this->ensureConnection(); if (!$conn) return 0;
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM musicas WHERE id_album = ?");
        $stmt->bind_param('i',$albumId);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return isset($r['c']) ? (int)$r['c'] : 0;
    }

    public function countAlbumsByArtist($artistId) {
        $conn = $this->ensureConnection(); if (!$conn) return 0;
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM albuns WHERE id_artista = ?");
        $stmt->bind_param('i',$artistId);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return isset($r['c']) ? (int)$r['c'] : 0;
    }

    public function countSongsByArtist($artistId) {
        $conn = $this->ensureConnection(); if (!$conn) return 0;
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM musicas WHERE id_artista = ?");
        $stmt->bind_param('i',$artistId);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        return isset($r['c']) ? (int)$r['c'] : 0;
    }

    public function deleteSong($id) {
        $conn = $this->ensureConnection(); if (!$conn) return false;
        try {
            $stmt = $conn->prepare("DELETE FROM musicas WHERE id_musica = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();
            return ($stmt->affected_rows > 0);
        } catch (Throwable $e) {
            error_log("deleteSong error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAlbum($id) {
        $conn = $this->ensureConnection(); if (!$conn) return 'Conexão com o banco indisponível';
        $songs = $this->countSongsByAlbum($id);
        if ($songs > 0) return "Existem {$songs} músicas vinculadas a este álbum. Use remoção forçada para remover também.";
        try {
            $stmt = $conn->prepare("DELETE FROM albuns WHERE id_album = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();
            return ($stmt->affected_rows > 0);
        } catch (Throwable $e) {
            error_log("deleteAlbum error: " . $e->getMessage());
            return 'Erro ao apagar álbum';
        }
    }

    public function deleteAlbumForce($id) {
        $conn = $this->ensureConnection(); if (!$conn) return 'Conexão com o banco indisponível';
        try {
            $conn->begin_transaction();
            $stmt1 = $conn->prepare("DELETE FROM musicas WHERE id_album = ?");
            $stmt1->bind_param('i',$id); $stmt1->execute();
            $stmt2 = $conn->prepare("DELETE FROM albuns WHERE id_album = ?");
            $stmt2->bind_param('i',$id); $stmt2->execute();
            $conn->commit();
            return true;
        } catch (Throwable $e) {
            // tenta rollback de forma segura (sem referenciar propriedade)
            try { $conn->rollback(); } catch (Throwable $_) {}
            error_log("deleteAlbumForce error: " . $e->getMessage());
            return 'Erro ao apagar álbum e suas músicas';
        }
    }

    public function deleteArtist($id) {
        $conn = $this->ensureConnection(); if (!$conn) return 'Conexão com o banco indisponível';
        $albums = $this->countAlbumsByArtist($id); $songs = $this->countSongsByArtist($id);
        if ($albums > 0 || $songs > 0) return "Existem {$albums} álbuns e {$songs} músicas vinculadas ao artista. Use remoção forçada para apagar tudo.";
        try {
            $stmt = $conn->prepare("DELETE FROM artistas WHERE id_artista = ?");
            $stmt->bind_param('i',$id);
            $stmt->execute();
            return ($stmt->affected_rows > 0);
        } catch (Throwable $e) {
            error_log("deleteArtist error: " . $e->getMessage());
            return 'Erro ao apagar artista';
        }
    }

    public function deleteArtistForce($id) {
        $conn = $this->ensureConnection(); if (!$conn) return 'Conexão com o banco indisponível';
        try {
            $conn->begin_transaction();
            $stmt1 = $conn->prepare("DELETE FROM musicas WHERE id_artista = ?");
            $stmt1->bind_param('i',$id); $stmt1->execute();
            $stmt2 = $conn->prepare("DELETE FROM albuns WHERE id_artista = ?");
            $stmt2->bind_param('i',$id); $stmt2->execute();
            $stmt3 = $conn->prepare("DELETE FROM artistas WHERE id_artista = ?");
            $stmt3->bind_param('i',$id); $stmt3->execute();
            $conn->commit();
            return true;
        } catch (Throwable $e) {
            try { $conn->rollback(); } catch (Throwable $_) {}
            error_log("deleteArtistForce error: " . $e->getMessage());
            return 'Erro ao apagar artista e dependências';
        }
    }
}

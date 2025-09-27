<?php
// app/Models/music_removal_model.php
class Music_Removal_Model {
    private $pdo = null;

    public function __construct(PDO $pdo = null) {
        if ($pdo instanceof PDO) { $this->pdo = $pdo; return; }
        $this->getConnection();
        if (!$this->pdo) {
            throw new Exception("Falha ao obter conexão PDO no Music_Removal_Model. Verifique a configuração do BD.");
        }
    }

    private function getConnection() {
        if ($this->pdo) return $this->pdo;

        $host = getenv('DB_HOST') ?: '127.0.0.1:3307';
        $db   = getenv('DB_NAME') ?: 'MyBeatDB';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $user, $pass, $options);
            $this->pdo = $pdo;
            return $pdo;
        } catch (Throwable $e) {
            // tenta arquivos de conexão do projeto (se houver) — sem instanciar classes externas
            $candidates = [
                __DIR__ . '/../../Database/database.php',
                __DIR__ . '/../../database.php',
                __DIR__ . '/../../config/database.php',
                __DIR__ . '/../Database/database.php',
                __DIR__ . '/../../app/Database/database.php'
            ];
            foreach ($candidates as $file) {
                if (file_exists($file)) {
                    try {
                        require_once $file;
                        if (isset($pdo) && $pdo instanceof PDO) { $this->pdo = $pdo; return $this->pdo; }
                        if (isset($db) && $db instanceof PDO) { $this->pdo = $db; return $this->pdo; }
                        if (isset($database) && $database instanceof PDO) { $this->pdo = $database; return $this->pdo; }
                        if (isset($conn) && $conn instanceof PDO) { $this->pdo = $conn; return $this->pdo; }
                        if (isset($connection) && $connection instanceof PDO) { $this->pdo = $connection; return $this->pdo; }
                    } catch (Throwable $ex) { /* ignora */ }
                }
            }
            error_log("Music_Removal_Model::getConnection error: " . $e->getMessage());
            return null;
        }
    }

    // lista músicas
    public function all(): array {
        $pdo = $this->getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT m.id_musica AS id, m.titulo AS title, a.nome AS artist_name, al.titulo AS album_title
                    FROM Musicas m
                    LEFT JOIN Artistas a ON m.id_artista = a.id_artista
                    LEFT JOIN Albuns al ON m.id_album = al.id_album
                    ORDER BY m.titulo ASC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => $r['id'] ?? ($r['id_musica'] ?? null),
                    'title' => $r['title'] ?? ($r['titulo'] ?? null),
                    'artist_name' => $r['artist_name'] ?? ($r['nome'] ?? null),
                    'album_title' => $r['album_title'] ?? ($r['titulo_album'] ?? null)
                ];
            }
            return $out;
        } catch (Throwable $e) {
            error_log("Music_Removal_Model::all error: " . $e->getMessage());
            return [];
        }
    }

    public function allAlbums(): array {
        $pdo = $this->getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT al.id_album AS id, al.titulo AS title, al.id_artista AS artist_id,
                           a.nome AS artist_name, al.data_lancamento AS release_date,
                           (SELECT COUNT(*) FROM Musicas m WHERE m.id_album = al.id_album) AS songs_count
                    FROM Albuns al
                    LEFT JOIN Artistas a ON al.id_artista = a.id_artista
                    ORDER BY al.titulo ASC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => $r['id'] ?? ($r['id_album'] ?? null),
                    'title' => $r['title'] ?? ($r['titulo'] ?? null),
                    'artist_name' => $r['artist_name'] ?? ($r['nome'] ?? null),
                    'release_date' => $r['release_date'] ?? null,
                    'songs_count' => isset($r['songs_count']) ? (int)$r['songs_count'] : 0
                ];
            }
            return $out;
        } catch (Throwable $e) {
            error_log("Music_Removal_Model::allAlbums error: " . $e->getMessage());
            return [];
        }
    }

    public function allArtists(): array {
        $pdo = $this->getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT a.id_artista AS id, a.nome AS name,
                           (SELECT COUNT(*) FROM Albuns al WHERE al.id_artista = a.id_artista) AS albums_count,
                           (SELECT COUNT(*) FROM Musicas m WHERE m.id_artista = a.id_artista) AS songs_count
                    FROM Artistas a
                    ORDER BY a.nome ASC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => $r['id'] ?? ($r['id_artista'] ?? null),
                    'name' => $r['name'] ?? ($r['nome'] ?? null),
                    'albums_count' => isset($r['albums_count']) ? (int)$r['albums_count'] : 0,
                    'songs_count' => isset($r['songs_count']) ? (int)$r['songs_count'] : 0
                ];
            }
            return $out;
        } catch (Throwable $e) {
            error_log("Music_Removal_Model::allArtists error: " . $e->getMessage());
            return [];
        }
    }

    // contadores
    public function countSongsByAlbum($albumId): int {
        $pdo = $this->getConnection();
        if (!$pdo) return 0;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM Musicas WHERE id_album = :id");
            $stmt->execute(['id' => $albumId]);
            $r = $stmt->fetch();
            return isset($r['cnt']) ? (int)$r['cnt'] : 0;
        } catch (Throwable $e) {
            error_log("countSongsByAlbum error: " . $e->getMessage());
            return 0;
        }
    }

    public function countAlbumsByArtist($artistId): int {
        $pdo = $this->getConnection();
        if (!$pdo) return 0;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM Albuns WHERE id_artista = :id");
            $stmt->execute(['id' => $artistId]);
            $r = $stmt->fetch();
            return isset($r['cnt']) ? (int)$r['cnt'] : 0;
        } catch (Throwable $e) {
            error_log("countAlbumsByArtist error: " . $e->getMessage());
            return 0;
        }
    }

    public function countSongsByArtist($artistId): int {
        $pdo = $this->getConnection();
        if (!$pdo) return 0;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM Musicas WHERE id_artista = :id");
            $stmt->execute(['id' => $artistId]);
            $r = $stmt->fetch();
            return isset($r['cnt']) ? (int)$r['cnt'] : 0;
        } catch (Throwable $e) {
            error_log("countSongsByArtist error: " . $e->getMessage());
            return 0;
        }
    }

    // deleções
    public function deleteSong(int $id) {
        $pdo = $this->getConnection();
        if (!$pdo) return false;
        try {
            $stmt = $pdo->prepare("DELETE FROM Musicas WHERE id_musica = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log("deleteSong error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAlbum(int $id) {
        $pdo = $this->getConnection();
        if (!$pdo) return 'Conexão com o banco indisponível';
        try {
            $count = $this->countSongsByAlbum($id);
            if ($count > 0) return "Existem {$count} músicas vinculadas a este álbum. Confirme para remover também as músicas.";
            $stmt = $pdo->prepare("DELETE FROM Albuns WHERE id_album = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0 ? true : 'Álbum não encontrado';
        } catch (Throwable $e) {
            error_log("deleteAlbum error: " . $e->getMessage());
            return 'Erro no banco de dados';
        }
    }

    public function deleteAlbumForce(int $id) {
        $pdo = $this->getConnection();
        if (!$pdo) return 'Conexão com o banco indisponível';
        try {
            $pdo->beginTransaction();
            $stmt1 = $pdo->prepare("DELETE FROM Musicas WHERE id_album = :id");
            $stmt1->execute(['id' => $id]);
            $stmt2 = $pdo->prepare("DELETE FROM Albuns WHERE id_album = :id");
            $stmt2->execute(['id' => $id]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("deleteAlbumForce error: " . $e->getMessage());
            return 'Erro ao apagar álbum e suas músicas';
        }
    }

    public function deleteArtist(int $id) {
        $pdo = $this->getConnection();
        if (!$pdo) return 'Conexão com o banco indisponível';
        try {
            $albums = $this->countAlbumsByArtist($id);
            $songs = $this->countSongsByArtist($id);
            if ($albums > 0 || $songs > 0) return "Existem {$albums} álbuns e {$songs} músicas vinculadas a este artista. Confirme para remover dependências.";
            $stmt = $pdo->prepare("DELETE FROM Artistas WHERE id_artista = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0 ? true : 'Artista não encontrado';
        } catch (Throwable $e) {
            error_log("deleteArtist error: " . $e->getMessage());
            return 'Erro no banco de dados';
        }
    }

    public function deleteArtistForce(int $id) {
        $pdo = $this->getConnection();
        if (!$pdo) return 'Conexão com o banco indisponível';
        try {
            $pdo->beginTransaction();
            // deletar músicas do artista
            $stmt1 = $pdo->prepare("DELETE FROM Musicas WHERE id_artista = :id");
            $stmt1->execute(['id' => $id]);
            // deletar albuns do artista
            $stmt2 = $pdo->prepare("DELETE FROM Albuns WHERE id_artista = :id");
            $stmt2->execute(['id' => $id]);
            // deletar artista
            $stmt3 = $pdo->prepare("DELETE FROM Artistas WHERE id_artista = :id");
            $stmt3->execute(['id' => $id]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("deleteArtistForce error: " . $e->getMessage());
            return 'Erro ao apagar artista e dependências';
        }
    }
}

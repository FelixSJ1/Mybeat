<?php
// app/Models/playlistM.php

class PlaylistModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getByUser(int $userId, string $q = ''): array {
        if ($q !== '') {
            $sql = "SELECT * FROM Playlists WHERE id_usuario = ? AND nome_playlist LIKE ? ORDER BY data_criacao DESC";
            $stmt = $this->conn->prepare($sql);
            $like = '%' . $q . '%';
            $stmt->bind_param("is", $userId, $like);
        } else {
            $sql = "SELECT * FROM Playlists WHERE id_usuario = ? ORDER BY data_criacao DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $playlists = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $playlists ?: [];
    }

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM Playlists WHERE id_playlist = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        return $row ?: null;
    }

    public function addMusicToPlaylist(int $playlistId, int $musicId): bool {
        // tenta inserir — UNIQUE KEY evita duplicação (musica_unica_por_playlist)
        $sql = "INSERT INTO Musicas_Playlist (id_playlist, id_musica, ordem_na_playlist) VALUES (?, ?, NULL)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $playlistId, $musicId);

        try {
            $ok = $stmt->execute();
            $stmt->close();
            if ($ok) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $id_usuario = $_SESSION['id_usuario'] ?? null;
                if ($id_usuario) {
                    $sql2 = "INSERT IGNORE INTO musicas_curtidas (id_usuario, id_musica) VALUES (?, ?)";
                    $stmt2 = $this->conn->prepare($sql2);
                    if ($stmt2) {
                        $stmt2->bind_param("ii", $id_usuario, $musicId);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
            }
            return (bool)$ok;
        } catch (mysqli_sql_exception $e) {
            // se der erro por duplicidade ou outro, retorna false
            $stmt->close();
            return false;
        }
    }

    public function createPlaylist(int $userId, string $nome, string $descricao = '', ?string $capaUrl = null) {
        $sql = "INSERT INTO Playlists (id_usuario, nome_playlist, descricao_playlist, capa_playlist_url, data_criacao, data_atualizacao) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("isss", $userId, $nome, $descricao, $capaUrl);
        try {
            $ok = $stmt->execute();
            if (!$ok) {
                $stmt->close();
                return false;
            }
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        } catch (mysqli_sql_exception $e) {
            $stmt->close();
            return false;
        }
    }

    /* FUNÇÕES PARA O SISTEMA DE "CURTIDAS COMO PLAYLIST" */
    public function getOrCreateLikedPlaylist(int $userId): ?int
    {
        $nomePlaylistCurtidas = "Músicas Curtidas";
        
        // 1. Tenta encontrar a playlist
        $sqlFind = "SELECT id_playlist FROM Playlists WHERE id_usuario = ? AND nome_playlist = ? LIMIT 1";
        $stmtFind = $this->conn->prepare($sqlFind);
        if (!$stmtFind) return null;
        $stmtFind->bind_param("is", $userId, $nomePlaylistCurtidas);
        $stmtFind->execute();
        $res = $stmtFind->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $stmtFind->close();
            return (int)$row['id_playlist'];
        }
        $stmtFind->close();

        $novoId = $this->createPlaylist(
            $userId, 
            $nomePlaylistCurtidas, 
            "Músicas que você curtiu.", 
            null 
        );

        return $novoId ? (int)$novoId : null;
    }
    
    public function addAllAlbumTracksToPlaylist(int $albumId, int $playlistId): bool
    {
        $sqlMusicas = "SELECT id_musica FROM Musicas WHERE id_album = ?";
        $stmtMusicas = $this->conn->prepare($sqlMusicas);
        $stmtMusicas->bind_param("i", $albumId);
        $stmtMusicas->execute();
        $resMusicas = $stmtMusicas->get_result();
        
        $musicasIds = [];
        while ($row = $resMusicas->fetch_assoc()) {
            $musicasIds[] = (int)$row['id_musica'];
        }
        $stmtMusicas->close();

        if (empty($musicasIds)) {
            return true; 
        }

        $sqlInsert = "INSERT IGNORE INTO Musicas_Playlist (id_playlist, id_musica) VALUES (?, ?)";
        $stmtInsert = $this->conn->prepare($sqlInsert);

        foreach ($musicasIds as $musicId) {
            $stmtInsert->bind_param("ii", $playlistId, $musicId);
            $stmtInsert->execute();
        }
        $stmtInsert->close();
        
        return true;
    }

    public function removeAllAlbumTracksFromPlaylist(int $albumId, int $playlistId): bool
    {
        $sqlDelete = "DELETE mp FROM Musicas_Playlist mp
                      JOIN Musicas m ON mp.id_musica = m.id_musica
                      WHERE mp.id_playlist = ? AND m.id_album = ?";
        
        $stmt = $this->conn->prepare($sqlDelete);
        $stmt->bind_param("ii", $playlistId, $albumId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    public function removeMusicFromPlaylist(int $playlistId, int $musicId): bool
    {
        $sql = "DELETE FROM Musicas_Playlist WHERE id_playlist = ? AND id_musica = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $playlistId, $musicId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    public function isTrackInPlaylist(int $playlistId, int $musicId): bool
    {
        $sql = "SELECT 1 FROM Musicas_Playlist WHERE id_playlist = ? AND id_musica = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $playlistId, $musicId);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        return (bool)$res->fetch_assoc();
    }
}


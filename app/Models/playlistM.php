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

    /**
     * Retorna as músicas de uma playlist com informações de álbum e artista.
     * Campos retornados (exemplo): id_musica_playlist, id_playlist, id_musica, ordem_na_playlist,
     * data_adicao, titulo, duracao_segundos, numero_faixa, album_titulo, capa_album_url, artista_nome
     */
    public function getMusicasByPlaylist(int $playlistId): array {
        $sql = "
            SELECT
                mp.id_musica_playlist,
                mp.id_playlist,
                m.id_musica,
                mp.ordem_na_playlist,
                DATE_FORMAT(mp.data_adicao, '%Y-%m-%d') AS data_adicao,
                m.titulo,
                m.duracao_segundos,
                m.numero_faixa,
                al.titulo AS album_titulo,
                COALESCE(al.capa_album_url, '/Mybeat/public/images/LogoF.png') AS capa_album_url,
                ar.nome AS artista_nome
            FROM Musicas_Playlist mp
            INNER JOIN Musicas m ON mp.id_musica = m.id_musica
            LEFT JOIN Albuns al ON m.id_album = al.id_album
            LEFT JOIN Artistas ar ON m.id_artista = ar.id_artista
            WHERE mp.id_playlist = ?
            ORDER BY
              CASE WHEN mp.ordem_na_playlist IS NULL THEN 1 ELSE 0 END,
              mp.ordem_na_playlist ASC,
              mp.data_adicao ASC
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $playlistId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows ?: [];
    }

    /**
     * Adiciona música a uma playlist (retorna true se adicionou, false se já existia ou erro)
     */
    public function addMusicToPlaylist(int $playlistId, int $musicId): bool {
        // tenta inserir — UNIQUE KEY evita duplicação (musica_unica_por_playlist)
        $sql = "INSERT INTO Musicas_Playlist (id_playlist, id_musica, ordem_na_playlist) VALUES (?, ?, NULL)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $playlistId, $musicId);

        try {
            $ok = $stmt->execute();
            $stmt->close();
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
}

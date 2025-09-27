<?php
declare(strict_types=1);

class EditMyBeatModels {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getAllArtistas(): array {
        $sql = "SELECT id_artista, nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem
                FROM artistas ORDER BY nome";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getArtistaById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT id_artista, nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem
                                      FROM artistas WHERE id_artista = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    public function updateArtista(int $id, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE artistas SET nome=?, biografia=?, foto_artista_url=?, ano_inicio_atividade=?, pais_origem=? 
                                      WHERE id_artista=?");
        $stmt->bind_param(
            "sssisi",
            $data['nome'],
            $data['biografia'],
            $data['foto_artista_url'],
            $data['ano_inicio_atividade'],
            $data['pais_origem'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getAllAlbuns(): array {
        $sql = "SELECT id_album, titulo, data_lancamento, capa_album_url, genero, tipo
                FROM albuns ORDER BY data_lancamento DESC, titulo";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAlbumById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT id_album, titulo, data_lancamento, capa_album_url, genero, tipo
                                      FROM albuns WHERE id_album = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    public function updateAlbum(int $id, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE albuns SET titulo=?, data_lancamento=?, capa_album_url=?, genero=?, tipo=? 
                                      WHERE id_album=?");
        $stmt->bind_param(
            "sssssi",
            $data['titulo'],
            $data['data_lancamento'],
            $data['capa_album_url'],
            $data['genero'],
            $data['tipo'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function getAllMusicas(): array {
        $sql = "SELECT id_musica, titulo, duracao_segundos, numero_faixa, id_album, id_artista
                FROM musicas ORDER BY numero_faixa, titulo";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getMusicaById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT id_musica, titulo, duracao_segundos, numero_faixa, id_album, id_artista
                                      FROM musicas WHERE id_musica = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }

    public function updateMusica(int $id, array $data): bool {
        $stmt = $this->conn->prepare("UPDATE musicas SET titulo=?, duracao_segundos=?, numero_faixa=? 
                                      WHERE id_musica=?");
        $stmt->bind_param(
            "siii",
            $data['titulo'],
            $data['duracao_segundos'],
            $data['numero_faixa'],
            $id
        );
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
?>

<?php
declare(strict_types=1);

class EditMyBeatModels {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllArtistas(): array {
        $sql = 'SELECT id_artista, nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem
                FROM artistas ORDER BY nome';
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function getArtistaById(int $id): ?array {
        $sql = 'SELECT id_artista, nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem
                FROM artistas WHERE id_artista = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateArtista(int $id, array $data): bool {
        $sql = 'UPDATE artistas SET nome=:nome, biografia=:biografia, foto_artista_url=:foto,
                ano_inicio_atividade=:ano, pais_origem=:pais WHERE id_artista = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $data['nome'] ?? '',
                ':biografia' => $data['biografia'] ?? '',
                ':foto' => $data['foto_artista_url'] ?? '',
                ':ano' => $data['ano_inicio_atividade'] ?? null,
                ':pais' => $data['pais_origem'] ?? '',
                ':id' => $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getAllAlbuns(): array {
        $sql = 'SELECT id_album, titulo, data_lancamento, capa_album_url, genero, tipo
                FROM albuns ORDER BY data_lancamento DESC, titulo';
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function getAlbumById(int $id): ?array {
        $sql = 'SELECT id_album, titulo, data_lancamento, capa_album_url, genero, tipo
                FROM albuns WHERE id_album = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateAlbum(int $id, array $data): bool {
        $sql = 'UPDATE albuns SET titulo=:titulo, data_lancamento=:data_lancamento,
                capa_album_url=:capa, genero=:genero, tipo=:tipo WHERE id_album = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $data['titulo'] ?? '',
                ':data_lancamento' => $data['data_lancamento'] ?? null,
                ':capa' => $data['capa_album_url'] ?? '',
                ':genero' => $data['genero'] ?? '',
                ':tipo' => $data['tipo'] ?? '',
                ':id' => $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getAllMusicas(): array {
        $sql = 'SELECT id_musica, titulo, duracao_segundos, numero_faixa, id_album, id_artista
                FROM musicas ORDER BY numero_faixa, titulo';
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function getMusicaById(int $id): ?array {
        $sql = 'SELECT id_musica, titulo, duracao_segundos, numero_faixa, id_album, id_artista
                FROM musicas WHERE id_musica = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateMusica(int $id, array $data): bool {
        $sql = 'UPDATE musicas SET titulo=:titulo, duracao_segundos=:duracao, numero_faixa=:num
                WHERE id_musica = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':titulo' => $data['titulo'] ?? '',
                ':duracao' => $data['duracao_segundos'] ?? null,
                ':num' => $data['numero_faixa'] ?? null,
                ':id' => $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
}

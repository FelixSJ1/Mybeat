<?php
// app/Models/painelmusicmodel.php
// PainelMusicModel - modelo para músicas/álbuns/avaliações

class PainelMusicModel {
    private $conn;

    public function __construct($conn = null) {
        if ($conn) {
            $this->conn = $conn;
        } else {
            require_once __DIR__ . '/../config/conector.php';
            $this->conn = $conn ?? ($GLOBALS['conn'] ?? null);
        }
    }

    // Retorna dados da música + álbum + artista
    public function getById($id_musica) {
        $sql = "SELECT m.*,
                       a.id_album, a.titulo AS titulo_album, a.tipo AS tipo_album, a.data_lancamento, a.capa_album_url, a.genero AS genero_album,
                       ar.id_artista, ar.nome AS nome_artista
                FROM Musicas m
                JOIN Albuns a ON m.id_album = a.id_album
                JOIN Artistas ar ON m.id_artista = ar.id_artista
                WHERE m.id_musica = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id_musica);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    // Retorna dados do álbum + artista (quando chamamos por id_album)
    public function getAlbumById($id_album) {
        $sql = "SELECT a.*, ar.id_artista, ar.nome AS nome_artista
                FROM Albuns a
                LEFT JOIN Artistas ar ON a.id_artista = ar.id_artista
                WHERE a.id_album = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    // Estatísticas de avaliação do álbum (média e total)
    public function getRatingStatsByAlbum($id_album) {
        $sql = "SELECT AVG(nota) AS media_nota, COUNT(id_avaliacao) AS total_avaliacoes
                FROM Avaliacoes
                WHERE id_album = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return ['media_nota' => null, 'total_avaliacoes' => 0];
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if (!$r) return ['media_nota' => null, 'total_avaliacoes' => 0];
        return $r;
    }

    // Distribuição por nota - garante chaves 5..1 retornadas (mesmo com 0)
    public function getRatingDistribution($id_album) {
        $stats = $this->getRatingStatsByAlbum($id_album);
        $total = (int)($stats['total_avaliacoes'] ?? 0);

        $sql = "SELECT nota, COUNT(*) AS cnt FROM Avaliacoes WHERE id_album = ? GROUP BY nota";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        $res = $stmt->get_result();

        // inicializa 5 a 1 com zeros
        $dist = [
            '5' => 0,
            '4' => 0,
            '3' => 0,
            '2' => 0,
            '1' => 0,
        ];
        while ($row = $res->fetch_assoc()) {
            // se nota for decimal (ex.: 4.0) arredondamos para inteiro para a distribuição por estrelas
            $nota = (int)round(floatval($row['nota']));
            if ($nota > 5) $nota = 5;
            if ($nota < 1) $nota = 1;
            $dist[(string)$nota] = (int)$row['cnt'];
        }

        // converte para array com percent
        $out = [];
        foreach ([5,4,3,2,1] as $n) {
            $cnt = $dist[(string)$n];
            $percent = $total > 0 ? round(($cnt / $total) * 100, 1) : 0.0;
            $out[(string)$n] = ['count' => $cnt, 'percent' => $percent];
        }
        return $out;
    }

    // Lista reviews (com nome do usuário)
    public function getReviewsByAlbum($id_album) {
        $sql = "SELECT av.*, u.nome_exibicao, u.nome_usuario
                FROM Avaliacoes av
                LEFT JOIN Usuarios u ON av.id_usuario = u.id_usuario
                WHERE av.id_album = ?
                ORDER BY av.data_avaliacao DESC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $id_album);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }

    // Adiciona avaliação
    public function addReview($id_usuario, $id_album, $nota, $texto_review) {
        $sql = "INSERT INTO Avaliacoes (id_usuario, id_album, nota, texto_review, data_avaliacao) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("iids", $id_usuario, $id_album, $nota, $texto_review);
        return $stmt->execute();
    }

    // Edita avaliação (verifica proprietário)
    public function editReview($id_avaliacao, $id_usuario, $nota, $texto_review) {
        $sql = "UPDATE Avaliacoes SET nota = ?, texto_review = ?, data_avaliacao = NOW() WHERE id_avaliacao = ? AND id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("sdii", $nota, $texto_review, $id_avaliacao, $id_usuario);
        return $stmt->execute();
    }

    // Deleta avaliação (somente dono ou admin - aqui exige id_usuario se for dono)
    public function deleteReview($id_avaliacao, $id_usuario = null) {
        if ($id_usuario !== null) {
            $sql = "DELETE FROM Avaliacoes WHERE id_avaliacao = ? AND id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param("ii", $id_avaliacao, $id_usuario);
            return $stmt->execute();
        } else {
            $sql = "DELETE FROM Avaliacoes WHERE id_avaliacao = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param("i", $id_avaliacao);
            return $stmt->execute();
        }
    }
}

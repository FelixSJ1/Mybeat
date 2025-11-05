<?php
class HomeExtras {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Albums with most evaluations in last 7 days
    public function getPopularesSemana($limit = 12) {
        $limit = (int)$limit;
        $sql = "SELECT a.*, ar.nome AS nome_artista, COUNT(av.id_avaliacao) AS qtd_avaliacoes, AVG(av.nota) AS media_nota
                FROM Albuns a
                JOIN Artistas ar ON a.id_artista = ar.id_artista
                JOIN Avaliacoes av ON a.id_album = av.id_album
                WHERE av.data_avaliacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY a.id_album
                HAVING COUNT(av.id_avaliacao) > 0
                ORDER BY qtd_avaliacoes DESC, media_nota DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    // Last evaluation by user (most recent)
    public function getUserLastEvaluation($id_usuario) {
        $sql = "SELECT av.*, a.titulo, a.genero, a.capa_album_url, a.id_album, ar.nome AS nome_artista, av.data_avaliacao
                FROM Avaliacoes av
                JOIN Albuns a ON av.id_album = a.id_album
                JOIN Artistas ar ON a.id_artista = ar.id_artista
                WHERE av.id_usuario = ?
                ORDER BY av.data_avaliacao DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    // Albums by genre excluding an album id
    public function getAlbumsByGenero($genero, $exclude_id = null, $limit = 12) {
        $limit = (int)$limit;
        if ($exclude_id !== null) {
            $sql = "SELECT a.*, ar.nome AS nome_artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.genero = ? AND a.id_album <> ?
                    ORDER BY a.data_lancamento DESC
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param("sii", $genero, $exclude_id, $limit);
        } else {
            $sql = "SELECT a.*, ar.nome AS nome_artista
                    FROM Albuns a
                    JOIN Artistas ar ON a.id_artista = ar.id_artista
                    WHERE a.genero = ?
                    ORDER BY a.data_lancamento DESC
                    LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) return [];
            $stmt->bind_param("si", $genero, $limit);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    // get last N avaliações do usuário (join com álbum/artista)
    public function getUserEvaluations($id_usuario, $limit = 10) {
        $limit = (int)$limit;
        $sql = "SELECT av.*, a.titulo, a.genero, a.capa_album_url, a.id_album, ar.nome AS nome_artista, av.data_avaliacao
                FROM Avaliacoes av
                JOIN Albuns a ON av.id_album = a.id_album
                JOIN Artistas ar ON a.id_artista = ar.id_artista
                WHERE av.id_usuario = ?
                ORDER BY av.data_avaliacao DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ii", $id_usuario, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    // procura entre as avaliações do usuário (da mais recente para a mais antiga) uma avaliação cuja genero tenha outros albuns.
    // retorna array com 'evaluation' (a avaliação escolhida) e 'similar' (array de albuns do mesmo genero)
    // checa as avaliações em ordem decrescente por data e retorna a primeira elegível
    public function findEvaluationWithSimilar($id_usuario, $checkLimit = 12, $needAtLeast = 1, $albumsLimit = 12) {
        $evaluations = $this->getUserEvaluations($id_usuario, $checkLimit);
        if (empty($evaluations)) return null;

        // as avaliações já vêm ordenadas por data desc (getUserEvaluations faz ORDER BY data_avaliacao DESC)
        foreach ($evaluations as $ev) {
            $genero = isset($ev['genero']) ? $ev['genero'] : null;
            $id_album = isset($ev['id_album']) ? (int)$ev['id_album'] : null;
            if (empty($genero) || !$id_album) continue;

            $similar = $this->getAlbumsByGenero($genero, $id_album, $albumsLimit);
            $count = is_array($similar) ? count($similar) : 0;
            if ($count >= $needAtLeast) {
                return [
                    'evaluation' => $ev,
                    'similar' => $similar
                ];
            }
        }
        return null; // nada elegível encontrado
    }

}
?>

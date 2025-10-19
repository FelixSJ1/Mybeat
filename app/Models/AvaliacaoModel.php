<?php

class AvaliacaoModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Busca o histórico completo de avaliações de um usuário
     * @param int $id_usuario - ID do usuário
     * @return array - Array com todas as avaliações do usuário
     */
    public function getHistoricoByUsuario($id_usuario) {
        $sql = "
            SELECT 
                av.id_avaliacao,
                av.nota,
                av.texto_review,
                av.data_avaliacao,
                al.id_album,
                al.titulo as titulo_album,
                al.capa_album_url,
                al.data_lancamento,
                al.genero,
                ar.nome as nome_artista
            FROM Avaliacoes av
            INNER JOIN Albuns al ON av.id_album = al.id_album
            INNER JOIN Artistas ar ON al.id_artista = ar.id_artista
            WHERE av.id_usuario = ?
            ORDER BY av.data_avaliacao DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        $avaliacoes = [];
        while ($row = $result->fetch_assoc()) {
            $avaliacoes[] = $row;
        }

        $stmt->close();
        return $avaliacoes;
    }
}
?>
<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/conector.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'buscar':
        buscarAlbuns($conn);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
        break;
}

function buscarAlbuns($conn) {
    $query = $_GET['q'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    
    try {
        if (empty($query)) {
            // Buscar álbuns mais populares/recentes
            $stmt = $conn->prepare("
                SELECT a.id_album, a.titulo, a.capa_album_url, art.nome as nome_artista, a.genero, a.data_lancamento
                FROM Albuns a
                INNER JOIN Artistas art ON a.id_artista = art.id_artista
                ORDER BY a.data_lancamento DESC
                LIMIT ?
            ");
            $stmt->bind_param("i", $limit);
        } else {
            // Buscar álbuns que correspondem à query
            $searchTerm = "%{$query}%";
            $stmt = $conn->prepare("
                SELECT a.id_album, a.titulo, a.capa_album_url, art.nome as nome_artista, a.genero, a.data_lancamento
                FROM Albuns a
                INNER JOIN Artistas art ON a.id_artista = art.id_artista
                WHERE a.titulo LIKE ? OR art.nome LIKE ?
                ORDER BY a.data_lancamento DESC
                LIMIT ?
            ");
            $stmt->bind_param("ssi", $searchTerm, $searchTerm, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $albuns = [];
        while ($row = $result->fetch_assoc()) {
            $albuns[] = $row;
        }
        
        $stmt->close();
        echo json_encode($albuns);
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar álbuns: ' . $e->getMessage()]);
    }
}
?>
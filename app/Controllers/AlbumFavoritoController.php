<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

require_once __DIR__ . '/../config/conector.php';

$id_usuario = (int)$_SESSION['id_usuario'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'adicionar':
        adicionarAlbumFavorito($conn, $id_usuario);
        break;
    
    case 'remover':
        removerAlbumFavorito($conn, $id_usuario);
        break;
    
    case 'listar':
        listarAlbunsFavoritos($conn, $id_usuario);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
        break;
}

function adicionarAlbumFavorito($conn, $id_usuario) {
    $id_album = (int)($_POST['id_album'] ?? 0);
    $posicao = (int)($_POST['posicao'] ?? 0);
    
    if ($id_album <= 0 || $posicao < 1 || $posicao > 5) {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        return;
    }
    
    try {
        // Verificar se já existe um álbum nessa posição
        $stmt = $conn->prepare("SELECT id_favorito FROM Albuns_Favoritos WHERE id_usuario = ? AND posicao = ?");
        $stmt->bind_param("ii", $id_usuario, $posicao);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Atualizar álbum existente
            $stmt = $conn->prepare("UPDATE Albuns_Favoritos SET id_album = ?, data_adicao = CURRENT_TIMESTAMP WHERE id_usuario = ? AND posicao = ?");
            $stmt->bind_param("iii", $id_album, $id_usuario, $posicao);
        } else {
            // Inserir novo álbum
            $stmt = $conn->prepare("INSERT INTO Albuns_Favoritos (id_usuario, id_album, posicao) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_usuario, $id_album, $posicao);
        }
        
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Álbum adicionado aos favoritos']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar álbum: ' . $e->getMessage()]);
    }
}

function removerAlbumFavorito($conn, $id_usuario) {
    $posicao = (int)($_POST['posicao'] ?? 0);
    
    if ($posicao < 1 || $posicao > 5) {
        echo json_encode(['success' => false, 'message' => 'Posição inválida']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM Albuns_Favoritos WHERE id_usuario = ? AND posicao = ?");
        $stmt->bind_param("ii", $id_usuario, $posicao);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Álbum removido dos favoritos']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao remover álbum: ' . $e->getMessage()]);
    }
}

function listarAlbunsFavoritos($conn, $id_usuario) {
    try {
        $stmt = $conn->prepare("
            SELECT af.posicao, a.id_album, a.titulo, a.capa_album_url, art.nome as nome_artista
            FROM Albuns_Favoritos af
            INNER JOIN Albuns a ON af.id_album = a.id_album
            INNER JOIN Artistas art ON a.id_artista = art.id_artista
            WHERE af.id_usuario = ?
            ORDER BY af.posicao ASC
        ");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $favoritos = [];
        while ($row = $result->fetch_assoc()) {
            $favoritos[] = $row;
        }
        
        $stmt->close();
        echo json_encode(['success' => true, 'favoritos' => $favoritos]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao listar favoritos: ' . $e->getMessage()]);
    }
}
?>
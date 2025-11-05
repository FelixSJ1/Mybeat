<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    die('Acesso inválido.');
}
if (!isset($_SESSION['id_usuario'])) {
    die('Usuário não logado.');
}

require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Models/playlistM.php'; 

$id_usuario = (int)$_SESSION['id_usuario'];
$action = $_POST['action']; // 'like_album', 'unlike_album', 'like_track', 'unlike_track'
$redirect_url = !empty($_POST['redirect_to'])
    ? $_POST['redirect_to']
    : $_SERVER['HTTP_REFERER']; // volta pra página anterior

try {
    $playlistModel = new PlaylistModel($conn);

    // 4. ENCONTRA OU CRIA A PLAYLIST "MÚSICAS CURTIDAS"
    $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario);
    if ($likedPlaylistId === null) {
        throw new Exception("Não foi possível criar a playlist de curtidas.");
    }

    switch ($action) {
        case 'like_album':
            $id_album = (int)($_POST['id_album'] ?? 0);
            if ($id_album > 0) {
                $playlistModel->addAllAlbumTracksToPlaylist($id_album, $likedPlaylistId);
                $_SESSION['mensagem_sucesso'] = "Álbum adicionado às Músicas Curtidas!";
            }
            break;
            
        case 'unlike_album':
            $id_album = (int)($_POST['id_album'] ?? 0);
            if ($id_album > 0) {
                $playlistModel->removeAllAlbumTracksFromPlaylist($id_album, $likedPlaylistId);
                $_SESSION['mensagem_sucesso'] = "Álbum removido das Músicas Curtidas.";
            }
            break;

        case 'like_track':
            $id_musica = (int)($_POST['id_musica'] ?? 0);
            if ($id_musica > 0) {
                $playlistModel->addMusicToPlaylist($likedPlaylistId, $id_musica);
                $_SESSION['mensagem_sucesso'] = "Música adicionada às Curtidas!";
            }
            break;
            
        case 'unlike_track':
            $id_musica = (int)($_POST['id_musica'] ?? 0);
            if ($id_musica > 0) {
                $playlistModel->removeMusicFromPlaylist($likedPlaylistId, $id_musica);
                $_SESSION['mensagem_sucesso'] = "Música removida das Curtidas.";
            }
            break;
    }

} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Erro: " . $e->getMessage();
}

// 6. REDIRECIONAMENTO DE VOLTA
if (!headers_sent()) {
    header("Location: " . $redirect_url);
    exit;
}

?>
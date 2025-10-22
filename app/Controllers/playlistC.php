<?php
// app/Controllers/playlistC.php

session_start();
require_once __DIR__ . '/../Models/playlistM.php';

class PlaylistController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new PlaylistModel($conn);
    }

    /**
     * Mostra playlists do usuário. Se houver ?add_music_id=xx, a view exibirá links para adicionar a música às playlists
     */
    public function index() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: listar_giovana.php?controller=home&action=index');
            exit;
        }

        $userId = (int) $_SESSION['id_usuario'];
        $q = trim($_GET['q'] ?? '');
        $playlists = $this->model->getByUser($userId, $q);

        // se vier um music id para adicionar
        $addingMusicId = isset($_GET['add_music_id']) ? (int) $_GET['add_music_id'] : null;

        // mensagem de resultado (adicionado / já existe / erro)
        $msg = $_GET['msg'] ?? '';

        require_once __DIR__ . '/../Views/playlist.php';
    }

    /**
     * Ação para adicionar a música à playlist
     * rota: listar_giovana.php?controller=playlist&action=adicionar&playlist_id=XX&music_id=YY
     */
    public function adicionar() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: listar_giovana.php?controller=home&action=index');
            exit;
        }

        $playlistId = isset($_GET['playlist_id']) ? (int) $_GET['playlist_id'] : 0;
        $musicId    = isset($_GET['music_id']) ? (int) $_GET['music_id'] : 0;

        if ($playlistId <= 0 || $musicId <= 0) {
            header('Location: listar_giovana.php?controller=playlist&action=index&msg=error');
            exit;
        }

        $ok = $this->model->addMusicToPlaylist($playlistId, $musicId);

        if ($ok) {
            header('Location: listar_giovana.php?controller=playlist&action=index&msg=added');
        } else {
            // pode ser duplicidade ou erro
            header('Location: listar_giovana.php?controller=playlist&action=index&msg=exists_or_error');
        }
        exit;
    }
}

// Nota: se você inclui este controller diretamente por require no front controller,
// o front controller chamará o método apropriado (index/adicionar).

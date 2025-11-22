<?php
// app/Controllers/playlistC.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Models/playlistM.php';
// REMOVIDO: include global de views - as views serão incluídas dentro dos métodos

class PlaylistController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new PlaylistModel($conn);
    }

    // index: mostra listagem de playlists OU a tela de adicionar música quando for o caso
    public function index() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: listar_giovana.php?controller=home&action=index');
            exit;
        }

        $userId = (int) $_SESSION['id_usuario'];
        $q = trim($_GET['q'] ?? '');
        $playlists = $this->model->getByUser($userId, $q);

        // se vier um music id para adicionar (fluxo: clicar no + de uma música)
        $addingMusicId = isset($_GET['add_music_id']) ? (int) $_GET['add_music_id'] : null;

        // mensagem de resultado (adicionado / já existe / erro)
        $msg = $_GET['msg'] ?? '';

        // Se for fluxo de adicionar música, carregar a view que trata disso (playlist.php)
        if ($addingMusicId !== null && $addingMusicId > 0) {
            // playlist.php espera $playlists, $addingMusicId, $q, $msg (verifique se a view usa essas variáveis)
            require_once __DIR__ . '/../Views/playlist.php';
            return;
        }

        // Caso contrário: mostrar a listagem de playlists (view independente)
        // playlist_listagem.php inicia sessão por conta própria e espera $playlists e $q
        require_once __DIR__ . '/../Views/playlist_listagem.php';
    }

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

    public function criar() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: listar_giovana.php?controller=home&action=index');
            exit;
        }
        // variáveis mínimas para a view
        $q = '';
        $addingMusicId = null;
        $msg = '';
        require_once __DIR__ . '/../Views/criar_playlist.php';
    }

    public function salvar() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: listar_giovana.php?controller=home&action=index');
            exit;
        }

        $userId = (int) $_SESSION['id_usuario'];
        $nome = trim($_POST['nome_playlist'] ?? '');
        $descricao = trim($_POST['descricao_playlist'] ?? '');

        if ($nome === '') {
            header('Location: listar_giovana.php?controller=playlist&action=criar&msg=nome_required');
            exit;
        }

        // tratar upload opcional
        $coverUrl = null;
        if (!empty($_FILES['capa_playlist']['name'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Mybeat/public/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $f = $_FILES['capa_playlist'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $safeName = 'pl_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . ($ext ?: 'jpg');
                $targetPath = $uploadDir . '/' . $safeName;

                if (move_uploaded_file($f['tmp_name'], $targetPath)) {
                    // url relativa usada no DB (ajuste conforme sua estrutura)
                    $coverUrl = '/Mybeat/public/uploads/' . $safeName;
                }
            }
            // em caso de erro no upload, simplesmente continua sem capa
        }

        $newId = $this->model->createPlaylist($userId, $nome, $descricao, $coverUrl);

        if ($newId) {
            header('Location: listar_giovana.php?controller=playlist&action=index&msg=created');
        } else {
            header('Location: listar_giovana.php?controller=playlist&action=criar&msg=error');
        }
        exit;
    }

    public function detalhes() {
        // id pode vir como id ou id_playlist
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['id_playlist']) ? (int) $_GET['id_playlist'] : 0);
        if ($id <= 0) {
            header('Location: listar_giovana.php?controller=playlist&action=index&msg=invalid_id');
            exit;
        }

        // buscar playlist
        $playlist = $this->model->getById($id);
        if (!$playlist) {
            $playlist = null;
            $musicas = [];
        } else {
            $musicas = $this->model->getMusicasByPlaylist($id);
        }

        require_once __DIR__ . '/../Views/playlist_detalhes.php';
    }
}

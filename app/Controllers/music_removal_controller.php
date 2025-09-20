<?php
// app/Controllers/music_removal_controller.php
require_once __DIR__ . '/../Models/music_removal_model.php';
if (session_status() === PHP_SESSION_NONE) session_start();

class Music_Removal_Controller {
    private $model;
    private $error;

    public function __construct() {
        try {
            $this->model = new Music_Removal_Model();
        } catch (Throwable $e) {
            $this->model = null;
            $this->error = $e->getMessage();
            error_log("Music_Removal_Controller::__construct error: " . $this->error);
        }
    }

    public function index() {
        // Se recebeu POST, processa aqui (sempre)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
            $this->handlePost();
            return; // handlePost faz redirect/exit
        }

        $msg = isset($_GET['msg']) ? $_GET['msg'] : null;

        $songs = [];
        $albums = [];
        $artists = [];

        if ($this->model) {
            try {
                $songs = $this->model->all();
                $albums = $this->model->allAlbums();
                $artists = $this->model->allArtists();
            } catch (Throwable $e) {
                $msg = "Erro ao carregar dados: " . $e->getMessage();
                error_log("Music_Removal_Controller::index load error: " . $e->getMessage());
            }
        } else {
            $msg = $this->error ?? "Modelo não inicializado. Verifique a conexão com o BD.";
        }

        // inclui a view (mantendo o layout original e links ao CSS)
        require_once __DIR__ . '/../Views/musicremoval.php';
    }

    private function handlePost() {
        if (!$this->model) {
            $this->redirectWithMsg('Modelo não inicializado. Verificar conexão com o BD.');
            return;
        }

        $action = $_POST['form_action'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            $this->redirectWithMsg('ID inválido para remoção.');
            return;
        }

        switch ($action) {
            case 'delete':
                $ok = $this->model->deleteSong($id);
                $msg = $ok ? 'Música removida com sucesso' : 'Falha ao remover música';
                break;
            case 'delete_album':
                $res = $this->model->deleteAlbum($id);
                $msg = ($res === true) ? 'Álbum removido com sucesso' : $res;
                break;
            case 'delete_album_force':
                $res = $this->model->deleteAlbumForce($id);
                $msg = ($res === true) ? 'Álbum e suas músicas removidos com sucesso' : $res;
                break;
            case 'delete_artist':
                $res = $this->model->deleteArtist($id);
                $msg = ($res === true) ? 'Artista removido com sucesso' : $res;
                break;
            case 'delete_artist_force':
                $res = $this->model->deleteArtistForce($id);
                $msg = ($res === true) ? 'Artista e dependências removidos com sucesso' : $res;
                break;
            default:
                $msg = 'Ação inválida';
                break;
        }

        $this->redirectWithMsg($msg);
    }

    private function redirectWithMsg($msg) {
        // redireciona para a mesma rota (preserva query)
        $uri = $_SERVER['REQUEST_URI'];
        $sep = (strpos($uri, '?') === false) ? '?' : '&';
        header('Location: ' . $uri . $sep . 'msg=' . urlencode($msg));
        exit;
    }
}

// fallback — se acessado diretamente
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $c = new Music_Removal_Controller();
    $c->index();
}

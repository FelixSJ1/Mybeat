<?php
// app/Controllers/music_removal_controller.php
require_once __DIR__ . '/../Models/music_removal_model.php';
$candidate = __DIR__ . '/../config/conector.php';
if (file_exists($candidate)) { require_once $candidate; }
if (session_status() === PHP_SESSION_NONE) session_start();

class Music_Removal_Controller {
    /** @var Music_Removal_Model|null $model */
    private $model = null;
    private $error = null;

    public function __construct() {
        try {
            global $conn;
            if (isset($conn) && $conn instanceof mysqli) {
                $this->model = new Music_Removal_Model($conn);
            } else {
                $this->model = new Music_Removal_Model();
            }
        } catch (Throwable $e) {
            $this->model = null;
            $this->error = $e->getMessage();
            error_log("Music_Removal_Controller::__construct error: " . $this->error);
        }
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
            $this->handlePost();
            return;
        }

        $songs = $this->model ? $this->model->all() : array();
        $albums = $this->model ? $this->model->allAlbums() : array();
        $artists = $this->model ? $this->model->allArtists() : array();

        $message = isset($_GET['msg']) ? $_GET['msg'] : '';
        require __DIR__ . '/../Views/partials/musicremoval.php';
    }

    private function handlePost() {
        $action = isset($_POST['form_action']) ? $_POST['form_action'] : '';
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        try {
            switch ($action) {
                case 'delete_song':
                    $ok = $this->model ? $this->model->deleteSong($id) : false;
                    $msg = $ok ? 'Música removida com sucesso' : 'Falha ao remover música';
                    break;
                case 'delete_album':
                    $res = $this->model ? $this->model->deleteAlbum($id) : 'Modelo ausente';
                    if ($res === true || $res === 1) $msg = 'Álbum removido com sucesso';
                    else $msg = is_string($res) ? $res : 'Falha ao remover álbum';
                    break;
                case 'delete_album_force':
                    $res = $this->model ? $this->model->deleteAlbumForce($id) : 'Modelo ausente';
                    $msg = ($res === true) ? 'Álbum e músicas removidos com sucesso' : $res;
                    break;
                case 'delete_artist':
                    $res = $this->model ? $this->model->deleteArtist($id) : 'Modelo ausente';
                    $msg = ($res === true || $res === 1) ? 'Artista removido' : $res;
                    break;
                case 'delete_artist_force':
                    $res = $this->model ? $this->model->deleteArtistForce($id) : 'Modelo ausente';
                    $msg = ($res === true) ? 'Artista e dependências removidos com sucesso' : $res;
                    break;
                default:
                    $msg = 'Ação inválida';
            }
        } catch (Throwable $e) {
            $msg = 'Erro: ' . $e->getMessage();
        }
        $uri = $_SERVER['REQUEST_URI'];
        $sep = (strpos($uri, '?') === false) ? '?' : '&';
        header('Location: ' . $uri . $sep . 'msg=' . urlencode($msg));
        exit;
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $c = new Music_Removal_Controller();
    $c->index();
}

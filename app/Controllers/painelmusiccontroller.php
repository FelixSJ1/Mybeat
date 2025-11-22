<?php
// app/Controllers/painelmusiccontroller.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Models/painelmusicmodel.php';

class PainelMusicController {
    private $model;
    private $conn;

    public function __construct($conn = null) {
        $this->conn = $conn ?? ($GLOBALS['conn'] ?? null);
        $this->model = new PainelMusicModel($this->conn);
    }

    // Exibe a página da música/álbum (aceita id_musica ou id_album)
    public function show() {
        $id_musica = isset($_GET['id_musica']) ? (int)$_GET['id_musica'] : 0;
        $id_album = isset($_GET['id_album']) ? (int)$_GET['id_album'] : 0;

        if ($id_musica > 0) {
            $music = $this->model->getById($id_musica);
            if (!$music) { http_response_code(404); echo "Música não encontrada."; exit; }
            $albumId = (int)$music['id_album'];
        } elseif ($id_album > 0) {
            $album = $this->model->getAlbumById($id_album);
            if (!$album) { http_response_code(404); echo "Álbum não encontrado."; exit; }
            // monta estrutura compatível com view
            $music = [
                'titulo' => $album['titulo'],
                'id_album' => $album['id_album'],
                'capa_album_url' => $album['capa_album_url'] ?? '',
                'tipo_album' => $album['tipo'] ?? ($album['tipo_album'] ?? 'Álbum'),
                'data_lancamento' => $album['data_lancamento'] ?? null,
                'genero_album' => $album['genero'] ?? ($album['genero_album'] ?? null),
                'nome_artista' => $album['nome_artista'] ?? '-',
                // outros campos musicais ficarão ausentes, view trata isso
            ];
            $albumId = $id_album;
        } else {
            http_response_code(404);
            echo "Parâmetros inválidos.";
            exit;
        }

        $ratingStats = $this->model->getRatingStatsByAlbum($albumId);
        $distribution = $this->model->getRatingDistribution($albumId);
        $reviews = $this->model->getReviewsByAlbum($albumId);

        $vars = [
            'music' => $music,
            'ratingStats' => $ratingStats,
            'distribution' => $distribution,
            'reviews' => $reviews,
        ];

        extract($vars, EXTR_SKIP);
        require_once __DIR__ . '/../Views/painelmusic.php';
    }

    // adiciona, editar, deletar - mantidos como antes
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }
        if (!isset($_SESSION['id_usuario'])) { $_SESSION['flash_msg'] = 'Você precisa estar logado para avaliar.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_album = (int)($_POST['id_album'] ?? 0);
        $nota = (float)($_POST['nota'] ?? 0);
        $texto_review = trim($_POST['texto_review'] ?? '');

        if ($id_album <= 0 || $nota <= 0) { $_SESSION['flash_msg'] = 'Dados inválidos.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $ok = $this->model->addReview($id_usuario, $id_album, $nota, $texto_review);
        $_SESSION['flash_msg'] = $ok ? 'Avaliação enviada com sucesso.' : 'Erro ao enviar avaliação.';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }
        if (!isset($_SESSION['id_usuario'])) { $_SESSION['flash_msg'] = 'Você precisa estar logado.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_avaliacao = (int)($_POST['id_avaliacao'] ?? 0);
        $nota = (float)($_POST['nota'] ?? 0);
        $texto_review = trim($_POST['texto_review'] ?? '');

        if ($id_avaliacao <= 0 || $nota <= 0) { $_SESSION['flash_msg'] = 'Dados inválidos.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $ok = $this->model->editReview($id_avaliacao, $id_usuario, $nota, $texto_review);
        $_SESSION['flash_msg'] = $ok ? 'Avaliação atualizada.' : 'Não foi possível atualizar (verifique se é sua avaliação).';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }
        if (!isset($_SESSION['id_usuario'])) { $_SESSION['flash_msg'] = 'Você precisa estar logado.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_avaliacao = (int)($_POST['id_avaliacao'] ?? 0);

        if ($id_avaliacao <= 0) { $_SESSION['flash_msg'] = 'Dados inválidos.'; header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/')); exit; }

        $ok = $this->model->deleteReview($id_avaliacao, $id_usuario);
        $_SESSION['flash_msg'] = $ok ? 'Avaliação excluída.' : 'Não foi possível excluir (somente o autor pode excluir).';
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }
}

// Dispatcher: aceita controller=painelmusic
if (php_sapi_name() !== 'cli' && isset($_GET['controller']) && strtolower($_GET['controller']) === 'painelmusic') {
    $ctrl = new PainelMusicController($GLOBALS['conn'] ?? null);
    $action = $_GET['action'] ?? 'show';
    if ($action === 'show') $ctrl->show();
    elseif ($action === 'save') $ctrl->save();
    elseif ($action === 'edit') $ctrl->edit();
    elseif ($action === 'delete') $ctrl->delete();
    else $ctrl->show();
}

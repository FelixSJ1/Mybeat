<?php

require_once __DIR__ . "/../config/conector.php";
require_once __DIR__ . '/../models/ModelsG.php';

class HomeController {
    private $albumModel;
    private $musicaModel;

    public function __construct($conn) {
        $this->albumModel  = new Album($conn);
        $this->musicaModel = new Musica($conn);
    }

    public function getAlbums($q = '') {
        return $this->albumModel->getAll($q);
    }

    public function getMusicas($q = '') {
        return $this->musicaModel->getAll($q);
    }
}

class AlbumController {
    private $albumModel;
    private $avaliacaoModel;

    public function __construct($conn) {
        $this->albumModel     = new Album($conn);
        $this->avaliacaoModel = new Avaliacao($conn);
    }

    public function detalhes() {
        $id_album = (int)($_GET['id'] ?? $_GET['id_album'] ?? 0);
        $album      = $this->albumModel->getById($id_album);
        $musicas    = $this->albumModel->getMusicas($id_album);
        $avaliacoes = $this->avaliacaoModel->getByAlbum($id_album);
        require __DIR__ . '/../views/detalhes_album.php';
    }
}

class MusicaController {
    private $musicaModel;

    public function __construct($conn) {
        $this->musicaModel = new Musica($conn);
    }

    public function detalhes() {
        // aceita tanto ?id=123 quanto ?id_musica=123
        $id_musica = (int)($_GET['id'] ?? $_GET['id_musica'] ?? 0);
        $musica = $this->musicaModel->getById($id_musica);
        require __DIR__ . '/../views/detalhes_musica.php';
    }
}

class AvaliacaoController {
    private $avaliacaoModel;

    public function __construct($conn) {
        $this->avaliacaoModel = new Avaliacao($conn);
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = 1; // fixo até login estar implementado
            $id_album   = (int)$_POST['id_album'];
            $nota       = (float)$_POST['nota'];
            $texto_review = trim($_POST['texto_review']);
            $this->avaliacaoModel->adicionar($id_usuario, $id_album, $nota, $texto_review);
            header("Location: listar_giovana.php?controller=album&action=detalhes&id_album=$id_album");
            exit;
        }
    }
}

// adição de avaliações do usuário//
class AvaliacaoUsuarioController {
    private $albumModel;
    private $avaliacaoModel;

    public function __construct($conn) {
        $this->albumModel = new Album($conn);
        $this->avaliacaoModel = new Avaliacao($conn);
    }

    // Mostrar página de avaliação
    public function avaliar() {
        $id_album = (int)($_GET['id_album'] ?? 0);
        if ($id_album <= 0) {
            die("Álbum inválido ou não informado.");
        }

        $album = $this->albumModel->getById($id_album);
        $musicas = $this->albumModel->getMusicas($id_album);
        $avaliacoes = $this->avaliacaoModel->getByAlbum($id_album);

        $ratingStats = $this->albumModel->getRatingStats($id_album);
        
        $album['media_nota'] = $ratingStats['media_nota'] ?? 0;
        $album['total_avaliacoes'] = $ratingStats['total_avaliacoes'] ?? 0;

        require __DIR__ . '/../views/avaliacao.php';
    }

    // Salvar avaliação enviada pelo form
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            session_start();
            $id_usuario = $_SESSION['id_usuario'] ?? 1;

            $id_album     = filter_input(INPUT_POST, 'id_album', FILTER_VALIDATE_INT);
            $nota         = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT);
            $texto_review = trim($_POST['texto_review']);

            if ($id_album && $nota && $id_usuario) {
                $this->avaliacaoModel->adicionar($id_usuario, $id_album, $nota, $texto_review);

                header("Location: listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=$id_album&msg=success");
                exit;
            } else {

                header("Location: listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=$id_album&msg=error");
                exit;
            }
        }
    }
    
}


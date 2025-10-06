<?php
require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../models/ModelsG.php';

class AvaliacaoUsuarioController {
    private $albumModel;
    private $avaliacaoModel;

    public function __construct($conn) {
        $this->albumModel = new Album($conn);
        $this->avaliacaoModel = new Avaliacao($conn);
    }

    public function avaliar() {
        $id_album = (int)($_GET['id_album'] ?? 0);
        if ($id_album <= 0) {
            die("Álbum inválido ou não informado.");
        }

        $album = $this->albumModel->getById($id_album);
        $musicas = $this->albumModel->getMusicas($id_album);
        $avaliacoes = $this->avaliacaoModel->getByAlbum($id_album);

        require __DIR__ . '/../views/avaliacao.php';
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $id_usuario = $_SESSION['id_usuario'] ?? null;
            if (!$id_usuario) {
                header("Location: /Mybeat/app/views/FaçaLoginMyBeat.php");
                exit;
            }

            $id_album = (int)$_POST['id_album'];
            $nota = (float)$_POST['nota'];
            $texto_review = trim($_POST['texto_review']);

            if ($id_album > 0 && $nota > 0) {
                $this->avaliacaoModel->adicionar($id_usuario, $id_album, $nota, $texto_review);
                header("Location: avaliacao.php?id_album=$id_album&msg=success");
                exit;
            } else {
                header("Location: avaliacao.php?id_album=$id_album&msg=error");
                exit;
            }
        }
    }
}

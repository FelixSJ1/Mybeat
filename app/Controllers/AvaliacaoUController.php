<?php
require_once __DIR__ . '/../config/conector.php';
require_once __DIR__ . '/../Models/ModelsG.php';
require_once __DIR__ . '/../Models/playlistM.php'; //AQUIIIIIIIIIIIIIIII

class AvaliacaoUController {
    private $albumModel;
    private $avaliacaoModel;
    private $conn; ///aquiiiiiiiiiiiiiiii

    public function __construct($conn) {
        $this->albumModel = new Album($conn);
        $this->avaliacaoModel = new Avaliacao($conn);
        $this->conn = $conn;//aquiiiiiiiiiiiiiiiiiii 
    }

    public function avaliar() {
        $id_album = (int)($_GET['id_album'] ?? 0);
        if ($id_album <= 0) {
            die("Álbum inválido ou não informado.");
        }

        $album = $this->albumModel->getById($id_album);
        $musicas = $this->albumModel->getMusicas($id_album);
        $avaliacoes = $this->avaliacaoModel->getByAlbum($id_album);
    
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php');
            exit();
        }

        $id_usuario_logado = (int)$_SESSION['id_usuario'];
        $playlistModel = new PlaylistModel($this->conn); 
        $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario_logado);
        $isAlbumCurtido = $this->albumModel->isAlbumCurtido($id_usuario_logado, $id_album);

        require __DIR__ . '/../views/avaliacao.php';
    }

    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            
            $id_usuario = $_SESSION['id_usuario'] ?? null;//AQUIIIIIIIIIIIIIIIII
            if (!$id_usuario) {
                header("Location: /Mybeat/app/views/FaçaLoginMyBeat.php");
                exit;
            }

            $id_album = (int)$_POST['id_album'];
            $nota = (float)$_POST['nota'];
            $texto_review = trim($_POST['texto_review']);

            if ($id_album > 0 && $nota > 0) {
                $this->avaliacaoModel->adicionar($id_usuario, $id_album, $nota, $texto_review);
                header("Location: /Mybeat/public/listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=$id_album&msg=success");
                exit;
            } else {
                header("Location: avaliacao.php?id_album=$id_album&msg=error");
                exit;
            }
        }
    }
    public function curtirMusica() {
        // Garante que a sessão está ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php');
            exit;
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_musica = isset($_GET['id_musica']) ? (int)$_GET['id_musica'] : 0;
        $id_album  = isset($_GET['id_album'])  ? (int)$_GET['id_album']  : 0;

        if ($id_musica <= 0 || $id_album <= 0) {
            echo "DEBUG: id_musica=$id_musica | id_album=$id_album";
            exit;
        }
        if ($id_musica <= 0) {
             header("Location: /Mybeat/public/listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=$id_album&msg=invalid_music");
             exit;
        }
        
        $playlistModel = new PlaylistModel($this->conn);
        $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario); 

        if ($playlistModel->isTrackInPlaylist($likedPlaylistId, $id_musica)) {
            $playlistModel->removeMusicFromPlaylist($likedPlaylistId, $id_musica);
            
            // Salva a mensagem de "descurtir" na sessão
            $_SESSION['flash_message'] = "Música removida das curtidas.";
            $_SESSION['flash_message_type'] = "alert-info"; 

        } else {
            $playlistModel->addMusicToPlaylist($likedPlaylistId, $id_musica);
            
            // Salva a mensagem de "curtir" na sessão
            $_SESSION['flash_message'] = "✅ Música adicionada às curtidas!";
            $_SESSION['flash_message_type'] = "alert-success";
        }

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /Mybeat/app/Views/home_usuario.php'); 
        }
        exit;
    }


    public function curtirAlbum() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /Mybeat/app/Views/home_usuario.php');
            }
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php');
            exit;
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $id_album = (int)($_GET['id_album'] ?? 0); 

        if ($id_album <= 0) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        if ($this->albumModel->isAlbumCurtido($id_usuario, $id_album)) {
            
            $this->albumModel->descurtirAlbum($id_usuario, $id_album);
            $_SESSION['flash_message'] = "Álbum removido dos curtidos.";
            $_SESSION['flash_message_type'] = "alert-info"; 
        } else {
            $this->albumModel->curtirAlbum($id_usuario, $id_album);
            $_SESSION['flash_message'] = "✅ Álbum adicionado aos curtidos!";
            $_SESSION['flash_message_type'] = "alert-success"; 
        }

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: /Mybeat/public/listar_giovana.php?controller=avaliacaoUsuario&action=avaliar&id_album=$id_album");
        }
        exit;
    }

    public function mostrarAlbunsCurtidos() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario'])) {
            header('Location: /Mybeat/app/Views/FaçaLoginMyBeat.php');
            exit;
        }

        $id_usuario_logado = (int)$_SESSION['id_usuario'];
        $albuns = $this->albumModel->getAlbunsCurtidosPorUsuario($id_usuario_logado);
        require __DIR__ . '/../views/meus_albuns_curtidos.php';
    }
} 


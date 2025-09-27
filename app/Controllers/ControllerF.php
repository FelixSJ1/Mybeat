<?php
<<<<<<< Updated upstream
=======
require_once __DIR__ . "/../config/conector.php";
>>>>>>> Stashed changes
require_once __DIR__ . "/../Models/ModelF.php";

class ControllerF {
    private $model;

    public function __construct($conn) {
        $this->model = new ModelF($conn);
    }

    public function processarRequisicao() {
        $msg = [
            "artista" => "",
            "album" => "",
            "musica" => ""
        ];

        // Inserção de artista
        if(isset($_POST['nome_artista']) && !empty($_POST['nome_artista'])){
            $ok = $this->model->adicionarArtista(
                $_POST['nome_artista'],
                $_POST['biografia_artista'] ?? '',
                $_POST['foto_artista_url'] ?? '',
                $_POST['ano_inicio_atividade'] ?? null,
                $_POST['pais_origem'] ?? ''
            );
            $msg['artista'] = $ok ? "Artista adicionado com sucesso!" : "Erro ao adicionar artista.";
        }

        // Inserção de álbum
        if(isset($_POST['titulo_album']) && !empty($_POST['titulo_album'])){
            $ok = $this->model->adicionarAlbum(
                $_POST['titulo_album'],
                $_POST['busca_artista'],
                $_POST['data_lancamento'],
                $_POST['capa_album_url'],
                $_POST['genero'],
                $_POST['tipo']
            );
            $msg['album'] = $ok ? "Álbum adicionado com sucesso!" : "Erro ao adicionar álbum (verifique o artista).";
        }

        // Inserção de música
        if(isset($_POST['titulo_musica']) && !empty($_POST['titulo_musica'])){
            $ok = $this->model->adicionarMusica(
                $_POST['titulo_musica'],
                $_POST['album_musica'],
                $_POST['busca_artista_musica'],
                $_POST['duracao_segundos'],
                $_POST['numero_faixa']
            );
            $msg['musica'] = $ok ? "Música adicionada com sucesso!" : "Erro ao adicionar música (verifique artista/álbum).";
        }

        return $msg;
    }
}

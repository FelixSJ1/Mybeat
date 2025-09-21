<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/EditMyBeatModels.php';

class EditMyBeatControllers {
    private EditMyBeatModels $model;

    public function __construct(PDO $pdo) {
        $this->model = new EditMyBeatModels($pdo);
    }

    public function handleRequest(string $action, ?string $type = null, ?int $id = null): array {
        $data = [
            'artistas' => [],
            'albuns' => [],
            'musicas' => [],
            'artista' => null,
            'album' => null,
            'musica' => null,
            'success' => null
        ];

        if ($action === 'home') {
            $action = 'list';
            $type = 'artistas';
        }

        switch ($action) {
            case 'list':
                if ($type === 'artistas') {
                    $data['artistas'] = $this->model->getAllArtistas();
                } elseif ($type === 'albuns') {
                    $data['albuns'] = $this->model->getAllAlbuns();
                } elseif ($type === 'musicas') {
                    $data['musicas'] = $this->model->getAllMusicas();
                }
                break;

            case 'edit':
                if ($type === 'artista' && $id !== null) {
                    $data['artista'] = $this->model->getArtistaById($id);
                } elseif ($type === 'album' && $id !== null) {
                    $data['album'] = $this->model->getAlbumById($id);
                } elseif ($type === 'musica' && $id !== null) {
                    $data['musica'] = $this->model->getMusicaById($id);
                }
                break;

            case 'update':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

                if ($type === 'artista') {
                    $idArtista = (int)($_POST['id_artista'] ?? 0);
                    $payload = [
                        'nome' => $_POST['nome'] ?? '',
                        'biografia' => $_POST['biografia'] ?? '',
                        'foto_artista_url' => $_POST['foto_artista_url'] ?? '',
                        'ano_inicio_atividade' => $_POST['ano_inicio_atividade'] ?? null,
                        'pais_origem' => $_POST['pais_origem'] ?? ''
                    ];
                    $ok = $this->model->updateArtista($idArtista, $payload);
                    header('Location: ?action=edit&type=artista&id=' . $idArtista . '&success=' . ($ok ? '1' : '0'));
                    exit;
                }

                if ($type === 'album') {
                    $idAlbum = (int)($_POST['id_album'] ?? 0);
                    $payload = [
                        'titulo' => $_POST['titulo'] ?? '',
                        'data_lancamento' => $_POST['data_lancamento'] ?? null,
                        'capa_album_url' => $_POST['capa_album_url'] ?? '',
                        'genero' => $_POST['genero'] ?? '',
                        'tipo' => $_POST['tipo'] ?? ''
                    ];
                    $ok = $this->model->updateAlbum($idAlbum, $payload);
                    header('Location: ?action=edit&type=album&id=' . $idAlbum . '&success=' . ($ok ? '1' : '0'));
                    exit;
                }

                if ($type === 'musica') {
                    $idMusica = (int)($_POST['id_musica'] ?? 0);
                    $payload = [
                        'titulo' => $_POST['titulo'] ?? '',
                        'duracao_segundos' => $_POST['duracao_segundos'] ?? null,
                        'numero_faixa' => $_POST['numero_faixa'] ?? null
                    ];
                    $ok = $this->model->updateMusica($idMusica, $payload);
                    header('Location: ?action=edit&type=musica&id=' . $idMusica . '&success=' . ($ok ? '1' : '0'));
                    exit;
                }
                break;

            default:
                break;
        }

        return $data;
    }
}

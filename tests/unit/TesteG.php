<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/Controllers/playlistC.php';
require_once __DIR__ . '/../../app/Models/playlistM.php';

class TesteG extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        // limpa a sessão entre testes
        $_SESSION = [];
        // limpa _GET/_POST/_FILES também por segurança
        $_GET = [];
        $_POST = [];
        $_FILES = [];
    }

    /**
     * Cria um "fake" de conexão que responde com stmts pré-configurados.
     * $map é um array onde chave => substring do SQL, valor => objeto stmt que será retornado.
     */
    private function makeFakeConn($map = [])
    {
        $conn = new class($map) {
            private $map;
            public function __construct($map) { $this->map = $map; }
            public function prepare($sql)
            {
                foreach ($this->map as $pattern => $stmt) {
                    if (strpos($sql, $pattern) !== false) {
                        return $stmt;
                    }
                }
                // stmt "genérico" que representa sucesso sem dados
                return new class {
                    public $insert_id = 0;
                    public function bind_param() { return true; }
                    public function execute() { return true; }
                    public function get_result() {
                        return new class {
                            public function fetch_all($t) { return []; }
                            public function fetch_assoc() { return null; }
                        };
                    }
                    public function close() {}
                };
            }
        };
        return $conn;
    }

    public function testCreatePlaylistReturnsIdOnSuccess()
    {
        // stmt que simula insert bem-sucedido
        $stmt = new class {
            public $insert_id = 123;
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function get_result() { return null; }
            public function close() {}
        };

        $conn = $this->makeFakeConn(['INSERT INTO Playlists' => $stmt]);
        $model = new PlaylistModel($conn);

        $res = $model->createPlaylist(5, 'Minha playlist', 'desc', '/url/capa.jpg');
        $this->assertEquals(123, $res);
    }

    public function testCreatePlaylistReturnsFalseWhenPrepareFails()
    {
        // conn que não consegue preparar statement
        $conn = new class {
            public function prepare($sql) { return false; }
        };
        $model = new PlaylistModel($conn);
        $res = $model->createPlaylist(5, 'x', 'y', null);
        $this->assertFalse($res);
    }

    public function testGetByUserReturnsArray()
    {
        $rows = [
            ['id_playlist'=>1,'id_usuario'=>5,'nome_playlist'=>'A','descricao_playlist'=>'d','capa_playlist_url'=>null,'data_criacao'=>'2025-01-01']
        ];
        $result = new class($rows) {
            private $rows;
            public function __construct($rows) { $this->rows = $rows; }
            public function fetch_all($mode) { return $this->rows; }
            public function fetch_assoc() { return $this->rows[0] ?? null; }
        };
        $stmt = new class($result) {
            private $result;
            public function __construct($result) { $this->result = $result; }
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function get_result() { return $this->result; }
            public function close() {}
        };

        $conn = $this->makeFakeConn(['SELECT * FROM Playlists' => $stmt]);
        $model = new PlaylistModel($conn);

        $res = $model->getByUser(5);
        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertEquals('A', $res[0]['nome_playlist']);
    }

    public function testGetByIdReturnsRowOrNull()
    {
        $row = ['id_playlist'=>2,'nome_playlist'=>'B'];
        $result = new class($row) {
            private $row;
            public function __construct($row) { $this->row = $row; }
            public function fetch_assoc() { return $this->row; }
        };
        $stmt = new class($result) {
            private $result;
            public function __construct($result) { $this->result = $result; }
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function get_result() { return $this->result; }
            public function close() {}
        };

        $conn = $this->makeFakeConn(['WHERE id_playlist' => $stmt]);
        $model = new PlaylistModel($conn);

        $res = $model->getById(2);
        $this->assertIsArray($res);
        $this->assertEquals('B', $res['nome_playlist']);

        // caso não exista
        $emptyResult = new class {
            public function fetch_assoc() { return null; }
        };
        $stmt2 = new class($emptyResult) {
            private $result;
            public function __construct($result) { $this->result = $result; }
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function get_result() { return $this->result; }
            public function close() {}
        };
        $conn2 = $this->makeFakeConn(['WHERE id_playlist' => $stmt2]);
        $model2 = new PlaylistModel($conn2);
        $res2 = $model2->getById(9999);
        $this->assertNull($res2);
    }

    public function testGetMusicasByPlaylistReturnsArray()
    {
        $rows = [
            ['id_musica_playlist'=>1,'titulo'=>'M1','artista_nome'=>'X']
        ];
        $result = new class($rows) {
            private $rows;
            public function __construct($rows) { $this->rows = $rows; }
            public function fetch_all($mode) { return $this->rows; }
        };
        $stmt = new class($result) {
            private $result;
            public function __construct($result) { $this->result = $result; }
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function get_result() { return $this->result; }
            public function close() {}
        };
        $conn = $this->makeFakeConn(['FROM Musicas_Playlist' => $stmt]);
        $model = new PlaylistModel($conn);
        $res = $model->getMusicasByPlaylist(1);
        $this->assertIsArray($res);
        $this->assertEquals('M1', $res[0]['titulo']);
    }

    public function testAddMusicToPlaylistTrueAndFalse()
    {
        // sucesso
        $stmtOk = new class {
            public function bind_param() { return true; }
            public function execute() { return true; }
            public function close() {}
        };
        $connOk = $this->makeFakeConn(['INSERT INTO Musicas_Playlist' => $stmtOk]);
        $modelOk = new PlaylistModel($connOk);
        $this->assertTrue($modelOk->addMusicToPlaylist(1,2));

        // falha (duplicidade) -> execute retorna false
        $stmtDup = new class {
            public function bind_param() { return true; }
            public function execute() { return false; }
            public function close() {}
        };
        $connDup = $this->makeFakeConn(['INSERT INTO Musicas_Playlist' => $stmtDup]);
        $modelDup = new PlaylistModel($connDup);
        $this->assertFalse($modelDup->addMusicToPlaylist(1,2));
    }

    public function testControllerIndexIncludesViewAndShowsPlaylists()
    {
        // cria controller com conn fake (não usado pelo stub de model)
        $conn = $this->makeFakeConn([]);
        $controller = new PlaylistController($conn);

        // stub de model que será injetado no controller
        $stubModel = new class {
            public function getByUser($id, $q='') {
                return [['id_playlist'=>10,'nome_playlist'=>'TestPlaylist']];
            }
        };

        // injeta via reflection (model é propriedade privada)
        $ref = new ReflectionClass($controller);
        $prop = $ref->getProperty('model');
        $prop->setAccessible(true);
        $prop->setValue($controller, $stubModel);

        // garante usuário logado
        $_SESSION['id_usuario'] = 5;

        // captura o output (a view será incluida)
        ob_start();
        $controller->index();
        $out = ob_get_clean();

        $this->assertStringContainsString('TestPlaylist', $out);
        $this->assertStringContainsString('<!DOCTYPE html>', $out);
    }

    public function testControllerDetalhesLoadsPlaylistView()
    {
        $conn = $this->makeFakeConn([]);
        $controller = new PlaylistController($conn);

        $stubModel = new class {
            public function getById($id) {
                return ['id_playlist'=>$id,'nome_playlist'=>'Detalhes'];
            }
            public function getMusicasByPlaylist($id) {
                return [['titulo'=>'M1']];
            }
        };
        $ref = new ReflectionClass($controller);
        $prop = $ref->getProperty('model');
        $prop->setAccessible(true);
        $prop->setValue($controller, $stubModel);

        // prepara id e captura view
        $_GET['id'] = 7;
        ob_start();
        $controller->detalhes();
        $out = ob_get_clean();

        $this->assertStringContainsString('Detalhes', $out);
        $this->assertStringContainsString('M1', $out);
    }
}

// alias para compatibilidade
class TestG extends TesteG {}

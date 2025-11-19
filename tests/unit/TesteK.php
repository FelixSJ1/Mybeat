<?php
use PHPUnit\Framework\TestCase;

// Inclui o Model a ser testado
require_once __DIR__ . '/../../app/Models/playlistM.php';

// ==================== STUBS PARA MYSQLI (Reutilizados do seu padrão) ====================

// Mock para simular o resultado de consultas SELECT
class MockMysqliResult {
    public $num_rows = 0;
    private $data = [];
    private $position = 0;

    public function __construct($data = [], $num_rows = 0) {
        $this->data = $data;
        $this->num_rows = $num_rows;
    }

    public function fetch_assoc() {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return null;
    }

    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->data;
    }
}

// Mock para simular prepared statements (INSERT/UPDATE/DELETE/SELECT)
class MockMysqliStmt {
    public $insert_id = 0;
    public $affected_rows = 0;
    private $shouldSucceed = true;
    private $resultData = [];

    public function __construct($shouldSucceed = true, $insertId = 0, $affectedRows = 0, $resultData = []) {
        $this->shouldSucceed = $shouldSucceed;
        $this->insert_id = $insertId;
        $this->affected_rows = $affectedRows;
        $this->resultData = $resultData;
    }

    public function bind_param(...$params) {
        return true;
    }

    public function execute() {
        // Simula a falha de execução se configurado
        if (!$this->shouldSucceed) {
            // Se a mockagem do seu projeto não for configurada para lançar exceção,
            // apenas retornar false é o suficiente.
            return false; 
        }
        return true;
    }

    public function get_result() {
        return new MockMysqliResult($this->resultData, count($this->resultData));
    }

    public function close() {
        return true;
    }
}

// Mock principal da conexão mysqli
class MockMysqli {
    private $prepareResponses = [];
    private $responseIndex = 0;

    // Define uma lista de respostas para a próxima sequência de chamadas a prepare()
    public function setPrepareResponses(array $stmts) {
        $this->prepareResponses = $stmts;
        $this->responseIndex = 0;
    }

    public function prepare($query) {
        if (isset($this->prepareResponses[$this->responseIndex])) {
            $response = $this->prepareResponses[$this->responseIndex];
            $this->responseIndex++;
            return $response;
        }
        // Retorna um MockStmt que falha se não houver resposta esperada
        return new MockMysqliStmt(false); 
    }
}

// ==================== TESTES ====================

class TesteK extends TestCase {
    private $mockDb;
    private $playlistModel;
    private $userId = 42;
    private $likedPlaylistName = "Músicas Curtidas";
    private $mockModel;
    private $controllerStub;

    protected function setUp(): void {
        $this->mockDb = new MockMysqli();
        $this->playlistModel = new PlaylistModel($this->mockDb);
        $this->mockModel = new MockPlaylistModel($this->mockDb);
        $this->controllerStub = new CurtidaC_Stub($this->mockDb, $this->mockModel);
        

        // Iniciar sessão para simular usuário logado
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        unset($_SESSION['id_usuario']);
        unset($_SESSION['mensagem_erro']);
        unset($_SESSION['mensagem_sucesso']);
        $_SERVER['HTTP_REFERER'] = '/detalhe_album.php?id=123';

        $_SESSION['mensagem_erro'] = '';

        // Limpar variáveis globais, se necessário (Model não usa, mas bom para consistência)
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    protected function tearDown(): void {
        unset($_SESSION['id_usuario']);
        unset($GLOBALS['conn']);
    }

    // -------------------------------------------------------------------
    // TESTES: getOrCreateLikedPlaylist()
    // -------------------------------------------------------------------

    public function testGetOrCreateLikedPlaylist_EncontraExistente() {
        // 1. Simula a primeira query (SELECT) encontrando a playlist
        $stmtFind = new MockMysqliStmt(true, 0, 0, [
            ['id_playlist' => 101]
        ]);
        
        $this->mockDb->setPrepareResponses([$stmtFind]);

        $id = $this->playlistModel->getOrCreateLikedPlaylist($this->userId);
        
        $this->assertEquals(101, $id, "Deve retornar o ID da playlist existente.");
    }

    public function testGetOrCreateLikedPlaylist_CriaNova() {
        // Simula o método createPlaylist para retornar um ID falso (202)
        // Usamos uma classe anônima para interceptar o createPlaylist, pois ele
        // é chamado internamente e não queremos executar o código SQL dele.
        $playlistModelStub = new class($this->mockDb) extends PlaylistModel {
            public $createCalled = false;
            
            public function createPlaylist(int $userId, string $nome, string $descricao = '', ?string $capaUrl = null) {
                $this->createCalled = true;
                return 202; // ID de playlist que será criado
            }
        };

        // 1. Simula a primeira query (SELECT) NÃO encontrando a playlist
        $stmtFind = new MockMysqliStmt(true, 0, 0, []);
        
        $this->mockDb->setPrepareResponses([$stmtFind]);

        $id = $playlistModelStub->getOrCreateLikedPlaylist($this->userId);
        
        $this->assertTrue($playlistModelStub->createCalled, "O método createPlaylist deve ser chamado se não for encontrado.");
        $this->assertEquals(202, $id, "Deve retornar o ID da nova playlist criada (simulada).");
    }

    public function testGetOrCreateLikedPlaylist_FalhaAoCriar() {
        // Simula o método createPlaylist falhando
        $playlistModelStub = new class($this->mockDb) extends PlaylistModel {
            public function createPlaylist(int $userId, string $nome, string $descricao = '', ?string $capaUrl = null) {
                return false; 
            }
        };
        
        // 1. Simula a primeira query (SELECT) NÃO encontrando a playlist
        $stmtFind = new MockMysqliStmt(true, 0, 0, []);
        
        $this->mockDb->setPrepareResponses([$stmtFind]);

        $id = $playlistModelStub->getOrCreateLikedPlaylist($this->userId);
        
        $this->assertNull($id, "Deve retornar NULL se a criação falhar.");
    }

    // -------------------------------------------------------------------
    // TESTES: isTrackInPlaylist()
    // -------------------------------------------------------------------

    public function testIsTrackInPlaylist_RetornaTrue() {
        // Simula o SELECT encontrando 1 resultado
        $stmt = new MockMysqliStmt(true, 0, 0, [
            ['1'] // Um resultado indica que existe
        ]);
        
        $this->mockDb->setPrepareResponses([$stmt]);
        
        $result = $this->playlistModel->isTrackInPlaylist(101, 50);
        
        $this->assertTrue($result, "Deve retornar TRUE se a música estiver na playlist.");
    }

    public function testIsTrackInPlaylist_RetornaFalse() {
        // Simula o SELECT NÃO encontrando resultados
        $stmt = new MockMysqliStmt(true, 0, 0, []);
        
        $this->mockDb->setPrepareResponses([$stmt]);
        
        $result = $this->playlistModel->isTrackInPlaylist(101, 51);
        
        $this->assertFalse($result, "Deve retornar FALSE se a música não estiver na playlist.");
    }
    
    // -------------------------------------------------------------------
    // TESTES: removeMusicFromPlaylist()
    // -------------------------------------------------------------------
    
    public function testRemoveMusicFromPlaylist_ComSucesso() {
        // Simula o DELETE executando e afetando 1 linha
        $stmt = new MockMysqliStmt(true, 0, 1);
        $this->mockDb->setPrepareResponses([$stmt]);

        $result = $this->playlistModel->removeMusicFromPlaylist(101, 50);

        $this->assertTrue($result, "Deve retornar TRUE em caso de DELETE bem-sucedido.");
    }
    
    public function testRemoveMusicFromPlaylist_ComFalha() {
        // Simula o DELETE falhando
        $stmt = new MockMysqliStmt(false, 0, 0);
        $this->mockDb->setPrepareResponses([$stmt]);

        $result = $this->playlistModel->removeMusicFromPlaylist(101, 50);

        $this->assertFalse($result, "Deve retornar FALSE em caso de falha no DELETE.");
    }

    // -------------------------------------------------------------------
    // TESTES: addAllAlbumTracksToPlaylist()
    // -------------------------------------------------------------------


    // Substitua o código completo do método:
    public function testAddAllAlbumTracksToPlaylist_AdicionaVariasFaixas() {
        $albumId = 77;
        $playlistId = 101;
    
        // Músicas do álbum que o SELECT deve retornar
        $musicasAlbum = [
            ['id_musica' => 301],
            ['id_musica' => 302],
            ['id_musica' => 303],
        ];

        // 1. Mock para a query SELECT de busca das faixas do álbum
        $stmtSelect = new MockMysqliStmt(true, 0, 0, $musicasAlbum);
    
        // 2. Mock para o INSERT IGNORE da primeira música
        $stmtInsert1 = new MockMysqliStmt(true, 0, 1);
    
        // 3. Mock para o INSERT IGNORE da segunda música
        $stmtInsert2 = new MockMysqliStmt(true, 0, 1);

        // 4. Mock para o INSERT IGNORE da terceira música
        $stmtInsert3 = new MockMysqliStmt(true, 0, 1);
    
        // O mockDB irá retornar um statement por vez, na ordem: SELECT, INSERT 1, INSERT 2, INSERT 3
        $this->mockDb->setPrepareResponses([
            $stmtSelect,
            $stmtInsert1,
            $stmtInsert2,
            $stmtInsert3,
        ]);

        $result = $this->playlistModel->addAllAlbumTracksToPlaylist($albumId, $playlistId);

        $this->assertTrue($result, "Deve retornar TRUE após a inserção das 3 músicas.");
    }

    // O restante do seu arquivo de teste (TesteK.php) pode permanecer o mesmo.
    
    public function testAddAllAlbumTracksToPlaylist_AlbumVazio() {
        $albumId = 78;
        $playlistId = 101;
        
        // 1. Simula a query SELECT (busca id_musica pelo id_album) - Retorna vazio
        $stmtSelect = new MockMysqliStmt(true, 0, 0, []);
        
        $this->mockDb->setPrepareResponses([
            $stmtSelect,
        ]);
        
        $result = $this->playlistModel->addAllAlbumTracksToPlaylist($albumId, $playlistId);
        
        $this->assertTrue($result, "Deve retornar TRUE se o álbum estiver vazio, sem tentar INSERTs.");
    }
    
    // -------------------------------------------------------------------
    // TESTES: removeAllAlbumTracksFromPlaylist()
    // -------------------------------------------------------------------
    
    public function testRemoveAllAlbumTracksFromPlaylist_ComSucesso() {
        $albumId = 77;
        $playlistId = 101;

        // Simula o DELETE executando e afetando múltiplas linhas
        $stmt = new MockMysqliStmt(true, 0, 5); 
        $this->mockDb->setPrepareResponses([$stmt]);

        $result = $this->playlistModel->removeAllAlbumTracksFromPlaylist($albumId, $playlistId);

        $this->assertTrue($result, "Deve retornar TRUE em caso de DELETE em massa bem-sucedido.");
    }

    public function testRemoveAllAlbumTracksFromPlaylist_ComFalha() {
        $albumId = 77;
        $playlistId = 101;
        
        // Simula o DELETE falhando
        $stmt = new MockMysqliStmt(false, 0, 0); 
        $this->mockDb->setPrepareResponses([$stmt]);

        $result = $this->playlistModel->removeAllAlbumTracksFromPlaylist($albumId, $playlistId);

        $this->assertFalse($result, "Deve retornar FALSE em caso de falha no DELETE.");
    }
    protected function setupLoggedUser($action, $data) {
        $_SESSION['id_usuario'] = $this->userId;
        $_POST['action'] = $action;
        $_POST = array_merge($_POST, $data);
    }

    // -------------------------------------------------------------------
    // NOVOS TESTES: SEGURANÇA / DIE
    // -------------------------------------------------------------------

    public function testSeguranca_UsuarioNaoLogado() {
        // Garante que a primeira checagem de segurança (POST/action) seja TRUE
        $_SERVER['REQUEST_METHOD'] = 'POST'; 
        $_POST['action'] = 'like_track'; // <-- ADICIONADO: Simula uma ação válida
    
        // A sessão continua vazia (unset no setUp)
        $this->controllerStub->execute();

        // ... (restante da asserção)
        $this->assertEquals('Usuário não logado.', $this->controllerStub->dieMessage, 
        "O Controller deve chamar die() com 'Usuário não logado.' se a sessão estiver vazia.");
    }
    
    // -------------------------------------------------------------------
    // NOVOS TESTES: AÇÃO (DISPATCH & FEEDBACK)
    // -------------------------------------------------------------------

    public function testAcao_like_album_ComSucesso() {
        $albumId = 55;
        $this->setupLoggedUser('like_album', ['id_album' => $albumId]);
        
        $this->controllerStub->execute();

        // Dispatch (Chamada ao Model)
        $this->assertTrue($this->mockModel->getOrCreateCalled, "Deve chamar getOrCreateLikedPlaylist.");
        $this->assertTrue($this->mockModel->addAlbumCalled, "Deve chamar addAllAlbumTracksToPlaylist.");
        
        // Feedback
        $this->assertEquals("Álbum adicionado às Músicas Curtidas!", $_SESSION['mensagem_sucesso']);
        $this->assertEmpty($_SESSION['mensagem_erro']);
        
        // Redirecionamento
        $this->assertStringContainsString('/detalhe_album.php?id=123', $this->controllerStub->redirectUrl);
    }
    
    public function testAcao_unlike_album_ComSucesso() {
        $albumId = 55;
        $this->setupLoggedUser('unlike_album', ['id_album' => $albumId]);
        
        $this->controllerStub->execute();

        // Dispatch (Chamada ao Model)
        $this->assertTrue($this->mockModel->removeAlbumCalled, "Deve chamar removeAllAlbumTracksFromPlaylist.");
        
        // Feedback
        $this->assertEquals("Álbum removido das Músicas Curtidas.", $_SESSION['mensagem_sucesso']);
    }
    
    public function testAcao_like_track_ComSucesso() {
        $musicId = 15;
        $this->setupLoggedUser('like_track', ['id_musica' => $musicId]);
        
        $this->controllerStub->execute();

        // Dispatch (Chamada ao Model)
        $this->assertTrue($this->mockModel->addTrackCalled, "Deve chamar addMusicToPlaylist.");
        
        // Feedback
        $this->assertEquals("Música adicionada às Curtidas!", $_SESSION['mensagem_sucesso']);
    }
    
    public function testAcao_unlike_track_ComSucesso() {
        $musicId = 15;
        $this->setupLoggedUser('unlike_track', ['id_musica' => $musicId]);
        
        $this->controllerStub->execute();

        // Dispatch (Chamada ao Model)
        $this->assertTrue($this->mockModel->removeTrackCalled, "Deve chamar removeMusicFromPlaylist.");
        
        // Feedback
        $this->assertEquals("Música removida das Curtidas.", $_SESSION['mensagem_sucesso']);
    }

    public function testFalha_CriacaoPlaylist() {
        $this->setupLoggedUser('like_album', ['id_album' => 55]);
        
        // Simula a falha no getOrCreateLikedPlaylist
        $this->mockModel->returnLikedPlaylistId = null;
        
        $this->controllerStub->execute();
        
        // Feedback de Erro
        $this->assertStringContainsString("Não foi possível criar a playlist de curtidas.", $_SESSION['mensagem_erro']);
        $this->assertFalse($this->mockModel->addAlbumCalled, "Não deve tentar adicionar faixas se a playlist falhou.");
    }
}

// ==================== STUB PARA SIMULAR AS DEPENDÊNCIAS DO CONTROLLER ====================

// Criamos uma Mock do PlaylistModel para controlar o que o Controller faz no DB.
// OBS: Esta classe é necessária porque o Controller instancia o Model internamente.
class MockPlaylistModel extends PlaylistModel {
    public $getOrCreateCalled = false;
    public $addAlbumCalled = false;
    public $removeAlbumCalled = false;
    public $addTrackCalled = false;
    public $removeTrackCalled = false;

    public $returnLikedPlaylistId = 101; // ID padrão de playlist curtida
    
    public function __construct($conn) {
        // Ignora a chamada ao parent::__construct para evitar erro no Mock
    }
    
    public function getOrCreateLikedPlaylist(int $userId): ?int {
        $this->getOrCreateCalled = true;
        return $this->returnLikedPlaylistId;
    }

    public function addAllAlbumTracksToPlaylist(int $albumId, int $playlistId): bool {
        $this->addAlbumCalled = true;
        return true;
    }
    
    public function removeAllAlbumTracksFromPlaylist(int $albumId, int $playlistId): bool {
        $this->removeAlbumCalled = true;
        return true;
    }
    
    public function addMusicToPlaylist(int $playlistId, int $musicId): bool {
        $this->addTrackCalled = true;
        return true;
    }
    
    public function removeMusicFromPlaylist(int $playlistId, int $musicId): bool {
        $this->removeTrackCalled = true;
        return true;
    }
}

// ==================== CONTROLLER STUB PARA MOCKAR FLUXO DE CONTROLE (DIE/HEADER) ====================

// Esta classe substitui a execução direta do Controller, permitindo a injeção do Mock Model 
// e capturando o fluxo de controle (die/redirecionamento)
class CurtidaC_Stub {
    private $mockDb;
    private $mockModel;

    public $dieMessage = null;
    public $redirectUrl = null;

    public function __construct($mockDb, $mockModel) {
        $this->mockDb = $mockDb;
        $this->mockModel = $mockModel;
    }

    // Método que simula o fluxo completo do CurtidaC.php
    public function execute() {
        // INJEÇÃO DO MOCK DB NO ESCOPO GLOBAL, usado pelo Model no Controller
        $GLOBALS['conn'] = $this->mockDb;

        // Simulação do fluxo de código do CurtidaC.php:

        // 1. Simulação do die('Acesso inválido.')
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
            $this->dieMessage = 'Acesso inválido.';
            return; 
        }

        // 2. Simulação do die('Usuário não logado.')
        if (!isset($_SESSION['id_usuario'])) {
            $this->dieMessage = 'Usuário não logado.';
            return;
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $action = $_POST['action'];
        
        $redirect_url = !empty($_POST['redirect_to'])
            ? $_POST['redirect_to']
            : ($_SERVER['HTTP_REFERER'] ?? '/index.php'); // Referer default

        try {
            // INSTANCIAÇÃO DO MODEL (SUBSTITUÍDA PELO MOCK)
            $playlistModel = $this->mockModel; 

            // ENCONTRA OU CRIA A PLAYLIST
            $likedPlaylistId = $playlistModel->getOrCreateLikedPlaylist($id_usuario);
            if ($likedPlaylistId === null) {
                throw new \Exception("Não foi possível criar a playlist de curtidas.");
            }
            
            // Lógica do SWITCH CASE
            switch ($action) {
                case 'like_album':
                    $id_album = (int)($_POST['id_album'] ?? 0);
                    if ($id_album > 0) {
                        $playlistModel->addAllAlbumTracksToPlaylist($id_album, $likedPlaylistId);
                        $_SESSION['mensagem_sucesso'] = "Álbum adicionado às Músicas Curtidas!";
                    }
                    break;
                case 'unlike_album':
                    $id_album = (int)($_POST['id_album'] ?? 0);
                    if ($id_album > 0) {
                        $playlistModel->removeAllAlbumTracksFromPlaylist($id_album, $likedPlaylistId);
                        $_SESSION['mensagem_sucesso'] = "Álbum removido das Músicas Curtidas.";
                    }
                    break;
                case 'like_track':
                    $id_musica = (int)($_POST['id_musica'] ?? 0);
                    if ($id_musica > 0) {
                        $playlistModel->addMusicToPlaylist($likedPlaylistId, $id_musica);
                        $_SESSION['mensagem_sucesso'] = "Música adicionada às Curtidas!";
                    }
                    break;
                case 'unlike_track':
                    $id_musica = (int)($_POST['id_musica'] ?? 0);
                    if ($id_musica > 0) {
                        $playlistModel->removeMusicFromPlaylist($likedPlaylistId, $id_musica);
                        $_SESSION['mensagem_sucesso'] = "Música removida das Curtidas.";
                    }
                    break;
            }

        } catch (\Exception $e) {
            $_SESSION['mensagem_erro'] = "Erro: " . $e->getMessage();
        }

        // REDIRECIONAMENTO (MOCADO)
        $this->redirectUrl = $redirect_url;
    }
}
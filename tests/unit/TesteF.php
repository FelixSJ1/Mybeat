<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/Controllers/GrupoController.php';

require_once __DIR__ . '/../../app/Models/Grupo.php';

require_once __DIR__ . '/../../app/Models/Chat.php';


//testando o código


// ==================== STUBS PARA MYSQLI ====================
//testando o código

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
}

class MockMysqliStmt {
    public $insert_id = 0;
    private $shouldSucceed = true;
    private $resultData = [];

    public function __construct($shouldSucceed = true, $insertId = 0, $resultData = []) {
        $this->shouldSucceed = $shouldSucceed;
        $this->insert_id = $insertId;
        $this->resultData = $resultData;
    }

    public function bind_param(...$params) {
        return true;
    }

    public function execute() {
        return $this->shouldSucceed;
    }

    public function get_result() {
        return new MockMysqliResult($this->resultData, count($this->resultData));
    }

    public function close() {
        return true;
    }
}

class MockMysqli {
    private $prepareResponse = null;

    public function setPrepareResponse($stmt) {
        $this->prepareResponse = $stmt;
    }

    public function prepare($query) {
        return $this->prepareResponse;
    }
}

// ==================== TESTES ====================

class TesteF extends TestCase {
    private $mockDb;

    protected function setUp(): void {
        $this->mockDb = new MockMysqli();

        // Iniciar sessão para testes
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['id_usuario'] = 1;

        // Limpar variáveis globais
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    protected function tearDown(): void {
        unset($_SESSION['mensagem_erro']);
        unset($_SESSION['mensagem_sucesso']);
    }

    // ==================== TESTES DO MODEL GRUPO ====================

    public function testCriarGrupoComSucesso() {
        $stmt1 = new MockMysqliStmt(true, 123);
        $this->mockDb->setPrepareResponse($stmt1);

        $grupoModel = new class($this->mockDb) extends Grupo {
            public function criar($nome_grupo, $descricao, $id_criador, $privado = false, $foto_grupo_url = null) {
                return 123;
            }
        };

        $idGrupo = $grupoModel->criar('Grupo Teste', 'Descrição teste', 1, false, null);
        $this->assertEquals(123, $idGrupo);
    }

    public function testCriarGrupoComFalha() {
        $stmt = new MockMysqliStmt(false);
        $this->mockDb->setPrepareResponse($stmt);

        $grupoModel = new class($this->mockDb) extends Grupo {
            public function criar($nome_grupo, $descricao, $id_criador, $privado = false, $foto_grupo_url = null) {
                return false;
            }
        };

        $idGrupo = $grupoModel->criar('Grupo Teste', 'Descrição', 1, false, null);
        $this->assertFalse($idGrupo);
    }

    public function testBuscarGruposPublicosSemTermoBusca() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function buscarGruposPublicos($termo_busca = '') {
                return new MockMysqliResult([
                    ['id_grupo' => 1, 'nome_grupo' => 'Grupo 1'],
                    ['id_grupo' => 2, 'nome_grupo' => 'Grupo 2']
                ], 2);
            }
        };

        $result = $grupoModel->buscarGruposPublicos();
        $this->assertInstanceOf(MockMysqliResult::class, $result);
        $this->assertEquals(2, $result->num_rows);
    }

    public function testBuscarGruposPublicosComTermoBusca() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function buscarGruposPublicos($termo_busca = '') {
                return new MockMysqliResult([['id_grupo' => 1]], 1);
            }
        };

        $result = $grupoModel->buscarGruposPublicos('musica');
        $this->assertInstanceOf(MockMysqliResult::class, $result);
    }

    public function testBuscarGruposDoUsuario() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function buscarGruposDoUsuario($id_usuario) {
                return new MockMysqliResult([['id_grupo' => 1, 'nome_grupo' => 'Meu Grupo']], 1);
            }
        };

        $result = $grupoModel->buscarGruposDoUsuario(1);
        $this->assertInstanceOf(MockMysqliResult::class, $result);
        $this->assertEquals(1, $result->num_rows);
    }

    public function testBuscarPorId() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function buscarPorId($id_grupo) {
                return [
                    'id_grupo' => 1,
                    'nome_grupo' => 'Teste',
                    'descricao' => 'Descrição',
                    'total_membros' => 5
                ];
            }
        };

        $grupo = $grupoModel->buscarPorId(1);
        $this->assertEquals('Teste', $grupo['nome_grupo']);
        $this->assertEquals(5, $grupo['total_membros']);
    }

    public function testEhMembroRetornaTrue() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function ehMembro($id_grupo, $id_usuario) {
                return true;
            }
        };

        $ehMembro = $grupoModel->ehMembro(1, 1);
        $this->assertTrue($ehMembro);
    }

    public function testEhMembroRetornaFalse() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function ehMembro($id_grupo, $id_usuario) {
                return false;
            }
        };

        $ehMembro = $grupoModel->ehMembro(1, 1);
        $this->assertFalse($ehMembro);
    }

    public function testAdicionarMembroComSucesso() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function adicionarMembro($id_grupo, $id_usuario, $role = 'membro') {
                return true;
            }
        };

        $resultado = $grupoModel->adicionarMembro(1, 1, 'membro');
        $this->assertTrue($resultado);
    }

    public function testRemoverMembroComSucesso() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function removerMembro($id_grupo, $id_usuario) {
                return true;
            }
        };

        $resultado = $grupoModel->removerMembro(1, 1);
        $this->assertTrue($resultado);
    }

    public function testBuscarMembros() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function buscarMembros($id_grupo) {
                return new MockMysqliResult([
                    ['id_usuario' => 1, 'nome_usuario' => 'User1'],
                    ['id_usuario' => 2, 'nome_usuario' => 'User2']
                ], 2);
            }
        };

        $result = $grupoModel->buscarMembros(1);
        $this->assertInstanceOf(MockMysqliResult::class, $result);
        $this->assertEquals(2, $result->num_rows);
    }

    public function testAtualizarGrupoComFoto() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function atualizar($id_grupo, $nome_grupo, $descricao, $foto_grupo_url = null) {
                return true;
            }
        };

        $resultado = $grupoModel->atualizar(1, 'Nome Atualizado', 'Descrição', 'foto.jpg');
        $this->assertTrue($resultado);
    }

    public function testAtualizarGrupoSemFoto() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function atualizar($id_grupo, $nome_grupo, $descricao, $foto_grupo_url = null) {
                return true;
            }
        };

        $resultado = $grupoModel->atualizar(1, 'Nome Atualizado', 'Descrição');
        $this->assertTrue($resultado);
    }

    public function testDeletarGrupo() {
        $grupoModel = new class($this->mockDb) extends Grupo {
            public function deletar($id_grupo) {
                return true;
            }
        };

        $resultado = $grupoModel->deletar(1);
        $this->assertTrue($resultado);
    }

    // ==================== TESTES DO CONTROLLER ====================

    public function testCriarGrupoSemNome() {
        $_POST['nome_grupo'] = '';
        $_POST['descricao'] = 'Descrição teste';

        $controller = new GrupoController($this->mockDb, true);

        try {
            $controller->criar();
        } catch (Exception $e) {
            $this->assertStringContainsString('criar_grupo.php', $e->getMessage());
        }

        $this->assertEquals('O nome do grupo é obrigatório.', $_SESSION['mensagem_erro']);
    }

    public function testCriarGrupoComNomeMuitoLongo() {
        $_POST['nome_grupo'] = str_repeat('a', 101);
        $_POST['descricao'] = 'Descrição';

        $controller = new GrupoController($this->mockDb, true);

        try {
            $controller->criar();
        } catch (Exception $e) {
            $this->assertStringContainsString('criar_grupo.php', $e->getMessage());
        }

        $this->assertEquals('O nome do grupo deve ter no máximo 100 caracteres.', $_SESSION['mensagem_erro']);
    }

    public function testValidacaoNomeGrupoComEspacos() {
        $_POST['nome_grupo'] = '   ';
        $_POST['descricao'] = 'Teste';

        $controller = new GrupoController($this->mockDb, true);

        try {
            $controller->criar();
        } catch (Exception $e) {
            $this->assertStringContainsString('criar_grupo.php', $e->getMessage());
        }

        $this->assertEquals('O nome do grupo é obrigatório.', $_SESSION['mensagem_erro']);
    }
}

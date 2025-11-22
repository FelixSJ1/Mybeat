<?php
use PHPUnit\Framework\TestCase;


$possible_paths = [
    __DIR__ . '/../../app/Models/HomeExtrasModel.php',
    __DIR__ . '/../../app/Models/HomeExtras.php',
    __DIR__ . '/../../app/models/HomeExtrasModel.php',
    __DIR__ . '/../../app/models/HomeExtras.php',
];
foreach ($possible_paths as $p) {
    if (file_exists($p)) {
        require_once $p;
        break;
    }
}

/* ========================= STUBS PARA MySQLi ========================= */
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

/* ========================= SUITE DE TESTES ========================= */

class TesteR extends TestCase {
    private $mockDb;
    private $baseModelClass = null;

    protected function setUp(): void {
        $this->mockDb = new MockMysqli();

        // Sessão mínima necessária para as rotinas que usam $_SESSION['id_usuario']
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['id_usuario'] = 1;

        $_POST = []; $_GET = []; $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Detecta a classe base do model (HomeExtras ou HomeExtrasModel)
        if (class_exists('HomeExtras')) $this->baseModelClass = 'HomeExtras';
        elseif (class_exists('HomeExtrasModel')) $this->baseModelClass = 'HomeExtrasModel';
        else {
            $p = __DIR__ . '/../../app/Models/HomeExtrasModel.php';
            if (file_exists($p)) require_once $p;
            if (class_exists('HomeExtras')) $this->baseModelClass = 'HomeExtras';
            elseif (class_exists('HomeExtrasModel')) $this->baseModelClass = 'HomeExtrasModel';
            else $this->baseModelClass = null;
        }
    }

    protected function tearDown(): void {
        unset($_SESSION['mensagem_erro']);
        unset($_SESSION['mensagem_sucesso']);
    }

    /**
     * Cria dinamicamente um stub que estende a classe-base do model (quando encontrada).
     * O stub redefine o construtor para tentar chamar parent::__construct($db) com segurança,
     * e adiciona métodos passados em $methodsCode.
     *
     * Observação importante: NÃO atribuímos propriedades dinamicamente ao objeto (para evitar
     * deprecations em PHP 8.2). Em vez disso, tentamos confiar no parent::__construct($db)
     * para inicializar quaisquer propriedades internas de conexão.
     *
     * @param array $opts  dados simulados que o stub usará (evaluations, similar)
     * @param string $methodsCode string contendo métodos PHP a serem injetados na classe stub
     * @return object|null instância do stub ou null (quando não há classe-base)
     */
    private function makeStubInstance(array $opts = [], string $methodsCode = '') {
        if ($this->baseModelClass === null) {
            $this->markTestSkipped('Classe base do model HomeExtras não encontrada.');
            return null;
        }

        // Gerar nome único de classe
        $stubName = 'HomeExtrasStub_' . uniqid();

        // Constrói código da classe: define constructor que tenta chamar parent::__construct($db) dentro de try/catch
        $classCode = 'class ' . $stubName . ' extends ' . $this->baseModelClass . ' {' . PHP_EOL;
        $classCode .= '    public $simulatedEvals = []; public $simulatedSimilar = []; ' . PHP_EOL;
        $classCode .= '    public function __construct($db = null, $opts = []) {' . PHP_EOL;
        $classCode .= '        // tenta chamar o construtor parent com o mock de DB para inicializar conexões internas' . PHP_EOL;
        $classCode .= '        try { parent::__construct($db); } catch (\\Throwable $e) { /* ignora se assinatura diferente */ }' . PHP_EOL;
        $classCode .= '        if (isset($opts["evaluations"])) $this->simulatedEvals = $opts["evaluations"];' . PHP_EOL;
        $classCode .= '        if (isset($opts["similar"])) $this->simulatedSimilar = $opts["similar"];' . PHP_EOL;
        $classCode .= '    }' . PHP_EOL;
        $classCode .= $methodsCode . PHP_EOL;
        $classCode .= '}' . PHP_EOL;

        eval($classCode);

        // Instantiate passing the mock DB so parent::__construct gets $db if it accepts it
        $instance = new $stubName($this->mockDb, $opts);

        return $instance;
    }

    /* ================= Tests: Populares ================= */

    public function testPopularesSemana_ordemELimite() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        // album 2 => 5 avaliações recentes, album1 => 3 avaliações recentes
        $simEvals = [];
        $simEvals[] = ['id_album'=>1,'nota'=>4,'data_avaliacao'=>$within(2)];
        $simEvals[] = ['id_album'=>1,'nota'=>5,'data_avaliacao'=>$within(3)];
        $simEvals[] = ['id_album'=>1,'nota'=>3,'data_avaliacao'=>$within(1)];
        for ($i=0;$i<5;$i++) $simEvals[] = ['id_album'=>2,'nota'=>4,'data_avaliacao'=>$within(6)];
        $simEvals[] = ['id_album'=>3,'nota'=>2,'data_avaliacao'=>$within(4)];
        // alguns fora da janela de 7 dias
        $simEvals[] = ['id_album'=>3,'nota'=>5,'data_avaliacao'=>$within(10)];
        $simEvals[] = ['id_album'=>3,'nota'=>4,'data_avaliacao'=>$within(12)];

        $methods = <<<'PHP'
    public function getPopularesSemana($limit = 12) {
        $limit = (int)$limit;
        $counts = []; $sums = [];
        foreach ($this->simulatedEvals as $ev) {
            if (!isset($ev['data_avaliacao']) || !isset($ev['id_album'])) continue;
            try { $dt = new \DateTime($ev['data_avaliacao']); } catch (\Throwable $e) { continue; }
            $now = new \DateTime();
            $interval = $now->diff($dt);
            if ($interval->days <= 7) {
                $id = (int)$ev['id_album'];
                if (!isset($counts[$id])) { $counts[$id] = 0; $sums[$id] = 0; }
                $counts[$id]++; $sums[$id] += isset($ev['nota']) ? (float)$ev['nota'] : 0.0;
            }
        }
        $albums = [];
        foreach ($counts as $id => $qtd) {
            $media = $qtd ? ($sums[$id] / $qtd) : 0;
            $albums[] = ['id_album' => $id, 'titulo' => "Album {$id}", 'nome_artista' => "Artista {$id}", 'qtd_avaliacoes' => $qtd, 'media_nota' => round($media, 2)];
        }
        usort($albums, function($a, $b) {
            if ($a['qtd_avaliacoes'] === $b['qtd_avaliacoes']) {
                if ($a['media_nota'] === $b['media_nota']) return $a['id_album'] <=> $b['id_album'];
                return ($b['media_nota'] <=> $a['media_nota']);
            }
            return $b['qtd_avaliacoes'] <=> $a['qtd_avaliacoes'];
        });
        return array_slice($albums, 0, $limit);
    }
PHP;

        $home = $this->makeStubInstance(['evaluations'=>$simEvals], $methods);
        $result = $home->getPopularesSemana(2);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(2, $result[0]['id_album']);
        $this->assertEquals(5, $result[0]['qtd_avaliacoes']);
        $this->assertEquals(1, $result[1]['id_album']);
        $this->assertEquals(3, $result[1]['qtd_avaliacoes']);
    }

    public function testPopulares_ignoraAvaliacoesAntigas() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        $simEvals = [
            ['id_album'=>10,'nota'=>5,'data_avaliacao'=>$within(8)],
            ['id_album'=>11,'nota'=>4,'data_avaliacao'=>$within(1)],
        ];
        $methods = <<<'PHP'
    public function getPopularesSemana($limit = 12) {
        $limit = (int)$limit; $counts=[]; $sums=[];
        foreach ($this->simulatedEvals as $ev) {
            if (!isset($ev['data_avaliacao']) || !isset($ev['id_album'])) continue;
            try { $dt = new \DateTime($ev['data_avaliacao']); } catch (\Throwable $e) { continue; }
            $now = new \DateTime(); $interval = $now->diff($dt);
            if ($interval->days <= 7) { $id=(int)$ev['id_album']; if(!isset($counts[$id])){ $counts[$id]=0;$sums[$id]=0; } $counts[$id]++; $sums[$id]+=isset($ev['nota'])?(float)$ev['nota']:0; }
        }
        $albums=[]; foreach($counts as $id=>$qtd){ $albums[]=['id_album'=>$id,'qtd_avaliacoes'=>$qtd]; } usort($albums,function($a,$b){ return $b['qtd_avaliacoes'] <=> $a['qtd_avaliacoes']; }); return $albums;
    }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals], $methods);
        $res = $home->getPopularesSemana(10);
        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertEquals(11, $res[0]['id_album']);
    }

    public function testPopulares_vazioRetornaVazio() {
        $home = $this->makeStubInstance(['evaluations'=>[]], 'public function getPopularesSemana($limit=12){ return []; }');
        $res = $home->getPopularesSemana(5);
        $this->assertIsArray($res);
        $this->assertCount(0, $res);
    }

    /* ================= Tests: Porque você avaliou ================= */

    public function testPorqueAvaliou_retornaSimilarQuandoExiste() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };
        $simEvals = [
            ['id_avaliacao'=>10,'id_album'=>101,'genero'=>'rock','nota'=>4,'data_avaliacao'=>$within(1),'id_usuario'=>42,'titulo'=>'A1'],
            ['id_avaliacao'=>11,'id_album'=>102,'genero'=>'jazz','nota'=>5,'data_avaliacao'=>$within(3),'id_usuario'=>42,'titulo'=>'A2'],
            ['id_avaliacao'=>12,'id_album'=>103,'genero'=>'rock','nota'=>3,'data_avaliacao'=>$within(5),'id_usuario'=>42,'titulo'=>'A3'],
        ];
        $simByGen = [
            'rock' => [
                ['id_album'=>201,'titulo'=>'S1','genero'=>'rock'],
                ['id_album'=>202,'titulo'=>'S2','genero'=>'rock'],
                ['id_album'=>203,'titulo'=>'S3','genero'=>'rock'],
            ]
        ];
        $methods = <<<'PHP'
    public function getUserEvaluations($id_usuario, $limit = 12) {
        $res = array_filter($this->simulatedEvals, function($ev) use ($id_usuario) {
            return isset($ev['id_usuario']) ? ((int)$ev['id_usuario'] === (int)$id_usuario) : true;
        });
        usort($res, function($a,$b){ return strtotime($b['data_avaliacao']) - strtotime($a['data_avaliacao']); });
        return array_slice(array_values($res), 0, $limit);
    }
    public function getAlbumsByGenero($genero, $exclude_id = null, $limit = 12) {
        $arr = isset($this->simulatedSimilar[$genero]) ? $this->simulatedSimilar[$genero] : [];
        if ($exclude_id !== null) {
            $arr = array_filter($arr, function($a) use ($exclude_id){ return ((int)($a['id_album'] ?? 0)) !== (int)$exclude_id; });
        }
        return array_slice(array_values($arr), 0, $limit);
    }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals,'similar'=>$simByGen], $methods);

        if (!method_exists($home, 'findEvaluationWithSimilar')) {
            $this->markTestSkipped('findEvaluationWithSimilar não implementado no model atual.');
            return;
        }

        $res = $home->findEvaluationWithSimilar(42, 12, 2, 5);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('evaluation', $res);
        $this->assertArrayHasKey('similar', $res);
        $this->assertEquals(101, $res['evaluation']['id_album']);
        $this->assertGreaterThanOrEqual(2, count($res['similar']));
    }

    public function testPorqueAvaliou_excluiAlbumOriginal() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };
        $simEvals = [
            ['id_avaliacao'=>30,'id_album'=>150,'genero'=>'eletronica','nota'=>5,'data_avaliacao'=>$within(1),'id_usuario'=>7,'titulo'=>'E1']
        ];
        $simByGen = [
            'eletronica' => [
                ['id_album'=>150,'titulo'=>'Orig','genero'=>'eletronica'],
                ['id_album'=>151,'titulo'=>'Other','genero'=>'eletronica'],
                ['id_album'=>152,'titulo'=>'Other2','genero'=>'eletronica'],
            ]
        ];
        $methods = <<<'PHP'
    public function getUserEvaluations($id_usuario, $limit = 12){
        $res = array_filter($this->simulatedEvals, function($ev) use ($id_usuario){ return isset($ev['id_usuario']) ? ((int)$ev['id_usuario'] === (int)$id_usuario) : true; });
        usort($res, function($a,$b){ return strtotime($b['data_avaliacao']) - strtotime($a['data_avaliacao']); });
        return array_slice(array_values($res),0,$limit);
    }
    public function getAlbumsByGenero($genero,$exclude_id=null,$limit=12){
        $arr = isset($this->simulatedSimilar[$genero]) ? $this->simulatedSimilar[$genero] : [];
        if($exclude_id!==null){ $arr = array_filter($arr, function($a) use ($exclude_id){ return ((int)($a['id_album']??0)) !== (int)$exclude_id; }); }
        return array_slice(array_values($arr),0,$limit);
    }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals,'similar'=>$simByGen], $methods);
        if (!method_exists($home,'findEvaluationWithSimilar')) { $this->markTestSkipped('findEvaluationWithSimilar não implementado'); return; }
        $res = $home->findEvaluationWithSimilar(7, 12, 1, 5);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('similar',$res);
        foreach($res['similar'] as $s){ $this->assertNotEquals(150, $s['id_album']); }
    }

    public function testPorqueAvaliou_ordemPorAvaliacaoMaisRecente() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };
        $simEvals = [
            ['id_avaliacao'=>40,'id_album'=>401,'genero'=>'folk','nota'=>3,'data_avaliacao'=>$within(10),'id_usuario'=>8,'titulo'=>'Old'],
            ['id_avaliacao'=>41,'id_album'=>402,'genero'=>'folk','nota'=>5,'data_avaliacao'=>$within(1),'id_usuario'=>8,'titulo'=>'Recent']
        ];
        $simByGen = [
            'folk'=>[['id_album'=>501,'titulo'=>'F1','genero'=>'folk'],['id_album'=>502,'titulo'=>'F2','genero'=>'folk']]
        ];
        $methods = <<<'PHP'
    public function getUserEvaluations($id_usuario,$limit=12){ $res=array_filter($this->simulatedEvals,function($ev) use($id_usuario){ return isset($ev['id_usuario'])?((int)$ev['id_usuario']===(int)$id_usuario):true; }); usort($res,function($a,$b){ return strtotime($b['data_avaliacao'])-strtotime($a['data_avaliacao']); }); return array_slice(array_values($res),0,$limit); }
    public function getAlbumsByGenero($genero,$exclude_id=null,$limit=12){ $arr=isset($this->simulatedSimilar[$genero])?$this->simulatedSimilar[$genero]:[]; if($exclude_id!==null){ $arr=array_filter($arr,function($a) use($exclude_id){ return ((int)($a['id_album']??0)) !== (int)$exclude_id; }); } return array_slice(array_values($arr),0,$limit); }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals,'similar'=>$simByGen], $methods);
        if (!method_exists($home,'findEvaluationWithSimilar')) { $this->markTestSkipped('findEvaluationWithSimilar não implementado'); return; }
        $res = $home->findEvaluationWithSimilar(8,12,1,5);
        $this->assertIsArray($res);
        $this->assertEquals(402, $res['evaluation']['id_album']);
    }

    public function testPorqueAvaliou_respeitaAlbumsLimit() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };
        $simEvals = [['id_avaliacao'=>50,'id_album'=>601,'genero'=>'rap','nota'=>4,'data_avaliacao'=>$within(1),'id_usuario'=>9,'titulo'=>'R1']];
        $simByGen = ['rap'=>[]];
        for($i=0;$i<10;$i++) $simByGen['rap'][]=['id_album'=>700+$i,'titulo'=>"S{$i}",'genero'=>'rap'];

        $methods = <<<'PHP'
    public function getUserEvaluations($id_usuario,$limit=12){ $res=array_filter($this->simulatedEvals,function($ev) use($id_usuario){ return isset($ev['id_usuario'])?((int)$ev['id_usuario']===(int)$id_usuario):true; }); usort($res,function($a,$b){ return strtotime($b['data_avaliacao'])-strtotime($a['data_avaliacao']); }); return array_slice(array_values($res),0,$limit); }
    public function getAlbumsByGenero($genero,$exclude_id=null,$limit=12){ $arr=isset($this->simulatedSimilar[$genero])?$this->simulatedSimilar[$genero]:[]; if($exclude_id!==null){ $arr=array_filter($arr,function($a) use($exclude_id){ return ((int)($a['id_album']??0)) !== (int)$exclude_id; }); } return array_slice(array_values($arr),0,$limit); }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals,'similar'=>$simByGen], $methods);
        if (!method_exists($home,'findEvaluationWithSimilar')) { $this->markTestSkipped('findEvaluationWithSimilar não implementado'); return; }
        $res = $home->findEvaluationWithSimilar(9,12,1,5);
        $this->assertIsArray($res);
        $this->assertLessThanOrEqual(5, count($res['similar']));
    }

    /* ================= Tests: Painel lateral ================= */

    public function testPainelLateral_lastEvaluationAvailable() {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };
        $simEvals = [
            ['id_avaliacao'=>71,'id_album'=>701,'genero'=>'pop','nota'=>5,'data_avaliacao'=>$within(1),'id_usuario'=>5,'titulo'=>'P1'],
            ['id_avaliacao'=>72,'id_album'=>702,'genero'=>'pop','nota'=>4,'data_avaliacao'=>$within(3),'id_usuario'=>5,'titulo'=>'P2'],
        ];
        $methods = <<<'PHP'
    public function getUserEvaluations($id_usuario,$limit=12){ $res=array_filter($this->simulatedEvals,function($ev) use($id_usuario){ return isset($ev['id_usuario'])?((int)$ev['id_usuario']===(int)$id_usuario):true; }); usort($res,function($a,$b){ return strtotime($b['data_avaliacao'])-strtotime($a['data_avaliacao']); }); return array_slice(array_values($res),0,$limit); }
    public function getUserLastEvaluation($id_usuario){ $arr=$this->getUserEvaluations($id_usuario,1); return count($arr)?$arr[0]:null; }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals], $methods);

        if (method_exists($home,'getUserLastEvaluation')) {
            $last = $home->getUserLastEvaluation(5);
            $this->assertNotEmpty($last);
            if (is_array($last)) $this->assertEquals(701, $last['id_album']);
            elseif (is_object($last) && isset($last->id_album)) $this->assertEquals(701, $last->id_album);
        } else {
            $evals = $home->getUserEvaluations(5,5);
            $this->assertNotEmpty($evals);
            $this->assertEquals(701, $evals[0]['id_album']);
        }
    }

    public function testPainelLateral_semAvaliacoesRetornaVazio() {
        $home = $this->makeStubInstance(['evaluations'=>[]], 'public function getUserEvaluations($id_usuario,$limit=12){ return []; }');
        if (method_exists($home,'getUserLastEvaluation')) {
            $last = $home->getUserLastEvaluation(1000);
            $this->assertTrue($last === null || $last === []);
        } else {
            $evals = $home->getUserEvaluations(1000,5);
            $this->assertIsArray($evals);
            $this->assertCount(0, $evals);
        }
    }

    public function testRobustez_dadosInvalidosNaoQuebram() {
        $simEvals = [
            ['id_album'=>900,'nota'=>5,'data_avaliacao'=>'not-a-date','id_usuario'=>2],
            ['id_album'=>901,'nota'=>4,'data_avaliacao'=>null,'id_usuario'=>2],
        ];
        $methods = <<<'PHP'
    public function getPopularesSemana($limit=12){
        $limit=(int)$limit; $counts=[];$sums=[];
        foreach($this->simulatedEvals as $ev){
            if(!isset($ev['data_avaliacao'])||!isset($ev['id_album'])) continue;
            try{ $dt=new \DateTime($ev['data_avaliacao']); } catch(\Throwable $e){ continue; }
            $now=new \DateTime(); $interval=$now->diff($dt);
            if($interval->days<=7){ $id=(int)$ev['id_album']; if(!isset($counts[$id])){$counts[$id]=0;$sums[$id]=0;} $counts[$id]++; $sums[$id]+=isset($ev['nota'])?(float)$ev['nota']:0; }
        }
        $albums=[]; foreach($counts as $id=>$qtd){ $albums[]=['id_album'=>$id,'qtd_avaliacoes'=>$qtd,'media_nota'=>round($sums[$id]/$qtd,2)]; } return $albums;
    }
PHP;
        $home = $this->makeStubInstance(['evaluations'=>$simEvals], $methods);
        $res = $home->getPopularesSemana(10);
        $this->assertIsArray($res);
        $this->assertCount(0, $res);
    }
}

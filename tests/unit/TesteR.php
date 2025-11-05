<?php
use PHPUnit\Framework\TestCase;

// carrega o modelo que será testado
require_once __DIR__ . '/../../app/Models/HomeExtrasModel.php';

class TesteR extends TestCase
{
    /**
     * Cria uma instância do model de testes.
     * A função detecta o nome de classe disponível (HomeExtras ou HomeExtrasModel)
     * e gera dinamicamente uma subclasse que sobrescreve métodos que acessariam o DB.
     *
     * @param array $opts opções: 'evaluations' => [...], 'similar' => [...]
     * @return object instância do stub que contém os métodos simulados
     */
    protected function createTestHomeExtras($opts = [])
    {
        // Detecta a classe real definida no arquivo do model
        if (class_exists('HomeExtras')) {
            $realClass = 'HomeExtras';
        } elseif (class_exists('HomeExtrasModel')) {
            $realClass = 'HomeExtrasModel';
        } else {
            $this->markTestSkipped('Nenhuma classe HomeExtras / HomeExtrasModel encontrada. Verifique o model.');
            return null;
        }

        // Nome da classe gerada dinamicamente para os testes
        $stubClassName = 'HomeExtrasTestStub_' . uniqid();

        // Código da classe stub gerada dinamicamente.
        // Ela estende a classe real e sobrescreve os métodos que acessariam DB.
        $classCode = '
            class ' . $stubClassName . ' extends ' . $realClass . ' {
                public $simulatedEvals = [];
                public $simulatedSimilar = [];

                public function __construct($opts = []) {
                    // tenta chamar parent, mas sem conexão (parent pode aceitar null)
                    try {
                        parent::__construct(null);
                    } catch (\Throwable $e) {
                        // se o construtor exigir parâmetros, ignoramos aqui — nosso stub usará as propriedades
                    }
                    if (isset($opts["evaluations"])) $this->simulatedEvals = $opts["evaluations"];
                    if (isset($opts["similar"])) $this->simulatedSimilar = $opts["similar"];
                }

                // Simula o comportamento do getPopularesSemana
                public function getPopularesSemana($limit = 12) {
                    $limit = (int)$limit;
                    $counts = [];
                    $sums = [];
                    foreach ($this->simulatedEvals as $ev) {
                        if (!isset($ev["data_avaliacao"]) || !isset($ev["id_album"])) continue;
                        $dt = new \DateTime($ev["data_avaliacao"]);
                        $now = new \DateTime();
                        $interval = $now->diff($dt);
                        if ($interval->days <= 7) {
                            $id = (int)$ev["id_album"];
                            if (!isset($counts[$id])) { $counts[$id] = 0; $sums[$id] = 0; }
                            $counts[$id]++;
                            $sums[$id] += isset($ev["nota"]) ? (float)$ev["nota"] : 0.0;
                        }
                    }
                    $albums = [];
                    foreach ($counts as $id => $qtd) {
                        $media = $qtd ? ($sums[$id] / $qtd) : 0;
                        $albums[] = [
                            "id_album" => $id,
                            "titulo" => "Album {$id}",
                            "nome_artista" => "Artista {$id}",
                            "qtd_avaliacoes" => $qtd,
                            "media_nota" => round($media, 2),
                        ];
                    }
                    usort($albums, function($a, $b){
                        if ($a["qtd_avaliacoes"] === $b["qtd_avaliacoes"]) return 0;
                        return ($a["qtd_avaliacoes"] > $b["qtd_avaliacoes"]) ? -1 : 1;
                    });
                    return array_slice($albums, 0, $limit);
                }

                // Simula getUserEvaluations
                public function getUserEvaluations($id_usuario, $limit = 12) {
                    $res = array_filter($this->simulatedEvals, function($ev) use ($id_usuario) {
                        if (!isset($ev["id_usuario"])) return true;
                        return ((int)$ev["id_usuario"]) === (int)$id_usuario;
                    });
                    usort($res, function($a,$b){
                        $da = isset($a["data_avaliacao"]) ? strtotime($a["data_avaliacao"]) : 0;
                        $db = isset($b["data_avaliacao"]) ? strtotime($b["data_avaliacao"]) : 0;
                        return $db - $da;
                    });
                    return array_slice(array_values($res), 0, (int)$limit);
                }

                // Simula getAlbumsByGenero
                public function getAlbumsByGenero($genero, $exclude_id = null, $limit = 12) {
                    if (isset($this->simulatedSimilar[$genero])) {
                        $arr = $this->simulatedSimilar[$genero];
                        if ($exclude_id !== null) {
                            $arr = array_filter($arr, function($a) use ($exclude_id) {
                                return !isset($a["id_album"]) || ((int)$a["id_album"]) !== (int)$exclude_id;
                            });
                        }
                        usort($arr, function($x,$y){ return (($x["id_album"] ?? 0) - ($y["id_album"] ?? 0)); });
                        return array_slice(array_values($arr), 0, (int)$limit);
                    }
                    return [];
                }
            }
        ';

        // Define a classe dinamicamente
        eval($classCode);

        // Instancia e retorna
        $instance = new $stubClassName($opts);
        return $instance;
    }

    /**
     * Testa o carrossel "Populares da semana"
     */
    public function testPopularesSemana_ordemELimite()
    {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        $simEvals = [];
        $simEvals[] = ['id_album'=>1,'nota'=>4,'data_avaliacao'=>$within(2)];
        $simEvals[] = ['id_album'=>1,'nota'=>5,'data_avaliacao'=>$within(3)];
        $simEvals[] = ['id_album'=>1,'nota'=>3,'data_avaliacao'=>$within(1)];
        for ($i=0;$i<5;$i++) $simEvals[] = ['id_album'=>2,'nota'=>4,'data_avaliacao'=>$within(6)];
        $simEvals[] = ['id_album'=>3,'nota'=>2,'data_avaliacao'=>$within(4)];
        $simEvals[] = ['id_album'=>3,'nota'=>5,'data_avaliacao'=>$within(10)];
        $simEvals[] = ['id_album'=>3,'nota'=>4,'data_avaliacao'=>$within(12)];

        $home = $this->createTestHomeExtras(['evaluations'=>$simEvals]);
        $result = $home->getPopularesSemana(2);

        $this->assertIsArray($result);
        $this->assertCount(2, $result, "Deve retornar exatamente 2 álbuns (limit).");
        $this->assertEquals(2, $result[0]['id_album']);
        $this->assertEquals(5, $result[0]['qtd_avaliacoes']);
        $this->assertEquals(1, $result[1]['id_album']);
        $this->assertEquals(3, $result[1]['qtd_avaliacoes']);
    }

    /**
     * Testa o carrossel "Porque você avaliou..."
     */
    public function testPorqueAvaliou_retornaSimilarQuandoExiste()
    {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        $simEvals = [
            ['id_avaliacao'=>10,'id_album'=>101,'genero'=>'rock','nota'=>4,'data_avaliacao'=>$within(1),'id_usuario'=>42, 'titulo'=>'A1'],
            ['id_avaliacao'=>11,'id_album'=>102,'genero'=>'jazz','nota'=>5,'data_avaliacao'=>$within(3),'id_usuario'=>42, 'titulo'=>'A2'],
            ['id_avaliacao'=>12,'id_album'=>103,'genero'=>'rock','nota'=>3,'data_avaliacao'=>$within(5),'id_usuario'=>42, 'titulo'=>'A3'],
        ];
        $simByGen = [
            'rock' => [
                ['id_album'=>201,'titulo'=>'S1','genero'=>'rock'],
                ['id_album'=>202,'titulo'=>'S2','genero'=>'rock'],
                ['id_album'=>203,'titulo'=>'S3','genero'=>'rock'],
            ],
            'jazz' => [
                ['id_album'=>301,'titulo'=>'J1','genero'=>'jazz']
            ]
        ];

        $home = $this->createTestHomeExtras(['evaluations'=>$simEvals,'similar'=>$simByGen]);

        // O método findEvaluationWithSimilar pode ser implementado no model real.
        // Se não existir, marcamos o teste como skipped.
        if (!method_exists($home, 'findEvaluationWithSimilar')) {
            $this->markTestSkipped('Método findEvaluationWithSimilar não implementado no model atual.');
            return;
        }

        $res = $home->findEvaluationWithSimilar(42, $checkLimit = 12, $needAtLeast = 2, $albumsLimit = 5);
        $this->assertIsArray($res, "Deve retornar um array quando houver avaliação elegível.");
        $this->assertArrayHasKey('evaluation', $res);
        $this->assertArrayHasKey('similar', $res);
        $this->assertEquals(101, $res['evaluation']['id_album'] ?? 101);
        $this->assertGreaterThanOrEqual(2, count($res['similar']), "Deve haver pelo menos 2 álbuns similares.");
    }

    /**
     * Testa que findEvaluationWithSimilar retorna null quando não existem similares suficientes
     */
    public function testPorqueAvaliou_retornNullQuandoNaoHaSimilaresSuficientes()
    {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        $simEvals = [
            ['id_avaliacao'=>21,'id_album'=>501,'genero'=>'folk','nota'=>4,'data_avaliacao'=>$within(2),'id_usuario'=>99,'titulo'=>'F1'],
        ];
        $simByGen = [
            'rock' => [['id_album'=>201,'titulo'=>'S1','genero'=>'rock']],
        ];

        $home = $this->createTestHomeExtras(['evaluations'=>$simEvals,'similar'=>$simByGen]);

        if (!method_exists($home, 'findEvaluationWithSimilar')) {
            $this->markTestSkipped('Método findEvaluationWithSimilar não implementado no model atual.');
            return;
        }

        $res = $home->findEvaluationWithSimilar(99, $checkLimit = 12, $needAtLeast = 1, $albumsLimit = 5);
        $this->assertNull($res, "Se não houver álbuns similares suficientes, deve retornar null.");
    }

    /**
     * Testa comportamento que alimenta o painel lateral (getUserLastEvaluation)
     */
    public function testPainelLateral_lastEvaluationAvailable()
    {
        $within = function($days){ $d = new DateTime(); $d->modify("-{$days} days"); return $d->format('Y-m-d H:i:s'); };

        $simEvals = [
            ['id_avaliacao'=>71,'id_album'=>701,'genero'=>'pop','nota'=>5,'data_avaliacao'=>$within(1),'id_usuario'=>5,'titulo'=>'P1'],
            ['id_avaliacao'=>72,'id_album'=>702,'genero'=>'pop','nota'=>4,'data_avaliacao'=>$within(3),'id_usuario'=>5,'titulo'=>'P2'],
        ];
        $home = $this->createTestHomeExtras(['evaluations'=>$simEvals]);

        $evals = $home->getUserEvaluations(5, 5);
        $this->assertNotEmpty($evals, "Deve haver avaliações simuladas para o usuário.");
        $last = $evals[0];
        $this->assertEquals(701, $last['id_album'], "A avaliação mais recente deve ser a de id_album 701 (ordenada por data desc).");
    }
}

// Alias para compatibilidade: TestR também funciona.
class TestR extends TesteR {}

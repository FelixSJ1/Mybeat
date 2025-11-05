<?php
namespace Mybeat\Tests\Unit; 

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject; 
use Mybeat\Controllers\SeguidoresMyBeatControllers; 
use Mybeat\Models\SeguidoresMyBeatModels;


class TesteAA extends TestCase
{
    
    protected $controller;
    protected $modelMock;

    protected function setUp(): void
    {
        
        $this->modelMock = $this->createMock(SeguidoresMyBeatModels::class);

        $this->controller = new SeguidoresMyBeatControllers($this->modelMock);
        
        unset($_GET, $_POST, $_SESSION); 
    }
    
    public function testBuscarDadosUsuarioPorIdComIdValido()
    {
        $id = 10;
        $dadosEsperados = ['nome' => 'João', 'email' => 'joao@example.com'];

        // Configura o Mock
        $this->modelMock->expects($this->once())
                        ->method('buscarDadosUsuarioPorId')
                        ->with($this->equalTo($id))
                        ->willReturn($dadosEsperados);

        $resultado = $this->controller->buscarDadosUsuarioPorId($id);

        $this->assertEquals($dadosEsperados, $resultado);
    }

    public function testBuscarDadosUsuarioPorIdComIdInvalido()
    {
        $id = 0;

        $this->modelMock->expects($this->never())
                        ->method('buscarDadosUsuarioPorId');

        $resultado = $this->controller->buscarDadosUsuarioPorId($id);

        $this->assertNull($resultado);
    }
    
    public function testBuscarComTermo()
    {
        $_GET['termo'] = 'maria';
        
        $usuariosEsperados = [['nome' => 'Maria'], ['nome' => 'Mariana']];

        $this->modelMock->expects($this->once())
                        ->method('buscarUsuarios')
                        ->with($this->equalTo('maria'))
                        ->willReturn($usuariosEsperados);

        $resultado = $this->controller->buscar();

        $this->assertEquals($usuariosEsperados, $resultado);
        
        unset($_GET['termo']);
    }

    public function testSeguirSeJaSegue()
    {
        $_SESSION['id_usuario'] = 1;
        $_POST['id_seguido'] = 5;

        $this->modelMock->expects($this->once())
                        ->method('jaSegue')
                        ->willReturn(true);

        $this->modelMock->expects($this->once())
                        ->method('deixarDeSeguir')
                        ->with(1, 5);

        $this->modelMock->expects($this->never())
                        ->method('seguirUsuario');

        $this->controller->seguir();
        
    
        unset($_SESSION['id_usuario'], $_POST['id_seguido']);
    }

    
    public function testSeguirSeNaoSegue()
    {
        
        $_SESSION['id_usuario'] = 2;
        $_POST['id_seguido'] = 7;


        $this->modelMock->expects($this->once())
                        ->method('jaSegue')
                        ->willReturn(false);

        
        $this->modelMock->expects($this->once())
                        ->method('seguirUsuario')
                        ->with(2, 7);

        
        $this->modelMock->expects($this->never())
                        ->method('deixarDeSeguir');

        $this->controller->seguir();
        
       
        unset($_SESSION['id_usuario'], $_POST['id_seguido']);
    }

}
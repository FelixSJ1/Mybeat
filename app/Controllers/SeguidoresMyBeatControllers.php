<?php
require_once __DIR__ . '/../Models/SeguidoresMyBeatModels.php';

class SeguidoresMyBeatControllers {
    public $model;

    public function __construct() {
        $this->model = new SeguidoresMyBeatModels();
    }
    
    // 🆕 NOVO MÉTODO: Liga a busca do Model à View/Página de Perfil
    public function buscarDadosUsuarioPorId(int $id): ?array 
    {
        if ($id <= 0) {
            return null;
        }
        return $this->model->buscarDadosUsuarioPorId($id); 
    }

    
    public function buscar() {
        $termo = $_GET['termo'] ?? '';
        return $this->model->buscarUsuarios($termo);
    }

    
    public function seguir() {
        session_start();
        if (!isset($_SESSION['id_usuario'])) {
            die('Usuário não autenticado.');
        }

        $idSeguidor = $_SESSION['id_usuario'];
        $idSeguido = $_POST['id_seguido'];

        if ($this->model->jaSegue($idSeguidor, $idSeguido)) {
            $this->model->deixarDeSeguir($idSeguidor, $idSeguido);
        } else {
            $this->model->seguirUsuario($idSeguidor, $idSeguido);
        }
    }

    public function listarSeguidores($idUsuario) {
        return $this->model->listarSeguidores($idUsuario);
    }

    public function listarSeguindo($idUsuario) {
        return $this->model->listarSeguindo($idUsuario);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_seguido'])) {
    $controller = new SeguidoresMyBeatControllers();
    $controller->seguir();

    
    $redirect = $_POST['redirect'] ?? '../Views/SeguidoresMyBeatViews.php';
    header("Location: " . $redirect);
    exit;
}
?>
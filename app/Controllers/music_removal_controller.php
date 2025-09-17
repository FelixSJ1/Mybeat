<?php
require_once __DIR__ . '/../Models/music_removal_model.php';

class Music_Removal_Controller {
    private $model;

    public function __construct() {
        $this->model = new Music_Removal_Model();
    }

    public function index() {
        $songs = $this->model->all();
        $message = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '';
        include __DIR__ . '/../Views/partials/musicremoval.php';
    }

    public function confirm($id) {
        $song = $this->model->find((int)$id);
        if (!$song) {
            header('Location: music_removal.php');
            exit;
        }
        $songs = $this->model->all();
        $message = '';
        include __DIR__ . '/../Views/partials/musicremoval.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: music_removal.php');
            exit;
        }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        // Simulação: não altera dados. Para ativar, implemente no Model.
        $this->model->delete($id);
        header('Location: music_removal.php');
        exit;
    }
}

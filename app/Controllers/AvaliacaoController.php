<?php

require_once __DIR__ . '/../Models/AvaliacaoModel.php';

class AvaliacaoController {
    private $model;

    public function __construct($conn) {
        $this->model = new AvaliacaoModel($conn);
    }

    /**
     * Retorna o histórico de avaliações de um usuário
     * @param int $id_usuario - ID do usuário
     * @return array - Array com as avaliações do usuário
     */
    public function getHistoricoUsuario($id_usuario) {
        try {
            return $this->model->getHistoricoByUsuario($id_usuario);
        } catch (Exception $e) {
            error_log("Erro ao buscar histórico de avaliações: " . $e->getMessage());
            return [];
        }
    }
}
?>
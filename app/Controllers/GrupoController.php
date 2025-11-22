<?php
class GrupoController {
    private $db;
    private $grupoModel;
    private $testing = false; // 🔹 ligar e desligar o modo de teste

    public function __construct($db, $testing = false) {
        $this->db = $db;
        $this->grupoModel = new Grupo($db);
        $this->testing = $testing;
    }

    // 🔹 Método auxiliar para simular redirecionamento nos testes
    private function redirect($url) {
        if ($this->testing) {
            throw new Exception("Redirect to $url");
        } else {
            header("Location: $url");
            exit();
        }
    }

    public function criar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../../Views/grupos/criar_grupo.php');
        }

        $nome_grupo = trim($_POST['nome_grupo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $privado = isset($_POST['privado']) ? 1 : 0;
        $id_criador = $_SESSION['id_usuario'];

        // 🔹 Validações
        if (empty($nome_grupo)) {
            $_SESSION['mensagem_erro'] = "O nome do grupo é obrigatório.";
            $this->redirect('../../Views/grupos/criar_grupo.php');
        }

        if (strlen($nome_grupo) > 100) {
            $_SESSION['mensagem_erro'] = "O nome do grupo deve ter no máximo 100 caracteres.";
            $this->redirect('../../Views/grupos/criar_grupo.php');
        }

        // 🔹 Upload de foto (opcional)
        $foto_grupo_url = null;
        if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['foto_grupo'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($extensao, $extensoes_permitidas)) {
                $nome_arquivo = uniqid('grupo_') . '.' . $extensao;
                $pastaDestino = __DIR__ . '/../../Views/grupos/public/images/grupos/';

                if (!file_exists($pastaDestino)) {
                    mkdir($pastaDestino, 0777, true);
                }

                $caminho_destino = $pastaDestino . $nome_arquivo;

                if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
                    $foto_grupo_url = 'Mybeat/app/Views/grupos/public/images/grupos/' . $nome_arquivo;
                }
            }
        }

        // 🔹 Criação do grupo
        $id_grupo = $this->grupoModel->criar($nome_grupo, $descricao, $id_criador, $privado, $foto_grupo_url);

        if ($id_grupo) {
            $_SESSION['mensagem_sucesso'] = "Grupo criado com sucesso!";
            $this->redirect('../../Views/grupos/grupo_chat.php?id=' . $id_grupo);
        } else {
            $_SESSION['mensagem_erro'] = "Erro ao criar grupo.";
            $this->redirect('../../Views/grupos/criar_grupo.php');
        }
    }
}

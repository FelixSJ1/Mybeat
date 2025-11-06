<?php
// app/Controllers/FaceAdminController.php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Usa o mesmo conector do projeto
$conector_path = __DIR__ . '/../config/conector.php';
if (!file_exists($conector_path)) {
    echo json_encode(['success' => false, 'message' => 'Erro de configuração do servidor']);
    exit;
}
require_once $conector_path;

if (!isset($conn) || !($conn instanceof mysqli)) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
    exit;
}

// Função para calcular distância euclidiana entre dois descritores
function euclideanDistance($desc1, $desc2) {
    if (count($desc1) !== count($desc2)) {
        return PHP_FLOAT_MAX;
    }
    
    $sum = 0;
    for ($i = 0; $i < count($desc1); $i++) {
        $diff = $desc1[$i] - $desc2[$i];
        $sum += $diff * $diff;
    }
    return sqrt($sum);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    // ============================================
    // CADASTRAR RECONHECIMENTO FACIAL
    // ============================================
    if ($action === 'register_face') {
        // Verifica se admin está logado
        if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'] ?? null;
        $descriptor = $input['descriptor'] ?? null;

        if (!$admin_id || !$descriptor || !is_array($descriptor)) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        // Valida o descriptor (deve ter 128 dimensões para face-api.js)
        if (count($descriptor) !== 128) {
            echo json_encode(['success' => false, 'message' => 'Descriptor facial inválido']);
            exit;
        }

        // Converte array para JSON para armazenar no banco
        $descriptor_json = json_encode($descriptor);

        // Atualiza no banco de dados
        $stmt = $conn->prepare("UPDATE Administradores 
                                SET face_descriptor = ?, face_registered = TRUE 
                                WHERE id_admin = ?");
        $stmt->bind_param("si", $descriptor_json, $admin_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Reconhecimento facial cadastrado com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados']);
        }

        $stmt->close();
    }

    // ============================================
    // AUTENTICAR COM RECONHECIMENTO FACIAL
    // ============================================
    elseif ($action === 'authenticate_face') {
        $descriptor = $input['descriptor'] ?? null;

        if (!$descriptor || !is_array($descriptor) || count($descriptor) !== 128) {
            echo json_encode(['success' => false, 'message' => 'Descriptor facial inválido']);
            exit;
        }

        // Busca todos os administradores com face cadastrada
        $result = $conn->query("SELECT id_admin, nome_admin, email, face_descriptor 
                                FROM Administradores 
                                WHERE face_registered = TRUE 
                                AND face_descriptor IS NOT NULL 
                                AND administrador = TRUE");

        if (!$result || $result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Nenhum administrador com reconhecimento facial cadastrado']);
            exit;
        }

        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;
        $threshold = 0.6; // Limiar de similaridade (quanto menor, mais restritivo)

        while ($admin = $result->fetch_assoc()) {
            $storedDescriptor = json_decode($admin['face_descriptor'], true);
            
            if (!$storedDescriptor || !is_array($storedDescriptor)) {
                continue;
            }

            $distance = euclideanDistance($descriptor, $storedDescriptor);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $admin;
            }
        }

        // Verifica se encontrou match dentro do threshold
        if ($bestMatch && $bestDistance < $threshold) {
            // Autentica o administrador (mesmas variáveis de sessão do LoginControllersADM)
            $_SESSION['admin_logged'] = true;
            $_SESSION['id_usuario'] = $bestMatch['id_admin'];
            $_SESSION['admin_id'] = $bestMatch['id_admin'];
            $_SESSION['admin_nome'] = $bestMatch['nome_admin'];
            $_SESSION['admin_email'] = $bestMatch['email'];
            unset($_SESSION['error']);

            echo json_encode([
                'success' => true, 
                'message' => 'Autenticação bem-sucedida',
                'admin' => $bestMatch['nome_admin'],
                'distance' => round($bestDistance, 4)
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Rosto não reconhecido. Tente novamente ou use login tradicional.',
                'distance' => $bestDistance < PHP_FLOAT_MAX ? round($bestDistance, 4) : null
            ]);
        }

        $result->free();
    }

    // ============================================
    // VERIFICAR STATUS DO RECONHECIMENTO FACIAL
    // ============================================
    elseif ($action === 'check_face_status') {
        if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'] ?? null;
        
        $stmt = $conn->prepare("SELECT face_registered FROM Administradores WHERE id_admin = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true, 
                'face_registered' => (bool)$row['face_registered']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Admin não encontrado']);
        }
        
        $stmt->close();
    }

    // ============================================
    // REMOVER RECONHECIMENTO FACIAL
    // ============================================
    elseif ($action === 'remove_face') {
        if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            exit;
        }

        $admin_id = $_SESSION['admin_id'] ?? null;

        $stmt = $conn->prepare("UPDATE Administradores 
                                SET face_descriptor = NULL, face_registered = FALSE 
                                WHERE id_admin = ?");
        $stmt->bind_param("i", $admin_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Reconhecimento facial removido com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao remover reconhecimento facial']);
        }

        $stmt->close();
    }

    else {
        echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }

} catch (Exception $e) {
    error_log("FaceAdminController Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}

if (isset($conn)) {
    $conn->close();
}
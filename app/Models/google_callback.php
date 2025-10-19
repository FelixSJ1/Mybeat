<?php
session_start();
require_once __DIR__ . '/../config/conector.php';

// Configurações do Google OAuth
define('GOOGLE_CLIENT_ID', 'rcontent.com');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', '');

if (!isset($_GET['code'])) {
    $_SESSION['mensagem_erro'] = 'Erro na autenticação com o Google.';
    header('Location: ../Views/cadastro.php');
    exit();
}

// Trocar o código de autorização por um token de acesso
$token_url = 'https://oauth2.googleapis.com/token';
$token_data = [
    'code' => $_GET['code'],
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
$response = curl_exec($ch);
curl_close($ch);

$token_info = json_decode($response, true);

if (!isset($token_info['access_token'])) {
    $_SESSION['mensagem_erro'] = 'Erro ao obter token de acesso do Google.';
    header('Location: ../Views/cadastro.php');
    exit();
}

// Obter informações do usuário
$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($user_info_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token_info['access_token']]);
$user_response = curl_exec($ch);
curl_close($ch);

$user_info = json_decode($user_response, true);

if (!isset($user_info['email'])) {
    $_SESSION['mensagem_erro'] = 'Erro ao obter informações do usuário.';
    header('Location: ../Views/cadastro.php');
    exit();
}

// Conectar ao banco de dados e verificar se o usuário já existe
try {
    // Verificar se o email já está cadastrado
    $stmt = $conn->prepare("SELECT id_usuario FROM Usuarios WHERE email = ?");
    $stmt->bind_param("s", $user_info['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Usuário já existe, fazer login e ir para home_usuario.php
        $usuario = $result->fetch_assoc();
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['email'] = $user_info['email'];
        $stmt->close();
        header('Location: ../Views/home_usuario.php');
        exit();
    } else {
        $stmt->close();
        
        // Criar novo usuário e redirecionar para login
        $nome_usuario = explode('@', $user_info['email'])[0]; // Usar parte do email como username
        $nome_exibicao = $user_info['name'] ?? $nome_usuario;
        $foto_perfil = $user_info['picture'] ?? null;
        
        // Gerar uma senha aleatória (usuário não precisará dela, pois usará Google)
        $senha_aleatoria = bin2hex(random_bytes(16));
        $hash_senha = password_hash($senha_aleatoria, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO Usuarios (nome_usuario, email, hash_senha, nome_exibicao, foto_perfil_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome_usuario, $user_info['email'], $hash_senha, $nome_exibicao, $foto_perfil);
        $stmt->execute();
        $stmt->close();
        
        // Redirecionar para a página de login após cadastro
        $_SESSION['mensagem_sucesso'] = 'Conta criada com sucesso! Faça login para continuar.';
        header('Location: ../views/FaçaLoginMyBeat.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = 'Erro ao processar cadastro: ' . $e->getMessage();
    header('Location: ../Views/cadastro.php');
    exit();
}
?>
<?php
session_start();
require_once __DIR__ . '/../config/conector.php';

// Configurações do Google OAuth
define('GOOGLE_CLIENT_ID', '');
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
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=MyBeatDB", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o email já está cadastrado
    $stmt = $pdo->prepare("SELECT id_usuario FROM Usuarios WHERE email = ?");
    $stmt->execute([$user_info['email']]);
    
    if ($stmt->rowCount() > 0) {
        // Usuário já existe, fazer login e ir para home_usuario.php
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['email'] = $user_info['email'];
        header('Location: ../Views/home_usuario.php');
        exit();
    } else {
        // Criar novo usuário e redirecionar para login
        $nome_usuario = explode('@', $user_info['email'])[0]; // Usar parte do email como username
        $nome_exibicao = $user_info['name'] ?? $nome_usuario;
        $foto_perfil = $user_info['picture'] ?? null;
        
        // Gerar uma senha aleatória (usuário não precisará dela, pois usará Google)
        $senha_aleatoria = bin2hex(random_bytes(16));
        $hash_senha = password_hash($senha_aleatoria, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO Usuarios (nome_usuario, email, hash_senha, nome_exibicao, foto_perfil_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome_usuario, $user_info['email'], $hash_senha, $nome_exibicao, $foto_perfil]);
        
        // Redirecionar para a página de login após cadastro
        $_SESSION['mensagem_sucesso'] = 'Conta criada com sucesso! Faça login para continuar.';
        header('Location: ../Views/FaçaLoginMyBeat.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = 'Erro ao processar cadastro: ' . $e->getMessage();
    header('Location: ../Views/cadastro.php');
    exit();
}
?>
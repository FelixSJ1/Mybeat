<?php

declare(strict_types=1);

// Configurações do banco de dados
$db_host = 'localhost'; 
$db_name = 'MyBeatDB'; 
$db_user = 'root'; 
$db_pass = ''; // Certifique-se de que a senha está correta

try {
    // Criar a conexão com o banco de dados
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8", $db_user, $db_pass);
    
    // Definir atributos para o PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // A conexão foi bem-sucedida, você pode usar o $pdo para interagir com o banco de dados
    echo "Conexão bem-sucedida ao banco de dados!";
    
} catch (PDOException $e) {
    // Exibir mensagem de erro detalhada
    die("Erro na conexão com o banco de dados: " . $e->getCode() . " - " . $e->getMessage());
}
?>

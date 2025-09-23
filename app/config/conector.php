<?php

// Credenciais do banco de dados
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "MyBeatDB";
$db_port = "";


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name, $db_port);

    
    mysqli_set_charset($conn, "utf8");

     //echo "Conexão bem-sucedida!"; // Remova ou comente após testar

} catch (mysqli_sql_exception $e) {
    

    die("Conexão com banco de dados falhou: " . $e->getMessage());
}

?>
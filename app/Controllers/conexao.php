<?php
// app/conexao.php

$host = "localhost";
$user = "root";   // ajuste se seu usuário do MySQL for outro
$pass = "";       // senha do MySQL
$db   = "MyBeatDB";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}
?>

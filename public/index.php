<?php

define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Define a constante para o caminho da pasta 'Views'.
// Isso facilita o uso em todo o código.
define('VIEWS_PATH', __DIR__ . '/../app/Views/');

// Pega o valor do parâmetro 'page' na URL.
// Se o parâmetro não existir, ele assume 'home' como valor padrão.
$page = $_GET['page'] ?? 'home';

// Cria um mapa para associar o parâmetro 'page' a um arquivo de view.
// 'home' é a sua página de menu.
$pages = [
    'home'      => 'juncao_kauelly.php',
    'adicionar' => 'AdicaoDeDadosF.php',
    'listar'    => 'Listar_giovana.php',
    'editar'    => 'EditMyBeatViews.php',
    'excluir'   => 'musicremoval.php',
];

// Verifica se a página solicitada existe no nosso mapa.
if (array_key_exists($page, $pages)) {
    // Se existir, inclua o arquivo correspondente.
    require_once VIEWS_PATH . $pages[$page];
} else {
    // Se a página não for encontrada, mostre uma mensagem de erro.
    echo "<h1>404 - Página não encontrada</h1>";
}
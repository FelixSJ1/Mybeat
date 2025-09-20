<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Admin - MyBeat</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../../public/css/juncao_kauelly.css">

    <style>
        :root {
            --primary-color: #5B3A92;
            --secondary-color: #EB8046;
            --text-light: #FFFFFF;
            --background-dark: #000000;
            --form-background: #1a1a1a;
            --input-background: #333333;
            --border-color: #444444;
        }

        /* Estilos globais */
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: var(--background-dark);
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Títulos */
        .page-title, .form-title, .menu-title {
            font-family: 'Lora', serif;
            color: var(--secondary-color);
            text-align: center;
        }

    </style>
</head>
<body>

    <header class="admin-header">
        <div class="logo">logo</div>
        <div class="user-profile"></div>
    </header>
    
    <div class="main-container">
        <aside class="admin-menu">
            <h2 class="menu-title">Gerenciar Dados</h2>
            <nav class="menu-navigation">
                <ul>
                    <li><a href="AdicaoDeDadosF.php">Adicionar Dados</a></li>
                    <li><a href="Listar_giovana.php">Listar Dados</a></li>
                    <li><a href="EditMyBeat.php">Editar Dados</a></li>
                    <li><a href="musicremoval.php">Excluir Dados</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content-area"></main>
    </div>

</body>
</html>
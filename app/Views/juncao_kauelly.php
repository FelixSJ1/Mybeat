<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Admin - MyBeat</title>
    <link rel="stylesheet" href="../../public/css/juncao_kauelly.css">
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
                    <li><a href="#add-data-popup">Adicionar Dados</a></li>
                    <li><a href="#list-data-popup">Listar Dados</a></li>
                    <li><a href="#edit-data-popup">Editar Dados</a></li>
                    <li><a href="#delete-data-popup">Excluir Dados</a></li>
                </ul>
            </nav>
        </aside>

        <div class="popup-overlay-css" id="add-data-popup">
            <div class="popup-content-css">
                <a href="#" class="close-btn-css">&times;</a>
                <h3>Adicionar Dados</h3>
                <p>O formulário de adição de dados virá aqui.</p>
            </div>
        </div>

        <div class="popup-overlay-css" id="list-data-popup">
            <div class="popup-content-css">
                <a href="#" class="close-btn-css">&times;</a>
                <h3>Listar Dados</h3>
                <p>A tabela de dados virá aqui.</p>
            </div>
        </div>

        <div class="popup-overlay-css" id="edit-data-popup">
            <div class="popup-content-css">
                <a href="#" class="close-btn-css">&times;</a>
                <h3>Editar Dados</h3>
                <p>O formulário de edição de dados virá aqui.</p>
            </div>
        </div>

        <div class="popup-overlay-css" id="delete-data-popup">
            <div class="popup-content-css">
                <a href="#" class="close-btn-css">&times;</a>
                <h3>Excluir Dados</h3>
                <p>A confirmação de exclusão virá aqui.</p>
            </div>
        </div>
        
        <main class="content-area"></main>
    </div>

</body>
</html>
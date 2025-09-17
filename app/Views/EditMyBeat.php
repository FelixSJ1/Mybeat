
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edição de Dados</title>

    <style>
        h1 {
            font-family: "Lora", serif;
            color: #eb8046;
        }
    </style>

    <style>
        textarea {
            font-family: "Open Sans", serif;
            color: #5b3a92;
            border-radius: 5%
        }
    </style>

    <style>
        label {
           color: #eb8046;
        }
        .negrito {
        font-weight: bold;
        }
    </style>
    <style>
    
        .user-icon {
            position: fixed; 
            top: 20px; 
            right: 20px; 
            width: 50px; 
            height: 50px; 
            background-color: #eb8046; 
            border-radius: 50%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            cursor: pointer; 
            box-shadow: 0 4px 8px rgba(125, 60, 158, 1); 
        }

        .user-icon::before {
            content: 'U'; 
            font-size: 24px;
            color: white; 
        }
    </style>

    <style>
        input{
            background-color: #5b3a92;
            color:#eb8046; 
            font-family: "Lora", serif;
            border-radius: 10%
        }
    </style>
</head>
<body style="background-color: black; color: white;">
    <h1>Editar Comentário</h1>
    <form action="/página-teste.php" method="post">
        <input type="submit" value="Mostrar seus comentários">
    </form>
    <form action="/sua-pagina-de-processamento.php" method="post">
        <label for="msg">Comentário:</label><br>
        <textarea id="msg" name="msg" rows="4" cols="50" placeholder="Escreva seu comentário aqui..."></textarea>
        <br><br>
        <input type="submit" value="Enviar">
    </form>
    <div class="user-icon"></div> </body>
</body>
</html>
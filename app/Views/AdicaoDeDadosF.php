<?php
define('BASE_URL', 'http://localhost/Mybeat');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Conteúdo - myBeat</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

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

        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-dark);
            color: var(--text-light);
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 0;
        }

        header {
            background-color: var(--primary-color);
            padding: 15px 0;
            border-bottom: 2px solid var(--secondary-color);
            text-align: center;
        }

        header .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .logo { height: 50px; width: auto; }
        .page-title {
            font-family: 'Lora', serif;
            font-size: 2.5em;
            color: var(--text-light);
            margin: 0;
        }

        .form-section {
            background-color: var(--form-background);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            margin-top: 40px;
        }
        
        .form-title {
            font-family: 'Lora', serif;
            color: var(--secondary-color);
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.8em;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="url"],
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--input-background);
            color: var(--text-light);
            font-size: 1em;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s ease;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }

        input:focus, select:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        .btn-submit {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: var(--secondary-color);
            color: var(--text-light);
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-submit:hover {
            background-color: #d16e3c;
            transform: translateY(-2px);
        }

        footer {
            background-color: var(--primary-color);
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 2px solid var(--secondary-color);
        }

        footer p { margin: 0; font-size: 0.9em; }
    </style>
</head>
<body>
    <header>
        <div class="container">
           
            <h1 class="page-title">Adicionar Conteúdo</h1>
        </div>
    </header>

    <main class="container">

        <section class="form-section">
            <h2 class="form-title">Adicionar Novo Álbum</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="titulo_album">Título do Álbum:</label>
                    <input type="text" id="titulo_album" name="titulo_album" required>
                </div>
                <div class="form-group">
                    <label for="id_artista_album">Artista:</label>
                    <select id="id_artista_album" name="id_artista_album" required>
                        <option value="">Selecione um Artista</option>
                        <option value="1">Artista Exemplo 1</option>
                        <option value="2">Artista Exemplo 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="data_lancamento">Data de Lançamento:</label>
                    <input type="date" id="data_lancamento" name="data_lancamento">
                </div>
                <div class="form-group">
                    <label for="capa_album_url">URL da Capa:</label>
                    <input type="url" id="capa_album_url" name="capa_album_url" placeholder="https://exemplo.com/capa.jpg">
                </div>
                <div class="form-group">
                    <label for="genero">Gênero:</label>
                    <input type="text" id="genero" name="genero">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo de Lançamento:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Álbum" selected>Álbum</option>
                        <option value="EP">EP</option>
                        <option value="Single">Single</option>
                        <option value="Coletânea">Coletânea</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Adicionar Álbum</button>
            </form>
        </section>


        <section class="form-section">
            <h2 class="form-title">Adicionar Nova Música</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="titulo_musica">Título da Música:</label>
                    <input type="text" id="titulo_musica" name="titulo_musica" required>
                </div>
                <div class="form-group">
                    <label for="id_album_musica">Álbum:</label>
                    <select id="id_album_musica" name="id_album_musica" required>
                        <option value="">Selecione o Álbum</option>
                        <option value="single">É single!</option>
                        <option value="1">Álbum Exemplo 1</option>
                        <option value="2">Álbum Exemplo 2</option>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="id_artista_musica">Artista da Música:</label>
                    <select id="id_artista_musica" name="id_artista_musica" required>
                        <option value="">Selecione o Artista</option>
                        <option value="1">Artista Exemplo 1</option>
                        <option value="2">Artista Exemplo 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duracao_segundos">Duração (em segundos):</label>
                    <input type="number" id="duracao_segundos" name="duracao_segundos" min="1" placeholder="Ex: 210">
                </div>
                <div class="form-group">
                    <label for="numero_faixa">Número da Faixa:</label>
                    <input type="number" id="numero_faixa" name="numero_faixa" min="1">
                </div>
                <button type="submit" class="btn-submit">Adicionar Música</button>
            </form>
        </section>

    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> myBeat. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>
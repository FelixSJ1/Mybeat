<?php
define('BASE_URL', 'http://localhost/Mybeat');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../config/conector.php");

// Inicializa variáveis de mensagens
$msg_artista = '';
$msg_album = '';
$msg_musica = '';

// Inserção de artista
if(isset($_POST['nome_artista']) && !empty($_POST['nome_artista'])){
    $nome_artista = $_POST['nome_artista'];
    $biografia = $_POST['biografia_artista'] ?? '';
    $foto_url = $_POST['foto_artista_url'] ?? '';
    $ano_inicio = $_POST['ano_inicio_atividade'] ?? null;
    $pais_origem = $_POST['pais_origem'] ?? '';

    $sql = "INSERT INTO Artistas (nome, biografia, foto_artista_url, ano_inicio_atividade, pais_origem)
            VALUES ('$nome_artista', '$biografia', '$foto_url', ".($ano_inicio ? $ano_inicio : "NULL").", '$pais_origem')";

    if(mysqli_query($conn, $sql)){
        $msg_artista = "Artista adicionado com sucesso!";
    } else {
        $msg_artista = "Erro ao adicionar artista.";
    }
}

// Inserção de álbum
if(isset($_POST['titulo_album']) && !empty($_POST['titulo_album'])){
    $titulo_do_album = $_POST['titulo_album'];
    $nome_artista_album = $_POST['busca_artista'];

    $result = mysqli_query($conn, "SELECT id_artista FROM Artistas WHERE nome = '$nome_artista_album' LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    $id_artista = $row['id_artista'] ?? null;
    $data_lancamento = $_POST['data_lancamento'];
    $url_da_capa = $_POST['capa_album_url'];
    $genero_album = $_POST['genero'];
    $tipo_album = $_POST['tipo'];

    if($id_artista) {
        $sql = "INSERT INTO Albuns (titulo, id_artista, data_lancamento,capa_album_url,genero,tipo) 
                VALUES ('$titulo_do_album', '$id_artista', '$data_lancamento','$url_da_capa','$genero_album','$tipo_album')";
        if(mysqli_query($conn, $sql)){
            $msg_album = "Álbum adicionado com sucesso!";
        } else {
            $msg_album = "Erro ao adicionar álbum.";
        }
    } else {
        $msg_album = "Artista não encontrado!";
    }
}

// Inserção de música
if(isset($_POST['titulo_musica']) && !empty($_POST['titulo_musica'])){
    $titulo_da_musica = $_POST['titulo_musica'];
    $nome_album = $_POST['album_musica']; // Nome digitado no formulário
    $nome_artista_da_musica = $_POST['busca_artista_musica'];
    $duracao = $_POST['duracao_segundos'];
    $numero_da_faixa = $_POST['numero_faixa'];

    // Busca pelo artista
    $result_artista = mysqli_query($conn, "SELECT id_artista FROM Artistas WHERE nome = '$nome_artista_da_musica' LIMIT 1");
    $row_artista = mysqli_fetch_assoc($result_artista);
    $id_artista_da_musica = $row_artista['id_artista'] ?? null;

    // Busca pelo álbum
    $id_album = null;
    if(strtolower(trim($nome_album)) !== 'single') {
        $result_album = mysqli_query($conn, "SELECT id_album FROM Albuns WHERE titulo = '$nome_album' LIMIT 1");
        $row_album = mysqli_fetch_assoc($result_album);
        $id_album = $row_album['id_album'] ?? null;
    }

    // Validação antes de inserir
    if(!$id_artista_da_musica){
        $msg_musica = "Artista não encontrado!";
    } elseif(strtolower(trim($nome_album)) !== 'single' && !$id_album){
        $msg_musica = "Álbum não encontrado!";
    } else {
        $id_album_inserir = strtolower(trim($nome_album)) === 'single' ? null : $id_album;

        $sql = "INSERT INTO Musicas (titulo, id_album, id_artista, duracao_segundos, numero_faixa) 
                VALUES ('$titulo_da_musica', ".($id_album_inserir ? $id_album_inserir : "NULL").", '$id_artista_da_musica', '$duracao', '$numero_da_faixa')";
        
        if(mysqli_query($conn,$sql)){
            $msg_musica = "Música adicionada com sucesso!";
        } else {
            $msg_musica = "Erro ao adicionar música.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Conteúdo - myBeat</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/styleF.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 class="page-title">Adicionar Conteúdo</h1>
        </div>
    </header>

    <main class="container">

        <!-- Formulário de Artistas -->
        <section class="form-section">
            <h2 class="form-title">Adicionar Novo Artista</h2>
            <?php if($msg_artista) echo "<p class='alert'>$msg_artista</p>"; ?>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="nome_artista">Nome do Artista:</label>
                    <input type="text" id="nome_artista" name="nome_artista" required>
                </div>
                <div class="form-group">
                    <label for="biografia_artista">Biografia:</label>
                    <textarea id="biografia_artista" name="biografia_artista" rows="5" style="width:100%; padding:12px; border:1px solid var(--border-color); border-radius:5px; background-color:var(--input-background); color:var(--text-light); font-family:'Open Sans', sans-serif;"></textarea>
                </div>
                <div class="form-group">
                    <label for="foto_artista_url">URL da Foto do Artista:</label>
                    <input type="url" id="foto_artista_url" name="foto_artista_url" placeholder="https://exemplo.com/foto.jpg">
                </div>
                <div class="form-group">
                    <label for="ano_inicio_atividade">Ano de Início da Atividade:</label>
                    <input type="number" id="ano_inicio_atividade" name="ano_inicio_atividade" min="1900" max="2099" step="1" placeholder="Ex: 2010">
                </div>
                <div class="form-group">
                    <label for="pais_origem">País de Origem:</label>
                    <input type="text" id="pais_origem" name="pais_origem" placeholder="Ex: Brasil">
                </div>
                <button type="submit" class="btn-submit">Adicionar Artista</button>
            </form>
        </section>

        <!-- Formulário de Álbuns -->
        <section class="form-section">
            <h2 class="form-title">Adicionar Novo Álbum</h2>
            <?php if($msg_album) echo "<p class='alert'>$msg_album</p>"; ?>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="titulo_album">Título do Álbum:</label>
                    <input type="text" id="titulo_album" name="titulo_album" required>
                </div>
                <div class="form-group">
                    <label for="busca_artista">Artista(s) dos Albuns:</label>
                    <input type="text" id="busca_artista" name="busca_artista" placeholder="Digite o nome do artista" required>
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

        <!-- Formulário de Músicas -->
        <section class="form-section">
            <h2 class="form-title">Adicionar Nova Música</h2>
            <?php if($msg_musica) echo "<p class='alert'>$msg_musica</p>"; ?>
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="titulo_musica">Título da Música:</label>
                    <input type="text" id="titulo_musica" name="titulo_musica" required>
                </div>
                <div class="form-group">
                    <label for="id_album_musica">Álbum:</label>
                    <input type="text" id="id_album_musica" name="album_musica" required>
                </div>
                <div class="form-group">
                    <label for="busca_artista_musica">Artista da Música:</label>
                    <input type="text" id="busca_artista_musica" name="busca_artista_musica" placeholder="Digite o nome do artista" required>
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

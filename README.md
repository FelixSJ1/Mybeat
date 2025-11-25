# Mybeat
Uma aplicação de gerenciamento de albums e músicas. Isso é, permite a manipulação CRUD de dados de música e o usuário pode registrar suas músicas, avaliá-las com notas.


             	Rede social de avaliação de músicas




Acesso ao projeto:

| Links Úteis                                                                                                                |
| :------------------------------------------------------------------------------------------------------------------------- |
| [Link do repositório](https://github.com/FelixSJ1/Mybeat)                                                                  |
| [link do vídeo do projeto](https://drive.google.com/file/d/1mG9KVm068_xUZe0n2N7-ApTqKkTdVzDn/view?usp=drive_link)          |                                                         |
|[link da landing page](https://felixsj1.github.io/Mybeat/)                                                                  |
|[link da última release](https://github.com/FelixSJ1/Mybeat/releases/tag/1.4)                                               |
---



Tutorial de instalação:

🎵 MyBeat — Tutorial de Instalação e Execução

Este projeto utiliza PHP, MySQL e XAMPP.
Siga os passos abaixo para configurar o ambiente e rodar o sistema localmente.

git clone https://github.com/FelixSJ1/Mybeat.git
cd Mybeat


🛠 2. Instalar Dependências

Antes de continuar, instale:

MySQL (recomendado: versão 8.x)

XAMPP (Apache + PHP)

🗄️ 3. Importar o Banco de Dados

Abra o phpMyAdmin ou qualquer cliente MySQL.

Crie um banco de dados:

CREATE DATABASE MyBeatDB;


Importe o arquivo SQL localizado em:

database/MyBeatDB.sql

🔧 4. Configurar o Arquivo de Conexão

Edite o arquivo:

conector.php


Ajuste as configurações conforme o seu ambiente (principalmente a porta do MySQL):

Observação: Se seu MySQL usa outra porta, altere o valor de $port.

▶️ 5. Executar o Projeto

Inicie Apache e MySQL pelo XAMPP.

Acesse o projeto no navegador:

http://localhost/seu-projeto


Se divirta!

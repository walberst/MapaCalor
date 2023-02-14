# Heatmap Visualization
Este é um projeto para visualização de dados em forma de heatmap. 
O projeto é composto por três arquivos principais, o arquivo `captureDados.js` e as classes `DataReceiver.php` e `Database.php`.

## Arquivo captureDados.js
Este é o arquivo principal do projeto, responsável por capturar as informações da página e enviá-las para o servidor. 
Ele usa a biblioteca html2canvas para capturar a tela e, em seguida, envia os dados para o arquivo `DataReceiver.php` através de uma chamada AJAX.

## Classe DataReceiver.php
Esta classe é responsável por receber os dados enviados pelo arquivo `captureDados.js` e armazená-los no banco de dados. 
Ele usa a classe `Database` para se conectar ao banco de dados e executar as consultas necessárias.

## Classe Database.php
Esta classe é responsável por gerenciar a conexão com o banco de dados. 
Ela fornece uma interface simples para executar consultas e recuperar resultados do banco de dados.

## Instalação
*    Clone ou baixe o repositório para o seu ambiente de desenvolvimento.
*    Configure o arquivo Database.php com as informações de acesso ao seu banco de dados.
*    Adicione o código javascript captureDados.js à sua página.
*    Adicione o código HTML da página de visualização de heatmap.
*    Execute a página e veja a visualização de heatmap funcionando.

## Observações
*    Certifique-se de que o servidor esteja configurado para receber chamadas AJAX e que o banco de dados esteja funcionando corretamente antes de executar o projeto.
*    No projeto aqui no github também está o modelo das tabelas utilizadas.

## Considerações Finais
Este código foi desenvolvido com o objetivo de capturar informações da página web para fins de análises de interesses. 
Caso precise de ajustes ou personalizações, fique à vontade para modificá-lo de acordo com suas necessidades.

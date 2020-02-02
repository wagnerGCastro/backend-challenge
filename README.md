# API Produto

Esta API tem a finalidade fazer cadastros de produto com operações de criar, deletar, atualizar e exibir, com usuários cadastrados e autenticado com JWT (Json Web Token) para que possam acessar rotas autorizadas, utlizando o framework Laravel como aplicação servidor.

## Instalação

Via Composer

``` bash
$ composer install
```

Via npm

``` bash
$ npm install
```
## Iniciar o Projeto

1-) Configurar banco de dados no arquivo .env na raiz do projeto, conforme as configurações do seu bando de dados.

#### 
``` txt
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_laravel
DB_USERNAME=username
DB_PASSWORD=password
```
2 -) Criar as tabelas no banco de dados

#### 
``` txt
  $ php artisan migrate
```

3 -) Precisa gerar uma chave secreta JWT, digite no terminal o comando abaixo. 

#### 
``` txt
  $ php artisan jwt:secret
```

Após ter gerado a chave, aparecerá no arquivo .env uma chave semelhante a esta:

#### 
``` txt
  JWT_SECRET=vhhVxo0AVyN1vdYsqpzjUVM7hzNqj7GOosROEUmizQVsNKzL
```

4 -) Subir o servidor 

#### 
``` txt
  $ php artisan serve
```

5 -) Agora já é possível testar a API, veja as rotas abaixo:

#### 
``` txt
  // Rotas públicas
  http://localhost/api/auth/register       - POST,    Registrar novo usuário
  http://localhost/api/auth/login          - POST,    Login para gerar token

  // Rotas privadas
  http://localhost/api/auth/logout         - GET,     Deslogar 
  http://localhost/api/v1/product          - GET,     Busca todos produtos
  http://localhost/api/v1/product/{id}     - GET,     Busca um produto pelo seu ID.
  http://localhost/api/v1/product          - POST,    Cria um novo produto
  http://localhost/api/v1/product/{id}     - PUT,     Atualiza um produto pelo seu ID.
  http://localhost/api/v1/product/{id}     - DELETE,  Deleta um produto pelo seu ID.

  http://localhost/api/v1/user             - GET,     Busca todos os usuários
```

Para realizar as operaçoes de Rotas privadas o usuário precisa enviar o token JWT, que não esteja expirado, caso contrário precisa novamente fazer login e gerar novo token.


## Documentação

Para mais informações e detalhes de como usar esta API veja o manual completo em html, junto com 2 arquivos .json de exportação de 2 dos maiores programas para testar API (postman e insomnia), localizados na raiz do projeto na pasta 'documentation_api'.

![alt text](https://github.com/wagnerGCastro/backend-challenge/blob/feature/06/documentation_api/1-manual.png)

![alt text](https://github.com/wagnerGCastro/backend-challenge/blob/feature/06/documentation_api/2-manual.png)
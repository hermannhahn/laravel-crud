# Projeto Laravel CRUD (Docker-Ready)

Este é um exemplo profissional de CRUD utilizando **Laravel 11**, **Inertia.js (Vue 3)**, **Tailwind CSS** e **Docker (Sail)**.

## Visão Geral

- **Backend:** Laravel 11 (PHP 8.4 no container).
- **Frontend:** Vue 3 com Inertia.js (Moderno e Fluido).
- **Banco de Dados:** MySQL 8.0 rodando via Docker.
- **Cache:** Redis.
- **E-mails:** Mailpit (Dashboard acessível na porta 8025).

## Como Iniciar o Projeto (Docker)

Siga estes passos profissionais no seu terminal:

1.  **Subir os Containers:**
    ```bash
    docker compose up -d
    ```

2.  **Entrar no Container (Opcional):**
    ```bash
    docker compose exec laravel.test bash
    ```

3.  **Configuração Inicial (Dentro do container):**
    ```bash
    composer install
    npm install
    php artisan migrate
    npm run build
    ```

4.  **Acessar a Aplicação:**
    Abra `http://localhost` no seu navegador. Registre-se em `/register` para gerenciar suas tarefas.

## Convenções de Desenvolvimento

- **Padrões:** PSR-12 para PHP, Componentes SFC para Vue 3.
- **Segurança:** Autorização via `TaskPolicy` (um usuário só acessa as suas tarefas).
- **Relacionamentos:** Cada `Task` pertence obrigatoriamente a um `User`.

## Comandos Úteis do Sail

Se você preferir usar o Sail (atalho oficial do Laravel para Docker):

- **Subir tudo:** `./vendor/bin/sail up -d`
- **Rodar Artisan:** `./vendor/bin/sail artisan [comando]`
- **Rodar NPM:** `./vendor/bin/sail npm run dev`

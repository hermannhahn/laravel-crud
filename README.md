# Projeto Laravel CRUD (Docker-Ready)

Este é um exemplo profissional de CRUD utilizando **Laravel 12**, **Inertia.js (Vue 3)**, **Tailwind CSS** e **Docker (Sail)**.

## 🚀 Como Iniciar o Projeto (Docker)

Siga estes passos profissionais no seu terminal:

1.  **Subir os Containers:**
    ```bash
    ./vendor/bin/sail up -d
    ```

2.  **Configuração Inicial:**
    ```bash
    ./vendor/bin/sail artisan migrate
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run build
    ```

3.  **Acessar a Aplicação:**
    Abra `http://localhost` no seu navegador. 
    Use as credenciais de teste:
    - **Usuário:** `gemini@gemini`
    - **Senha:** `gemini123`

## 🛠️ Tecnologias
- **Backend:** Laravel 12 (PHP 8.2+).
- **Frontend:** Vue 3 com Inertia.js.
- **Banco de Dados:** MySQL 8.4.
- **Estilização:** Tailwind CSS.

## 📂 Documentação Adicional
- [Guia Técnico](./docs/tecnico.md)
- [Planejamento (TODO)](./TODO.md)
- [Instruções Gemini](./GEMINI.md)

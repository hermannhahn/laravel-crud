# Projeto Laravel CRUD

Este repositório contém um exemplo de aplicação CRUD (Create, Read, Update, Delete) desenvolvida com o framework **Laravel**.

## Visão Geral do Projeto

O projeto está atualmente em seu estado inicial (nascent), contendo apenas a estrutura básica de repositório Git, README e arquivos de log. O objetivo é implementar um sistema de gerenciamento de dados seguindo as melhores práticas do ecossistema Laravel.

*   **Tecnologias Planejadas:** PHP (Laravel), Composer para dependências, e possivelmente Firebase (conforme indicado pelo log de depuração presente no diretório).
*   **Arquitetura:** Seguirá o padrão MVC (Model-View-Controller) do Laravel.

## Inicialização e Execução

Como o projeto ainda não foi inicializado com a estrutura completa do Laravel, os seguintes passos são recomendados:

1.  **Instalação do Laravel:**
    *   `composer create-project laravel/laravel .` (para instalar na raiz do diretório atual)
    *   *Nota: PHP e Composer devem estar instalados no ambiente.*

2.  **Configuração do Ambiente:**
    *   Copiar o arquivo `.env.example` para `.env`.
    *   Gerar a chave da aplicação: `php artisan key:generate`.

3.  **Execução do Servidor:**
    *   `php artisan serve`

4.  **Testes:**
    *   `php artisan test`

> **TODO:** Implementar a estrutura base do Laravel e configurar o banco de dados.

## Convenções de Desenvolvimento

*   **Linguagem:** O código, commits e documentação técnica devem ser mantidos em **Inglês**.
*   **Estilo de Código:** Seguir as recomendações da PSR-12 para PHP.
*   **Interações com Gemini:** Utilizar este arquivo `GEMINI.md` como contexto para futuras solicitações de implementação de funcionalidades CRUD.

## Arquivos Chave Atuais

*   `README.md`: Breve descrição do projeto.
*   `.gitignore`: Configurações de exclusão de arquivos para o Git, já otimizadas para o ecossistema Laravel.
*   `firebase-debug.log`: Log de interações recentes com ferramentas Firebase.

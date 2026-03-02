# Laravel CRUD - Guia Técnico

## 🛠️ Tecnologias
- **PHP 8.2+ / Laravel 12**
- **Vue 3 / Inertia.js**
- **Tailwind CSS**
- **MySQL 8.4**
- **Docker (Laravel Sail)**

## 🛡️ Segurança (Policies)
A autorização é gerida pelo `TaskPolicy`. 
- Um usuário só pode ver, editar ou deletar suas próprias tarefas.
- O trait `AuthorizesRequests` deve estar presente no `app/Http/Controllers/Controller.php`.

## 📂 Estrutura de Rotas
- `GET /tasks`: Listagem de tarefas do usuário logado.
- `GET /tasks/create`: Formulário de nova tarefa.
- `POST /tasks`: Salva nova tarefa.
- `GET /tasks/{id}/edit`: Formulário de edição.
- `PUT/PATCH /tasks/{id}`: Atualiza tarefa.
- `DELETE /tasks/{id}`: Remove tarefa (Soft Deletes habilitado).

## 🧪 Testes de Interface
Para testes manuais ou automatizados via browser:
1. Acesse `/login`.
2. Use credenciais válidas.
3. Navegue para `/tasks`.

# Laravel CRUD - Technical Guide

## 🛠️ Technologies
- **PHP 8.2+ / Laravel 12**
- **Vue 3 / Inertia.js**
- **Tailwind CSS**
- **MySQL 8.4**
- **Docker (Laravel Sail)**

## 🛡️ Security (Policies)
Authorization is managed by `TaskPolicy`. 
- A user can only view, edit, or delete their own tasks.
- The `AuthorizesRequests` trait must be present in `app/Http/Controllers/Controller.php`.

## 📂 Route Structure
- `GET /tasks`: List tasks for the logged-in user.
- `GET /tasks/create`: New task form.
- `POST /tasks`: Save new task.
- `GET /tasks/{id}/edit`: Edit form.
- `PUT/PATCH /tasks/{id}`: Update task.
- `DELETE /tasks/{id}`: Remove task (Soft Deletes enabled).

## 🧪 Interface Testing
For manual or automated browser tests:
1. Access `/login`.
2. Use valid credentials.
3. Navigate to `/tasks`.

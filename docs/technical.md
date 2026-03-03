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
- Administrators bypass most restrictions to allow global system management.
- The `AuthorizesRequests` trait must be present in `app/Http/Controllers/Controller.php`.

## 📂 Route Structure
- `GET /tasks`: List tasks for the logged-in user (filtered by role).
- `GET /tasks/create`: New task form.
- `POST /tasks`: Save new task.
- `GET /tasks/{id}/edit`: Edit form.
- `PUT/PATCH /tasks/{id}`: Update task.
- `DELETE /tasks/{id}`: Remove task (Soft Deletes enabled).
- `GET /professionals`: Manage professional-company authorizations.
- `GET /areas`: Manage task categories.

## 📊 Dashboard Statistics
- **Company:** Local stats (Pending, Completed).
- **Professional:** Local stats (Assigned, Completed).
- **Admin:** Global stats (Total Pending, Total Completed across all companies).

## 🔍 Search Implementation
Search in `TaskController@index` uses `orWhereHas` to query relationships:
```php
$query->where('title', 'like', '%...%')
      ->orWhere('description', 'like', '%...%')
      ->orWhereHas('professional', ...)
      ->orWhereHas('company', ...);
```

## 🧪 Interface Testing
For manual or automated browser tests:
1. Access `/login`.
2. Use valid credentials (see `GEMINI.md` for test accounts).
3. Navigate to `/tasks`, `/professionals`, or `/areas`.

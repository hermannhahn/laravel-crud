# Laravel CRUD - Planning (TODO)

## 📅 Completed
- [x] Initial project setup (Laravel 12 + Inertia + Vue 3).
- [x] Implementation of Model, Migration, Factory, and Seeder for Tasks.
- [x] Implementation of TaskController with RESTful methods.
- [x] Implementation of TaskPolicy for authorization (security).
- [x] Fix `AuthorizesRequests` trait in base Controller.
- [x] Full project versioning on GitHub.
- [x] Docker environment configuration (Sail).
- [x] Automated validation via Chrome DevTools (Login, Edit, Delete).
- [x] Initial documentation in `docs/`.
- [x] User and Permission Management System:
    - [x] Database: Migration for `role` and `avatar` in `users` table.
    - [x] Database: Migration for `user_permissions` table (module-based).
    - [x] Backend: Admin Middleware and Authorization logic.
    - [x] Backend: User Management Controller (Admin only).
    - [x] Backend: Profile Controller (Avatar upload, personal data, password).
    - [x] Backend: Logic to enforce monthly task limits.
    - [x] Frontend: User Management pages (List, Edit Permissions).
    - [x] Frontend: User Profile page.
    - [x] Frontend: Conditional Navigation Menu based on roles/permissions.
    - [x] Validation: Test Admin and Common User flows via Chrome DevTools.
- [x] Advanced User Management (Admin):
    - [x] Database: Migration to add `is_active` to `users` table.
    - [x] Backend: Update `UserController` with `show`, `destroy`, and `toggleStatus` methods.
    - [x] Backend: Global middleware to block inactive users.
    - [x] Frontend: Update `Users/Index.vue` with status toggle and delete actions.
    - [x] Frontend: Create `Users/Show.vue` for detailed user information.
    - [x] Validation: Test account deactivation and deletion via Chrome DevTools.

## 📋 Pending
- [ ] Improve visual feedback (toasts) in the frontend.
- [ ] Add search filters to the task listing.
- [ ] Implement sorting by due date.
- [ ] Create automated integration tests (Pest).

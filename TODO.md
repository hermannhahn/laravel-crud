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
- [x] User and Permission Management System (Role-based).
- [x] Advanced User Management (Admin can Activate/Deactivate/Delete).
- [x] Multi-Tenant Task Management (Company vs Professional):
    - [x] Database: Migration to add `user_type` to `users` table.
    - [x] Database: Migration for `company_professional` (linking professionals to companies with permissions).
    - [x] Database: Update `tasks` table to include `company_id` and `assigned_professional_id`.
    - [x] Database: Create `task_responses` table for professional feedback.
    - [x] Backend: Update Registration logic to include `user_type` selection.
    - [x] Backend: Company Management Controller (for companies to manage "their" professionals).
    - [x] Backend: Task Assignment and Response logic.
    - [x] Backend: Advanced Policies (Company manages its tasks/team, Professional sees assigned tasks).
    - [x] Frontend: Updated Register page with User Type selection.
    - [x] Frontend: Company Dashboard (Manage Team, Assign Tasks).
    - [x] Frontend: Professional Dashboard (View Assigned Tasks, Respond).
- [x] Task Areas and Professional Specialties:
    - [x] Database: Create `task_areas` table (Company-specific areas).
    - [x] Database: Update `company_professional` pivot with `task_area_id`.
    - [x] Database: Update `tasks` table with `task_area_id`.
    - [x] Backend: `TaskAreaController` for companies to manage their areas.
    - [x] Backend: Update `CompanyTeamController` to assign professionals to areas.
    - [x] Backend: Filter `TaskController@index` pool by the professional's assigned area.
    - [x] Frontend: `Company/Areas.vue` management page.
    - [x] Frontend: Update `Team.vue` and `Tasks/Create.vue` with Area selection.
- [x] Task "Claim/Release" Flow:
    - [x] Backend: Update `TaskController@index` for pool visibility.
    - [x] Backend: Implement `accept`, `release`, and `unassign` methods.
    - [x] Frontend: Update `Tasks/Index.vue` with "Accept" button for unassigned tasks.
    - [x] Frontend: Update `Tasks/Show.vue` with Accept/Release/Unassign actions.

## 🚀 In Progress
- [ ] Refinement and polish of the Multi-Tenant workflow.

## 📋 Pending
- [ ] Improve visual feedback (toasts) in the frontend.
- [ ] Add search filters to the task listing.
- [ ] Implement sorting by due date.
- [ ] Create automated integration tests (Pest).

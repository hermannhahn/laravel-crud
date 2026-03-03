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
- [x] Multi-Tenant Task Management (Company vs Professional).
- [x] Task Areas and Professional Specialties (Multi-Area & Multi-Company support).
- [x] Task "Claim/Release" Flow (Internal Marketplace).
- [x] ID-based Professional Authorization system.
- [x] **Admin Global Management:**
    - [x] Admin Dashboard: Global stats (Pending/Completed) and charts.
    - [x] Admin Navigation: Visible "Professionals" and "Areas" tabs.
    - [x] Admin CRUD: Create Areas and Authorize Professionals for any company.
- [x] **Search Enhancements:**
    - [x] Search by Professional Name.
    - [x] Search by Company Name.
- [x] UI/UX General Refinement:
    - [x] Tasks: Add search bar and status/area filters to the index page.
    - [x] Tasks: Implement sorting (due date, latest).
    - [x] Notifications: Implement a global Toast system for success/error messages.
    - [x] Styling: General layout polish (empty states, buttons, transitions).

## 🚀 In Progress
- [ ] Final polishing and edge case testing.

## 📋 Pending
- [ ] Create automated integration tests (Pest).

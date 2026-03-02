# Gemini Technical Guidelines & Project Context

This file contains instructions, standards, and critical session data for the Laravel CRUD project.

## 📋 Coding Standards
- **Backend:** PSR-12 for PHP.
- **Frontend:** Vue 3 SFC with `<script setup lang="ts">`.
- **Language:** Code, comments, documentation MUST be in English. Portuguese is for chat only.
- **Security:** Use Laravel Policies. All controllers must inherit from a base class using `AuthorizesRequests`.

## 🔑 Test Accounts (Development)
| Name | Email | Password | Role | User Type |
| :--- | :--- | :--- | :--- | :--- |
| Gemini Admin | `gemini@gemini` | `gemini123` | `admin` | `professional` |
| Tech Corp | `company@tech.com` | `password` | `user` | `company` |
| John Pro | `pro@john.com` | `password` | `user` | `professional` |

## 🏗️ Multi-Tenant Architecture
- **User Types:** `company` (creates tasks) and `professional` (executes tasks).
- **Team Link:** Companies add professionals to their team via `company_professional` pivot table.
- **Areas:** Companies define `task_areas`. Professionals are assigned to an area within a company.
- **Task Pool:** 
    - Professionals see tasks assigned to them **OR** unassigned tasks from their company that match their **Area**.
    - Actions: `Accept` (Claim), `Release` (Return to pool), `Respond` (Post updates).
    - Only the assigned professional or company owner can edit/respond.

## 📍 Project State (Last Stopped)
- **Status:** Multi-tenant architecture complete and validated.
- **Last Action:** Validated Task Area filtering. John Pro (Backend) only sees Backend tasks in the pool.
- **Next Steps:** Improve UI/UX (toasts, better loading states), add search filters to task list.

## 🛠️ Workflow Reminders
1. **Testing:** Use `chrome-devtools` extension. Re-use existing tabs/windows to avoid clutter.
2. **Build:** Run `./vendor/bin/sail npm run build` after frontend changes.
3. **Commit:** Always English messages. Commit changed files before heavy operations.

# Gemini Technical Guidelines & Project Context

This file contains instructions, standards, and critical session data for the Laravel CRUD project.

## 📋 Coding Standards
- **Backend:** PSR-12 for PHP.
- **Frontend:** Vue 3 SFC with `<script setup lang="ts">`.
- **Language:** Code, comments, documentation MUST be in English. Portuguese is for chat only.
- **Security:** Use Laravel Policies. All controllers must inherit from a base class using `AuthorizesRequests`.

## 🔑 Test Accounts (Development)
| ID | Name | Email | Password | Role | User Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 2 | Gemini Admin | `gemini@gemini` | `gemini123` | `admin` | `professional` |
| 4 | Tech Corp | `company@tech.com` | `password` | `user` | `company` |
| 5 | John Pro | `pro@john.com` | `password` | `user` | `professional` |

## 🏗️ Multi-Tenant Architecture
- **User Types:** `company` (creates tasks) and `professional` (executes tasks).
- **Professionals Management:** Companies authorize professionals by their **User ID**.
- **Multi-Area Support:** Professionals can be authorized in multiple areas (Backend, Frontend, etc.) per company.
- **Task Pool:** 
    - Professionals see tasks from all authorized companies that match their assigned **Areas**.
    - Filtering & Sorting: Integrated in the main tasks list.
    - Notifications: Global Toast system replaces static flash messages.

## 📍 Project State (Last Stopped)
- **Status:** General Refinement (UI/UX) completed.
- **Last Action:** Implemented filtering, sorting, and global Toast notifications. Refined admin task creation.
- **Next Steps:** Automated integration testing (Pest) and final production readiness checks.

## 🛠️ Workflow Reminders
1. **Testing:** Use `chrome-devtools` extension. Re-use existing tabs/windows.
2. **Build:** Run `./vendor/bin/sail npm run build` after frontend changes.
3. **Commit:** Always English messages. Commit changed files before heavy operations.

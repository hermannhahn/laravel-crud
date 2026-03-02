# Gemini Technical Guidelines

This file contains the approved instructions and standards for developing this project.

## 📋 Coding Standards
- **Backend:** Follow PSR-12 for PHP.
- **Frontend:** Vue 3 components using `<script setup lang="ts">`.
- **Commits:** Messages in English, clear and concise.
- **Authorization:** Always use Laravel `Policies` to protect resources.
- **Controllers:** Keep controllers thin.
- **Language:** Code, comments, documentation, and metadata must be in English.

## 🛠️ Workflow
1. **Code Changes:** Commit only changed files before any deployment or heavy build.
2. **Push:** Perform `git push` after each feature commit.
3. **Validation:** Always use the `chrome-devtools` extension to validate visual changes or user flows.
4. **Documentation:** Keep `TODO.md` updated with task progress.

## 🔍 Automated Testing
- Prioritize using Chrome DevTools to simulate real user interactions.
- Document bugs found in `TODO.md`.

## 💾 Useful Memories
- Default test user is `gemini@gemini` with password `gemini123`.
- The `AuthorizesRequests` trait must be used in the base `Controller.php` class.

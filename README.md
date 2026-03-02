# Laravel CRUD Project (Docker-Ready)

This is a professional CRUD example using **Laravel 12**, **Inertia.js (Vue 3)**, **Tailwind CSS**, and **Docker (Sail)**.

## 🚀 Getting Started (Docker)

Follow these professional steps in your terminal:

1.  **Bring up the Containers:**
    ```bash
    ./vendor/bin/sail up -d
    ```

2.  **Initial Configuration:**
    ```bash
    ./vendor/bin/sail artisan migrate
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run build
    ```

3.  **Access the Application:**
    Open `http://localhost` in your browser. 
    Use the test credentials:
    - **User:** `gemini@gemini`
    - **Password:** `gemini123`

## 🛠️ Technologies
- **Backend:** Laravel 12 (PHP 8.2+).
- **Frontend:** Vue 3 with Inertia.js.
- **Database:** MySQL 8.4.
- **Styling:** Tailwind CSS.

## 📂 Additional Documentation
- [Technical Guide](./docs/technical.md)
- [Planning (TODO)](./TODO.md)
- [Gemini Instructions](./GEMINI.md)

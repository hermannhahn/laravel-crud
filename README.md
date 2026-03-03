# Laravel CRUD Multi-Tenant Project

A professional task management system with multi-tenant support, featuring **Laravel 12**, **Inertia.js (Vue 3)**, **Tailwind CSS**, and **Docker (Sail)**.

## 🚀 Installation Guide

Follow these steps to set up the project locally:

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd laravel-crud
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Configure Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Install Node dependencies:**
    ```bash
    npm install
    ```

5.  **Start the environment:**
    ```bash
    npm run start
    ```

6.  **Run Migrations:**
    ```bash
    npm run migrate
    ```

7.  **Build Frontend:**
    ```bash
    npm run build
    ```

8.  **First Access (Administrator Registration):**
    Open `http://localhost/register` in your browser. 
    **Important:** The **first user registered** in a fresh database is automatically granted the **System Administrator** role with full access.

## 🛠️ Commands

- `npm run start`: Starts the Docker containers in the background.
- `npm run stop`: Stops the Docker containers.
- `npm run migrate`: Runs the database migrations.
- `npm run build`: Compiles the frontend assets for production.
- `npm run dev`: Starts the Vite development server.

## 🔑 User Roles

- **System Administrator:** Created during first registration. Full visibility of all tasks, users, and financial data.
- **Company:** Can create tasks, manage professions, and authorize professionals.
- **Professional:** Can accept tasks, post updates, and track their earnings.

## 💰 Financial System

The system includes a dedicated **Finance** module:
- **Companies:** Track total spending on completed tasks.
- **Professionals:** Monitor total earnings and monthly performance.
- **Admins:** Global view of the entire platform's financial volume.

## 📂 Documentation
- [Technical Details](./docs/technical.md)
- [Project Goals (TODO)](./TODO.md)

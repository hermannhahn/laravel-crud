# Laravel CRUD Multi-Tenant Project

A professional task management system with multi-tenant support, featuring **Laravel 12**, **Inertia.js (Vue 3)**, **Tailwind CSS**, and **Docker (Sail)**.

## 🚀 Installation Guide

Follow these steps to set up the project locally using Laravel Sail:

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

4.  **Start the Docker containers:**
    ```bash
    ./vendor/bin/sail up -d
    ```

5.  **Run Migrations:**
    ```bash
    ./vendor/bin/sail artisan migrate
    ```

6.  **Install and Build Frontend:**
    ```bash
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run build
    ```

7.  **First Access (Administrator Registration):**
    Open `http://localhost/register` in your browser. 
    **Important:** The **first user registered** in a fresh database is automatically granted the **System Administrator** role with full access.

## 🔑 User Roles

- **System Administrator:** Created during first registration. Full visibility of all tasks, users, and financial data.
- **Company:** Can create tasks, manage professions, and authorize professionals.
- **Professional:** Can accept tasks, post updates, and track their earnings.

## 💰 Financial System

The system includes a dedicated **Finance** module:
- **Companies:** Track total spending on completed tasks.
- **Professionals:** Monitor total earnings and monthly performance.
- **Admins:** Global view of the entire platform's financial volume.

## 🛠️ Main Features
- **Multi-Tenant Architecture:** Data isolation between companies and professionals.
- **Task Marketplace:** Professionals see and accept tasks from authorized companies.
- **Interactive Dashboard:** Visual charts and real-time statistics.
- **Dark Mode Support:** Full compatibility with system-wide dark themes.

## 📂 Documentation
- [Technical Details](./docs/technical.md)
- [Project Goals (TODO)](./TODO.md)

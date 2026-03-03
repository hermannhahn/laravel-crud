# Laravel CRUD Multi-Tenant Project

A professional task management system with multi-tenant support, featuring **Laravel 12**, **Inertia.js (Vue 3)**, **Tailwind CSS**, and **Docker (Sail)**.

## 🚀 Installation & Setup

Follow these steps to get the system running locally:

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd laravel-crud
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Prepare environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Start the server:**
    ```bash
    npm run start
    ```

5.  **Initialize Database & Assets:**
    ```bash
    npm run migrate
    npm run build
    ```

6.  **Access the System:**
    Open [http://localhost](http://localhost) in your browser.

    *Note: The **first user registered** at `http://localhost/register` will automatically become the **System Administrator**.*

## 🛠️ Management Commands

- `npm run start`: Starts the system containers.
- `npm run stop`: Stops the system.
- `npm run migrate`: Updates database structure.
- `npm run build`: Finalizes interface components.

## 🔑 User Roles

- **System Administrator:** Full platform visibility and user management.
- **Company:** Create tasks and manage service offerings.
- **Professional:** Execute tasks and track earnings.

## 💰 Financial Tracking

The system includes integrated financial monitoring:
- **Companies:** View total project expenditures.
- **Professionals:** Track monthly and lifetime earnings.
- **Admins:** Oversee total platform financial volume.

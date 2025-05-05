# Laravel Project with Docker (MySQL Only)
This project is a Laravel-based application with Docker used only for running MySQL. Laravel's backend and Vue.js frontend are managed locally.

## ✨ Features

- ✅ **CRUD Operations**  
  Create, read, update, and delete records via clean user interfaces and Laravel API routes.

- ✅ **Search & Filtering**  
  Search through records using keyword filters and field-based filtering options.

- ✅ **Export to Excel**  
  Export filtered or all data—up to **6000 records**—to an Excel file effortlessly.

## Prerequisites

Ensure that you have the following software installed:

- Docker (for MySQL)
- Docker Compose
- PHP 8.x (for Laravel)
- Composer (for Laravel dependencies)
- Node.js (for Vue.js frontend)
- NPM or Yarn (for frontend package management)

## Installation

### 1. Clone the Repository

First, clone the repository to your local machine.

```bash
git clone https://github.com/nadaikasari/Technical-Test-Vodjo.git
cd <project-directory>
```

### 2. Install Composer Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

## Setup

### 1. Set Up Environment Configuration

```bash
cp .env.example .env
```
### 2. Configure Database in .env
Ensure that your .env file is properly configured for MySQL, which is managed by Docker. Update the database credentials like this:
```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=laravel
    DB_PASSWORD=laravel
```
## Running the Project

### 1. Start the Laravel Development Server

```bash
npm run dev
```

### 2. Start the Laravel Development Server

```bash
php artisan serve
```

### 3. Running Docker for MySQL

```bash
docker-compose up -d mysql
```

## Database Setup

### 1. Run Laravel Migrations

```bash
php artisan migrate
```

### 2. Seed the Database

```bash
php artisan db:seed
```

## Screenshot
![image](https://github.com/user-attachments/assets/c6313fe7-3334-40d8-bffb-89c8a7851709)
![image](https://github.com/user-attachments/assets/601132e2-6294-412e-8ac3-b5b70b3709c4)
<img width="1158" alt="image" src="https://github.com/user-attachments/assets/7acca1ac-eb66-4d21-859c-113b63466af4" />
<img width="1061" alt="image" src="https://github.com/user-attachments/assets/658289ae-844b-4c9d-aea2-04ac437d9f44" />








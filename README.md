# Task Management System - Installation Guide

Simple guide to get the application up and running using Docker.

## 🐳 Docker Setup

The application uses **3 Docker containers**:
- **app** - PHP-FPM container (Laravel application)
- **nginx** - Web server (exposed on port 8080)
- **db** - MySQL database server

---
## ⚡ Quick Start
### Step 1: Clone & Configure

```bash
# Clone the repository
git clone git@github.com:karim-abdelrhman/soft-expert-task.git

cd soft-expert-task

# Copy environment file
cp .env.example .env
```

### Step 2: Start Docker Containers

```bash
# Build and start all containers
docker compose up -d --build

# This will create and start:
# - app (PHP-FPM)
# - nginx (Web server on port 8080)
# - db (MySQL database)
```

### Step 3: Install Dependencies & Setup Database

```bash
# Enter the app container
docker compose exec -it app bash

# Install Composer dependencies
composer install

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with initial data
php artisan db:seed

```

### Step 4: Access the Application

The API is now running at:
```
http://localhost:8080
```

---

## 📦 What's Included After Seeding

The `db:seed` command creates:

✅ **3 Users:**
- Manager: `manager@gmail.com` / `password`
- User1: `user1@gmail.com` / `password`
- User2: `user2@gmail.com` / `password`

✅ **Sample Tasks** with dependencies to test the system

---

## 🧪 Running Tests

```bash
# Enter app container
docker compose exec -it app bash
# Run all tests
php artisan test
```

**All tests should pass ✅**

---


## 🐛 Troubleshooting

### Port 8080 already in use?
```
# Edit docker-compose.yml and change:
ports:
  - "8081:80"  # Use 8081 instead
```
### Permission issues?
```b
docker-compose exec -it app chmod -R 777 storage bootstrap/cache
```

---

## 📋 Default Credentials

After running `php artisan db:seed`:

| Email             | Password | Role |
|-------------------|----------|------|
| manager@gmail.com | password | Manager |
| user1@gmail.com   | password | User |
| user2@gmail.com | password | User |

---
## 🗄️ ERD For Database


## 🎯 Summary

This project demonstrates:
- ✅ RESTful API design
- ✅ Docker containerization (3 containers)
- ✅ Laravel Sanctum authentication
- ✅ Role-based authorization with Policies
- ✅ Clean code architecture
- ✅ Comprehensive testing



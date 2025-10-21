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
## 📮 API Documentation

### Postman Collection
Import the Postman collection to test all API endpoints:

https://documenter.getpostman.com/view/31443966/2sB3QQL8TD

**Or click here:**
- [📄 View Collection](./postman/Soft_Expert(v1).postman_collection.json)
---

## 🧪 Running Tests

```bash
# Enter app container
docker compose exec -it app bash
# Run all tests
php artisan test
```

**All tests should pass ✅**

<img width="1337" height="554" alt="Screenshot from 2025-10-20 22-57-47" src="https://github.com/user-attachments/assets/21631aa8-d738-4f52-97b5-f2bdbe4747e3" />
---


## 🗄️ ERD For Database

<img width="1490" height="686" alt="Screenshot from 2025-10-18 14-21-22" src="https://github.com/user-attachments/assets/df800801-cf79-4827-b037-b13e16cbbd38" />


## 🐛 Troubleshooting

### Port 8080 already in use?
```
# Edit docker-compose.yml and change:
ports:
  - "8081:80"  # Use 8081 instead
```
### Permission issues?
```b
docker compose exec -it app bash chmod -R 777 storage bootstrap/cache
```

---

## 📋 Default Credentials

After running `php artisan db:seed`:
users , tasks , task_dependencies tables    

| Email             | Password | Role |
|-------------------|----------|------|
| manager@gmail.com | password | Manager|
| user1@gmail.com   | password | User |
| user2@gmail.com   | password | User |
| user3@gmail.com   | password | User |

| title             | assignee_id | status    |
|-------------------|-------------|-----------|
| do your homework | 2           | pending   |
| Go to School   | 2           | pending   |
| wash your hands   | 3           | pending   |
| clean the floor   | 3           | completed |
| Solve problem   | null        | canclled  |

| id | task_id | depends_on |
|----|---------|------------|
| 1  | 1       | 2          |
| 2  | 3       | 4          |

---



## 🎯 Summary

This project demonstrates:
- ✅ RESTful API design
- ✅ Docker containerization (3 containers)
- ✅ Laravel Sanctum authentication
- ✅ Role-based authorization with Policies
- ✅ Clean code architecture
- ✅ Comprehensive testing



# 🧩 Freelance Project Management API

This is a simple RESTful API built with **PHP (MVC + PDO)**, **MySQL**, and **JWT authentication**. It allows **freelancers** to register, manage projects, upload files, and more.

---

## 📚 Technologies

- PHP (MVC, PDO)
- MySQL
- JWT (Authentication)
- Composer
- Postman (for testing)
- Angular (Frontend, not included here)

---

## 📦 Features

- ✅ User registration & login (JWT)
- ✅ Project CRUD
- ✅ File upload/download/delete by project
- ✅ Authentication via Bearer Token

---

## 🨠 Environment Setup

Create a `.env` file in the root:

```env
DB_HOST=localhost
DB_NAME=freelance_db
DB_USER=root
DB_PASS=your_password

JWT_SECRET=super_secret_key
JWT_ISSUER=localhost
JWT_AUDIENCE=localhost
JWT_EXPIRES_IN=86400
```

Run:

```bash
composer install
php -S localhost:8000
```

---

## 🔐 Authentication Endpoints

### ✅ Register

`POST /register`

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "123456"
}
```

### ✅ Login

`POST /login`

```json
{
  "email": "john@example.com",
  "password": "123456"
}
```

Response:

```json
{
  "message": "Login successful",
  "token": "JWT_TOKEN_HERE",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

## 📁 Project Endpoints

> All routes require `Authorization: Bearer YOUR_TOKEN`

### ➕ Create project

`POST /project`

```json
{
  "title": "My Web App",
  "description": "Build a web app using Angular",
  "start_date": "2025-04-22",
  "delivery_date": "2025-05-10",
  "status": "pending"
}
```

### 📋 List user projects

`GET /projects`

### 🔍 Get project by ID

`GET /projects/{id}`

### ✏️ Update project

`PUT /project`

```json
{
  "id": 1,
  "title": "Updated Title",
  "description": "Updated Description",
  "start_date": "2025-04-22",
  "delivery_date": "2025-05-15",
  "status": "in progress"
}
```

### ❌ Delete project

`DELETE /project/{id}`

---

## 📎 File Endpoints

> All routes require `Authorization: Bearer YOUR_TOKEN`

### 📄 Upload file

`POST /file/{project_id}`

**Body**: form-data  
Key: `file`  
Type: File

Allowed types:
- `.pdf`, `.jpg`, `.png`, `.doc`, `.docx`

### 📂 List files by project

`GET /files/{project_id}`

### 📅 Download file

`GET /file/{file_id}`

### ❌ Delete file

`DELETE /file/{file_id}`

---

# Online TOR Request System - Setup Guide

## Overview

This is a complete Online Transcript of Records (TOR) Request System built with Laravel 12, featuring user authentication using Laravel Sanctum and Axios for API communication.

## Features

### Authentication

- User registration and login
- Token-based authentication using Laravel Sanctum
- Secure API endpoints with token validation
- Logout with token revocation

### TOR Request Management

- Submit TOR requests with complete information:
    - Full Name
    - Date of Birth
    - Place of Birth
    - Student ID
    - Course/Program
    - Degree (optional)
    - Year of Graduation (optional)
    - Purpose of Request (optional)
    - Number of Copies Needed

- View all submitted TOR requests with status tracking
- Delete pending requests
- Track request status: Pending, Processing, Approved, Rejected, Ready for Pickup
- View detailed request information

## Installation & Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite or MySQL database

### Step 1: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 2: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Database Setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed demo data
php artisan db:seed
```

### Step 4: Build Assets

```bash
# Development build
npm run dev

# Or production build
npm run build
```

### Step 5: Start the Development Server

```bash
# Start Laravel development server
php artisan serve

# In another terminal, watch for asset changes (optional)
npm run dev
```

The application will be available at `http://localhost:8000`

## API Endpoints

### Authentication Endpoints

#### Register

```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**

```json
{
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "auth_token_here"
}
```

#### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "auth_token_here"
}
```

#### Get Current User

```http
GET /api/user
Authorization: Bearer {token}
```

#### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

### TOR Request Endpoints

All TOR endpoints require authentication (`Authorization: Bearer {token}`)

#### Create TOR Request

```http
POST /api/tor-requests
Content-Type: application/json
Authorization: Bearer {token}

{
  "full_name": "John Doe",
  "birthplace": "Manila",
  "birthdate": "1995-05-15",
  "student_id": "2019-00123",
  "course": "Bachelor of Science in Information Technology",
  "degree": "Bachelor of Science",
  "year_of_graduation": 2023,
  "purpose": "Job application",
  "number_of_copies": 2
}
```

#### Get All TOR Requests (User's Requests)

```http
GET /api/tor-requests
Authorization: Bearer {token}
```

#### Get Single TOR Request

```http
GET /api/tor-requests/{id}
Authorization: Bearer {token}
```

#### Delete TOR Request (Only Pending)

```http
DELETE /api/tor-requests/{id}
Authorization: Bearer {token}
```

## Frontend Routes

- `/` - Welcome page
- `/login` - User login
- `/register` - User registration
- `/tor/create` - Submit new TOR request (Protected)
- `/tor/requests` - View all TOR requests (Protected)

## Database Structure

### users table

- id
- name
- email
- email_verified_at
- password
- remember_token
- created_at
- updated_at

### tor_requests table

- id
- user_id (FK to users)
- full_name
- birthplace
- birthdate
- student_id (unique)
- course
- degree
- year_of_graduation
- purpose
- number_of_copies
- status (pending, processing, approved, rejected, ready_for_pickup)
- remarks
- requested_at
- completed_at
- created_at
- updated_at

### personal_access_tokens table

- id
- tokenable_type
- tokenable_id
- name
- token (unique)
- abilities
- last_used_at
- expires_at
- created_at
- updated_at

## Client-Side Authentication (Axios)

The system uses Axios for API communication with automatic token management.

### Configuration Files

**[resources/js/api.js](resources/js/api.js)** - Axios instance with:

- CSRF token support
- Automatic Bearer token injection
- Automatic logout on 401 errors

**[resources/js/auth.js](resources/js/auth.js)** - Authentication helper with methods:

- `login(email, password)` - Login user
- `logout()` - Logout user
- `getUser()` - Get current user
- `isAuthenticated()` - Check if user has valid token
- `getStoredUser()` - Get stored user from localStorage

### Usage Example

```javascript
import api from "/resources/js/api.js";
import { auth } from "/resources/js/auth.js";

// Login
const response = await api.post("/api/login", {
    email: "john@example.com",
    password: "password123",
});

// Store token
localStorage.setItem("auth_token", response.data.token);

// Make authenticated request
const requests = await api.get("/api/tor-requests");

// Logout
await auth.logout();
```

## Security Features

1. **CSRF Protection** - All forms include CSRF token validation
2. **API Token Authentication** - Sanctum tokens for API endpoints
3. **Password Hashing** - Bcrypt password hashing
4. **Authorization** - Users can only access their own TOR requests
5. **Validation** - Server-side validation for all inputs
6. **Token Revocation** - Tokens are revoked on logout

## Development Notes

### Extending the System

#### Add Admin Panel

Create new routes and controllers for admin functionality to manage TOR requests and update statuses.

#### Email Notifications

Implement email notifications when TOR request status changes using Laravel's mailable classes.

#### File Uploads

Add support for document uploads (birth certificate, ID copy, etc.) by extending the migration and controller.

#### Status History

Track status changes with timestamps by creating a separate `tor_request_status_history` table.

## Troubleshooting

### Token Not Being Sent

- Check that localStorage has the `auth_token` key
- Verify the API response includes a `token` field
- Check browser console for errors

### 401 Unauthorized Errors

- Ensure token is valid and not expired
- Check that `Authorization: Bearer {token}` header is being sent
- Verify the route requires `auth:sanctum` middleware

### CSRF Token Errors

- Ensure `<meta name="csrf-token">` tag is in the HTML head
- Check that Laravel's CSRF middleware is enabled
- Verify the token matches between meta tag and request headers

### Database Errors

- Run `php artisan migrate:fresh` to reset migrations
- Check database credentials in `.env` file
- Ensure database exists and is accessible

## Support

For Laravel documentation, visit [laravel.com/docs](https://laravel.com/docs)

For Sanctum documentation, visit [laravel.com/docs/sanctum](https://laravel.com/docs/sanctum)

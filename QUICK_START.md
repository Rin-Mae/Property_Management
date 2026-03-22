# 🎓 Online TOR Request System - Complete Setup Summary

## What Has Been Created

I've successfully built a complete **Online Transcript of Records (TOR) Request System** with Laravel 12, featuring user authentication using Laravel Sanctum and Axios for secure API communication.

---

## 📋 System Overview

### Features Implemented

✅ **User Authentication**

- User registration with validation
- Login with email and password
- Token-based authentication (Laravel Sanctum)
- Secure logout with token revocation
- Session persistence using localStorage

✅ **TOR Request Management**

- Submit TOR requests with comprehensive information
- Full Name, Birthplace, Birthdate (required)
- Student ID, Course, Degree (required/optional)
- Year of Graduation, Purpose (optional)
- Number of Copies Needed
- View all submitted requests with status tracking
- Delete pending requests
- View detailed request information in modal popup
- Request status: Pending, Processing, Approved, Rejected, Ready for Pickup

✅ **Security Features**

- CSRF token protection on all forms
- Password hashing with Bcrypt
- API token authentication (Sanctum)
- User isolation (can only access own requests)
- Server-side form validation
- Automatic redirection on unauthorized access

✅ **User Experience**

- Beautiful gradient UI with Tailwind CSS
- Form validation with error messages
- Responsive design (mobile, tablet, desktop)
- Real-time form feedback
- Status badges with color coding
- Modal dialogs for viewing details

---

## 📁 Files Created/Modified

### Core Application Files

**Models:**

- `app/Models/User.php` ✏️ (Modified - Added Sanctum support)
- `app/Models/TORRequest.php` ✨ (New - TOR request entity)

**Controllers:**

- `app/Http/Controllers/Auth/LoginController.php` ✨ (New)
- `app/Http/Controllers/Auth/RegisterController.php` ✨ (New)
- `app/Http/Controllers/TORRequestController.php` ✨ (New)

**Routes:**

- `routes/web.php` ✏️ (Modified - Added web routes)
- `routes/api.php` ✨ (New - API endpoints)

**Migrations:**

- `database/migrations/0001_01_01_000003_create_tor_requests_table.php` ✨ (New)
- `database/migrations/0001_01_01_000004_create_personal_access_tokens_table.php` ✨ (New)

**Frontend - JavaScript:**

- `resources/js/api.js` ✨ (New - Axios configuration)
- `resources/js/auth.js` ✨ (New - Auth helpers)

**Frontend - Views:**

- `resources/views/auth/login.blade.php` ✨ (New)
- `resources/views/auth/register.blade.php` ✨ (New)
- `resources/views/tor/create.blade.php` ✨ (New - TOR form)
- `resources/views/tor/requests.blade.php` ✨ (New - Requests list)

**Configuration:**

- `bootstrap/app.php` ✏️ (Modified - Added API routing)
- `config/auth.php` ✏️ (Modified - Added Sanctum guard)

**Documentation:**

- `SETUP.md` ✨ (Installation & API guide)
- `IMPLEMENTATION.md` ✨ (Technical details & file structure)
- `TESTING.md` ✨ (Testing checklist & user guide)
- `QUICK_START.md` ✨ (This file)

---

## 🚀 Quick Start Guide

### Step 1: Install Dependencies

```bash
cd c:\Users\acer\OneDrive\Desktop\online_tor_request
composer install
npm install
```

### Step 2: Environment Setup

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Database Setup

```bash
# Run all migrations
php artisan migrate
```

You should see migrations for:

- users table
- cache table
- jobs table
- tor_requests table ✨ (NEW)
- personal_access_tokens table ✨ (NEW)

### Step 4: Build Frontend

```bash
npm run build
```

### Step 5: Start Development Server

```bash
php artisan serve
```

The application will be available at: **http://localhost:8000**

---

## 🌐 Application Routes

### Web Routes (Browser)

| Route           | Purpose            | Access        |
| --------------- | ------------------ | ------------- |
| `/`             | Welcome page       | Public        |
| `/login`        | Login form         | Guest only    |
| `/register`     | Registration form  | Guest only    |
| `/tor/create`   | Submit TOR request | Authenticated |
| `/tor/requests` | View all requests  | Authenticated |

### API Routes (AJAX/Axios)

| Method | Endpoint                 | Purpose             | Auth |
| ------ | ------------------------ | ------------------- | ---- |
| POST   | `/api/register`          | Register user       | No   |
| POST   | `/api/login`             | Login user          | No   |
| GET    | `/api/user`              | Get current user    | Yes  |
| POST   | `/api/logout`            | Logout user         | Yes  |
| POST   | `/api/tor-requests`      | Create request      | Yes  |
| GET    | `/api/tor-requests`      | Get user's requests | Yes  |
| GET    | `/api/tor-requests/{id}` | Get single request  | Yes  |
| DELETE | `/api/tor-requests/{id}` | Delete request      | Yes  |

---

## 🔐 Authentication Flow

### User Registration

```
1. User visits /register
2. Fills name, email, password
3. Form sent to /api/register via Axios
4. Server creates user & generates token
5. Token stored in localStorage
6. Redirect to TOR form
```

### User Login

```
1. User visits /login
2. Enters email & password
3. Form sent to /api/login via Axios
4. Server validates & generates token
5. Token stored in localStorage
6. Redirect to TOR form
```

### API Requests

```
1. Axios interceptor adds header: Authorization: Bearer {token}
2. Also includes CSRF token from meta tag
3. Server validates token with Sanctum guard
4. Returns data if authorized, 401 if not
5. Auto-logout on 401 error
```

---

## 📊 Database Schema

### users table

```sql
- id (Primary Key)
- name (string)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string, hashed)
- remember_token (string, nullable)
- created_at, updated_at (timestamps)
```

### tor_requests table

```sql
- id (Primary Key)
- user_id (Foreign Key → users table)
- full_name (string)
- birthplace (string)
- birthdate (date)
- student_id (string, unique)
- course (string)
- degree (string, nullable)
- year_of_graduation (year, nullable)
- purpose (text, nullable)
- number_of_copies (integer)
- status (enum: pending, processing, approved, rejected, ready_for_pickup)
- remarks (text, nullable)
- requested_at (timestamp)
- completed_at (timestamp, nullable)
- created_at, updated_at (timestamps)
```

### personal_access_tokens table

```sql
- id (Primary Key)
- tokenable_type (string) → "App\\Models\\User"
- tokenable_id (integer) → user id
- name (string) → "auth_token"
- token (string, unique)
- abilities (text, nullable)
- last_used_at (timestamp, nullable)
- expires_at (timestamp, nullable)
- created_at, updated_at (timestamps)
```

---

## ✨ Key Features Explained

### 1. Axios Configuration (`resources/js/api.js`)

- Automatically adds Bearer token to all API requests
- Includes CSRF token from `<meta name="csrf-token">`
- Auto-logout on 401 Unauthorized response
- Base URL set to application root

### 2. Authentication Module (`resources/js/auth.js`)

Helper functions for managing authentication:

- `login(email, password)` - Login user
- `logout()` - Logout and clear storage
- `getUser()` - Fetch current user
- `isAuthenticated()` - Check if user has valid token
- `getStoredUser()` - Get user from localStorage

### 3. Form Validation

**Server-Side:**

- Required field validation
- Email format validation
- Password confirmation
- Unique student ID
- Date validation (birthdate must be past)
- Max multiple copies limit

**Client-Side:**

- HTML5 input validation
- Real-time error display
- Field highlighting

### 4. Status Tracking

Requests show status with color badges:

- 🟡 Pending (Yellow)
- 🔵 Processing (Blue)
- 🟢 Approved (Green)
- 🔴 Rejected (Red)
- 🟢 Ready for Pickup (Green)

### 5. User Isolation

Each user can only:

- Access their own TOR requests
- Delete only pending requests
- View only their own request details

---

## 🧪 Testing the System

### Quick Test Workflow

1. **Register User**
    - Go to http://localhost:8000/register
    - Create account with test data
    - Should redirect to TOR form

2. **Submit TOR Request**
    - Fill form with test data
    - Click "Submit TOR Request"
    - View success message

3. **View Requests**
    - Click "View My Requests"
    - See table with submitted request
    - Click "View" to see details

4. **Test Logout**
    - Click "Logout" button
    - Should redirect to login
    - Try accessing /tor/create - should redirect to login

### API Testing

See `TESTING.md` for detailed curl commands to test all API endpoints.

---

## 📚 Documentation Files

1. **SETUP.md** - Complete setup instructions & API reference
2. **IMPLEMENTATION.md** - Technical details & file structure
3. **TESTING.md** - Testing guide with test cases & curl commands
4. **QUICK_START.md** (This file) - Quick reference

---

## 🛠️ Technology Stack

**Backend:**

- Laravel 12 Framework
- PHP 8.2+
- Laravel Sanctum (API tokens)
- Eloquent ORM

**Frontend:**

- Blade templating
- Axios (HTTP client)
- Tailwind CSS (Styling)
- Vite (Asset bundler)
- JavaScript (Vanilla, no Vue/React)

**Database:**

- SQLite (default) or MySQL/PostgreSQL

**Security:**

- CSRF token protection
- Bcrypt password hashing
- Bearer token authentication
- User authorization checks

---

## ⚠️ Important Notes

1. **Migrations**: Run `php artisan migrate` to create all tables
2. **Environment**: Configure `.env` with database credentials
3. **Assets**: Run `npm run build` before deployment
4. **Tokens**: Stored in localStorage - only valid during session
5. **CORS**: If frontend on different domain, configure in `config/cors.php`

---

## 🔧 Common Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Reset database (delete all data)
php artisan migrate:fresh

# Create migration
php artisan make:migration create_table_name

# Create model
php artisan make:model ModelName

# Create controller
php artisan make:controller ControllerName

# Run tests
php artisan test

# Build frontend
npm run build

# Watch frontend changes (dev mode)
npm run dev

# Clear cache
php artisan cache:clear
```

---

## 🚨 Troubleshooting

### "Table not found" Error

```bash
# Solution: Run migrations
php artisan migrate
```

### "CSRF token mismatch"

```bash
# Solution: Clear browser cache and cookies
# Ensure meta tag in views has csrf token
```

### Cannot login/Can't create requests

```bash
# Solution: Check if personal_access_tokens table exists
php artisan migrate
```

### Assets not loading (CSS/JS not working)

```bash
# Solution: Build assets
npm run build
```

### Port 8000 already in use

```bash
# Use different port
php artisan serve --port=8001
```

---

## 📞 Support & Next Steps

### To Extend the System

1. **Add Admin Panel**
    - Create admin routes and controllers
    - Add user roles/permissions

2. **Email Notifications**
    - Create Mailable classes
    - Send emails on status changes

3. **File Uploads**
    - Add document upload fields
    - Store files in storage/

4. **Payment Integration**
    - Add Stripe/PayMongo payment
    - Track paid/unpaid requests

5. **Advanced Reports**
    - Generate PDF reports
    - Export to CSV/Excel

---

## ✅ Setup Complete!

Your Online TOR Request System is ready to use. Here's what to do next:

1. ✅ Install dependencies: `composer install && npm install`
2. ✅ Setup environment: `cp .env.example .env && php artisan key:generate`
3. ✅ Create database: `php artisan migrate`
4. ✅ Build assets: `npm run build`
5. ✅ Start server: `php artisan serve`
6. ✅ Visit http://localhost:8000 and register a test account

**Happy coding! 🚀**

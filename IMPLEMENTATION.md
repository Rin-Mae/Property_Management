# Online TOR Request System - File Structure & Implementation Guide

## Summary of Created/Modified Files

### 1. Database Migrations

#### [database/migrations/0001_01_01_000003_create_tor_requests_table.php](database/migrations/0001_01_01_000003_create_tor_requests_table.php)

- Creates `tor_requests` table with all TOR request fields
- Includes foreign key relationship to users table
- Tracks request status, remarks, and timestamps

#### [database/migrations/0001_01_01_000004_create_personal_access_tokens_table.php](database/migrations/0001_01_01_000004_create_personal_access_tokens_table.php)

- Creates `personal_access_tokens` table for Sanctum
- Enables token-based API authentication

### 2. Models

#### [app/Models/User.php](app/Models/User.php) - **MODIFIED**

- Added `HasApiTokens` trait for Sanctum support
- Added `torRequests()` relationship method

#### [app/Models/TORRequest.php](app/Models/TORRequest.php)

- Entity representing a TOR request
- Includes relationship to User model
- Casts dates and timestamps properly

### 3. Controllers

#### [app/Http/Controllers/Auth/LoginController.php](app/Http/Controllers/Auth/LoginController.php)

- `show()` - Display login form
- `login(Request)` - API endpoint for user login
- `logout(Request)` - API endpoint for logout
- `user(Request)` - Get current authenticated user

#### [app/Http/Controllers/Auth/RegisterController.php](app/Http/Controllers/Auth/RegisterController.php)

- `show()` - Display registration form
- `register(Request)` - API endpoint for user registration

#### [app/Http/Controllers/TORRequestController.php](app/Http/Controllers/TORRequestController.php)

- `create()` - Display TOR request form
- `store(Request)` - Create new TOR request (API)
- `index()` - Get all user's TOR requests (API)
- `show(TORRequest)` - Get single TOR request (API)
- `destroy(TORRequest)` - Delete TOR request (API)

### 4. Frontend - JavaScript/Axios

#### [resources/js/api.js](resources/js/api.js)

- Axios instance configuration
- CSRF token handling
- Auto Bearer token injection in requests
- Auto-logout on 401 errors
- Request/response interceptors

#### [resources/js/auth.js](resources/js/auth.js)

- Authentication helper module
- Methods: login, logout, getUser, isAuthenticated, getStoredUser

### 5. Frontend - Views

#### [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php)

- Beautiful login form with gradient background
- Form validation display
- Axios-based form submission
- Automatic redirect on success
- Link to registration page

#### [resources/views/auth/register.blade.php](resources/views/auth/register.blade.php)

- User registration form
- Password confirmation field
- Real-time error display
- Axios-based request handling
- Link back to login page

#### [resources/views/tor/create.blade.php](resources/views/tor/create.blade.php)

- Complete TOR request form
- Organized into 3 sections:
    - Personal Information (Name, DOB, Birthplace)
    - Academic Information (Student ID, Course, Degree, Year)
    - Request Details (Purpose, Number of Copies)
- Form validation and error messages
- View My Requests button
- Responsive design with proper styling

#### [resources/views/tor/requests.blade.php](resources/views/tor/requests.blade.php)

- Dashboard showing all user's TOR requests
- Table with columns: Student ID, Course, Copies, Status, Requested, Actions
- Status badges with color coding
- View details modal popup
- Delete button for pending requests (with confirmation)
- Empty state when no requests exist
- Responsive mobile-friendly design

### 6. Routes

#### [routes/web.php](routes/web.php) - **MODIFIED**

- `/login` - Show login form
- `/register` - Show registration form
- `/tor/create` - Show TOR request form (protected)
- `/tor/requests` - Show TOR requests list (protected)
- `/` - Welcome page

#### [routes/api.php](routes/api.php) - **CREATED**

- `POST /api/login` - User login
- `POST /api/register` - User registration
- `GET /api/user` - Get current user (protected)
- `POST /api/logout` - Logout (protected)
- `POST /api/tor-requests` - Create TOR request (protected)
- `GET /api/tor-requests` - Get user's TOR requests (protected)
- `GET /api/tor-requests/{id}` - Get single request (protected)
- `DELETE /api/tor-requests/{id}` - Delete request (protected)

### 7. Configuration

#### [bootstrap/app.php](bootstrap/app.php) - **MODIFIED**

- Added `api: __DIR__.'/../routes/api.php'` to routing configuration

#### [config/auth.php](config/auth.php) - **MODIFIED**

- Added Sanctum guard configuration for API token authentication

## Quick Start

### Installation Steps

1. **Install Dependencies**

    ```bash
    composer install
    npm install
    ```

2. **Setup Environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

3. **Create Database**

    ```bash
    php artisan migrate
    ```

4. **Build Assets**

    ```bash
    npm run build
    ```

5. **Start Server**

    ```bash
    php artisan serve
    ```

6. **Access Application**
    - Navigate to `http://localhost:8000`
    - Click "Register" to create new account
    - After login, submit TOR requests

### Testing the API

#### Register a User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Create TOR Request (with token from login)

```bash
curl -X POST http://localhost:8000/api/tor-requests \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "full_name": "John Doe",
    "birthplace": "Manila",
    "birthdate": "1995-05-15",
    "student_id": "2019-00123",
    "course": "Bachelor of Science in Information Technology",
    "degree": "Bachelor of Science",
    "year_of_graduation": 2023,
    "purpose": "Job application",
    "number_of_copies": 2
  }'
```

## Authentication Flow

1. **Registration**
    - User fills registration form
    - Form data sent to `/api/register` via Axios
    - Server creates user and returns Sanctum token
    - Token stored in localStorage
    - User redirected to TOR request form

2. **Login**
    - User enters credentials
    - Data sent to `/api/login` via Axios
    - Server validates and returns Sanctum token
    - Token stored in localStorage
    - User redirected to TOR request form

3. **API Requests**
    - axios interceptor automatically adds `Authorization: Bearer {token}` header
    - CSRF token added via middleware
    - Protected routes validate token with Sanctum guard

4. **Logout**
    - Token revoked on backend
    - Token removed from localStorage
    - User redirected to login page

## Key Features

✅ **User Authentication** - Registration, login, logout with Sanctum tokens
✅ **Token-Based API** - Secure API endpoints with Bearer tokens
✅ **CSRF Protection** - All forms protected with CSRF tokens
✅ **Form Validation** - Server and client-side validation
✅ **Responsive Design** - Works on desktop and mobile
✅ **Status Tracking** - TOR requests show current status
✅ **User Isolation** - Users can only access their own requests
✅ **Error Handling** - Comprehensive error messages and logging

## Database Schema

### users

- id (PK)
- name
- email (unique)
- email_verified_at
- password
- remember_token
- timestamps

### tor_requests

- id (PK)
- user_id (FK → users)
- full_name
- birthplace
- birthdate
- student_id (unique)
- course
- degree
- year_of_graduation
- purpose
- number_of_copies
- status (enum)
- remarks
- requested_at
- completed_at
- timestamps

### personal_access_tokens

- id (PK)
- tokenable_type / tokenable_id (polymorphic)
- name
- token (unique)
- abilities
- last_used_at
- expires_at
- timestamps

## Future Enhancements

- Admin panel for managing TOR requests
- Email notifications for status updates
- Document upload support
- Payment processing for TOR fees
- Request tracking history
- Report generation
- Search and filtering for admin
- Two-factor authentication

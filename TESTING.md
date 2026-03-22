# Online TOR Request System - Testing Checklist & User Guide

## Pre-Deployment Checklist

### Database

- [ ] Run migrations: `php artisan migrate`
- [ ] Verify personal_access_tokens table created
- [ ] Verify tor_requests table created
- [ ] Verify users table exists

### Environment Setup

- [ ] Copy `.env.example` to `.env`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Set database credentials in `.env`
- [ ] Verify `APP_URL` is set correctly

### Dependencies

- [ ] Install PHP packages: `composer install`
- [ ] Install Node packages: `npm install`
- [ ] Build assets: `npm run build`

### Application

- [ ] Start server: `php artisan serve`
- [ ] Access http://localhost:8000 in browser
- [ ] Check for any errors in console/terminal

---

## Functional Testing Guide

### 1. User Registration

**Steps:**

1. Navigate to http://localhost:8000/register
2. Fill in the form:
    - Full Name: "John Doe"
    - Email: "john@example.com"
    - Password: "password123"
    - Confirm Password: "password123"
3. Click "Create Account"

**Expected Results:**

- ✅ Form validates input
- ✅ Success message appears
- ✅ Redirects to TOR request form
- ✅ User data stored in database
- ✅ Auth token generated and stored in localStorage
- ✅ User info shows "Welcome, John Doe" at top

**Error Cases:**

- Try duplicate email - should show error
- Try short password (<8 chars) - should show error
- Try mismatched passwords - should show error

### 2. User Login

**Steps:**

1. If logged in, click "Logout"
2. Navigate to http://localhost:8000/login
3. Enter credentials:
    - Email: "john@example.com"
    - Password: "password123"
4. Click "Login"

**Expected Results:**

- ✅ Form validates input
- ✅ Success message appears
- ✅ Redirects to TOR request form
- ✅ Auth token stored in localStorage
- ✅ User name displays at top

**Error Cases:**

- Try wrong password - should show error message
- Try non-existent email - should show error message
- Try with empty fields - should show validation error

### 3. Submit TOR Request

**Steps:**

1. Logged in as user, on TOR request form
2. Fill in all required fields:
    - Full Name: "John Doe"
    - Date of Birth: "1995-05-15"
    - Place of Birth: "Manila, Philippines"
    - Student ID: "2019-00123"
    - Course: "Bachelor of Science in Information Technology"
3. Fill optional fields:
    - Degree: "Bachelor of Science"
    - Year of Graduation: "2023"
    - Purpose: "Employment application"
    - Number of Copies: "2"
4. Click "Submit TOR Request"

**Expected Results:**

- ✅ Form validates all required fields
- ✅ Success message appears: "✓ TOR request submitted successfully!"
- ✅ Form clears after submission
- ✅ Request stored in database with user_id
- ✅ Status set to "pending"

**Error Cases:**

- Try duplicate student ID - should show error
- Try invalid date (future date) - should show error
- Try non-number for copies - should show error
- Try more than 10 copies - should show error

### 4. View My Requests

**Steps:**

1. Click "View My Requests" button on form, OR
2. Navigate to http://localhost:8000/tor/requests

**Expected Results:**

- ✅ Table shows all submitted requests
- ✅ Each row shows: Student ID, Course, Copies, Status, Requested date, Actions
- ✅ Status badge displays with correct color:
    - Yellow for "Pending"
    - Blue for "Processing"
    - Green for "Approved"
    - Red for "Rejected"
- ✅ View button works on each request
- ✅ Delete button appears only for pending requests

### 5. View Request Details

**Steps:**

1. On requests list page
2. Click "View" button for any request

**Expected Results:**

- ✅ Modal popup appears
- ✅ Shows all request details:
    - Full Name
    - Date of Birth
    - Place of Birth
    - Student ID
    - Course
    - Degree
    - Year of Graduation
    - Copies Requested
    - Status
    - Requested Date
    - Remarks (if any)
- ✅ Close button (X) closes modal
- ✅ Click outside modal closes it

### 6. Delete Request

**Steps:**

1. On requests list page
2. Find a pending request
3. Click "Delete" button
4. Confirm deletion

**Expected Results:**

- ✅ Confirmation dialog appears
- ✅ Request removed from table
- ✅ Request deleted from database
- ✅ Non-pending requests have no delete button

### 7. Logout

**Steps:**

1. Click "Logout" button at top right
2. Should be on login page

**Expected Results:**

- ✅ Token removed from localStorage
- ✅ User redirected to login page
- ✅ Cannot access protected pages without login
- ✅ Going back to /tor/create redirects to login

---

## API Testing (Using curl or Postman)

### Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Expected Response (201):**

```json
{
    "message": "Registration successful",
    "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "created_at": "2024-03-20T12:00:00Z",
        "updated_at": "2024-03-20T12:00:00Z"
    },
    "token": "1|xxxxxxxxxxxx..."
}
```

### Login User

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jane@example.com",
    "password": "password123"
  }'
```

**Expected Response (200):**

```json
{
    "message": "Login successful",
    "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com"
    },
    "token": "2|xxxxxxxxxxxx..."
}
```

### Create TOR Request

```bash
curl -X POST http://localhost:8000/api/tor-requests \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 2|xxxxxxxxxxxx..." \
  -d '{
    "full_name": "Jane Smith",
    "birthplace": "Cebu",
    "birthdate": "1996-08-20",
    "student_id": "2018-00456",
    "course": "Bachelor of Arts in English",
    "degree": "Bachelor of Arts",
    "year_of_graduation": 2020,
    "purpose": "Visa application",
    "number_of_copies": 1
  }'
```

**Expected Response (201):**

```json
{
    "message": "TOR request submitted successfully",
    "request": {
        "id": 1,
        "user_id": 2,
        "full_name": "Jane Smith",
        "birthplace": "Cebu",
        "birthdate": "1996-08-20",
        "student_id": "2018-00456",
        "course": "Bachelor of Arts in English",
        "degree": "Bachelor of Arts",
        "year_of_graduation": 2020,
        "purpose": "Visa application",
        "number_of_copies": 1,
        "status": "pending",
        "remarks": null,
        "requested_at": "2024-03-20T12:00:00Z",
        "completed_at": null,
        "created_at": "2024-03-20T12:00:00Z",
        "updated_at": "2024-03-20T12:00:00Z"
    }
}
```

### Get User's TOR Requests

```bash
curl -X GET http://localhost:8000/api/tor-requests \
  -H "Authorization: Bearer 2|xxxxxxxxxxxx..."
```

**Expected Response (200):**

```json
[
    {
        "id": 1,
        "user_id": 2,
        "full_name": "Jane Smith",
        "birthplace": "Cebu",
        "birthdate": "1996-08-20",
        "student_id": "2018-00456",
        "course": "Bachelor of Arts in English",
        "degree": "Bachelor of Arts",
        "year_of_graduation": 2020,
        "purpose": "Visa application",
        "number_of_copies": 1,
        "status": "pending",
        "remarks": null,
        "requested_at": "2024-03-20T12:00:00Z",
        "completed_at": null,
        "created_at": "2024-03-20T12:00:00Z",
        "updated_at": "2024-03-20T12:00:00Z"
    }
]
```

### Get Single TOR Request

```bash
curl -X GET http://localhost:8000/api/tor-requests/1 \
  -H "Authorization: Bearer 2|xxxxxxxxxxxx..."
```

**Expected Response (200):** Same as single request object

### Delete TOR Request

```bash
curl -X DELETE http://localhost:8000/api/tor-requests/1 \
  -H "Authorization: Bearer 2|xxxxxxxxxxxx..."
```

**Expected Response (200):**

```json
{
    "message": "TOR request deleted successfully"
}
```

---

## Error Scenarios Testing

### Authentication Errors

- [ ] Test accessing protected route without token → Should redirect to login
- [ ] Test with invalid token → Should return 401 Unauthorized
- [ ] Test with expired token → Should return 401 Unauthorized
- [ ] Test login with wrong password → Should show error message
- [ ] Test registration with existing email → Should show validation error

### Validation Errors

- [ ] Test TOR request without required fields
- [ ] Test with invalid date format
- [ ] Test with duplicate student ID
- [ ] Test with invalid email format
- [ ] Test password too short

### Authorization Errors

- [ ] User A tries to delete User B's request → Should return 403 Forbidden
- [ ] User A tries to view User B's request → Should return 403 Forbidden

---

## Performance Testing

### Load Testing

- Submit 10 TOR requests in quick succession
- Verify all requests are saved correctly
- Check no duplicate entries created

### Database Testing

- [ ] Check database for orphaned records
- [ ] Verify indexes on frequently queried fields
- [ ] Check constraint violations

---

## Security Testing

- [ ] CSRF token present in all forms
- [ ] Passwords hashed in database (not plain text)
- [ ] Tokens cannot be reused after logout
- [ ] SQL injection attempts fail
- [ ] XSS payloads are escaped
- [ ] API requires valid token for protected endpoints

---

## Responsive Design Testing

Test on multiple devices/screen sizes:

- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

Verify:

- [ ] Forms are readable
- [ ] Buttons are clickable
- [ ] No horizontal scrolling
- [ ] Text is properly sized

---

## Browser Compatibility

Test on:

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

---

## Documentation Testing

- [ ] All API endpoints work as documented
- [ ] All required parameters are validated
- [ ] All response formats match documentation
- [ ] Error messages are clear and helpful

---

## Post-Deployment Checklist

- [ ] All migrations run successfully
- [ ] Assets built and served correctly
- [ ] Environment variables configured
- [ ] HTTPS enabled
- [ ] Backups configured
- [ ] Monitoring/logging enabled
- [ ] Rate limiting in place
- [ ] Database backups scheduled

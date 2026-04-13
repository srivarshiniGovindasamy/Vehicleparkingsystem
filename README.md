# Vehicle Parking System Backend

This branch contains a backend-only PHP implementation for a Vehicle Parking Management System inspired by the project report in `AJU190584.pdf`.

## Included backend features

- Admin login and logout
- Session-based authentication
- Admin profile fetch and update
- Change password
- Forgot password and token-based reset flow
- Vehicle category CRUD
- Parking slot CRUD
- Vehicle entry registration
- Vehicle exit processing with parking charge calculation
- Vehicle search by receipt, registration number, owner name, or phone number
- Dashboard statistics
- Date-range reports

## Suggested local setup

1. Import [`database/schema.sql`](/C:/Users/SRIVARSHINI%20G/Desktop/Vehicleparkingsystem/database/schema.sql).
2. Update database credentials in [`includes/config.php`](/C:/Users/SRIVARSHINI%20G/Desktop/Vehicleparkingsystem/includes/config.php).
3. Serve the project with PHP or Apache so requests go through [`index.php`](/C:/Users/SRIVARSHINI%20G/Desktop/Vehicleparkingsystem/index.php).

## API overview

All endpoints return JSON.

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `GET /api/admin/profile`
- `PUT /api/admin/profile`
- `PUT /api/admin/password`
- `GET /api/dashboard/stats`
- `GET /api/categories`
- `POST /api/categories`
- `GET /api/categories/{id}`
- `PUT /api/categories/{id}`
- `DELETE /api/categories/{id}`
- `GET /api/slots`
- `POST /api/slots`
- `GET /api/slots/{id}`
- `PUT /api/slots/{id}`
- `DELETE /api/slots/{id}`
- `GET /api/vehicles`
- `POST /api/vehicles`
- `GET /api/vehicles/{id}`
- `POST /api/vehicles/{id}/exit`
- `GET /api/vehicles/search?query=ABC123`
- `GET /api/reports/vehicles?from=2026-04-01&to=2026-04-30`

## Notes for teammates

- Frontend pages can call these endpoints directly.
- Schema includes seed data for one admin account:
  - username: `admin`
  - password: `admin123`
- Passwords are stored with PHP's `password_hash`.

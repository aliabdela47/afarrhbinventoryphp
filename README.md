# AfarRHB Inventory Management System

A secure, user-friendly inventory management system for the Afar Regional Health Bureau (AfarRHB) built with plain PHP 8, MySQL, Bootstrap 5, and Alpine.js.

## Features

- **Role-Based Access Control**: Admin, Manager, Staff, and Viewer roles
- **Bilingual UI**: English and Amharic (አማርኛ) support
- **Ethiopian Calendar**: Toggle between Gregorian and Ethiopian calendars
- **Inventory Management**: Track items with Model-19 forms
- **Request Management**: Handle requests with Model-20 forms
- **Issuance Management**: Manage issuances with Model-22 forms
- **Audit Logging**: Track all system activities
- **Dashboard**: Overview of inventory status and recent activities
- **CRUD Operations**: Manage warehouses, categories, employees, and customers

## Installation

1. Import the database schema:
   ```bash
   mysql -u root -p < init.sql
   ```

2. Configure database connection in `config.php`

3. Access the application:
   - URL: http://localhost/afarrhbinventoryphp
   - Default credentials: admin@example.com / Admin@123

## Technology Stack

- **Backend**: PHP 8.x (no frameworks)
- **Database**: MySQL with PDO
- **Frontend**: Bootstrap 5, Alpine.js, SweetAlert2
- **Icons**: Bootstrap Icons

## Security

- CSRF protection on all forms
- Prepared statements for database queries
- Password hashing with bcrypt
- Role-based access control
- File upload validation
- Audit logging

## Branch: init_afarrhb

This is the Bootstrap 5 variant of the application.

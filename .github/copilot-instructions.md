# Copilot Coding Instructions for AfarRHB Inventory

## Project Overview
AfarRHB Inventory is a secure inventory management system for the Afar Regional Health Bureau built with **plain PHP 8 + MySQL**. No frameworks, no Composer dependencies.

## Tech Stack

### Backend
- **PHP 8.x** (plain PHP only, no frameworks)
- **MySQL** with PDO and prepared statements exclusively
- No Composer or package managers

### Frontend (Branch: init_afarrhb)
- Bootstrap 5 (CDN)
- Alpine.js (CDN)
- SweetAlert2 (CDN)
- Bootstrap Icons

### Frontend (Branch: init_afarrhb_tailwind)
- Tailwind CSS (Play CDN)
- Flowbite
- Alpine.js (CDN)
- SweetAlert2 (CDN)
- Heroicons

### Reports
- FPDF (bundled in the repository) for PDF generation
- HTML/CSV for Excel exports

## Core Features

### Authentication & Authorization
- Role-based access control (RBAC) with roles: admin, manager, staff, viewer
- Session-based authentication
- Password hashing with `password_hash()` and `password_verify()`
- CSRF token protection on all POST forms
- Secure session configuration

### Internationalization
- Bilingual UI: English and Amharic
- Language files stored in `lang/` directory
- Helper function `t($key)` for translations
- Language toggle in header

### Ethiopian Calendar Support
- Toggle between Gregorian and Ethiopian calendars
- PHP-based conversion using Julian Day Number (JDN)
- JavaScript conversion for client-side display
- User preference stored in session

### Audit Logging
- All create/update/delete operations logged
- AUDITLOG table tracks: user, action, table, record_id, old_value, new_value, timestamp, ip_address

### File Management
- Document attachments for items
- File upload validation (type and size)
- Sanitized filenames to prevent security issues
- Files stored in `uploads/` directory (outside web root if possible)

### Reporting
- PDF reports using FPDF library
- Excel exports (HTML tables or CSV)
- Printable layouts for Model-19, Model-20, Model-22 forms

### Search
- Global search across: items, requests, issuances, customers
- Search functionality in header

### Notifications
- SweetAlert2 for flash messages, confirmations, and alerts
- Session-based flash messages

## Database Schema

### Required Tables
All tables must be defined in `init.sql`:

1. **USERS**
   - id, username, email, password_hash, full_name, role, is_active, created_at, updated_at

2. **WAREHOUSES**
   - id, name, location, manager_id, is_active, created_at, updated_at

3. **CATEGORIES**
   - id, name, description, parent_id, is_active, created_at, updated_at

4. **EMPLIST**
   - id, employee_code, full_name, department, position, phone, email, is_active, created_at, updated_at

5. **ITEMS**
   - id, item_code, name, description, category_id, unit, reorder_level, warehouse_id, current_stock, unit_price, is_active, created_at, updated_at

6. **ITEMDOCUMENTS**
   - id, item_id, document_name, file_path, file_type, file_size, uploaded_by, uploaded_at

7. **CUSTOMERS**
   - id, customer_code, name, contact_person, phone, email, address, is_active, created_at, updated_at

8. **REQUESTS**
   - id, request_number, requester_id, department, request_date, purpose, status, approved_by, approved_at, notes, created_at, updated_at

9. **REQUESTITEMS**
   - id, request_id, item_id, requested_quantity, approved_quantity, notes

10. **ISSUANCES**
    - id, issuance_number, request_id, issued_to, issued_by, issue_date, receiver_signature, issuer_signature, notes, created_at, updated_at

11. **ISSUANCEITEMS**
    - id, issuance_id, item_id, quantity, unit_price, notes

12. **ITEMMOVEMENTS**
    - id, item_id, movement_type (IN/OUT), quantity, reference_type, reference_id, warehouse_id, moved_by, movement_date, notes, created_at

13. **AUDITLOG**
    - id, user_id, action, table_name, record_id, old_value, new_value, ip_address, created_at

### Seed Data
Include in `init.sql`:
- Admin user: email `admin@example.com`, password `Admin@123` (hashed)
- Sample categories: Medical Supplies, Office Supplies, Equipment, etc.
- Sample warehouses: Main Warehouse, Regional Office, etc.

## Coding Standards

### Security Requirements
1. **Database Access**: All queries MUST use PDO prepared statements
   ```php
   $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   ```

2. **CSRF Protection**: All POST forms must include CSRF tokens
   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // In form
   <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
   
   // Validate
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       die('CSRF token validation failed');
   }
   ```

3. **Password Security**: Use `password_hash()` with PASSWORD_DEFAULT
   ```php
   $hash = password_hash($password, PASSWORD_DEFAULT);
   if (password_verify($password, $hash)) { /* valid */ }
   ```

4. **Output Escaping**: All output MUST be escaped with `e()` helper
   ```php
   function e($string) {
       return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
   }
   ```

5. **File Upload Validation**:
   - Check file type against whitelist
   - Validate file size
   - Sanitize filename: `$safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);`
   - Store with random name or outside web root

### Code Organization
```
/
├── config/
│   ├── database.php       # Database connection
│   └── config.php         # Application configuration
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── helpers.php        # Helper functions (e, t, redirect, flash, etc.)
│   ├── header.php         # Page header
│   ├── sidebar.php        # Navigation sidebar
│   └── footer.php         # Page footer
├── lang/
│   ├── en.php             # English translations
│   └── am.php             # Amharic translations
├── lib/
│   └── fpdf/              # FPDF library
├── pages/
│   ├── dashboard.php      # Dashboard
│   ├── warehouses/        # Warehouse CRUD
│   ├── categories/        # Category CRUD
│   ├── items/             # Item CRUD
│   ├── requests/          # Request management
│   ├── issuances/         # Issuance management
│   ├── customers/         # Customer CRUD
│   ├── employees/         # Employee CRUD
│   └── reports/           # Report generation
├── uploads/               # Uploaded files
├── assets/
│   ├── css/               # Custom CSS
│   └── js/                # Custom JavaScript
├── init.sql               # Database initialization
├── index.php              # Login page
├── login.php              # Login handler
├── logout.php             # Logout handler
└── .htaccess              # Apache configuration
```

### Naming Conventions
- Files: lowercase with underscores (e.g., `item_list.php`)
- Functions: camelCase (e.g., `getUserById()`)
- Variables: camelCase (e.g., `$userName`)
- Constants: UPPERCASE (e.g., `DB_HOST`)
- Database tables: UPPERCASE (e.g., `USERS`)
- Database columns: lowercase with underscores (e.g., `user_name`)

### Response Design
- All pages must be responsive and mobile-friendly
- Use Bootstrap grid system or Tailwind responsive classes
- Test on mobile, tablet, and desktop viewports

## Layout Structure

### Header
Must include:
- Brand/logo (AfarRHB Inventory)
- Global search input
- Language toggle (EN/አማ)
- Calendar toggle (Gregorian/Ethiopian)
- Dark/light mode toggle
- Notifications bell with badge
- User menu (profile, settings, logout)

### Sidebar
Must include:
- Collapsible navigation
- Role-aware menu items (show/hide based on user role)
- Icons for each menu item
- Active state highlighting
- Main sections:
  - Dashboard
  - Inventory (Items, Categories, Warehouses)
  - Requests
  - Issuances
  - Customers
  - Employees
  - Reports
  - Settings (admin only)
  - Audit Logs (admin only)

### Footer
- Compact design
- Application version
- Copyright notice
- Quick links (if any)

### Dashboard
Must include:
- Metric cards: Total Items, Low Stock Items, Pending Requests, Recent Issuances
- Quick action buttons: New Request, Issue Items, Add Item
- Recent activity feed: Latest requests, issuances, and stock movements
- Charts (optional): Stock levels, monthly issuances

## Branching Strategy
- `init_afarrhb`: Bootstrap 5 + Alpine.js version
- `init_afarrhb_tailwind`: Tailwind CSS + Flowbite version
- Both branches MUST share identical backend logic (all PHP files)
- Only frontend templates and CSS differ between branches

## Development Workflow

### Definition of Done
Before marking any feature complete, verify:
- [ ] Application runs locally with PHP 8 + MySQL
- [ ] Login works with `admin@example.com` / `Admin@123` after importing `init.sql`
- [ ] CRUD operations work for warehouses, categories, employees, customers
- [ ] Model-19/20/22 forms and lists function correctly
- [ ] Calendar toggle works; Ethiopian dates convert to Gregorian for storage
- [ ] Language toggle updates all UI text
- [ ] Audit logs record all create/update/delete actions
- [ ] PDF exports generate correctly
- [ ] Excel exports download properly
- [ ] File uploads attach and display correctly
- [ ] RBAC enforced (users cannot access unauthorized pages)
- [ ] All database access uses PDO prepared statements
- [ ] All forms include CSRF protection
- [ ] All output is escaped with `e()`
- [ ] Code is plain PHP with no framework dependencies

### Testing Checklist
1. Install XAMPP/WAMP with PHP 8+
2. Import `init.sql` into MySQL
3. Update `config/database.php` with local credentials
4. Access application at `http://localhost/afarrhbinventoryphp`
5. Login with admin credentials
6. Test each CRUD module
7. Test reports and exports
8. Test file uploads
9. Test calendar and language toggles
10. Verify audit logs are created

## Common Patterns

### Database Connection
```php
// config/database.php
$host = 'localhost';
$dbname = 'afarrhb_inventory';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

### Authentication Check
```php
// includes/auth.php
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('index.php');
    }
}

function requireRole($roles) {
    requireAuth();
    if (!in_array($_SESSION['user_role'], $roles)) {
        flash('error', t('access_denied'));
        redirect('dashboard.php');
    }
}
```

### Translation Helper
```php
// includes/helpers.php
function t($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

// Load translations
$lang = $_SESSION['lang'] ?? 'en';
$translations = require "lang/{$lang}.php";
```

### Flash Messages
```php
// Set flash
function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Display flash
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    // Show SweetAlert2
}
```

### Ethiopian Calendar Conversion
```php
// Convert Ethiopian to Gregorian using JDN
function ethiopianToGregorian($ethYear, $ethMonth, $ethDay) {
    // JDN calculation
    $jdn = ethToJDN($ethYear, $ethMonth, $ethDay);
    return jdnToGregorian($jdn);
}
```

## Important Notes

1. **No Composer**: All dependencies must be bundled or loaded via CDN
2. **Security First**: Always sanitize input, escape output, use prepared statements
3. **Consistency**: Both Bootstrap and Tailwind branches must have identical functionality
4. **Plain PHP**: No frameworks like Laravel, CodeIgniter, or Symfony
5. **Modern PHP**: Use PHP 8 features but maintain compatibility
6. **User Experience**: Fast, responsive, intuitive UI with proper feedback
7. **Data Integrity**: Validate all input, use transactions where needed
8. **Audit Trail**: Log all important actions for accountability

## Error Handling
- Development: Display detailed errors
- Production: Log errors to file, show user-friendly messages
- Use try-catch blocks for database operations
- Always validate user input before processing

## Performance
- Use indexes on frequently queried columns
- Limit result sets with pagination
- Cache translations in session
- Optimize database queries
- Minimize CDN requests

When working on this project, always prioritize security, data integrity, and user experience. Follow these guidelines strictly to maintain code quality and consistency across the application.

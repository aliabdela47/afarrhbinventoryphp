# AfarRHB Inventory Management System

A secure, user-friendly inventory management system for the Afar Regional Health Bureau (AfarRHB) built with **plain PHP 8 + MySQL**.

## 🚀 Features

- **Role-Based Access Control (RBAC)**: Admin, Manager, Staff, and Viewer roles
- **Bilingual Support**: English and Amharic (አማርኛ) with easy language toggle
- **Ethiopian Calendar**: Toggle between Gregorian and Ethiopian calendars
- **Comprehensive Inventory Management**: Items, categories, warehouses, stock movements
- **Request & Issuance System**: Model-19, Model-20, Model-22 forms
- **Customer & Employee Management**: Complete CRUD operations
- **Audit Logging**: Track all create/update/delete operations
- **File Attachments**: Upload and manage documents for items
- **Reports**: PDF and Excel exports using FPDF
- **Global Search**: Search across items, requests, issuances, and customers
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Security First**: CSRF protection, password hashing, prepared statements

## 🛠️ Tech Stack

### Backend
- PHP 8.x (plain PHP, no frameworks)
- MySQL with PDO
- No Composer dependencies

### Frontend (Bootstrap Branch)
- Bootstrap 5 (CDN)
- Alpine.js (CDN)
- SweetAlert2 (CDN)
- Bootstrap Icons

### Frontend (Tailwind Branch)
- Tailwind CSS Play CDN
- Flowbite
- Alpine.js (CDN)
- SweetAlert2 (CDN)
- Heroicons

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, mbstring

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/aliabdela47/afarrhbinventoryphp.git
cd afarrhbinventoryphp
```

### 2. Database Setup

Import the database schema and seed data:

```bash
mysql -u root -p < init.sql
```

Or using phpMyAdmin:
1. Create a new database named `afarrhb_inventory`
2. Import the `init.sql` file

### 3. Configure Database Connection

Edit `config/database.php` and update your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'afarrhb_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Set Permissions

Ensure the `uploads/` directory is writable:

```bash
chmod -R 755 uploads/
```

### 5. Access the Application

Open your browser and navigate to:
```
http://localhost/afarrhbinventoryphp
```

## 🔐 Default Credentials

After importing `init.sql`, you can login with:

- **Admin**: admin@example.com / Admin@123
- **Manager**: manager@example.com / Admin@123
- **Staff**: staff@example.com / Admin@123
- **Viewer**: viewer@example.com / Admin@123

**⚠️ IMPORTANT**: Change these passwords immediately in production!

## 📁 Project Structure

```
afarrhbinventoryphp/
├── .github/
│   └── copilot-instructions.md    # Copilot coding guidelines
├── config/
│   ├── config.php                 # Application configuration
│   └── database.php               # Database connection
├── includes/
│   ├── auth.php                   # Authentication functions
│   ├── helpers.php                # Helper functions
│   ├── header.php                 # Page header
│   ├── sidebar.php                # Navigation sidebar
│   └── footer.php                 # Page footer
├── lang/
│   ├── en.php                     # English translations
│   └── am.php                     # Amharic translations
├── lib/
│   └── fpdf/                      # FPDF library (to be added)
├── pages/
│   ├── items/                     # Item management
│   ├── categories/                # Category management
│   ├── warehouses/                # Warehouse management
│   ├── requests/                  # Request management
│   ├── issuances/                 # Issuance management
│   ├── customers/                 # Customer management
│   ├── employees/                 # Employee management
│   └── reports/                   # Report generation
├── uploads/                       # Uploaded files
├── assets/
│   ├── css/                       # Custom CSS
│   └── js/                        # Custom JavaScript
├── init.sql                       # Database initialization
├── index.php                      # Login page
├── login.php                      # Login handler
├── logout.php                     # Logout handler
├── dashboard.php                  # Dashboard
└── README.md                      # This file
```

## 🔒 Security Features

- **PDO Prepared Statements**: All database queries use prepared statements
- **CSRF Protection**: All POST forms include CSRF tokens
- **Password Hashing**: Passwords are hashed using `password_hash()`
- **Output Escaping**: All output is escaped with `htmlspecialchars()`
- **File Upload Validation**: Type and size validation for uploads
- **Session Security**: Secure session configuration
- **Role-Based Access**: Pages protected by role requirements
- **Audit Logging**: All important actions are logged

## 🌍 Internationalization

The application supports English and Amharic:

- Language files: `lang/en.php` and `lang/am.php`
- Translation function: `t('key')`
- Language toggle in header
- Session-based language preference

## 📅 Ethiopian Calendar

- Toggle between Gregorian and Ethiopian calendars
- PHP conversion using Julian Day Number (JDN)
- JavaScript conversion for client-side display
- Ethiopian dates stored as Gregorian in database

## 📊 Database Schema

The system includes the following tables:

- **USERS**: User accounts and authentication
- **WAREHOUSES**: Warehouse locations
- **CATEGORIES**: Item categories (hierarchical)
- **EMPLIST**: Employee list
- **ITEMS**: Inventory items
- **ITEMDOCUMENTS**: File attachments for items
- **CUSTOMERS**: Customer information
- **REQUESTS**: Material requests
- **REQUESTITEMS**: Request line items
- **ISSUANCES**: Material issuances
- **ISSUANCEITEMS**: Issuance line items
- **ITEMMOVEMENTS**: Stock movement history
- **AUDITLOG**: Audit trail

## 🔄 Branching Strategy

- **`init_afarrhb`**: Bootstrap 5 + Alpine.js version
- **`init_afarrhb_tailwind`**: Tailwind CSS + Flowbite version

Both branches share identical backend logic. Only frontend templates differ.

## 🧪 Testing

1. Import `init.sql` to set up test data
2. Login with default credentials
3. Test each module:
   - ✅ Items CRUD
   - ✅ Categories CRUD
   - ✅ Warehouses CRUD
   - ✅ Requests workflow
   - ✅ Issuances workflow
   - ✅ Reports generation
   - ✅ File uploads
   - ✅ Language toggle
   - ✅ Calendar toggle

## 📝 Development Guidelines

When contributing to this project, please follow these guidelines:

1. **Security First**: Always use prepared statements, escape output, validate input
2. **No Frameworks**: Keep it plain PHP, no frameworks or Composer
3. **Consistent Styling**: Follow existing code style
4. **Document Changes**: Update README and comments
5. **Test Thoroughly**: Test all changes before committing
6. **Bilingual**: All UI text must be in language files
7. **Responsive**: All pages must work on mobile devices

For detailed coding guidelines, see `.github/copilot-instructions.md`.

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Follow coding guidelines
4. Test your changes
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 👥 Authors

- **AfarRHB Development Team**

## 🙏 Acknowledgments

- Afar Regional Health Bureau
- Bootstrap Team
- Alpine.js Team
- FPDF Library

## 📞 Support

For support, please contact: support@afarrhb.gov.et

---

**Version**: 1.0.0  
**Last Updated**: 2024-10-24

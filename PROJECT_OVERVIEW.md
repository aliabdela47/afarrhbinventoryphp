# AfarRHB Inventory - Project Overview

## 📋 Project Information

- **Name**: AfarRHB Inventory Management System
- **Version**: 1.0.0
- **Type**: Plain PHP Web Application
- **License**: MIT
- **Organization**: Afar Regional Health Bureau

## 🎯 Purpose

A secure, bilingual inventory management system designed for the Afar Regional Health Bureau to manage medical supplies, equipment, and materials across multiple warehouses with Ethiopian calendar support.

## 🏗️ Architecture

### Technology Stack

#### Backend
- **PHP**: 8.0+ (plain PHP, no frameworks)
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **PDO**: For database access with prepared statements
- **Session Management**: Secure session handling

#### Frontend (Bootstrap Version)
- **Bootstrap 5**: Responsive UI framework (CDN)
- **Alpine.js**: Lightweight JavaScript framework (CDN)
- **SweetAlert2**: Beautiful alerts and notifications (CDN)
- **Bootstrap Icons**: Icon library (CDN)

#### Frontend (Tailwind Version)
- **Tailwind CSS**: Utility-first CSS framework (Play CDN)
- **Flowbite**: Component library
- **Alpine.js**: Lightweight JavaScript framework (CDN)
- **SweetAlert2**: Beautiful alerts and notifications (CDN)
- **Heroicons**: Icon library

### Project Structure

```
afarrhbinventoryphp/
│
├── 📁 .github/               # GitHub configuration
│   └── copilot-instructions.md   # Copilot coding guidelines
│
├── 📁 api/                   # API endpoints
│   ├── change-calendar.php       # Calendar toggle
│   └── change-language.php       # Language toggle
│
├── 📁 assets/                # Static assets
│   ├── css/
│   │   └── custom.css           # Custom styles
│   └── js/
│       └── ethiopian-calendar.js # Calendar utilities
│
├── 📁 config/                # Configuration files
│   ├── config.php               # Application config
│   └── database.php             # Database connection
│
├── 📁 includes/              # Reusable components
│   ├── auth.php                 # Authentication functions
│   ├── footer.php               # Page footer
│   ├── header.php               # Page header
│   ├── helpers.php              # Helper functions
│   └── sidebar.php              # Navigation sidebar
│
├── 📁 lang/                  # Language files
│   ├── am.php                   # Amharic translations
│   └── en.php                   # English translations
│
├── 📁 lib/                   # Third-party libraries
│   └── fpdf/                    # PDF generation (to be added)
│
├── 📁 pages/                 # Application pages
│   ├── categories/              # Category management
│   ├── customers/               # Customer management
│   ├── employees/               # Employee management
│   ├── issuances/               # Issuance management
│   ├── items/                   # Item management
│   ├── reports/                 # Report generation
│   ├── requests/                # Request management
│   └── warehouses/              # Warehouse management
│       └── list.php             # Example CRUD page
│
├── 📁 uploads/               # User uploaded files
│   └── .gitkeep                 # Keep directory in git
│
├── 📄 .gitignore             # Git ignore rules
├── 📄 .htaccess              # Apache configuration
├── 📄 CONTRIBUTING.md        # Contribution guidelines
├── 📄 LICENSE                # MIT license
├── 📄 QUICK_REFERENCE.md     # Code snippets reference
├── 📄 README.md              # Project documentation
├── 📄 SETUP.md               # Installation guide
├── 📄 dashboard.php          # Dashboard page
├── 📄 index.php              # Login page
├── 📄 init.sql               # Database schema & seed data
├── 📄 login.php              # Login handler
├── 📄 logout.php             # Logout handler
└── 📄 validate-schema.php    # Schema validation tool
```

## 💾 Database Schema

### Tables (13 total)

1. **USERS** - User accounts and authentication
2. **WAREHOUSES** - Warehouse/storage locations
3. **CATEGORIES** - Item categories (hierarchical)
4. **EMPLIST** - Employee directory
5. **ITEMS** - Inventory items
6. **ITEMDOCUMENTS** - File attachments for items
7. **CUSTOMERS** - Customer information
8. **REQUESTS** - Material requests (Model-19)
9. **REQUESTITEMS** - Request line items
10. **ISSUANCES** - Material issuances (Model-20/22)
11. **ISSUANCEITEMS** - Issuance line items
12. **ITEMMOVEMENTS** - Stock movement history
13. **AUDITLOG** - Audit trail for all operations

### Relationships

```
USERS ──┬─ manages ──→ WAREHOUSES
        ├─ approves ─→ REQUESTS
        ├─ issues ───→ ISSUANCES
        └─ creates ──→ AUDITLOG

ITEMS ──┬─ belongs to ─→ CATEGORIES
        ├─ stored in ─→ WAREHOUSES
        ├─ has ───────→ ITEMDOCUMENTS
        ├─ in ────────→ REQUESTITEMS
        ├─ in ────────→ ISSUANCEITEMS
        └─ tracks ────→ ITEMMOVEMENTS

REQUESTS ─┬─ requested by ─→ EMPLIST
          ├─ contains ─────→ REQUESTITEMS
          └─ leads to ─────→ ISSUANCES

ISSUANCES ─┬─ issued to ──→ EMPLIST
           └─ contains ───→ ISSUANCEITEMS
```

## 🔒 Security Features

### Authentication & Authorization
- Session-based authentication
- Password hashing with `password_hash()`
- Role-based access control (Admin, Manager, Staff, Viewer)
- Session timeout and security settings

### Data Protection
- All database queries use PDO prepared statements
- CSRF token protection on all POST forms
- Output escaping with `htmlspecialchars()`
- Input validation and sanitization
- SQL injection prevention

### File Security
- File type validation
- File size limits (5MB)
- Sanitized filenames
- Secure upload handling

### Audit Trail
- All create/update/delete operations logged
- User ID, action, table, old/new values tracked
- IP address logging
- Timestamp for all actions

## 🌍 Internationalization

### Languages Supported
- English (EN)
- Amharic (አማርኛ)

### Features
- 150+ translation keys
- Session-based language preference
- Dynamic language switching
- `t()` helper function for all text
- Both languages fully translated

## 📅 Ethiopian Calendar

### Features
- Toggle between Gregorian and Ethiopian calendars
- PHP conversion functions using Julian Day Number
- JavaScript conversion for client-side display
- 13-month Ethiopian calendar support
- Month names in English and Amharic
- Bidirectional conversion

### Usage
```php
// PHP
$ethDate = gregorianToEthiopian(2024, 10, 24);
// Returns: ['year' => 2017, 'month' => 2, 'day' => 14]

// JavaScript
const ethDate = gregorianToEthiopian(2024, 10, 24);
// Returns: { year: 2017, month: 2, day: 14 }
```

## 👥 User Roles & Permissions

### Admin
- Full system access
- User management
- System settings
- View audit logs
- All CRUD operations

### Manager
- Warehouse management
- Approve/reject requests
- Issue materials
- View reports
- Limited user management

### Staff
- Create requests
- View inventory
- Update stock levels
- Basic operations

### Viewer
- Read-only access
- View inventory
- View reports
- No modifications

## 📊 Core Features

### Inventory Management
- Item catalog with categories
- Multi-warehouse support
- Stock level tracking
- Reorder level alerts
- Low stock notifications

### Request & Issuance
- Material request workflow (Model-19)
- Approval process
- Material issuance (Model-20/22)
- Digital signatures
- Request-to-issuance tracking

### Document Management
- File attachments for items
- Multiple file types supported
- Secure file storage
- File metadata tracking

### Reporting
- Stock reports
- Movement reports
- Request reports
- Issuance reports
- PDF generation (FPDF)
- Excel export (HTML/CSV)

### Dashboard
- Real-time metrics
- Low stock alerts
- Pending requests
- Recent activity feed
- Quick action buttons

## 🎨 User Interface

### Layout Components
- **Header**: Brand, search, language toggle, calendar toggle, notifications, user menu
- **Sidebar**: Role-aware navigation, collapsible menu, active state
- **Footer**: Version info, copyright
- **Dashboard**: Metric cards, quick actions, activity feed

### Responsive Design
- Mobile-first approach
- Bootstrap grid system
- Responsive tables
- Mobile-friendly navigation
- Touch-friendly interface

### User Experience
- SweetAlert2 for notifications
- Loading indicators
- Form validation
- Confirmation dialogs
- Intuitive navigation

## 🔧 Development Tools

### Validation
- `validate-schema.php` - Database schema validator
- PHP syntax checking
- Security scanning ready

### Documentation
- Comprehensive inline comments
- API documentation in code
- Quick reference guide
- Contributing guidelines

## 📈 Performance

### Optimization
- Minimal database queries
- Pagination for large datasets
- Indexed database columns
- Session caching for translations
- CDN for static assets

### Caching
- OpCache for PHP (recommended)
- Browser caching for static files
- Query result caching (planned)

## 🔄 Branching Strategy

### Main Branches
- `main` - Stable production code
- `init_afarrhb` - Bootstrap 5 version
- `init_afarrhb_tailwind` - Tailwind CSS version

### Feature Development
- `feature/feature-name` - New features
- `fix/bug-name` - Bug fixes
- `docs/doc-name` - Documentation updates

## 📝 Code Standards

### Naming Conventions
- Files: `lowercase_with_underscores.php`
- Functions: `camelCase()`
- Variables: `$camelCase`
- Constants: `UPPERCASE_WITH_UNDERSCORES`
- Tables: `UPPERCASE`
- Columns: `lowercase_with_underscores`

### Code Style
- PSR-12 compatible
- 4-space indentation
- Clear variable names
- Comprehensive comments
- Security-first approach

## 🚀 Quick Start

1. Clone repository
2. Import `init.sql` to MySQL
3. Configure `config/database.php`
4. Set `uploads/` permissions (755)
5. Access http://localhost/afarrhbinventoryphp
6. Login: admin@example.com / Admin@123

## 📚 Documentation

- **README.md** - Project overview and setup
- **SETUP.md** - Detailed installation guide
- **CONTRIBUTING.md** - Development guidelines
- **QUICK_REFERENCE.md** - Code snippets and patterns
- **.github/copilot-instructions.md** - Copilot guidelines

## 🤝 Contributing

We welcome contributions! Please:
1. Read CONTRIBUTING.md
2. Follow coding standards
3. Write tests (when applicable)
4. Submit pull requests

## 📞 Support

- **Documentation**: See README.md and guides
- **Issues**: GitHub Issues
- **Email**: support@afarrhb.gov.et

## 📄 License

MIT License - See LICENSE file

---

**Built with ❤️ for Afar Regional Health Bureau**

Version 1.0.0 | Last Updated: 2024-10-24

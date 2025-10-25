# Contributing to AfarRHB Inventory

Thank you for your interest in contributing to the AfarRHB Inventory Management System! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help maintain a welcoming environment
- Follow the project's coding standards

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:

1. **Clear title**: Describe the bug in one sentence
2. **Steps to reproduce**: Detailed steps to reproduce the issue
3. **Expected behavior**: What should happen
4. **Actual behavior**: What actually happens
5. **Environment**: PHP version, MySQL version, OS
6. **Screenshots**: If applicable

### Suggesting Features

For feature requests:

1. Check if the feature already exists or is planned
2. Create an issue with "Feature Request" label
3. Describe the feature and its benefits
4. Provide use cases and examples

### Contributing Code

1. **Fork the repository**
   ```bash
   git clone https://github.com/YOUR-USERNAME/afarrhbinventoryphp.git
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes** following the coding standards below

4. **Test your changes** thoroughly

5. **Commit your changes**
   ```bash
   git add .
   git commit -m "Add: Brief description of changes"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Create a Pull Request** from your fork to the main repository

## Coding Standards

### PHP Standards

1. **Use PHP 8 syntax** but maintain compatibility
2. **Follow PSR-12** coding style where applicable
3. **Use meaningful variable names** (camelCase)
4. **Add comments** for complex logic
5. **No frameworks**: Keep it plain PHP

### Security Requirements

**MANDATORY** - All code must follow these security practices:

1. **Database Access**
   ```php
   // ✅ CORRECT - Use prepared statements
   $stmt = $pdo->prepare("SELECT * FROM USERS WHERE id = ?");
   $stmt->execute([$id]);
   
   // ❌ WRONG - Never concatenate SQL
   $query = "SELECT * FROM USERS WHERE id = $id";
   ```

2. **Output Escaping**
   ```php
   // ✅ CORRECT - Escape all output
   echo e($user['name']);
   
   // ❌ WRONG - Direct output
   echo $user['name'];
   ```

3. **CSRF Protection**
   ```php
   // ✅ CORRECT - Include CSRF token in forms
   <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
   
   // Validate on submission
   if (!verifyCsrfToken($_POST['csrf_token'])) {
       die('CSRF validation failed');
   }
   ```

4. **Password Handling**
   ```php
   // ✅ CORRECT - Hash passwords
   $hash = password_hash($password, PASSWORD_DEFAULT);
   
   // Verify passwords
   if (password_verify($password, $hash)) {
       // Valid
   }
   ```

5. **File Uploads**
   ```php
   // ✅ CORRECT - Validate file uploads
   $errors = validateFileUpload($_FILES['file']);
   $safeName = sanitizeFilename($_FILES['file']['name']);
   ```

### File Organization

```
feature-name/
├── pages/
│   └── feature/
│       ├── list.php      # List view
│       ├── create.php    # Create form
│       ├── edit.php      # Edit form
│       ├── view.php      # Detail view
│       └── delete.php    # Delete handler
```

### Naming Conventions

- **Files**: lowercase_with_underscores.php
- **Functions**: camelCase()
- **Variables**: $camelCase
- **Constants**: UPPERCASE_WITH_UNDERSCORES
- **Classes**: PascalCase (if needed)
- **Database Tables**: UPPERCASE
- **Database Columns**: lowercase_with_underscores

### Code Structure

Every page should follow this structure:

```php
<?php
/**
 * Page Title
 * AfarRHB Inventory Management System
 */

// 1. Include required files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';

// 2. Authentication check
requireAuth();
// or: requireRole(['admin', 'manager']);

// 3. Set page title
$pageTitle = t('page_title') . ' - ' . APP_NAME;

// 4. Handle form submission (if POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        flash('error', 'Invalid request');
        redirect('list.php');
    }
    
    // Process form
    // ...
}

// 5. Fetch data (if needed)
try {
    $stmt = $pdo->prepare("SELECT * FROM TABLE WHERE condition = ?");
    $stmt->execute([$param]);
    $data = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $data = [];
}

// 6. Include header and sidebar
include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<!-- 7. HTML content -->
<main class="main-content" id="mainContent">
    <!-- Your content here -->
</main>

<?php 
// 8. Include footer
include '../../includes/footer.php'; 
?>
```

### Translation

All user-facing text must be translatable:

```php
// ✅ CORRECT
echo e(t('welcome_message'));

// ❌ WRONG
echo "Welcome to the system";
```

Add translations to both `lang/en.php` and `lang/am.php`:

```php
// lang/en.php
'welcome_message' => 'Welcome to the system',

// lang/am.php
'welcome_message' => 'ወደ ስርዓቱ እንኳን በደህና መጡ',
```

### Database Migrations

When adding new tables or columns:

1. Update `init.sql` with the new schema
2. Document the changes in your PR
3. Provide migration SQL for existing installations

Example:
```sql
-- Add new column to existing table
ALTER TABLE USERS ADD COLUMN phone VARCHAR(20) AFTER email;
```

### Testing

Before submitting a PR, test:

1. **Functionality**: Feature works as expected
2. **Security**: No SQL injection, XSS vulnerabilities
3. **Permissions**: RBAC works correctly
4. **Responsive**: Works on mobile devices
5. **Languages**: Works in both English and Amharic
6. **Calendar**: Works with both Gregorian and Ethiopian
7. **Error Handling**: Graceful error messages
8. **Audit Logs**: Actions are logged correctly

### Documentation

Update documentation when:

- Adding new features
- Changing existing features
- Adding configuration options
- Changing database schema

## Pull Request Guidelines

### PR Title Format

```
[Type] Brief description

Types:
- Feature: New feature
- Fix: Bug fix
- Refactor: Code refactoring
- Docs: Documentation update
- Style: Code style changes
- Security: Security improvements
```

### PR Description

Include:

1. **What**: What does this PR do?
2. **Why**: Why is this change needed?
3. **How**: How does it work?
4. **Testing**: How was it tested?
5. **Screenshots**: If UI changes

### PR Checklist

- [ ] Code follows project standards
- [ ] Security best practices followed
- [ ] All database queries use prepared statements
- [ ] All output is escaped
- [ ] CSRF tokens added to forms
- [ ] Translations added to both language files
- [ ] Works with both calendar types
- [ ] Tested on desktop and mobile
- [ ] No console errors
- [ ] Documentation updated
- [ ] Audit logging implemented (if applicable)

## Development Setup

### Local Development

1. Use XAMPP or similar local server
2. Enable error reporting:
   ```php
   define('DEV_MODE', true);
   ```
3. Check error logs regularly
4. Use browser DevTools for debugging

### Code Style

Use consistent indentation (4 spaces) and formatting:

```php
// ✅ CORRECT
function getUserById($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM USERS WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return null;
    }
}

// ❌ WRONG - Inconsistent indentation
function getUserById($id){
  global $pdo;
  try{
  $stmt=$pdo->prepare("SELECT * FROM USERS WHERE id = ?");
  $stmt->execute([$id]);
  return $stmt->fetch();
  }catch(PDOException $e){
  return null;
  }
}
```

## Branch Strategy

- `main`: Stable production code
- `init_afarrhb`: Bootstrap 5 version (active development)
- `init_afarrhb_tailwind`: Tailwind CSS version
- Feature branches: `feature/feature-name`
- Bug fixes: `fix/bug-description`

## Questions?

If you have questions about contributing:

1. Check existing issues and documentation
2. Ask in issue comments
3. Email: dev@afarrhb.gov.et

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to AfarRHB Inventory! 🎉

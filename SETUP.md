# AfarRHB Inventory - Setup and Configuration Guide

This guide will help you set up the AfarRHB Inventory Management System on your local development environment or production server.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0 or higher** with the following extensions:
  - PDO
  - PDO_MySQL
  - mbstring
  - fileinfo
  - json
  
- **MySQL 5.7+ or MariaDB 10.2+**

- **Web Server**: Apache 2.4+ (with mod_rewrite) or Nginx

- **Recommended**: phpMyAdmin for database management

## Installation Steps

### 1. Download or Clone the Repository

```bash
git clone https://github.com/aliabdela47/afarrhbinventoryphp.git
cd afarrhbinventoryphp
```

### 2. Database Setup

#### Option A: Using MySQL Command Line

```bash
# Login to MySQL
mysql -u root -p

# Create the database and import the schema
mysql -u root -p < init.sql
```

#### Option B: Using phpMyAdmin

1. Open phpMyAdmin in your browser
2. Click "New" to create a database
3. Name it `afarrhb_inventory`
4. Select "UTF-8 Unicode (utf8mb4)" as the collation
5. Click "Create"
6. Go to the "Import" tab
7. Choose the `init.sql` file
8. Click "Go"

### 3. Configure Database Connection

Edit the file `config/database.php`:

```php
define('DB_HOST', 'localhost');      // Your database host
define('DB_NAME', 'afarrhb_inventory'); // Your database name
define('DB_USER', 'root');           // Your database username
define('DB_PASS', '');               // Your database password
```

### 4. Configure Application Settings

Edit the file `config/config.php`:

```php
// Update the APP_URL to match your installation
define('APP_URL', 'http://localhost/afarrhbinventoryphp');

// Set DEV_MODE to false in production
define('DEV_MODE', false);
```

### 5. Set Directory Permissions

Ensure the `uploads/` directory is writable by the web server:

```bash
# On Linux/Mac
chmod -R 755 uploads/
chown -R www-data:www-data uploads/

# On Windows (using XAMPP)
# Right-click uploads folder → Properties → Security → 
# Give full control to Users
```

### 6. Apache Configuration

The `.htaccess` file is already configured. Ensure `mod_rewrite` is enabled:

```bash
# On Ubuntu/Debian
sudo a2enmod rewrite
sudo systemctl restart apache2
```

If using virtual host, add this to your Apache config:

```apache
<VirtualHost *:80>
    ServerName afarrhb.local
    DocumentRoot /path/to/afarrhbinventoryphp
    
    <Directory /path/to/afarrhbinventoryphp>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/afarrhb_error.log
    CustomLog ${APACHE_LOG_DIR}/afarrhb_access.log combined
</VirtualHost>
```

### 7. Nginx Configuration (Optional)

If using Nginx, create this configuration:

```nginx
server {
    listen 80;
    server_name afarrhb.local;
    root /path/to/afarrhbinventoryphp;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml)$ {
        expires 1y;
        log_not_found off;
    }
}
```

### 8. Test the Installation

1. Open your browser and navigate to your installation URL:
   ```
   http://localhost/afarrhbinventoryphp
   ```

2. You should see the login page

3. Login with default admin credentials:
   - **Email**: admin@example.com
   - **Password**: Admin@123

4. After successful login, you'll be redirected to the dashboard

## Default User Accounts

The `init.sql` creates four default users:

| Role    | Email                  | Password  |
|---------|------------------------|-----------|
| Admin   | admin@example.com      | Admin@123 |
| Manager | manager@example.com    | Admin@123 |
| Staff   | staff@example.com      | Admin@123 |
| Viewer  | viewer@example.com     | Admin@123 |

**⚠️ IMPORTANT**: Change these passwords immediately, especially in production!

## Verifying Installation

After logging in, verify the following:

1. **Dashboard loads** with metric cards showing:
   - Total Items: 10
   - Low Stock Items: Should show items with stock below reorder level
   - Pending Requests: 1
   - Recent Issuances: 0

2. **Navigation works**: Click on menu items in the sidebar:
   - Items
   - Categories
   - Warehouses
   - Employees
   - Customers

3. **Language toggle**: Click the language dropdown in the header and switch between English and አማርኛ

4. **Calendar toggle**: Toggle between Gregorian and Ethiopian calendars

5. **Sample data exists**:
   - Navigate to Items to see 10 sample items
   - Navigate to Warehouses to see 3 warehouses
   - Navigate to Categories to see 9 categories

## Troubleshooting

### Database Connection Error

**Error**: "Database connection failed"

**Solution**:
- Check `config/database.php` credentials
- Ensure MySQL service is running
- Verify database exists: `SHOW DATABASES;` in MySQL

### Page Not Found (404)

**Error**: 404 errors when clicking links

**Solution**:
- Enable Apache `mod_rewrite`: `sudo a2enmod rewrite`
- Check `.htaccess` exists in root directory
- Verify Apache config allows `.htaccess` overrides

### Permission Denied Errors

**Error**: Cannot write to uploads directory

**Solution**:
```bash
chmod -R 755 uploads/
chown -R www-data:www-data uploads/
```

### Login Not Working

**Error**: "Invalid email or password"

**Solution**:
- Verify `init.sql` was imported successfully
- Check USERS table exists and has data:
  ```sql
  SELECT email FROM USERS;
  ```
- Ensure passwords are hashed correctly in database

### Blank Page After Login

**Error**: White/blank page

**Solution**:
- Enable error reporting in `config/config.php`:
  ```php
  define('DEV_MODE', true);
  ```
- Check PHP error logs
- Verify all required files exist

### Session Issues

**Error**: Redirected to login repeatedly

**Solution**:
- Check session directory is writable
- Verify `session_start()` works:
  ```bash
  php -r "session_start(); echo 'Sessions working';"
  ```

## Production Deployment

### Security Checklist

Before deploying to production:

1. **Change default passwords** for all users

2. **Update `config/config.php`**:
   ```php
   define('DEV_MODE', false);
   define('APP_URL', 'https://your-domain.com');
   ```

3. **Enable HTTPS**: Get SSL certificate and update `.htaccess`:
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]
   ```

4. **Secure file permissions**:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   chmod -R 755 uploads/
   ```

5. **Database security**:
   - Create dedicated database user (not root)
   - Grant only necessary privileges
   - Use strong password

6. **Backup strategy**:
   - Set up automated database backups
   - Backup uploaded files regularly

7. **Monitor logs**:
   - Check error logs regularly
   - Review audit logs for suspicious activity

### Performance Optimization

1. **Enable OpCache** in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Database optimization**:
   - Add indexes on frequently queried columns
   - Optimize queries with EXPLAIN
   - Consider query caching

3. **Static asset caching**:
   - Already configured in `.htaccess`
   - Consider CDN for static files

## Maintenance

### Regular Tasks

1. **Backup database daily**:
   ```bash
   mysqldump -u root -p afarrhb_inventory > backup_$(date +%Y%m%d).sql
   ```

2. **Clean audit logs** (older than 6 months):
   ```sql
   DELETE FROM AUDITLOG WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
   ```

3. **Check disk space** for uploads directory

4. **Update dependencies** (if any added later)

### Monitoring

Monitor these metrics:

- Database size growth
- Upload directory size
- Error log entries
- Failed login attempts (in audit logs)
- Response times

## Support

For support and questions:

- **Email**: support@afarrhb.gov.et
- **Documentation**: See README.md
- **GitHub Issues**: https://github.com/aliabdela47/afarrhbinventoryphp/issues

## License

This project is licensed under the MIT License.

---

**Version**: 1.0.0  
**Last Updated**: 2024-10-24

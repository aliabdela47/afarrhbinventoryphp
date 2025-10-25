# Deployment Checklist for AfarRHB Inventory

Use this checklist before deploying the AfarRHB Inventory Management System to production.

## Pre-Deployment Checklist

### Environment Setup
- [ ] PHP 8.0+ installed and configured
- [ ] MySQL 5.7+ / MariaDB 10.2+ installed
- [ ] Web server (Apache/Nginx) configured
- [ ] Required PHP extensions enabled (PDO, PDO_MySQL, mbstring, fileinfo)
- [ ] SSL/TLS certificate installed (HTTPS)

### Database Configuration
- [ ] Database `afarrhb_inventory` created
- [ ] Database user created (not root)
- [ ] Appropriate permissions granted to database user
- [ ] `init.sql` imported successfully
- [ ] Database connection tested
- [ ] Run `php validate-schema.php` to verify schema

### Application Configuration
- [ ] Update `config/database.php` with production credentials
- [ ] Set `DEV_MODE` to `false` in `config/config.php`
- [ ] Update `APP_URL` in `config/config.php`
- [ ] Verify `UPLOAD_PATH` is correctly set
- [ ] Check `SESSION_LIFETIME` setting

### Security Hardening
- [ ] Change all default passwords (admin, manager, staff, viewer)
- [ ] Review and update `.htaccess` settings
- [ ] Enable HTTPS redirect in `.htaccess`
- [ ] Set secure session cookie settings (cookie_secure = 1)
- [ ] Configure proper file permissions (files: 644, directories: 755)
- [ ] Set `uploads/` directory permissions (755)
- [ ] Disable directory listing
- [ ] Configure error logging (not display)
- [ ] Review and configure security headers

### File Permissions
```bash
# Set file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Uploads directory
chmod -R 755 uploads/
chown -R www-data:www-data uploads/

# Ensure config files are not world-readable
chmod 600 config/database.php
```

### Apache Configuration
- [ ] `mod_rewrite` enabled
- [ ] `.htaccess` files processed
- [ ] Virtual host configured (if applicable)
- [ ] Error pages configured
- [ ] HTTPS redirect working

### Testing
- [ ] Login functionality works
- [ ] All default user accounts accessible
- [ ] Dashboard loads correctly
- [ ] Navigation works
- [ ] Language toggle functional
- [ ] Calendar toggle functional
- [ ] CRUD operations work (test with warehouses)
- [ ] File uploads work
- [ ] Reports generate correctly
- [ ] Session timeout works
- [ ] Logout functionality works
- [ ] CSRF protection active
- [ ] Audit logging working

### Performance Optimization
- [ ] PHP OpCache enabled
- [ ] Database indexes verified
- [ ] GZIP compression enabled
- [ ] Browser caching configured
- [ ] Static assets served via CDN (optional)

### Monitoring & Logging
- [ ] Error logging configured
- [ ] Log rotation set up
- [ ] Monitoring tool installed (optional)
- [ ] Backup script configured
- [ ] Alert system configured (optional)

### Backup Strategy
- [ ] Database backup script created
- [ ] Automated daily backups configured
- [ ] Backup retention policy defined
- [ ] File backup includes `uploads/` directory
- [ ] Backup restoration tested
- [ ] Off-site backup configured (recommended)

### Documentation
- [ ] README.md reviewed and updated
- [ ] SETUP.md accessible to team
- [ ] Administrator credentials documented securely
- [ ] Deployment notes documented
- [ ] Contact information updated

## Post-Deployment Checklist

### Immediate Actions
- [ ] Verify application is accessible
- [ ] Test login with all user roles
- [ ] Check error logs for issues
- [ ] Verify HTTPS is working
- [ ] Test critical workflows
- [ ] Confirm email notifications work (if configured)

### Within 24 Hours
- [ ] Monitor application performance
- [ ] Review error logs
- [ ] Check audit logs
- [ ] Test backup restoration
- [ ] Verify scheduled tasks (if any)
- [ ] Review security logs

### Within 1 Week
- [ ] Conduct security audit
- [ ] Review user feedback
- [ ] Optimize slow queries
- [ ] Clean up test data
- [ ] Update documentation based on deployment experience

## Production Maintenance

### Daily Tasks
- [ ] Monitor error logs
- [ ] Check backup completion
- [ ] Review security alerts

### Weekly Tasks
- [ ] Review audit logs
- [ ] Check disk space
- [ ] Monitor database size
- [ ] Review failed login attempts

### Monthly Tasks
- [ ] Security updates
- [ ] Performance review
- [ ] Backup restoration test
- [ ] Clean old audit logs (>6 months)
- [ ] Review user accounts

### Quarterly Tasks
- [ ] Full security audit
- [ ] Database optimization
- [ ] Review and update documentation
- [ ] Disaster recovery drill

## Security Incident Response

### In Case of Security Breach
1. Immediately disable affected accounts
2. Change all passwords
3. Review audit logs for suspicious activity
4. Backup current state before changes
5. Patch vulnerabilities
6. Notify stakeholders
7. Document incident
8. Review and improve security measures

## Rollback Plan

### If Deployment Fails
1. Switch to maintenance mode
2. Restore previous database backup
3. Restore previous application files
4. Verify restoration
5. Document issues
6. Plan fixes
7. Test in staging
8. Retry deployment

## Support Contacts

- **Technical Support**: support@afarrhb.gov.et
- **Database Admin**: [Contact]
- **System Admin**: [Contact]
- **Project Manager**: [Contact]

## Additional Resources

- Installation Guide: SETUP.md
- Development Guide: CONTRIBUTING.md
- Quick Reference: QUICK_REFERENCE.md
- Project Overview: PROJECT_OVERVIEW.md

---

**Important**: Review this checklist thoroughly before each deployment. Update as needed based on your specific environment and requirements.

**Version**: 1.0.0  
**Last Updated**: 2024-10-24

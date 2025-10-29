# MPDO System Installation Checklist

Use this checklist to ensure proper installation and security configuration.

## 📦 Pre-Installation

- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed
- [ ] Apache/Nginx configured
- [ ] Backup existing data (if upgrading)

## 🔧 Installation Steps

### 1. File Setup
- [ ] Upload all files to server
- [ ] Set directory permissions: `chmod 755 uploads/ logs/`
- [ ] Create `.env` from `.env.example`
- [ ] Configure database credentials in `.env`
- [ ] Verify `.gitignore` includes `.env`

### 2. Database Setup
- [ ] Create database: `mpdo_db`
- [ ] Import `database_schema.sql`
- [ ] Verify tables created (users, documents, audit_logs)
- [ ] Check default admin user exists

### 3. Configuration
- [ ] Test database connection
- [ ] Verify BASE_PATH is correct
- [ ] Check error logging path
- [ ] Test file upload directory is writable

### 4. Security
- [ ] Change default admin password
- [ ] Verify CSRF tokens working
- [ ] Test file upload restrictions (PDF only)
- [ ] Check user permissions
- [ ] Disable error display (production)
- [ ] Review `.htaccess` protection

### 5. Testing
- [ ] Login with admin account
- [ ] Create test document
- [ ] Edit document
- [ ] Delete document
- [ ] Test search functionality
- [ ] Export to Excel
- [ ] View audit logs (admin)
- [ ] Test mobile responsiveness

## 🔐 Security Checklist

### Critical
- [ ] ✅ `.env` file not in git
- [ ] ✅ Database credentials secured
- [ ] ✅ Admin password changed
- [ ] ✅ HTTPS enabled (production)
- [ ] ✅ File permissions correct

### Recommended
- [ ] ✅ Error logging enabled
- [ ] ✅ Error display disabled (production)
- [ ] ✅ PHP updated to latest version
- [ ] ✅ MySQL updated to latest version
- [ ] ✅ Regular backups scheduled

### Optional
- [ ] WAF (Web Application Firewall)
- [ ] DDoS protection
- [ ] Rate limiting
- [ ] Two-factor authentication

## 📊 Verification Tests

### Login System
```
Test Case: Valid Login
- Username: admin
- Password: (your new password)
- Expected: Redirect to dashboard
- Status: [ ]
```

### Document Upload
```
Test Case: PDF Upload
- Select document type
- Fill required fields
- Upload PDF (<10MB)
- Expected: Success message
- Status: [ ]
```

### Search Function
```
Test Case: Search by Type
- Select document type filter
- Click search
- Expected: Filtered results
- Status: [ ]
```

### Export Function
```
Test Case: Export to Excel
- Apply filters
- Click Export button
- Expected: CSV download
- Status: [ ]
```

### Mobile Access
```
Test Case: Mobile View
- Access from phone/tablet
- Test menu toggle
- Test document view
- Expected: Responsive layout
- Status: [ ]
```

## 🐛 Common Issues & Solutions

### Issue: Cannot login
**Solution:**
1. Check database connection
2. Verify user exists: `SELECT * FROM users WHERE username='admin'`
3. Check user status is 'active'
4. Clear browser cookies

### Issue: File upload fails
**Solution:**
1. Check permissions: `ls -la uploads/`
2. Should be: `drwxr-xr-x` (755)
3. Check PHP settings: `php -i | grep upload`
4. Verify disk space: `df -h`

### Issue: CSRF token error
**Solution:**
1. Clear browser cache
2. Check PHP sessions working
3. Verify session.save_path writable
4. Test: `<?php session_start(); var_dump($_SESSION); ?>`

### Issue: Blank page
**Solution:**
1. Check error logs: `tail -f logs/php-error.log`
2. Enable display_errors temporarily
3. Check file permissions
4. Verify all files uploaded correctly

### Issue: PDF not displaying
**Solution:**
1. Check file exists: `ls -la uploads/`
2. Verify file_path in database
3. Check BASE_PATH configuration
4. Test direct URL access

## 📝 Post-Installation

### Immediate Actions
- [ ] Change admin password
- [ ] Create staff user accounts
- [ ] Test all features
- [ ] Configure backup system
- [ ] Document custom settings

### Within 24 Hours
- [ ] Monitor error logs
- [ ] Check audit logs
- [ ] Verify email notifications (if configured)
- [ ] Test from different devices
- [ ] User acceptance testing

### Within 1 Week
- [ ] Performance monitoring
- [ ] Security audit
- [ ] User training
- [ ] Backup verification
- [ ] Documentation updates

## 🎓 Training Checklist

### For Admins
- [ ] System overview
- [ ] User management
- [ ] Audit log review
- [ ] Backup procedures
- [ ] Troubleshooting basics

### For Staff
- [ ] Login process
- [ ] Creating documents
- [ ] Searching documents
- [ ] Editing documents
- [ ] Exporting data

### For Viewers
- [ ] Login process
- [ ] Viewing documents
- [ ] Using search filters
- [ ] Understanding document types

## 📞 Support Contacts

**Technical Issues:**
- System Administrator: _____________
- Database Admin: _____________

**User Support:**
- MPDO Office: _____________
- IT Department: _____________

## ✅ Sign-Off

Installation completed by: _____________
Date: _____________
Verified by: _____________
Date: _____________

---

**Keep this checklist for future reference and audits.**
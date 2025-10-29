# MPDO Database System v2.0

A comprehensive document management system for the Municipal Planning & Development Office.

## 🔧 Troubleshooting

### Cannot Login
- Check database credentials in `.env`
- Verify database connection
- Check if user exists and status is 'active'

### File Upload Fails
- Check `uploads/` directory permissions (755)
- Verify PHP `upload_max_filesize` (should be >= 10MB)
- Check disk space

### CSRF Token Error
- Clear browser cookies/cache
- Check that sessions are working
- Verify PHP session.save_path is writable

### PDF Not Displaying
- Check file path in database
- Verify file exists in `uploads/` directory
- Check browser PDF viewer settings

### Blank Page/Errors
- Check `logs/php-error.log`
- Verify all PHP extensions are installed
- Check file permissions

## 🔄 Updating from Old Version

If you're updating from the old code:

1. **Backup everything first!**
   ```bash
   mysqldump -u mpdo_admin -p mpdo_db > backup_$(date +%Y%m%d).sql
   tar -czf backup_files_$(date +%Y%m%d).tar.gz .
   ```

2. **Update files** (replace old files with new ones)

3. **Create .env file** with your database credentials

4. **Update config.php** to use the new version

5. **Test thoroughly** before going live

## 📱 Mobile Access

The system is fully responsive. Access from mobile devices:

1. Use modern browser (Chrome, Safari, Firefox)
2. All features available
3. Touch-optimized interface
4. Sidebar toggles on mobile

## 🛠️ Development

### Adding New Document Types

1. Edit `documents/create.php`:
   - Add option to `<select name="doc_type">`
   - Create new field section (div with doc-field class)
   - Update JavaScript switch statement

2. Edit `api/save_document.php`:
   - Add validation case for new type

3. Update CSS badge colors in `css/style.css`

### Customizing Design

All styles are in `css/style.css`. Key variables:

```css
:root {
    --primary: #2563eb;
    --success: #059669;
    --danger: #dc2626;
    --warning: #d97706;
    --maroon: #800000;
}
```

## 🐛 Known Issues

- Large PDF files (>5MB) may take time to load
- Export limited to 10,000 records (can be increased)
- IE11 not supported (use modern browsers)

## 📧 Support

For issues or questions:
- Check logs in `logs/php-error.log`
- Review audit logs for user activities
- Contact system administrator

## 📄 License

Proprietary - MPDO Limay, Bataan

## 🔖 Version History

### Version 2.0 (Current)
- ✅ Fixed all security vulnerabilities
- ✅ Added CSRF protection
- ✅ Improved file validation
- ✅ Added search & export features
- ✅ Modern responsive design
- ✅ Dashboard statistics
- ✅ Mobile-friendly interface
- ✅ Environment-based configuration

### Version 1.0 (Original)
- Basic document management
- User authentication
- File uploads
- Audit logging

## ⚡ Performance Tips

1. **Database Optimization**
   - Indexes are pre-configured
   - Run `OPTIMIZE TABLE documents;` monthly

2. **File Storage**
   - Consider moving uploads to separate storage
   - Implement CDN for large deployments

3. **Caching**
   - Enable PHP OPcache
   - Configure browser caching

4. **Backups**
   - Automated daily database backups
   - Weekly file system backups
   - Keep 30 days of backups

## 🚨 Important Notes

1. **Never commit `.env` to version control**
2. **Change default admin password immediately**
3. **Keep backups before updates**
4. **Test in staging before production**
5. **Monitor logs regularly**
6. **Update PHP and MySQL regularly**

## 📊 Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**⚠️ CHANGE IMMEDIATELY AFTER FIRST LOGIN!**

---

**Developed for MPDO Limay, Bataan**  
**Version 2.0 - 2024**🚀 Features

- **Secure Authentication** - Login with CSRF protection and password hashing
- **Document Management** - Upload, view, edit, and delete PDF documents
- **Advanced Search** - Filter by type, date range, and keywords
- **Export to Excel** - Download filtered results as CSV
- **Audit Logging** - Track all system activities
- **Role-Based Access** - Admin, Staff, and Viewer roles
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Dashboard Statistics** - Real-time document metrics

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PDO PHP Extension
- 100MB disk space minimum

## 🔧 Installation

### Step 1: Upload Files

Upload all files to your web server (e.g., `/var/www/html/mpdo-system/`)

### Step 2: Configure Environment

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your database credentials:
   ```
   DB_HOST=192.168.130.189
   DB_NAME=mpdo_db
   DB_USER=mpdo_admin
   DB_PASS=Mpdo1Limay@
   ```

3. **IMPORTANT**: Never commit `.env` to git!

### Step 3: Set Up Database

1. Import the database schema:
   ```bash
   mysql -u mpdo_admin -p mpdo_db < database_schema.sql
   ```

2. Or use phpMyAdmin to import `database_schema.sql`

### Step 4: Set Permissions

```bash
chmod 755 uploads/
chmod 755 logs/
chmod 644 .env
```

### Step 5: Configure Apache (if needed)

Add to your Apache config or `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
</IfModule>

# Protect sensitive files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Step 6: Test Installation

1. Navigate to: `http://your-domain/mpdo-system/`
2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **IMMEDIATELY change the admin password!**

## 🔐 Security Checklist

- [ ] Change default admin password
- [ ] Set up `.env` file (never use hardcoded credentials)
- [ ] Add `.env` to `.gitignore`
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable HTTPS in production
- [ ] Disable error display in production (already configured in config.php)
- [ ] Keep PHP and MySQL updated
- [ ] Regular database backups

## 📁 Directory Structure

```
mpdo-system/
├── api/
│   ├── delete_document.php
│   ├── log_activity.php
│   └── save_document.php
├── assets/
│   └── limay.png
├── css/
│   └── style.css
├── documents/
│   ├── create.php
│   ├── edit.php
│   ├── export.php
│   └── index.php
├── includes/
│   ├── navbar.php
│   └── sidebar.php
├── logs/
│   └── php-error.log
├── reports/
│   └── audit.php
├── uploads/
│   └── (PDF files here)
├── .env (create this!)
├── .env.example
├── .gitignore
├── change_password.php
├── config.php
├── dashboard.php
├── database_schema.sql
├── index.php (login)
├── logout.php
├── README.md
└── register.php
```

## 👥 User Roles

### Admin
- Full system access
- Create/edit/delete all documents
- Register new users
- View audit logs
- Export data

### Staff
- Create/edit/delete own documents
- View all documents
- Export data

### Viewer
- View documents only
- No create/edit/delete access

## 📝 Usage

### Creating a Document

1. Navigate to **Documents** > **Submit New**
2. Select document type
3. Fill in required fields
4. Upload PDF file (max 10MB)
5. Click **Submit Document**

### Searching Documents

1. Go to **Documents** > **View All**
2. Use search filters:
   - Search by name/subject/number
   - Filter by document type
   - Set date range
3. Click **Search**

### Exporting Data

1. Apply desired filters
2. Click **Export** button
3. Excel/CSV file will download

##

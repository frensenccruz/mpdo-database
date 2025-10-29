# Quick Start Guide - 10 Minutes to Running System

Get your MPDO system up and running in 10 minutes!

## ⚡ Super Quick Installation

### 1️⃣ Upload Files (2 minutes)
Upload all files to your server via FTP/SFTP or cPanel File Manager.

### 2️⃣ Create Environment File (1 minute)
```bash
# Copy template
cp .env.example .env

# Edit with your credentials
nano .env
```

Put your actual database credentials:
```env
DB_HOST=192.168.130.189
DB_NAME=mpdo_db
DB_USER=mpdo_admin
DB_PASS=Mpdo1Limay@
```

### 3️⃣ Import Database (2 minutes)

**Option A: Command Line**
```bash
mysql -u mpdo_admin -p mpdo_db < database_schema.sql
```

**Option B: phpMyAdmin**
1. Go to phpMyAdmin
2. Select `mpdo_db` database
3. Click "Import" tab
4. Choose `database_schema.sql`
5. Click "Go"

### 4️⃣ Set Permissions (1 minute)
```bash
chmod 755 uploads/ logs/
chmod 644 .env
```

**Or via FTP:**
- Right-click `uploads/` → Permissions → 755
- Right-click `logs/` → Permissions → 755
- Right-click `.env` → Permissions → 644

### 5️⃣ Test Login (1 minute)

Navigate to: `http://your-domain.com/mpdo-system/`

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

### 6️⃣ Change Admin Password (1 minute)
1. Click user icon → "Change Password"
2. Create strong password
3. Save

### 7️⃣ Test Features (2 minutes)
- [ ] Create a test document
- [ ] Search for it
- [ ] View the PDF
- [ ] Check mobile view

## 🎉 Done!

Your system is now running securely!

---

## 🔧 Troubleshooting (If Something's Wrong)

### Can't Login?
**Check database connection:**
```bash
php -r "require 'config.php'; echo 'Connected!';"
```

**If it fails:**
1. Double-check `.env` credentials
2. Verify database exists
3. Check MySQL is running

### File Upload Fails?
```bash
# Check permissions
ls -la uploads/
# Should show: drwxr-xr-x

# Fix if needed
chmod 755 uploads/
```

### Blank Page?
```bash
# Check error log
tail logs/php-error.log
```

### CSRF Token Error?
1. Clear browser cookies
2. Try different browser
3. Check PHP sessions are working

---

## 📋 First Time Setup Tasks

### Immediately After Installation

**1. Change Admin Password** (Required)
- Go to: Change Password
- Use strong password with uppercase, lowercase, numbers

**2. Create User Accounts** (If admin)
- Go to: Register User
- Create accounts for staff members

**3. Test Document Upload**
- Go to: Documents → Submit New
- Try uploading a test PDF
- Verify it saves correctly

**4. Review Settings**
- Check audit logs working
- Test search function
- Try export feature

### Within First Hour

**5. Configure Backups**
Set up automatic backups:
```bash
# Add to crontab
0 2 * * * mysqldump -u mpdo_admin -p mpdo_db > /backups/mpdo_$(date +\%Y\%m\%d).sql
```

**6. Document Your Setup**
- Note any custom configurations
- Document user roles
- Save backup locations

**7. Train Users**
- Show staff how to upload documents
- Explain search features
- Demonstrate export function

---

## 🚀 Using the System

### For Admins

**Register New User:**
1. Click "Register User" in sidebar
2. Enter full name, username, password
3. Select role (admin/staff/viewer)
4. Set status (active/inactive)
5. Click "Register User"

**View Audit Logs:**
1. Go to Reports → Audit Log
2. See all user activities
3. Check for suspicious actions

### For Staff

**Submit Document:**
1. Go to Documents → Submit New
2. Select document type
3. Fill required fields
4. Upload PDF file
5. Click "Submit Document"

**Edit Document:**
1. Go to Documents → View All
2. Find your document
3. Click "Edit"
4. Update fields or replace file
5. Click "Update Document"

**Delete Document:**
1. Find document in list
2. Click "Delete" (only your own documents)
3. Confirm deletion

### For Everyone

**Search Documents:**
1. Go to Documents → View All
2. Use search filters:
   - Keyword search
   - Document type
   - Date range
3. Click "Search"

**Export Data:**
1. Apply desired filters
2. Click "Export" button
3. Excel file downloads

**View PDF:**
1. Click "View" button on document
2. PDF opens in modal popup
3. Close when done

---

## 💡 Pro Tips

### Keyboard Shortcuts
- `Ctrl/Cmd + K` - Focus search box (when on documents page)
- `Esc` - Close PDF modal

### Mobile Usage
- Tap menu icon (☰) to open sidebar
- Swipe left to close sidebar
- Landscape mode recommended for PDF viewing

### Best Practices
1. **Use descriptive subjects** - Makes searching easier
2. **Upload PDFs under 5MB** - Faster loading
3. **Regular backups** - Daily database dumps
4. **Change password every 90 days** - Better security
5. **Review audit logs weekly** - Catch issues early

### Common Workflows

**Daily Document Processing:**
```
1. Check dashboard for new documents
2. Review assigned documents
3. Upload new documents
4. Export daily report (if needed)
```

**Monthly Reporting:**
```
1. Set date range filter (last month)
2. Export all documents
3. Review statistics on dashboard
4. Check audit logs
```

---

## 📊 Understanding the Dashboard

### Statistics Cards
- **Total Documents** - All documents in system
- **This Month** - Documents added this month
- **This Week** - Documents added this week
- **Active Users** - Users with active status

### Recent Documents
Shows last 5 documents submitted with:
- Document type (color-coded badge)
- Subject/name
- Who submitted it
- When submitted
- Quick view button

### Document Types Breakdown
Shows distribution of document types with:
- Count for each type
- Progress bar showing percentage
- Visual representation

### Quick Actions
Fast access to:
- Create new document
- Search all documents
- View audit logs (admin only)

---

## 🔐 Security Best Practices

### Password Requirements
✅ At least 8 characters
✅ One uppercase letter
✅ One lowercase letter
✅ One number
✅ One special character (recommended)

### Safe Usage
- ✅ Log out when leaving computer
- ✅ Don't share passwords
- ✅ Use strong, unique passwords
- ✅ Report suspicious activity
- ✅ Keep browser updated

### For Admins
- ✅ Review audit logs regularly
- ✅ Disable inactive user accounts
- ✅ Monitor failed login attempts
- ✅ Keep system updated
- ✅ Maintain regular backups

---

## 📞 Getting Help

### Self-Service
1. Check error logs: `logs/php-error.log`
2. Review README.md
3. Consult INSTALLATION_CHECKLIST.md
4. Search audit logs for clues

### Contact Support
- IT Department: ___________
- System Admin: ___________
- MPDO Office: ___________

### Emergency
If system is down:
1. Check server status
2. Review error logs
3. Restore from backup if needed
4. Contact administrator

---

## ✅ Quick Verification Checklist

After installation, verify these work:

- [ ] Can access login page
- [ ] Can log in with admin
- [ ] Dashboard shows statistics
- [ ] Can create document
- [ ] PDF upload works
- [ ] Can search documents
- [ ] Can view PDF
- [ ] Export button works
- [ ] Mobile menu toggles
- [ ] Password change works
- [ ] Audit log shows activity (admin)
- [ ] Can log out

**All checked?** ✅ You're good to go!

---

**Questions?** See README.md for detailed documentation.
**Problems?** Check INSTALLATION_CHECKLIST.md for troubleshooting.

**Happy document managing! 🎉**
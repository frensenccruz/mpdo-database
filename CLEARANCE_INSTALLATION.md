# Locational Clearance Module - Installation Guide

## 📁 Step 1: Create Folder Structure

Create a new folder called `clearance` in your root directory:

```
mpdo-database/
├── api/
├── clearance/          ← CREATE THIS FOLDER
│   ├── create.php      ← New file
│   ├── edit.php        ← New file
│   ├── export.php      ← New file
│   └── index.php       ← New file
├── documents/
├── includes/
└── ...
```

## 📝 Step 2: Create All Files

Copy and paste these files into the `clearance/` folder:

1. **clearance/create.php** - Submit new clearance form
2. **clearance/index.php** - View all clearances with search
3. **clearance/edit.php** - Edit existing clearance
4. **clearance/export.php** - Export to Excel

## 🔧 Step 3: Create API Files

In the `api/` folder, create these new files:

1. **api/save_clearance.php** - Save new clearance
2. **api/delete_clearance.php** - Delete clearance

## 🗄️ Step 4: Create Database Table

Run this SQL in phpMyAdmin or MySQL command line:

```sql
CREATE TABLE IF NOT EXISTS clearances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ## id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    application_no VARCHAR(100) NOT NULL,
    applicant VARCHAR(200) NOT NULL,
    address VARCHAR(255) NOT NULL,
    corporation_name VARCHAR(200) NULL,
    corporation_address VARCHAR(255) NULL,
    project_type VARCHAR(100) NOT NULL,
    area_location TEXT NOT NULL,
    right_over_land VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_application_no (application_no),
    INDEX idx_applicant (applicant),
    INDEX idx_right_over_land (right_over_land),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 📂 Step 5: Create Upload Directory

Create folder for clearance files:

```bash
mkdir -p uploads/clearances
chmod 755 uploads/clearances
```

Or via FTP:
1. Create folder: `uploads/clearances/`
2. Set permissions to 755

## 🎨 Step 6: Update Sidebar

Replace your **includes/sidebar.php** file with the updated version that includes the Locational Clearance menu.

## ✅ Step 7: Test Everything

1. Refresh your browser (Ctrl+F5)
2. You should see **"Locational Clearance"** in the sidebar
3. Click it and try:
   - ✅ Submit New Clearance
   - ✅ View all clearances
   - ✅ Search and filter
   - ✅ View PDF attachment
   - ✅ Edit clearance
   - ✅ Delete clearance
   - ✅ Export to Excel

## 📋 Complete File List

### New Files to Create:

```
clearance/
├── create.php          (Submit new form)
├── index.php           (List all with search)
├── edit.php            (Edit form)
└── export.php          (Export to CSV)

api/
├── save_clearance.php  (Save handler)
└── delete_clearance.php (Delete handler)

uploads/
└── clearances/         (PDF storage)
```

### Files to Replace:

```
includes/
└── sidebar.php         (Updated with new menu)
```

## 🎯 Features Included

✅ **Create** - Submit new locational clearance with PDF  
✅ **Read** - View all clearances in table format  
✅ **Update** - Edit clearance details  
✅ **Delete** - Remove clearances (with permission check)  
✅ **Search** - Filter by keyword, type, date range  
✅ **Export** - Download filtered results as Excel/CSV  
✅ **PDF Preview** - View attachments in modal  
✅ **Audit Trail** - Logs all actions  
✅ **Security** - CSRF protection, file validation, permissions  

## 🔐 Permissions

- **Admin**: Can view, create, edit, and delete ALL clearances
- **Staff**: Can view all, create new, edit/delete OWN clearances
- **Viewer**: Can only view clearances

## 📊 Database Fields

### Required Fields:
- Application No.
- Applicant
- Address
- Type of Project
- Area and Location
- Right over Land
- File attachment (PDF)

### Optional Fields:
- Name of Corporation
- Corporation Address

## 🎨 UI Features

- Modern card-based design
- Color-coded badges
- Responsive table
- Mobile-friendly
- PDF preview modal
- Success/error messages
- Form validation
- Auto proper case for names

## 🔍 Search Capabilities

Filter by:
- **Keyword** - Application No., Applicant, Project Type
- **Right over Land** - Owned, Leased, Rented, Government, Other
- **Date Range** - From date to date

## 📤 Export Format

CSV file includes:
- All clearance details
- Submitted by user
- Date submitted
- UTF-8 encoding (Excel compatible)

---

**All set! Your Locational Clearance module is ready to use! 🎉**
# Module 3 Content Transfer Instructions

This folder contains exported content from the EPAS-E system that needs to be imported at home.

## Files Included

1. **module3_content.sql** - Database content (information sheets, topics, self-checks, task sheets, job sheets)
2. **information_sheets_images.zip** - All images for the information sheets (copy via USB/cloud)

---

## STEP 1: Pull Latest Code from GitHub

Open Command Prompt or Terminal in your EPAS-E project folder:

```
git pull origin main
```

---

## STEP 2: Import Database Content

### For Windows (XAMPP):

**Option A - Using Command Prompt:**
```cmd
cd C:\xampp\mysql\bin
mysql -u root -p epas_db < "C:\path\to\EPAS-E\database\exports\module3_content.sql"
```
(Press Enter when asked for password if you have no password)

**Option B - Using phpMyAdmin (Easier):**
1. Open browser: http://localhost/phpmyadmin
2. Click on `epas_db` database on the left
3. Click "Import" tab at the top
4. Click "Choose File" and select `database/exports/module3_content.sql`
5. Click "Go" at the bottom

### For Mac (XAMPP):
```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root epas_db < database/exports/module3_content.sql
```

---

## STEP 3: Extract Images

### For Windows:

**Option A - Using File Explorer:**
1. Navigate to `database\exports\`
2. Right-click `information_sheets_images.zip`
3. Select "Extract All..."
4. Extract to: `storage\app\public\`
5. Make sure the folder structure is: `storage\app\public\information-sheets\`

**Option B - Using Command Prompt:**
```cmd
cd C:\path\to\EPAS-E
tar -xf database\exports\information_sheets_images.zip -C storage\app\public\
```

### For Mac:
```bash
cd /path/to/EPAS-E
unzip database/exports/information_sheets_images.zip -d storage/app/public/
```

---

## STEP 4: Verify Storage Link

Make sure the storage symlink exists:

### Windows (Run as Administrator):
```cmd
cd C:\path\to\EPAS-E
php artisan storage:link
```

### Mac:
```bash
php artisan storage:link
```

---

## STEP 5: Clear Cache

```
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## STEP 6: Run Performance Criteria Seeder (Optional)

If performance criteria are not showing:

```
php artisan db:seed --class=PerformanceCriteriaSeeder
```

---

## Troubleshooting

### Images not showing?
1. Check if `public/storage` folder exists and is a shortcut/symlink to `storage/app/public`
2. Run `php artisan storage:link` again
3. Make sure images are in `storage/app/public/information-sheets/`

### Database import error?
1. Make sure XAMPP MySQL is running
2. Make sure `epas_db` database exists
3. Try importing via phpMyAdmin instead

### Permission errors on Windows?
1. Run Command Prompt as Administrator
2. Or use phpMyAdmin for database import

---

## Content Summary

This export includes:

| Sheet | Title |
|-------|-------|
| 1.2 | Electronic Components (Resistors) |
| 1.3 | Capacitors and Diodes |
| 1.4 | Transistors, ICs & Transformers |
| 1.5 | Schematic Diagrams & PCB Making |
| 1.6 | Soldering & Troubleshooting |

Each sheet includes:
- Information Sheet content with topics
- Self-Check questions
- Task Sheets with instructions
- Performance Criteria
- Images

---

*Generated: April 1, 2026*

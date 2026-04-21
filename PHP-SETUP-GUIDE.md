# PHP Configuration Guide for JOMS LMS

This guide helps you set up PHP properly for running JOMS LMS on any Windows PC with XAMPP.

## Quick Start

1. **Run the setup script:**
   ```cmd
   setup-xampp.bat
   ```

2. **Check PHP extensions:**
   ```cmd
   php check-php-extensions.php
   ```

## Required PHP Extensions

Your XAMPP PHP installation **must** have these extensions enabled:

| Extension | Used By | Purpose |
|-----------|---------|---------|
| **mbstring** | Laravel | String manipulation (multibyte) |
| **xml** | Excel package, Laravel | XML parsing |
| **gd** | QR codes, PDF | Image processing |
| **zip** | Excel export | ZIP file handling |
| **fileinfo** | Laravel | File upload validation |
| **curl** | HTTP requests, Email | API calls |
| **openssl** | Laravel encryption | SSL/TLS support |
| **pdo_mysql** | Database | MySQL connection |

### Optional but Recommended Extensions

| Extension | Purpose |
|-----------|---------|
| **intl** | Internationalization |
| **bcmath** | Precision math |

## How to Enable Extensions in XAMPP

### Step 1: Find php.ini
XAMPP's php.ini is located at:
```
C:\xampp\php\php.ini
```

### Step 2: Edit php.ini
1. Open `C:\xampp\php\php.ini` in a text editor (Notepad++, VS Code, etc.)
2. Search for each extension (e.g., `;extension=gd`)
3. Remove the semicolon (`;`) at the beginning of the line
4. Save the file

### Step 3: Restart Apache
1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache again

## Lines to Uncomment in php.ini

Find these lines and **remove the semicolon** at the start:

```ini
extension=mbstring
extension=xml
extension=gd
extension=zip
extension=fileinfo
extension=curl
extension=openssl
extension=pdo_mysql
```

For optional extensions:
```ini
extension=intl
extension=bcmath
```

## Verifying Installation

Run the extension checker:
```cmd
php check-php-extensions.php
```

You should see all extensions marked `[OK]`.

## Common Issues & Solutions

### Issue: "Class 'ZipArchive' not found"
**Solution:** Enable `extension=zip` in php.ini and restart Apache.

### Issue: "Call to undefined function imagecreate()"
**Solution:** Enable `extension=gd` in php.ini and restart Apache.

### Issue: "Could not find driver"
**Solution:** Enable `extension=pdo_mysql` in php.ini and restart Apache.

### Issue: Composer fails with platform requirements
**Solution 1:** Run with ignore platform flag:
```cmd
composer install --ignore-platform-reqs
```

**Solution 2:** Update PHP to 8.2 or higher from the XAMPP website.

### Issue: Permissions error on storage folder
**Solution:** Run the setup script as Administrator, or manually:
```cmd
icacls storage /grant Everyone:(OI)(CI)F /T
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T
```

## Minimum System Requirements

- **PHP:** 8.2 or higher
- **Composer:** 2.x or higher
- **Node.js:** 18+ (for frontend assets)
- **MySQL:** 5.7+ or MariaDB 10.3+
- **Memory:** 512MB minimum, 1GB+ recommended
- **Disk Space:** 500MB for the application

## Setting Up on a New PC

1. **Copy the entire `joms` folder** to `C:\xampp\htdocs\joms`

2. **Install XAMPP** (if not already installed)
   - Download from: https://www.apachefriends.org/

3. **Install Composer** (if not already installed)
   - Download from: https://getcomposer.org/Composer-Setup.exe

4. **Run the setup script:**
   ```cmd
   cd C:\xampp\htdocs\joms
   setup-xampp.bat
   ```

5. **Enable PHP extensions** if the checker reports any missing

6. **Create the database:**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Create database named `epas_db`

7. **Run migrations:**
   ```cmd
   php artisan migrate
   ```

8. **Access the application:**
   - http://localhost/joms/public

## Alternative: Using PHP Built-in Server

If you don't want to use XAMPP Apache, you can use Laravel's built-in server:

```cmd
php artisan serve
```

Then access at: http://localhost:8000

**Note:** You still need MySQL/MariaDB running for the database.

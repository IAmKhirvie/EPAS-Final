# JOMS - XAMPP Setup Guide for Windows

This guide will help you set up JOMS (EPAS-E Learning Platform) on Windows using XAMPP with PHP 8.2.x.

## Prerequisites

- **XAMPP** with PHP 8.2.x installed
- **Composer** (PHP package manager) - [Download here](https://getcomposer.org/download/)
- **Node.js** (for frontend assets) - [Download here](https://nodejs.org/) (LTS version recommended)

## Quick Setup

### Step 1: Copy Project to XAMPP

Copy the `joms` folder to your XAMPP htdocs directory:
```
C:\xampp\htdocs\joms
```

### Step 2: Run Setup Script

1. Open Command Prompt as Administrator
2. Navigate to the project folder:
   ```cmd
   cd C:\xampp\htdocs\joms
   ```
3. Run the setup script:
   ```cmd
   setup-xampp.bat
   ```

### Step 3: Create Database

1. Start XAMPP (Apache and MySQL)
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create a new database named: `epas_db`
4. Set collation to: `utf8mb4_unicode_ci`

### Step 4: Run Migrations

```cmd
php artisan migrate
```

If you have a database dump, import it via phpMyAdmin instead.

### Step 5: (Optional) Seed Database

```cmd
php artisan db:seed
```

### Step 6: Access the Application

Open your browser and go to:
- **Main URL:** http://localhost/joms
- **Or:** http://localhost/joms/public

---

## Manual Setup (Alternative)

If the batch script doesn't work, follow these manual steps:

### 1. Install Dependencies

```cmd
cd C:\xampp\htdocs\joms
composer install --no-dev --optimize-autoloader
npm install
```

### 2. Environment Setup

Copy `.env.example` to `.env` if needed:
```cmd
copy .env.example .env
```

Generate application key:
```cmd
php artisan key:generate
```

### 3. Create Storage Link

```cmd
php artisan storage:link
```

### 4. Clear Caches

```cmd
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 5. Build Frontend Assets

For production:
```cmd
npm run build
```

For development (with hot reload):
```cmd
npm run dev
```

---

## Configuration

### Database Settings (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=epas_db
DB_USERNAME=root
DB_PASSWORD=
```

If your XAMPP MySQL has a password, update `DB_PASSWORD` accordingly.

### Email Settings (.env)

For Gmail SMTP:
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** For Gmail, use [App Passwords](https://support.google.com/accounts/answer/185833) (requires 2FA enabled).

---

## Troubleshooting

### Common Issues

#### 1. "Class not found" errors
```cmd
composer dump-autoload
php artisan config:clear
```

#### 2. Storage permission errors
Run Command Prompt as Administrator:
```cmd
icacls storage /grant Everyone:F /T
icacls bootstrap\cache /grant Everyone:F /T
```

#### 3. 500 Internal Server Error
- Check `storage/logs/laravel.log` for details
- Ensure all folders in `storage/` are writable
- Verify `.env` file exists and has correct settings

#### 4. Blank page / White screen
- Enable error display in `.env`: `APP_DEBUG=true`
- Check Apache error logs: `C:\xampp\apache\logs\error.log`

#### 5. CSS/JS not loading
Build the frontend assets:
```cmd
npm run build
```

#### 6. Database connection refused
- Make sure MySQL is running in XAMPP Control Panel
- Verify database `epas_db` exists
- Check DB credentials in `.env`

#### 7. mod_rewrite not working
Enable mod_rewrite in Apache:
1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment it
4. Restart Apache

---

## PHP Extensions Required

Make sure these extensions are enabled in `php.ini` (`C:\xampp\php\php.ini`):

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
```

Remove the `;` at the start of each line to enable.

---

## Development Mode

For development with live reload:

**Terminal 1 - Vite Dev Server:**
```cmd
npm run dev
```

**Terminal 2 - Laravel Server (optional, if not using Apache):**
```cmd
php artisan serve
```

---

## Default Accounts

After running `php artisan db:seed`, these accounts will be created:

| Role | Email | Password |
|------|-------|----------|
| Admin | Juswa@gmail.com | Password123 |
| Instructor | karl142412@gmail.com | Password@123 |
| Instructor | KebinSy2121252@gmail.com | Password@123 |
| Student | Sheila1112421152@gmail.com | Password@123 |

Plus 50 randomly generated student/instructor accounts with password: `Password@123`

---

## File Structure for XAMPP

```
C:\xampp\htdocs\joms\
├── app\                 # Application code
├── bootstrap\           # Framework bootstrap
├── config\              # Configuration files
├── database\            # Migrations, seeders
├── public\              # Web root (index.php)
├── resources\           # Views, CSS, JS
├── routes\              # Route definitions
├── storage\             # Logs, cache, uploads
├── vendor\              # Composer dependencies
├── .env                 # Environment config
├── .htaccess            # Redirects to public/
├── composer.json        # PHP dependencies
├── package.json         # Node dependencies
└── setup-xampp.bat      # Windows setup script
```

---

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Check Apache logs in XAMPP
3. Ensure all services (Apache, MySQL) are running
4. Verify PHP version: `php -v` (should be 8.2.x)

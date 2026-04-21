# JOMS - Windows XAMPP Setup Guide

## Requirements
- XAMPP with PHP 8.2+ and MySQL/MariaDB
- Composer (https://getcomposer.org/download/)
- Node.js & NPM (https://nodejs.org/)

## Quick Setup

### 1. Move Project to XAMPP
Copy the `joms` folder to:
```
C:\xampp\htdocs\joms
```

### 2. Open Command Prompt as Administrator
Press `Win + X` and select "Terminal (Admin)" or "Command Prompt (Admin)"

### 3. Navigate to Project
```cmd
cd C:\xampp\htdocs\joms
```

### 4. Install Dependencies
```cmd
composer install
npm install
```

### 5. Configure Environment
```cmd
copy .env.example .env
php artisan key:generate
```

### 6. Edit .env File
Open `C:\xampp\htdocs\joms\.env` in Notepad and set:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joms
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Create Database
- Open XAMPP Control Panel
- Start Apache and MySQL
- Click "Admin" next to MySQL (opens phpMyAdmin)
- Create new database named `joms`

### 8. Run Migrations
```cmd
php artisan migrate
```

### 9. Create Storage Link
```cmd
php artisan storage:link
```

### 10. Build Frontend Assets
```cmd
npm run build
```

### 11. Access the Application
Open browser and go to:
```
http://localhost/joms/public
```

## Troubleshooting

### Storage Link Issues on Windows
If `php artisan storage:link` fails, manually create the link:
1. Open Command Prompt as Administrator
2. Run:
```cmd
mklink /D "C:\xampp\htdocs\joms\public\storage" "C:\xampp\htdocs\joms\storage\app\public"
```

### Permission Issues
Make sure these folders are writable:
- `storage/`
- `bootstrap/cache/`

### PHP Extensions
Ensure these extensions are enabled in `php.ini` (C:\xampp\php\php.ini):
- `extension=fileinfo`
- `extension=gd`
- `extension=mbstring`
- `extension=openssl`
- `extension=pdo_mysql`

### Clear Cache After Changes
```cmd
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Virtual Host Setup (Optional)

For cleaner URL like `http://joms.local`:

### 1. Edit hosts file
Open Notepad as Admin, then open:
```
C:\Windows\System32\drivers\etc\hosts
```
Add line:
```
127.0.0.1 joms.local
```

### 2. Edit Apache vhosts
Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/joms/public"
    ServerName joms.local
    <Directory "C:/xampp/htdocs/joms/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Restart Apache
Restart Apache from XAMPP Control Panel.

## New Quiz Features

This update includes 10 new question types:
- Multiple Select (checkboxes)
- Numeric (with tolerance)
- Classification (sort into categories)
- Image Identification (name the picture)
- Hotspot (click on image area)
- Image Labeling (label diagram parts)
- Audio Question (listen and answer)
- Video Question (watch and answer)
- Drag & Drop (drag items to zones)
- Slider (select value on range)

All question types support partial credit scoring where applicable.

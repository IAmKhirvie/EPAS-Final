@echo off
setlocal enabledelayedexpansion

echo ============================================
echo    JOMS - XAMPP Setup Script for Windows
echo ============================================
echo.

REM Change to script directory
cd /d "%~dp0"

REM Check if running from project folder
if not exist "composer.json" (
    echo ERROR: Please run this script from the joms folder
    echo Example: C:\xampp\htdocs\joms
    pause
    exit /b 1
)

echo Step 1: Checking PHP version and extensions...
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: PHP is not recognized in your PATH!
    echo.
    echo Please add PHP to your PATH or use XAMPP Shell:
    echo 1. Open XAMPP Control Panel
    echo 2. Click "Shell" button
    echo 3. Navigate to: cd htdocs\joms
    echo 4. Run: setup-xampp.bat
    pause
    exit /b 1
)

php -v
echo.

echo Checking required PHP extensions...
php check-php-extensions.php
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo WARNING: Some PHP extensions are missing!
    echo The setup may fail. Please enable the extensions listed above.
    echo.
    echo Press any key to continue anyway (not recommended)...
    pause >nul
)

echo.
echo Step 2: Installing Composer dependencies...
echo.

REM Check if composer is available
composer --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Composer is not installed or not in PATH!
    echo.
    echo To install Composer:
    echo 1. Download: https://getcomposer.org/Composer-Setup.exe
    echo 2. Run the installer
    echo 3. Reopen your command prompt and try again
    echo.
    echo Or use the included composer.phar (if available):
    php composer.phar install --no-dev --optimize-autoloader
    pause
    exit /b 1
)

echo Found Composer version:
composer --version
echo.

composer install --no-dev --optimize-autoloader
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Composer install failed!
    echo.
    echo Common fixes:
    echo 1. Make sure all PHP extensions are enabled (see Step 1)
    echo 2. Try: composer install --ignore-platform-reqs
    echo 3. Check your internet connection
    echo 4. Delete vendor folder and composer.lock, then try again
    pause
    exit /b 1
)
echo.

echo Step 3: Checking environment file...
if not exist ".env" (
    echo Creating .env from .env.example...
    copy .env.example .env >nul
) else (
    echo .env file already exists
)
echo.

echo Step 4: Generating application key...
php artisan key:generate
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to generate application key!
    pause
    exit /b 1
)
echo.

echo Step 5: Creating storage link...
php artisan storage:link
echo.

echo Step 6: Setting folder permissions...
icacls storage /grant Everyone:(OI)(CI)F /T >nul 2>&1
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T >nul 2>&1
echo Storage and cache folders are now writable.
echo.

echo Step 7: Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo.

echo Step 8: Installing NPM dependencies...
npm --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Node.js/npm not found. Frontend assets will not be built.
    echo.
    echo To install Node.js: https://nodejs.org/
    echo.
) else (
    echo Found npm version:
    npm --version
    echo.
    echo Installing dependencies...
    call npm install
    if %ERRORLEVEL% NEQ 0 (
        echo WARNING: npm install failed, but continuing...
    ) else (
        echo Building frontend assets...
        call npm run build
        if %ERRORLEVEL% NEQ 0 (
            echo WARNING: Frontend build failed, run "npm run dev" for development
        )
    )
)
echo.

echo ============================================
echo    SETUP COMPLETE!
echo ============================================
echo.
echo NEXT STEPS:
echo.
echo 1. Create database 'epas_db' in phpMyAdmin (http://localhost/phpmyadmin)
echo.
echo 2. Import the database (if you have a dump):
echo    mysql -u root epas_db ^< dump-epas_db-202510290233.sql
echo.
echo 3. Run migrations:
echo    php artisan migrate
echo.
echo 4. Access the application at:
echo    http://localhost/joms/public
echo.
echo Or use PHP artisan serve:
echo    php artisan serve
echo.
echo TROUBLESHOOTING:
echo - If you get errors about missing extensions, check your php.ini
echo - Located at: C:\xampp\php\php.ini
echo - Required extensions: mbstring, xml, gd, zip, curl, openssl
echo.
echo - For development with hot reload:
echo    npm run dev
echo.
pause

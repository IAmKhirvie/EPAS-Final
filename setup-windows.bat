@echo off
setlocal enabledelayedexpansion

echo ========================================
echo JOMS - Windows Setup Script
echo ========================================
echo.

REM Change to script directory
cd /d "%~dp0"

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo WARNING: Not running as administrator.
    echo Some operations like folder permissions may fail.
    echo For best results, right-click and select "Run as administrator".
    echo.
)

echo [1/8] Checking PHP version and extensions...
php -v >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: PHP is not in your PATH!
    echo Please install PHP or add it to your system PATH.
    pause
    exit /b 1
)

php -v
echo.

echo Checking required PHP extensions...
php check-php-extensions.php
if %errorLevel% neq 0 (
    echo.
    echo WARNING: Some PHP extensions are missing!
    echo Setup may fail. Please enable the extensions in php.ini.
    echo.
    echo Press any key to continue anyway...
    pause >nul
)
echo.

echo [2/8] Checking Composer...
composer --version >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Composer not found!
    echo Install from: https://getcomposer.org/download/
    pause
    exit /b 1
)

echo Found Composer:
composer --version --no-ansi
echo.

echo Installing Composer dependencies...
call composer install --no-interaction
if %errorLevel% neq 0 (
    echo.
    echo ERROR: Composer install failed!
    echo.
    echo Try: composer install --ignore-platform-reqs
    echo.
    pause
    exit /b 1
)
echo.

echo [3/8] Checking Node.js/NPM...
npm --version >nul 2>&1
if %errorLevel% neq 0 (
    echo WARNING: Node.js not found!
    echo Install from: https://nodejs.org/
    echo Frontend assets will not be built.
    echo.
) else (
    echo Found NPM:
    npm --version
    echo.
    echo Installing NPM dependencies...
    call npm install
    if %errorLevel% neq 0 (
        echo WARNING: npm install had errors, but continuing...
    )
)
echo.

echo [4/8] Setting up environment file...
if not exist .env (
    copy .env.example .env
    echo Created .env file from .env.example
) else (
    echo .env file already exists
)
echo.

echo [5/8] Generating application key...
call php artisan key:generate --no-interaction
if %errorLevel% neq 0 (
    echo ERROR: Failed to generate application key!
    pause
    exit /b 1
)
echo.

echo [6/8] Creating storage link...
call php artisan storage:link 2>nul
echo.

echo [7/8] Setting folder permissions...
icacls storage /grant Everyone:(OI)(CI)F /T >nul 2>&1
icacls bootstrap\cache /grant Everyone:(OI)(CI)F /T >nul 2>&1
echo.

echo [8/8] Building frontend assets...
if exist node_modules (
    call npm run build
    if %errorLevel% neq 0 (
        echo WARNING: Build failed, run "npm run dev" for development
    )
) else (
    echo Skipping build (node_modules not found)
)
echo.

echo Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan view:clear
call php artisan route:clear
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo NEXT STEPS:
echo.
echo 1. Create a database named 'epas_db' or 'joms' in phpMyAdmin
echo.
echo 2. Edit .env file and set your database credentials:
echo    DB_CONNECTION=mysql
echo    DB_HOST=127.0.0.1
echo    DB_PORT=3306
echo    DB_DATABASE=epas_db
echo    DB_USERNAME=root
echo    DB_PASSWORD=
echo.
echo 3. Run database migrations:
echo    php artisan migrate
echo.
echo 4. (Optional) Run seeders:
echo    php artisan db:seed
echo.
echo 5. Access the application:
echo    - Via XAMPP: http://localhost/joms/public
echo    - Via artisan: php artisan serve
echo.
pause

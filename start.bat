@echo off
echo ========================================
echo    Starting EPAS-E Development Server
echo ========================================
echo.
echo Starting Laravel server on http://localhost:8000
echo Press Ctrl+C to stop the server
echo.
start cmd /k "npm run dev"
php artisan serve

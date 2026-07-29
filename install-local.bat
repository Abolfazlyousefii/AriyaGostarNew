@echo off
chcp 65001 >nul
echo پاک‌سازی کش لاراول...
php artisan optimize:clear
if errorlevel 1 goto error

echo اجرای Migration پنل کاربران آنلاین...
php artisan migrate --path=database/migrations/2026_07_28_120000_create_live_visitor_analytics_tables.php
if errorlevel 1 goto error

echo.
echo نصب با موفقیت انجام شد.
echo آدرس پنل: http://127.0.0.1:8000/admin/statistics/live-visitors
pause
exit /b 0

:error
echo.
echo عملیات با خطا متوقف شد. Terminal را از ریشه پروژه اجرا کن و متن خطا را بررسی کن.
pause
exit /b 1

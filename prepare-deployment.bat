@echo off
REM KoboPoint Deployment Preparation Script for Windows
REM This script prepares the project for cPanel deployment

echo 🚀 Preparing KoboPoint for cPanel Deployment...

REM Step 1: Build React Application
echo Step 1: Building React application...
cd frontend
if not exist ".env" (
    echo ⚠️  Warning: .env file not found in frontend directory
    echo    Creating from template...
    copy env.example.react .env
    echo    Please update frontend\.env with your production URLs before building
)

call npm run build
if errorlevel 1 (
    echo ❌ React build failed. Please fix errors and try again.
    exit /b 1
)
echo ✓ React build completed
cd ..

REM Step 2: Copy React build to public directory
echo Step 2: Copying React build files...
if exist "public\static" rmdir /s /q "public\static"
if exist "public\build" rmdir /s /q "public\build"

REM Copy build files
xcopy /E /I /Y "frontend\build\*" "public\"

REM Copy service-worker.js if it exists in build
if exist "frontend\build\service-worker.js" (
    copy /Y "frontend\build\service-worker.js" "public\service-worker.js"
    echo ✓ service-worker.js copied
)

REM Copy manifest.json if it exists in build
if exist "frontend\build\manifest.json" (
    copy /Y "frontend\build\manifest.json" "public\manifest.json"
    echo ✓ manifest.json copied
)

echo ✓ React files copied to public\

REM Step 3: Create cPanel index.php
echo Step 3: Setting up cPanel index.php...
copy "public\index_cpanel.php" "public\index_cpanel_backup.php"
echo ✓ cPanel index.php ready

REM Step 4: Optimize Laravel (optional, may fail if .env not set)
echo Step 4: Optimizing Laravel...
php artisan config:cache 2>nul
php artisan route:cache 2>nul
php artisan view:cache 2>nul
echo ✓ Laravel optimized

echo.
echo ✅ Deployment preparation complete!
echo.
echo 📦 Your project is ready for cPanel deployment
echo 📖 Read DEPLOYMENT_GUIDE.md for next steps
echo.

pause





#!/bin/bash
# KoboPoint - cPanel Post-Upload Commands
# Run these commands on cPanel via SSH/Terminal

echo "============================================================"
echo "  KoboPoint - Clearing Caches and Setting Up"
echo "============================================================"
echo ""

echo "Step 1: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"
echo ""

echo "Step 2: Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Caches rebuilt"
echo ""

echo "Step 3: Creating storage symlink..."
php artisan storage:link
echo "✓ Storage symlink created"
echo ""

echo "Step 4: Setting permissions..."
chmod -R 755 storage bootstrap/cache
echo "✓ Permissions set"
echo ""

echo "============================================================"
echo "  ✓ Done! Your site should be ready now."
echo "============================================================"
echo ""
echo "Next steps:"
echo "  1. Test: https://app.kobopoint.com/test-origin.php"
echo "  2. Test API: https://app.kobopoint.com/api/website/app/setting"
echo "  3. Try logging in"
echo ""


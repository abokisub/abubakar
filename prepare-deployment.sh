#!/bin/bash

# KoboPoint Deployment Preparation Script
# This script prepares the project for cPanel deployment
# Run this BEFORE uploading to cPanel

echo "🚀 Preparing KoboPoint for cPanel Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Build React Application
echo -e "${YELLOW}Step 1: Building React application...${NC}"
cd frontend
if [ ! -f ".env" ]; then
    echo "⚠️  Warning: .env file not found in frontend directory"
    echo "   Creating from template..."
    cp env.example.react .env
    echo "   Please update frontend/.env with your production URLs before building"
fi

npm run build
if [ $? -ne 0 ]; then
    echo "❌ React build failed. Please fix errors and try again."
    exit 1
fi
echo -e "${GREEN}✓ React build completed${NC}"
cd ..

# Step 2: Copy React build to public directory
echo -e "${YELLOW}Step 2: Copying React build files...${NC}"
if [ -d "public/static" ]; then
    rm -rf public/static
fi
if [ -d "public/build" ]; then
    rm -rf public/build
fi

# Copy build files
cp -r frontend/build/* public/
echo -e "${GREEN}✓ React files copied to public/${NC}"

# Step 3: Create cPanel index.php
echo -e "${YELLOW}Step 3: Setting up cPanel index.php...${NC}"
cp public/index_cpanel.php public/index_cpanel_backup.php
echo -e "${GREEN}✓ cPanel index.php ready${NC}"

# Step 4: Optimize Laravel
echo -e "${YELLOW}Step 4: Optimizing Laravel...${NC}"
php artisan config:cache 2>/dev/null || echo "⚠️  Could not cache config (this is OK if .env is not set)"
php artisan route:cache 2>/dev/null || echo "⚠️  Could not cache routes (this is OK if .env is not set)"
php artisan view:cache 2>/dev/null || echo "⚠️  Could not cache views"
echo -e "${GREEN}✓ Laravel optimized${NC}"

# Step 5: Create deployment package info
echo -e "${YELLOW}Step 5: Creating deployment info...${NC}"
cat > DEPLOYMENT_INFO.txt << EOF
KoboPoint Deployment Package
Generated: $(date)

📦 Package Contents:
- Laravel backend (app/, config/, database/, etc.)
- React frontend (built files in public/)
- Vendor dependencies (vendor/)
- Database migrations (database/migrations/)

📋 Next Steps:
1. Upload all files to your cPanel server
2. Follow DEPLOYMENT_GUIDE.md for detailed instructions
3. Update .env file with your domain and database details
4. Import database from database/dump.sql

🔧 Required .env Variables:
- APP_URL=https://yourdomain.com
- FRONTEND_URL=https://yourdomain.com
- API_URL=https://yourdomain.com/api
- DB_* (database credentials)
- APP_KEY (generate a random 32-char key)
- ADEX_APP_KEY=https://yourdomain.com,https://www.yourdomain.com

📚 See DEPLOYMENT_GUIDE.md for complete instructions
EOF
echo -e "${GREEN}✓ Deployment info created${NC}"

echo ""
echo -e "${GREEN}✅ Deployment preparation complete!${NC}"
echo ""
echo "📦 Your project is ready for cPanel deployment"
echo "📖 Read DEPLOYMENT_GUIDE.md for next steps"
echo ""









#!/bin/bash

# =============================================================================
# SimpleAkunting v4 - Deployment Script
# =============================================================================
# Usage: ./deploy.sh [production|staging]
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default environment
ENVIRONMENT="${1:-production}"

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}   SimpleAkunting v4 - Deployment Script${NC}"
echo -e "${BLUE}   Environment: ${YELLOW}${ENVIRONMENT}${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# Function to print step
step() {
    echo -e "${GREEN}[STEP]${NC} $1"
}

# Function to print warning
warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# Function to print error
error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to print success
success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# 1. Pull latest changes from Git
step "Pulling latest changes from Git..."
git pull origin main

# 2. Install/update Composer dependencies
step "Installing Composer dependencies..."
if [ "$ENVIRONMENT" == "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --no-interaction
fi

# 3. Install/update NPM dependencies and build assets
step "Installing NPM dependencies..."
export NODE_OPTIONS="--max-old-space-size=512"
npm install --legacy-peer-deps --no-audit --no-fund

step "Building assets..."
npm run build

# 4. Run database migrations
step "Running database migrations..."
php artisan migrate --force

# 5. Clear and optimize cache
step "Clearing old cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Optimize for production
if [ "$ENVIRONMENT" == "production" ]; then
    step "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan optimize
fi

# 7. Set proper permissions (if needed)
step "Setting proper permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
fi
if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
fi

# 8. Create storage symlink if not exists
if [ ! -L "public/storage" ]; then
    step "Creating storage symlink..."
    php artisan storage:link
fi

# 9. Restart queue workers (if using queues)
# Uncomment if you use Laravel queues
# step "Restarting queue workers..."
# php artisan queue:restart

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}   Deployment completed successfully!${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""

# Show current version info
step "Current deployment info:"
echo "  - Git commit: $(git rev-parse --short HEAD)"
echo "  - Branch: $(git branch --show-current)"
echo "  - Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

success "Application is ready!"

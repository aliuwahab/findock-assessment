#!/bin/bash

# FinDock Panel Assignment - Local Environment Setup Script
# This script automates the setup of the Laravel application

set -e  # Exit on any error

echo "======================================"
echo "FinDock Address Validation Setup"
echo "======================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo -e "${RED}Error: Composer is not installed.${NC}"
    echo "Please install Composer: https://getcomposer.org/"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}Error: PHP is not installed.${NC}"
    echo "Please install PHP 8.2 or higher"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
REQUIRED_VERSION="8.2"
if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PHP_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then 
    echo -e "${RED}Error: PHP version 8.2+ is required. Current version: $PHP_VERSION${NC}"
    exit 1
fi

echo -e "${GREEN}✓ PHP version $PHP_VERSION detected${NC}"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${YELLOW}Warning: npm is not installed. Frontend assets won't be built.${NC}"
    echo "You can install Node.js later: https://nodejs.org/"
    SKIP_NPM=true
else
    echo -e "${GREEN}✓ npm detected${NC}"
    SKIP_NPM=false
fi

echo ""
echo "Step 1: Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo -e "${GREEN}✓ Composer dependencies installed${NC}"

# Install npm dependencies
if [ "$SKIP_NPM" = false ]; then
    echo ""
    echo "Step 2: Installing npm dependencies..."
    npm install
    echo -e "${GREEN}✓ npm dependencies installed${NC}"
else
    echo ""
    echo -e "${YELLOW}⊘ Skipping npm dependencies${NC}"
fi

# Setup environment file
echo ""
echo "Step 3: Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created from .env.example${NC}"
else
    echo -e "${YELLOW}⊘ .env file already exists, skipping${NC}"
fi

# Generate application key
echo ""
echo "Step 4: Generating application key..."
php artisan key:generate --no-interaction
echo -e "${GREEN}✓ Application key generated${NC}"

# Setup database
echo ""
echo "Step 5: Setting up SQLite database..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo -e "${GREEN}✓ SQLite database file created${NC}"
else
    echo -e "${YELLOW}⊘ database.sqlite already exists${NC}"
fi

# Publish Sanctum migrations
echo ""
echo "Step 5b: Publishing Sanctum migrations..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force --no-interaction 2>/dev/null || echo -e "${YELLOW}⊘ Sanctum already published${NC}"
echo -e "${GREEN}✓ Sanctum migrations ready${NC}"

# Run migrations
echo ""
echo "Step 6: Running database migrations..."
php artisan migrate:fresh --no-interaction --force
echo -e "${GREEN}✓ Database migrations completed${NC}"

# Run seeders
echo ""
echo "Step 7: Seeding database with test users..."
php artisan db:seed --no-interaction --force

# Create storage link
echo ""
echo "Step 8: Creating storage link..."
php artisan storage:link --no-interaction 2>/dev/null || echo -e "${YELLOW}⊘ Storage link already exists${NC}"

# Build frontend assets (if npm is available)
if [ "$SKIP_NPM" = false ]; then
    echo ""
    echo "Step 9: Building frontend assets..."
    npm run build
    echo -e "${GREEN}✓ Frontend assets built${NC}"
fi

echo ""
echo "======================================"
echo -e "${GREEN}Setup completed successfully!${NC}"
echo "======================================"
echo ""
echo "Next steps:"
echo ""
echo "1. Configure your Geoapify API key in .env:"
echo "   ${YELLOW}GEOAPIFY_API_KEY=your_actual_api_key_here${NC}"
echo "   Sign up at: https://www.geoapify.com/"
echo ""
echo "2. Start the development server:"
echo "   ${YELLOW}php artisan serve${NC}"
echo ""
echo "3. In another terminal, start the queue worker:"
echo "   ${YELLOW}php artisan queue:work${NC}"
echo ""
if [ "$SKIP_NPM" = false ]; then
    echo "4. (Optional) Start Vite for hot module replacement:"
    echo "   ${YELLOW}npm run dev${NC}"
    echo ""
fi
echo "5. Register a user and start uploading CSVs!"
echo "   Visit: http://localhost:8000"
echo ""
echo ""

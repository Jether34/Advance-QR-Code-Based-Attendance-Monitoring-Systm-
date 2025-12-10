#!/bin/bash

# Deploy TapIn system to XAMPP htdocs/puta/ directory
# Usage: ./tools/deploy_to_htdocs.sh [path/to/xampp/htdocs]
# Example: ./tools/deploy_to_htdocs.sh /opt/lampp/htdocs

set -e

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Default XAMPP htdocs paths based on OS
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    DEFAULT_HTDOCS="${HOME}/xampp/htdocs"
    if [ ! -d "$DEFAULT_HTDOCS" ]; then
        DEFAULT_HTDOCS="/opt/lampp/htdocs"
    fi
elif [[ "$OSTYPE" == "darwin"* ]]; then
    DEFAULT_HTDOCS="/Applications/XAMPP/xamppfiles/htdocs"
elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "cygwin" ]]; then
    DEFAULT_HTDOCS="C:/xampp/htdocs"
else
    DEFAULT_HTDOCS="${HOME}/xampp/htdocs"
fi

# Get target directory from argument or use default
TARGET_DIR="${1:-$DEFAULT_HTDOCS}"
DEPLOY_PATH="${TARGET_DIR}/puta"

# Script directory (where this script is located)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  TapIn XAMPP Deployment Script${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Validation
if [ ! -d "$TARGET_DIR" ]; then
    echo -e "${RED}Error: htdocs directory not found at: ${TARGET_DIR}${NC}"
    echo -e "${YELLOW}Please provide valid XAMPP htdocs path:${NC}"
    echo "Usage: $0 [path/to/xampp/htdocs]"
    exit 1
fi

# Check if already deployed
if [ -d "$DEPLOY_PATH" ]; then
    echo -e "${YELLOW}Warning: ${DEPLOY_PATH} already exists${NC}"
    read -p "Overwrite? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Deployment cancelled."
        exit 0
    fi
    echo -e "${YELLOW}Removing existing deployment...${NC}"
    rm -rf "$DEPLOY_PATH"
fi

# Deploy
echo -e "${BLUE}Deploying from: ${SCRIPT_DIR}${NC}"
echo -e "${BLUE}Deploying to: ${DEPLOY_PATH}${NC}"
echo ""

mkdir -p "$DEPLOY_PATH"
cp -r "$SCRIPT_DIR"/* "$DEPLOY_PATH/"

# Create necessary directories with proper permissions
echo -e "${BLUE}Setting up directories...${NC}"
mkdir -p "$DEPLOY_PATH/uploads"
mkdir -p "$DEPLOY_PATH/storage"
mkdir -p "$DEPLOY_PATH/logs"

# Set permissions
chmod -R 755 "$DEPLOY_PATH"
chmod -R 775 "$DEPLOY_PATH/uploads" 2>/dev/null || true
chmod -R 775 "$DEPLOY_PATH/storage" 2>/dev/null || true
chmod -R 775 "$DEPLOY_PATH/logs" 2>/dev/null || true

echo -e "${GREEN}✓ Deployment successful!${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Create .env file in: ${DEPLOY_PATH}/"
echo "2. Create database: tapin_db in phpMyAdmin"
echo "3. Import SQL: ${DEPLOY_PATH}/create_tables.sql"
echo "4. Start XAMPP (Apache + MySQL)"
echo "5. Access: http://localhost/puta"
echo ""
echo -e "${YELLOW}Documentation:${NC}"
echo "See XAMPP_LOCAL_SETUP.md for detailed setup instructions"
echo ""

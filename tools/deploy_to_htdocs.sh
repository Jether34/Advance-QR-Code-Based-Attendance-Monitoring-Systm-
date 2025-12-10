#!/bin/bash
# Deploy TapIn to XAMPP htdocs/puta/ folder

set -euo pipefail

if [ $# -eq 0 ]; then
    echo "Usage: ./tools/deploy_to_htdocs.sh /path/to/xampp/htdocs"
    echo "Example: ./tools/deploy_to_htdocs.sh ~/xampp/htdocs"
    exit 1
fi

HTDOCS_PATH="$1"
TARGET_PATH="$HTDOCS_PATH/puta"

if [ ! -d "$HTDOCS_PATH" ]; then
    echo "Error: htdocs path does not exist: $HTDOCS_PATH"
    exit 1
fi

echo "Creating target folder: $TARGET_PATH"
mkdir -p "$TARGET_PATH"

echo "Copying files..."
cp -r . "$TARGET_PATH/"

echo "Setting permissions..."
chmod -R 755 "$TARGET_PATH"
chmod -R 775 "$TARGET_PATH/uploads" 2>/dev/null || true
chmod -R 775 "$TARGET_PATH/storage" 2>/dev/null || true

echo ""
echo "✓ TapIn deployed to $TARGET_PATH"
echo ""
echo "Next steps:"
echo "  1. Create .env file in $TARGET_PATH"
echo "  2. Create database 'tapin_db' in MySQL"
echo "  3. Import SQL schema from create_tables.sql"
echo "  4. Access http://localhost/puta in your browser"
echo ""
echo "For detailed setup instructions, see: XAMPP_LOCAL_SETUP.md"

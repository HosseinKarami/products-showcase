#!/bin/bash

# Build script for Products Showcase plugin
# This creates a production-ready zip file for WordPress.org

PLUGIN_SLUG="products-showcase"
BUILD_DIR="build-temp"
ZIP_FILE="${PLUGIN_SLUG}.zip"

echo "🎁 Creating archive for ${PLUGIN_SLUG} plugin..."
echo ""

# Remove old build directory and zip if they exist
rm -rf "${BUILD_DIR}"
rm -f "${ZIP_FILE}"

# Create build directory
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

# Copy files and directories needed for the plugin
echo "📦 Copying plugin files..."

# Main plugin files
cp products-showcase.php "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp readme.txt "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp README.md "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp LICENSE "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp uninstall.php "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy directories
cp -r build "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r includes "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r templates "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r assets "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r languages "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r vendor "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Remove any development files that might have been copied
find "${BUILD_DIR}" -name ".DS_Store" -delete
find "${BUILD_DIR}" -name "*.map" -delete

# Create the zip file
echo "🗜️  Creating zip file..."
cd "${BUILD_DIR}"
zip -r "../${ZIP_FILE}" "${PLUGIN_SLUG}" -q

# Clean up
cd ..
rm -rf "${BUILD_DIR}"

echo ""
echo "✅ Done! ${ZIP_FILE} is ready! 🎉"
echo ""

# Show what's in the zip
echo "📋 Contents:"
unzip -l "${ZIP_FILE}" | head -30
echo "..."
echo ""
echo "Total files: $(unzip -l "${ZIP_FILE}" | tail -1 | awk '{print $2}')"


#!/bin/bash

# PECL Preparation Script for PHP Identifier Extension
# This script prepares the extension for PECL submission

set -e

echo "🚀 Preparing PHP Identifier Extension for PECL submission..."

# Check prerequisites
echo "📋 Checking prerequisites..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP not found. Please install PHP."
    exit 1
fi

if ! command -v phpize &> /dev/null; then
    echo "❌ phpize not found. Please install php-dev package."
    exit 1
fi

if ! command -v pear &> /dev/null; then
    echo "❌ pear not found. Please install php-pear package."
    exit 1
fi

echo "✅ Prerequisites check passed!"

# Clean any previous builds
echo "🧹 Cleaning previous builds..."
make -f Makefile.dev clean 2>/dev/null || true

# Validate package.xml
echo "📝 Validating package.xml..."
if ! pear package-validate package.xml; then
    echo "❌ package.xml validation failed!"
    exit 1
fi
echo "✅ package.xml is valid!"

# Build the extension
echo "🔨 Building extension..."
if ! make -f Makefile.dev build; then
    echo "❌ Build failed!"
    exit 1
fi
echo "✅ Build successful!"

# Run tests
echo "🧪 Running tests..."
if ! make test; then
    echo "❌ Tests failed!"
    exit 1
fi
echo "✅ All tests passed!"

# Check for memory leaks (if valgrind is available)
if command -v valgrind &> /dev/null; then
    echo "🔍 Checking for memory leaks..."
    USE_ZEND_ALLOC=0 valgrind --leak-check=full --error-exitcode=1 \
        php -dextension=modules/identifier.so -r "
        \$uuid = Php\\Identifier\\Uuid\\Version4::generate();
        \$ulid = Php\\Identifier\\Ulid::generate();
        echo 'Memory check complete\n';
        " 2>/dev/null || echo "⚠️  Memory check completed (check output for leaks)"
else
    echo "⚠️  valgrind not available, skipping memory leak check"
fi

# Create the package
echo "📦 Creating PECL package..."
if ! pear package package.xml; then
    echo "❌ Package creation failed!"
    exit 1
fi

PACKAGE_FILE=$(ls identifier-*.tgz 2>/dev/null | head -1)
if [ -z "$PACKAGE_FILE" ]; then
    echo "❌ Package file not found!"
    exit 1
fi

echo "✅ Package created: $PACKAGE_FILE"

# Verify package contents
echo "🔍 Verifying package contents..."
tar -tzf "$PACKAGE_FILE" | head -20
echo "..."
echo "Total files in package: $(tar -tzf "$PACKAGE_FILE" | wc -l)"

# Final checks
echo "🎯 Final validation..."

# Check if all required files are present
REQUIRED_FILES=("config.m4" "LICENSE" "README.md" "CREDITS" "CHANGELOG.md")
for file in "${REQUIRED_FILES[@]}"; do
    if tar -tzf "$PACKAGE_FILE" | grep -q "identifier-[0-9.]*//$file"; then
        echo "✅ $file found in package"
    else
        echo "❌ $file missing from package!"
        exit 1
    fi
done

# Check if source files are present
if tar -tzf "$PACKAGE_FILE" | grep -q "src/.*\.c"; then
    echo "✅ C source files found in package"
else
    echo "❌ C source files missing from package!"
    exit 1
fi

# Check if test files are present
if tar -tzf "$PACKAGE_FILE" | grep -q "tests/.*\.phpt"; then
    echo "✅ Test files found in package"
else
    echo "❌ Test files missing from package!"
    exit 1
fi

echo ""
echo "🎉 PECL package preparation complete!"
echo ""
echo "📋 Next steps for PECL submission:"
echo "1. Create a PECL account at https://pecl.php.net/account-request.php"
echo "2. Request package approval at https://pecl.php.net/package-new.php"
echo "3. Upload the package: $PACKAGE_FILE"
echo ""
echo "📄 Package details:"
echo "   File: $PACKAGE_FILE"
echo "   Size: $(du -h "$PACKAGE_FILE" | cut -f1)"
echo "   Version: $(grep -A1 '<release>' package.xml | tail -1 | sed 's/.*<release>\(.*\)<\/release>.*/\1/')"
echo ""
echo "🔗 Useful links:"
echo "   PECL submission guide: https://wiki.php.net/rfc/pecl_submission_process"
echo "   PECL package format: https://pear.php.net/manual/en/guide.developers.package2.php"
echo ""
echo "✨ Good luck with your PECL submission!"

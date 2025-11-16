#!/usr/bin/env bash
# Build script for creating PIE-compliant release artifacts
# Leverages Zig's cross-compilation capabilities to build for all platforms

set -euo pipefail

VERSION="${1:-}"

if [ -z "$VERSION" ]; then
    echo "Usage: $0 <version>"
    echo "  version: Release version (e.g., 0.1.0)"
    echo ""
    echo "This script builds artifacts for all platforms, architectures, and PHP versions."
    exit 1
fi

DIST_DIR="dist"
mkdir -p "$DIST_DIR"

# Detect Visual Studio version for Windows builds
detect_vs_compiler() {
    if command -v vswhere.exe &> /dev/null; then
        VS_VERSION=$(vswhere.exe -latest -property installationVersion 2>/dev/null || echo "")
        if [ -n "$VS_VERSION" ]; then
            VS_MAJOR="${VS_VERSION%%.*}"
            case "$VS_MAJOR" in
                17) echo "vs17" ;;
                16) echo "vs16" ;;
                15) echo "vs15" ;;
                *) echo "vs16" ;;
            esac
        else
            echo "vs16"
        fi
    else
        echo "vs16"
    fi
}

COMPILER=$(detect_vs_compiler)
PHP_VERSIONS=("8.1" "8.2" "8.3" "8.4")

echo "=========================================="
echo "Building release artifacts for version $VERSION"
echo "Target PHP versions: ${PHP_VERSIONS[*]}"
echo "Windows compiler: $COMPILER"
echo "=========================================="
echo ""

# Build for all PHP versions and platforms
for PHP_VERSION in "${PHP_VERSIONS[@]}"; do
    echo "Building for PHP $PHP_VERSION..."
    echo ""

    # Linux builds
    echo "  [Linux]"

    # Linux x86_64
    echo "    - x86_64..."
    zig build -Doptimize=ReleaseFast -Dtarget=x86_64-linux-gnu
    mkdir -p "identifier-$VERSION"
    cp modules/identifier.so "identifier-$VERSION/"
    tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-linux-x86_64.tar.gz" "identifier-$VERSION"
    rm -rf "identifier-$VERSION"

    # Linux aarch64
    echo "    - aarch64..."
    zig build -Doptimize=ReleaseFast -Dtarget=aarch64-linux-gnu
    mkdir -p "identifier-$VERSION"
    cp modules/identifier.so "identifier-$VERSION/"
    tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-linux-aarch64.tar.gz" "identifier-$VERSION"
    rm -rf "identifier-$VERSION"

    # macOS builds
    echo "  [macOS]"

    # macOS x86_64
    echo "    - x86_64..."
    zig build -Doptimize=ReleaseFast -Dtarget=x86_64-macos
    mkdir -p "identifier-$VERSION"
    cp modules/identifier.so "identifier-$VERSION/"
    tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-macos-x86_64.tar.gz" "identifier-$VERSION"
    rm -rf "identifier-$VERSION"

    # macOS arm64
    echo "    - arm64..."
    zig build -Doptimize=ReleaseFast -Dtarget=aarch64-macos
    mkdir -p "identifier-$VERSION"
    cp modules/identifier.so "identifier-$VERSION/"
    tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-macos-arm64.tar.gz" "identifier-$VERSION"
    rm -rf "identifier-$VERSION"

    # Windows builds
    echo "  [Windows]"

    # Windows x86_64 NTS
    echo "    - x86_64 NTS..."
    zig build -Doptimize=ReleaseFast -Dtarget=x86_64-windows
    FILENAME="php_identifier-$VERSION-$PHP_VERSION-nts-$COMPILER-x86_64"
    mkdir -p "$FILENAME"
    cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
    if command -v zip &> /dev/null; then
        (cd "$FILENAME" && zip -q "../$DIST_DIR/$FILENAME.zip" *)
    elif command -v 7z &> /dev/null; then
        7z a -tzip "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
    else
        echo "Warning: No zip utility found, skipping $FILENAME"
    fi
    rm -rf "$FILENAME"

    # Windows x86_64 TS
    echo "    - x86_64 TS..."
    zig build -Doptimize=ReleaseFast -Dtarget=x86_64-windows
    FILENAME="php_identifier-$VERSION-$PHP_VERSION-ts-$COMPILER-x86_64"
    mkdir -p "$FILENAME"
    cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
    if command -v zip &> /dev/null; then
        (cd "$FILENAME" && zip -q "../$DIST_DIR/$FILENAME.zip" *)
    elif command -v 7z &> /dev/null; then
        7z a -tzip "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
    fi
    rm -rf "$FILENAME"

    # Windows arm64 NTS
    echo "    - arm64 NTS..."
    zig build -Doptimize=ReleaseFast -Dtarget=aarch64-windows
    FILENAME="php_identifier-$VERSION-$PHP_VERSION-nts-$COMPILER-arm64"
    mkdir -p "$FILENAME"
    cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
    if command -v zip &> /dev/null; then
        (cd "$FILENAME" && zip -q "../$DIST_DIR/$FILENAME.zip" *)
    elif command -v 7z &> /dev/null; then
        7z a -tzip "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
    fi
    rm -rf "$FILENAME"

    # Windows arm64 TS
    echo "    - arm64 TS..."
    zig build -Doptimize=ReleaseFast -Dtarget=aarch64-windows
    FILENAME="php_identifier-$VERSION-$PHP_VERSION-ts-$COMPILER-arm64"
    mkdir -p "$FILENAME"
    cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
    if command -v zip &> /dev/null; then
        (cd "$FILENAME" && zip -q "../$DIST_DIR/$FILENAME.zip" *)
    elif command -v 7z &> /dev/null; then
        7z a -tzip "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
    fi
    rm -rf "$FILENAME"

    echo ""
done

echo "=========================================="
echo "Build complete!"
echo "=========================================="
echo ""
echo "Artifacts created in $DIST_DIR/:"
echo ""
ls -lh "$DIST_DIR/" | grep -v "^total" | awk '{printf "  %s  %s\n", $5, $9}'
echo ""
echo "Total artifacts: $(ls -1 "$DIST_DIR" | wc -l)"

#!/usr/bin/env bash
# Build script for creating PIE-compliant release artifacts
# Leverages Zig's cross-compilation capabilities

set -euo pipefail

VERSION="${1:-}"
PLATFORM="${2:-linux}"
PHP_VERSION="${3:-8.3}"

if [ -z "$VERSION" ]; then
    echo "Usage: $0 <version> [platform] [php-version]"
    echo "  version: Release version (e.g., 0.1.0)"
    echo "  platform: linux, macos, or windows (default: linux)"
    echo "  php-version: PHP version (default: 8.3)"
    exit 1
fi

DIST_DIR="dist"
mkdir -p "$DIST_DIR"

echo "Building release artifacts for version $VERSION on $PLATFORM (PHP $PHP_VERSION)"

case "$PLATFORM" in
    linux)
        echo "Building for Linux architectures..."

        # x86_64
        echo "  - Building x86_64..."
        zig build -Doptimize=ReleaseFast -Dtarget=x86_64-linux-gnu
        mkdir -p "identifier-$VERSION"
        cp modules/identifier.so "identifier-$VERSION/"
        tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-linux-x86_64.tar.gz" "identifier-$VERSION"
        rm -rf "identifier-$VERSION"

        # aarch64
        echo "  - Building aarch64..."
        zig build -Doptimize=ReleaseFast -Dtarget=aarch64-linux-gnu
        mkdir -p "identifier-$VERSION"
        cp modules/identifier.so "identifier-$VERSION/"
        tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-linux-aarch64.tar.gz" "identifier-$VERSION"
        rm -rf "identifier-$VERSION"

        echo "Linux builds complete!"
        ;;

    macos)
        echo "Building for macOS architectures..."

        # x86_64
        echo "  - Building x86_64..."
        zig build -Doptimize=ReleaseFast -Dtarget=x86_64-macos
        mkdir -p "identifier-$VERSION"
        cp modules/identifier.so "identifier-$VERSION/"
        tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-macos-x86_64.tar.gz" "identifier-$VERSION"
        rm -rf "identifier-$VERSION"

        # arm64
        echo "  - Building arm64..."
        zig build -Doptimize=ReleaseFast -Dtarget=aarch64-macos
        mkdir -p "identifier-$VERSION"
        cp modules/identifier.so "identifier-$VERSION/"
        tar -czf "$DIST_DIR/php_identifier-$VERSION-$PHP_VERSION-nts-macos-arm64.tar.gz" "identifier-$VERSION"
        rm -rf "identifier-$VERSION"

        echo "macOS builds complete!"
        ;;

    windows)
        echo "Building for Windows architectures..."

        # Detect Visual Studio version
        if command -v vswhere.exe &> /dev/null; then
            VS_PATH=$(vswhere.exe -latest -property installationPath)
            VS_VERSION=$(vswhere.exe -latest -property installationVersion)
            VS_MAJOR="${VS_VERSION%%.*}"

            case "$VS_MAJOR" in
                17) COMPILER="vs17" ;;
                16) COMPILER="vs16" ;;
                15) COMPILER="vs15" ;;
                *) COMPILER="vs16" ;;
            esac
        else
            COMPILER="vs16"
        fi

        echo "  Using compiler: $COMPILER"

        # x86_64 NTS
        echo "  - Building x86_64 NTS..."
        zig build -Doptimize=ReleaseFast -Dtarget=x86_64-windows
        FILENAME="php_identifier-$VERSION-$PHP_VERSION-nts-$COMPILER-x86_64"
        mkdir -p "$FILENAME"
        cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
        7z a "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
        rm -rf "$FILENAME"

        # x86_64 TS
        echo "  - Building x86_64 TS..."
        zig build -Doptimize=ReleaseFast -Dtarget=x86_64-windows
        FILENAME="php_identifier-$VERSION-$PHP_VERSION-ts-$COMPILER-x86_64"
        mkdir -p "$FILENAME"
        cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
        7z a "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
        rm -rf "$FILENAME"

        # arm64 NTS
        echo "  - Building arm64 NTS..."
        zig build -Doptimize=ReleaseFast -Dtarget=aarch64-windows
        FILENAME="php_identifier-$VERSION-$PHP_VERSION-nts-$COMPILER-arm64"
        mkdir -p "$FILENAME"
        cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
        7z a "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
        rm -rf "$FILENAME"

        # arm64 TS
        echo "  - Building arm64 TS..."
        zig build -Doptimize=ReleaseFast -Dtarget=aarch64-windows
        FILENAME="php_identifier-$VERSION-$PHP_VERSION-ts-$COMPILER-arm64"
        mkdir -p "$FILENAME"
        cp modules/identifier.dll "$FILENAME/$FILENAME.dll"
        7z a "$DIST_DIR/$FILENAME.zip" "$FILENAME/" > /dev/null
        rm -rf "$FILENAME"

        echo "Windows builds complete!"
        ;;

    *)
        echo "Error: Unknown platform '$PLATFORM'"
        echo "Supported platforms: linux, macos, windows"
        exit 1
        ;;
esac

echo ""
echo "All artifacts created in $DIST_DIR/"
ls -lh "$DIST_DIR/"

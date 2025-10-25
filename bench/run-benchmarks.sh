#!/bin/bash

# Benchmark runner script for Docker environment
set -e

echo "🚀 PHP Identifier Extension Benchmarks"
echo "======================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed or not in PATH"
    exit 1
fi

# Docker Compose not needed for simple Docker operations

# Change to project root directory
cd "$(dirname "$0")/.."

# Run quick comparison using Zig build system
print_status "Running quick performance comparison..."
echo ""
zig build bench

# Run PHPBench if requested
if [ "$1" = "--full" ] || [ "$1" = "-f" ]; then
    print_status "Running comprehensive PHPBench analysis..."
    echo ""
    zig build bench-full

    print_status "Generating HTML report..."
    zig build bench-html

    if [ -f "bench/results/report.html" ]; then
        print_success "HTML report generated: bench/results/report.html"
    fi
fi

# Show available commands
echo ""
print_status "Available benchmark commands:"
echo "  zig build bench               - Quick comparison"
echo "  zig build bench-full          - Comprehensive PHPBench analysis"
echo "  zig build bench-html          - Generate HTML report"
echo "  zig build bench-local         - Local benchmarks (no Docker)"

print_success "Benchmarks complete!"

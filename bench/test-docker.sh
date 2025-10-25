#!/bin/bash

# Test script to verify Docker benchmark setup
set -e

echo "🧪 Testing Docker benchmark setup..."

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    echo "❌ Docker not found - please install Docker"
    exit 1
fi

echo "✅ Docker found"

# Test building the container
echo "🔨 Testing container build..."
if docker build -f bench/Dockerfile -t identifier-bench . > /dev/null 2>&1; then
    echo "✅ Container builds successfully"
else
    echo "❌ Container build failed"
    exit 1
fi

# Test running a simple command
echo "🧪 Testing container execution..."
if docker run --rm identifier-bench echo "Container works!" > /dev/null 2>&1; then
    echo "✅ Container execution works"
else
    echo "❌ Container execution failed"
    exit 1
fi

echo "🎉 Docker benchmark setup is ready!"
echo ""
echo "Run benchmarks with:"
echo "  zig build bench       # Quick comparison"
echo "  zig build bench-full  # Full analysis"
echo "  zig build bench-html  # HTML report"

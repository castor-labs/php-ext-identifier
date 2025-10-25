#!/bin/bash

# Script to open the HTML performance report
set -e

REPORT_FILE="bench/results/performance_report.html"

echo "🚀 PHP Identifier Extension - Performance Report"
echo "================================================="

if [ ! -f "$REPORT_FILE" ]; then
    echo "❌ Report file not found: $REPORT_FILE"
    echo "The HTML report should be available after running benchmarks."
    exit 1
fi

echo "📊 Opening performance report..."
echo "Report location: $(pwd)/$REPORT_FILE"

# Try to open with different browsers/commands
if command -v xdg-open &> /dev/null; then
    xdg-open "$REPORT_FILE"
elif command -v open &> /dev/null; then
    open "$REPORT_FILE"
elif command -v start &> /dev/null; then
    start "$REPORT_FILE"
else
    echo "✅ Report is ready!"
    echo "📂 Open this file in your browser:"
    echo "   file://$(pwd)/$REPORT_FILE"
fi

echo ""
echo "📈 Report Features:"
echo "   • Interactive charts with Chart.js"
echo "   • Detailed performance tables"
echo "   • Beautiful responsive design"
echo "   • Real benchmark data from your extension"

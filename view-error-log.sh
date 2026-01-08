#!/bin/bash

# Script to view WhatsApp error logs
# Usage: ./view-error-log.sh [lines]

cd "$(dirname "$0")"

LINES=${1:-50}  # Default 50 lines if not specified

echo "=== WhatsApp Error Log ==="
echo ""

if [ -f "logs/whatsapp-error.log" ]; then
    echo "📄 File: logs/whatsapp-error.log"
    echo "📊 Size: $(du -h logs/whatsapp-error.log | cut -f1)"
    ERROR_COUNT=$(grep -c "\[ERROR\]" logs/whatsapp-error.log 2>/dev/null || echo "0")
    WARN_COUNT=$(grep -c "\[WARN\]" logs/whatsapp-error.log 2>/dev/null || echo "0")
    echo "❌ Total Errors: $ERROR_COUNT"
    echo "⚠️  Total Warnings: $WARN_COUNT"
    echo ""
    echo "📝 Last $LINES lines:"
    echo "─────────────────────────────────────────────────────────────"
    tail -n "$LINES" logs/whatsapp-error.log
    echo "─────────────────────────────────────────────────────────────"
    echo ""
    echo "💡 To follow live: tail -f logs/whatsapp-error.log"
    echo "💡 To view all: cat logs/whatsapp-error.log"
    echo "💡 To clear: > logs/whatsapp-error.log"
else
    echo "✅ No error log found - no errors or warnings yet!"
fi

echo ""
echo "=== End of Error Log ==="


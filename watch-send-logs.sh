#!/bin/bash

# Script to watch send message logs in real-time
# Usage: ./watch-send-logs.sh

cd "$(dirname "$0")"

echo "=== Monitoring Send Message Logs ==="
echo ""
echo "📝 Watching multiple log sources..."
echo ""
echo "Press Ctrl+C to stop"
echo ""
echo "─────────────────────────────────────────────────────────────"
echo ""

# Monitor both Laravel and WhatsApp logs for send message activity
tail -f storage/logs/laravel.log logs/whatsapp-server.log 2>/dev/null | grep --line-buffered -iE "send.*message|POST /api/device.*send-message|📤|📨|messageId" --color=always


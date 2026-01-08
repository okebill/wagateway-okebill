#!/bin/bash

# Script to watch sync contacts logs in real-time
# Usage: ./watch-sync-logs.sh

cd "$(dirname "$0")"

echo "=== Monitoring Sync Contacts Logs ==="
echo ""
echo "📝 Watching multiple log sources..."
echo ""
echo "Press Ctrl+C to stop"
echo ""
echo "─────────────────────────────────────────────────────────────"
echo ""

# Monitor both Laravel and WhatsApp logs for sync-related activity
tail -f storage/logs/laravel.log logs/whatsapp-server.log 2>/dev/null | grep --line-buffered -i "sync\|contact\|GET /api/device.*contacts" --color=always


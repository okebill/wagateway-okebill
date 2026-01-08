#!/bin/bash

# Script to check WhatsApp server status
# Usage: ./check-whatsapp-server.sh

echo "=== WhatsApp Server Status Check ==="
echo ""

# Check if process is running
echo "1. Process Check (port 3001):"
PID=$(lsof -ti :3001 2>/dev/null)
if [ -z "$PID" ]; then
    echo "   ❌ Server is NOT running on port 3001"
else
    echo "   ✅ Server is running"
    echo "   PID: $PID"
    ps -p $PID -o pid,user,cmd --no-headers | sed 's/^/   /'
fi
echo ""

# Check log files
echo "2. Log Files:"
if [ -f "./logs/whatsapp-server.log" ]; then
    echo "   ✅ whatsapp-server.log exists ($(du -h logs/whatsapp-server.log | cut -f1))"
    echo "   Last 10 lines:"
    tail -n 10 ./logs/whatsapp-server.log | sed 's/^/      /'
else
    echo "   ❌ whatsapp-server.log not found"
    echo "   (Server may not have been started yet)"
fi
echo ""

if [ -f "./logs/whatsapp-error.log" ]; then
    echo "   ✅ whatsapp-error.log exists ($(du -h logs/whatsapp-error.log | cut -f1))"
    ERROR_COUNT=$(grep -c "\[ERROR\]\|\[WARN\]" ./logs/whatsapp-error.log 2>/dev/null || echo "0")
    echo "   Total errors/warnings: $ERROR_COUNT"
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo "   Last 5 errors/warnings:"
        tail -n 5 ./logs/whatsapp-error.log | sed 's/^/      /'
    fi
else
    echo "   ℹ️  whatsapp-error.log not found (no errors yet)"
fi
echo ""

# Check health endpoint
echo "3. Health Check:"
if curl -s http://localhost:3001/health > /dev/null 2>&1; then
    echo "   ✅ Server is responding"
    curl -s http://localhost:3001/health | python3 -m json.tool 2>/dev/null || curl -s http://localhost:3001/health
else
    echo "   ❌ Cannot connect to server (http://localhost:3001/health)"
fi
echo ""

# Check if logs directory exists
echo "4. Directories:"
if [ -d "./logs" ]; then
    echo "   ✅ logs/ directory exists"
    echo "   Files in logs/:"
    ls -lh logs/ 2>/dev/null | tail -n +2 | sed 's/^/      /' || echo "      (empty)"
else
    echo "   ❌ logs/ directory not found"
fi
echo ""

echo "=== End of Status Check ==="

#!/bin/bash

# Script to stop WhatsApp server
# Usage: ./stop-whatsapp-server.sh

echo "Stopping WhatsApp Server..."

# Find process running on port 3001
PID=$(lsof -ti :3001)

if [ -z "$PID" ]; then
    echo "⚠️  No server found running on port 3001"
    exit 1
fi

echo "Found server process: $PID"
kill $PID

# Wait a moment
sleep 1

# Check if still running
if lsof -Pi :3001 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Server still running, force killing..."
    kill -9 $PID
    sleep 1
fi

if lsof -Pi :3001 -sTCP:LISTEN -t >/dev/null ; then
    echo "❌ Failed to stop server"
    exit 1
else
    echo "✅ Server stopped successfully"
fi


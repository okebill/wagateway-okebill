#!/bin/bash
cd "$(dirname "$0")"

echo "🔄 Restarting WhatsApp Server..."
echo ""

# Stop server
./stop-whatsapp-server.sh

# Wait a moment
sleep 2

# Start server in background
./start-whatsapp-server-background.sh


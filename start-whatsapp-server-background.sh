#!/bin/bash

# Script to start WhatsApp server in background (without PM2)
# Usage: ./start-whatsapp-server-background.sh

echo "Starting WhatsApp Server in background..."

# Change to script directory
cd "$(dirname "$0")"

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed!"
    exit 1
fi

# Check if dependencies are installed
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
fi

# Create logs directory if it doesn't exist
mkdir -p logs

# Check if server is already running
if lsof -Pi :3001 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Server is already running on port 3001"
    echo "   PID: $(lsof -Pi :3001 -sTCP:LISTEN -t)"
    exit 1
fi

# Start the server in background
echo "🚀 Starting WhatsApp server in background on port 3001..."
echo "📝 Logs will be written to: logs/whatsapp-server.log"
echo ""

# Run in background and redirect output
nohup node whatsapp-server.js >> logs/whatsapp-server.log 2>&1 &

# Get PID
PID=$!

# Wait a moment to check if it started
sleep 2

if ps -p $PID > /dev/null; then
    echo "✅ Server started successfully!"
    echo "   PID: $PID"
    echo "   Port: 3001"
    echo "   Log file: logs/whatsapp-server.log"
    echo "   Error log: logs/whatsapp-error.log"
    echo ""
    echo "To stop the server: kill $PID"
    echo "To view logs: tail -f logs/whatsapp-server.log"
    echo "To view errors: tail -f logs/whatsapp-error.log"
    echo "To check status: curl http://localhost:3001/health"
else
    echo "❌ Failed to start server. Check logs/whatsapp-error.log for errors"
    exit 1
fi


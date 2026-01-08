#!/bin/bash

# Script to start WhatsApp server without PM2
# Usage: ./start-whatsapp-server.sh

echo "Starting WhatsApp Server..."

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

# Start the server
echo "🚀 Starting WhatsApp server on port 3001..."
echo "📝 Logs will be written to: logs/whatsapp-server.log"
echo "💡 Press Ctrl+C to stop the server"
echo ""

# Run the server and redirect output
node whatsapp-server.js 2>&1 | tee -a logs/whatsapp-server.log


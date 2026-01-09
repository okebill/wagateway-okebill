#!/bin/bash

# Quick Fix untuk LID Error - WhatsApp Gateway
# Script ini akan restart WhatsApp server dengan kode yang sudah diperbaiki

echo "=========================================="
echo "  QUICK FIX - No LID for user ERROR"
echo "=========================================="
echo ""

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Step 1: Check if whatsapp-server is running
echo -e "${BLUE}[1/5]${NC} Checking WhatsApp server status..."
if pgrep -f "whatsapp-server.js" > /dev/null; then
    echo -e "${YELLOW}✓${NC} WhatsApp server is running"
    
    # Stop the server
    echo -e "${BLUE}[2/5]${NC} Stopping WhatsApp server..."
    ./stop-whatsapp-server.sh
    sleep 3
    
    if pgrep -f "whatsapp-server.js" > /dev/null; then
        echo -e "${RED}✗${NC} Failed to stop server. Killing forcefully..."
        pkill -9 -f "whatsapp-server.js"
        sleep 2
    fi
    echo -e "${GREEN}✓${NC} Server stopped"
else
    echo -e "${YELLOW}✓${NC} Server is not running"
fi

# Step 2: Verify the fix is in place
echo -e "${BLUE}[3/5]${NC} Verifying fix is in place..."
if grep -q "ensureContact" whatsapp-server.js && grep -q "getNumberId" whatsapp-server.js; then
    echo -e "${GREEN}✓${NC} LID error fix is present in whatsapp-server.js"
else
    echo -e "${RED}✗${NC} Fix not found! Please ensure code is updated."
    exit 1
fi

# Step 3: Start the server
echo -e "${BLUE}[4/5]${NC} Starting WhatsApp server with fix..."
./start-whatsapp-server.sh

# Wait for server to start
echo -e "${YELLOW}⏳${NC} Waiting for server to initialize (10 seconds)..."
sleep 10

# Step 4: Verify server is running
echo -e "${BLUE}[5/5]${NC} Verifying server is running..."
if pgrep -f "whatsapp-server.js" > /dev/null; then
    echo -e "${GREEN}✓${NC} WhatsApp server is running!"
else
    echo -e "${RED}✗${NC} Failed to start server. Check logs:"
    echo "    tail -f logs/whatsapp-server.log"
    exit 1
fi

# Check health endpoint
echo ""
echo -e "${BLUE}Testing health endpoint...${NC}"
HEALTH=$(curl -s http://localhost:3001/health 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Server health check passed"
    echo "$HEALTH" | python3 -m json.tool 2>/dev/null || echo "$HEALTH"
else
    echo -e "${YELLOW}⚠${NC} Health check unavailable (server might still be starting)"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}  FIX APPLIED SUCCESSFULLY!${NC}"
echo "=========================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Test koneksi ke nomor sendiri:"
echo "   curl -X POST 'https://wa.okebil.com/send-message' \\"
echo "     -H 'Content-Type: application/json' \\"
echo "     -d '{\"api_key\":\"wa_bv4VOSGr3pEcX5mzCbfHbitfqQngsSEK\",\"sender\":\"628988409756\",\"number\":\"628988409756\",\"message\":\"Test fix LID error\"}'"
echo ""
echo "2. Monitor log untuk melihat proses validasi nomor:"
echo "   tail -f logs/whatsapp-server.log | grep -E '🔍|🔐|Verifying|ensureContact'"
echo ""
echo "3. Test ke nomor pelanggan dari billing WiFi"
echo ""
echo "4. Jika masih error, baca dokumentasi:"
echo "   cat SOLUSI-LID-ERROR.md"
echo ""
echo "=========================================="
echo ""

# Optional: Show recent logs
echo -e "${BLUE}Recent logs (last 20 lines):${NC}"
echo "--------------------------------------"
tail -n 20 logs/whatsapp-server.log 2>/dev/null || echo "No logs available yet"
echo ""


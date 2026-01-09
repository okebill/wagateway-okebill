#!/bin/bash

# Script Testing untuk LID Error Fix
# Test berbagai skenario pengiriman pesan

echo "=========================================="
echo "  TESTING LID ERROR FIX"
echo "=========================================="
echo ""

# Warna
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Konfigurasi
API_URL="https://wa.okebil.com/send-message"
API_KEY="wa_bv4VOSGr3pEcX5mzCbfHbitfqQngsSEK"
SENDER="628988409756"

# Fungsi untuk test pengiriman
test_send() {
    local test_name="$1"
    local number="$2"
    local message="$3"
    
    echo ""
    echo -e "${BLUE}Testing: ${test_name}${NC}"
    echo "  Number: $number"
    echo "  Message: $message"
    echo ""
    
    RESPONSE=$(curl -s -X POST "$API_URL" \
        -H "Content-Type: application/json" \
        -d "{\"api_key\":\"$API_KEY\",\"sender\":\"$SENDER\",\"number\":\"$number\",\"message\":\"$message\"}")
    
    # Parse response
    SUCCESS=$(echo "$RESPONSE" | grep -o '"success":[^,}]*' | cut -d':' -f2)
    
    if [ "$SUCCESS" == "true" ]; then
        echo -e "${GREEN}✓ BERHASIL${NC}"
        echo "Response:"
        echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
    else
        echo -e "${RED}✗ GAGAL${NC}"
        echo "Response:"
        echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
        
        # Check for specific errors
        if echo "$RESPONSE" | grep -q "No LID"; then
            echo ""
            echo -e "${YELLOW}⚠ LID Error terdeteksi!${NC}"
            echo "Solusi:"
            echo "  1. Tambahkan nomor $number ke kontak WhatsApp $SENDER"
            echo "  2. Atau minta penerima chat duluan ke $SENDER"
            echo "  3. Atau tunggu sync kontak selesai"
        elif echo "$RESPONSE" | grep -q "not registered"; then
            echo ""
            echo -e "${YELLOW}⚠ Nomor tidak terdaftar di WhatsApp!${NC}"
            echo "Verifikasi nomor $number apakah benar dan aktif WhatsApp"
        elif echo "$RESPONSE" | grep -q "not connected"; then
            echo ""
            echo -e "${YELLOW}⚠ Device tidak terhubung!${NC}"
            echo "Scan QR code di: https://wa.okebil.com/whatsapp/devices"
        fi
    fi
    
    echo "--------------------------------------"
    sleep 2
}

# Check server status first
echo -e "${BLUE}[1/4] Checking server status...${NC}"
HEALTH=$(curl -s http://localhost:3001/health 2>/dev/null)
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} WhatsApp server is running"
    echo "$HEALTH" | python3 -m json.tool 2>/dev/null || echo "$HEALTH"
else
    echo -e "${RED}✗${NC} Server not responding! Start it first:"
    echo "    ./start-whatsapp-server.sh"
    exit 1
fi

echo ""
echo -e "${BLUE}[2/4] Checking device status...${NC}"
# Check device status via API
DEVICE_STATUS=$(curl -s "http://localhost:3001/api/device/$API_KEY/status" 2>/dev/null)
if echo "$DEVICE_STATUS" | grep -q '"status":"connected"'; then
    echo -e "${GREEN}✓${NC} Device is connected"
else
    echo -e "${YELLOW}⚠${NC} Device status:"
    echo "$DEVICE_STATUS" | python3 -m json.tool 2>/dev/null || echo "$DEVICE_STATUS"
    echo ""
    echo "If device is not connected, scan QR code first:"
    echo "    https://wa.okebil.com/whatsapp/devices"
    echo ""
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo -e "${BLUE}[3/4] Running tests...${NC}"
echo "=========================================="

# Test 1: Send to self (should always work)
test_send "Test 1: Kirim ke nomor sendiri" \
    "$SENDER" \
    "Test 1: Kirim ke nomor sendiri - Harus berhasil ✅"

# Test 2: Send to problematic number from log
test_send "Test 2: Kirim ke nomor pelanggan (6281292999022)" \
    "6281292999022" \
    "Test 2: Invoice tagihan dari billing WiFi - Test LID fix"

# Test 3: Send to another format
test_send "Test 3: Kirim ke nomor dengan format berbeda" \
    "081292999022" \
    "Test 3: Format nomor lokal 08xxx"

# Test 4: Send to invalid number (should fail gracefully)
test_send "Test 4: Kirim ke nomor tidak valid" \
    "621234567890" \
    "Test 4: Nomor tidak valid - Harus gagal dengan error yang jelas"

echo ""
echo -e "${BLUE}[4/4] Checking logs for validation process...${NC}"
echo "=========================================="
echo ""
echo "Recent log entries with validation process:"
echo ""
tail -n 50 logs/whatsapp-server.log 2>/dev/null | grep -E "🔍|🔐|Verifying|ensureContact|getNumberId|LID" | tail -n 20 || echo "No validation logs found"

echo ""
echo "=========================================="
echo -e "${GREEN}  TESTING COMPLETED${NC}"
echo "=========================================="
echo ""
echo "Analisis Hasil:"
echo ""
echo "1. Jika Test 1 BERHASIL tapi Test 2 GAGAL dengan LID error:"
echo "   → Nomor 6281292999022 perlu ditambahkan ke kontak"
echo ""
echo "2. Jika semua test GAGAL:"
echo "   → Cek koneksi device di dashboard"
echo "   → Restart WhatsApp server: ./quick-fix-lid-error.sh"
echo ""
echo "3. Jika Test 2 BERHASIL:"
echo "   → Fix berhasil! Uji coba dari billing WiFi"
echo ""
echo "Monitor real-time:"
echo "   tail -f logs/whatsapp-server.log | grep -E 'send-message|🔍|🔐|✅|❌'"
echo ""


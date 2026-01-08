#!/bin/bash

# Debug script untuk check device status dan functionality
# Usage: ./debug-device.sh [device_key]

if [ -z "$1" ]; then
    echo "Usage: ./debug-device.sh [device_key]"
    exit 1
fi

DEVICE_KEY=$1

echo "=== Device Debug Information ==="
echo ""
echo "🔍 Device Key: $DEVICE_KEY"
echo ""

# 1. Check database status
echo "1️⃣ Database Status:"
php artisan tinker --execute="
\$device = App\Models\WhatsappDevice::where('device_key', '$DEVICE_KEY')->first();
if (\$device) {
    echo '   Status: ' . \$device->status . PHP_EOL;
    echo '   isConnected(): ' . (\$device->isConnected() ? 'TRUE' : 'FALSE') . PHP_EOL;
    echo '   Phone: ' . (\$device->phone_number ?: 'NULL') . PHP_EOL;
    echo '   Connected At: ' . (\$device->connected_at ? \$device->connected_at : 'NULL') . PHP_EOL;
} else {
    echo '   ❌ Device not found!' . PHP_EOL;
}
"
echo ""

# 2. Check Node.js server status
echo "2️⃣ Node.js Server Status:"
curl -s "http://localhost:3001/api/device/$DEVICE_KEY/status" | python3 -m json.tool 2>/dev/null || curl -s "http://localhost:3001/api/device/$DEVICE_KEY/status"
echo ""
echo ""

# 3. Test send message (dry run)
echo "3️⃣ Test Send Message Capability:"
echo "   Checking if device can send messages..."
RESPONSE=$(curl -s -X POST "http://localhost:3001/api/device/$DEVICE_KEY/send-message" \
  -H "Content-Type: application/json" \
  -d '{"number":"628123456789","message":"test"}' 2>&1)
  
if echo "$RESPONSE" | grep -q "success"; then
    echo "   ✅ Device CAN send messages"
else
    echo "   ❌ Device CANNOT send messages"
    echo "   Response: $RESPONSE"
fi
echo ""

# 4. Test contacts capability
echo "4️⃣ Test Contacts Capability:"
CONTACTS_RESPONSE=$(curl -s "http://localhost:3001/api/device/$DEVICE_KEY/contacts" 2>&1)
if echo "$CONTACTS_RESPONSE" | grep -q "success\|contacts"; then
    echo "   ✅ Device CAN access contacts"
else
    echo "   ❌ Device CANNOT access contacts"
    echo "   Response: $CONTACTS_RESPONSE"
fi
echo ""

# 5. Check Laravel logs for errors
echo "5️⃣ Recent Laravel Logs (last 10 lines):"
if [ -f "storage/logs/laravel.log" ]; then
    tail -n 10 storage/logs/laravel.log | grep -E "error|failed|sync|send" -i || echo "   No relevant errors found"
else
    echo "   No log file found"
fi
echo ""

# 6. Check WhatsApp error logs
echo "6️⃣ Recent WhatsApp Error Logs:"
if [ -f "logs/whatsapp-error.log" ]; then
    tail -n 10 logs/whatsapp-error.log
else
    echo "   No error log file found"
fi

echo ""
echo "=== End Debug ==="


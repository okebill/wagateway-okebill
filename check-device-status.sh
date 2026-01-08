#!/bin/bash

# Script to check device status from database
# Usage: ./check-device-status.sh [device_key]

cd "$(dirname "$0")"

if [ -z "$1" ]; then
    echo "Usage: ./check-device-status.sh [device_key]"
    echo ""
    echo "Example: ./check-device-status.sh wa_ABC123..."
    echo ""
    echo "Or check all devices:"
    php artisan tinker --execute="WhatsappDevice::all(['id', 'device_name', 'device_key', 'status', 'phone_number', 'connected_at'])->each(function(\$d) { echo \$d->device_name . ' (' . \$d->device_key . '): ' . \$d->status . ' - ' . (\$d->phone_number ?: 'No phone') . ' - Connected: ' . (\$d->connected_at ?: 'Never') . PHP_EOL; });"
    exit 1
fi

DEVICE_KEY=$1

echo "=== Device Status Check ==="
echo ""
echo "🔍 Checking device: $DEVICE_KEY"
echo ""

# Check from database using Laravel tinker
php artisan tinker --execute="
\$device = App\Models\WhatsappDevice::where('device_key', '$DEVICE_KEY')->first();
if (\$device) {
    echo '📱 Device Name: ' . \$device->device_name . PHP_EOL;
    echo '🔑 Device Key: ' . \$device->device_key . PHP_EOL;
    echo '📊 Status: ' . \$device->status . PHP_EOL;
    echo '📞 Phone: ' . (\$device->phone_number ?: 'Not available') . PHP_EOL;
    echo '🔗 Connected At: ' . (\$device->connected_at ? \$device->connected_at->format('Y-m-d H:i:s') : 'Never') . PHP_EOL;
    echo '👁️  Last Seen: ' . (\$device->last_seen ? \$device->last_seen->format('Y-m-d H:i:s') : 'Never') . PHP_EOL;
    echo '✅ isConnected(): ' . (\$device->isConnected() ? 'true' : 'false') . PHP_EOL;
} else {
    echo '❌ Device not found!' . PHP_EOL;
}
"

echo ""
echo "🌐 Checking from Node.js server..."
curl -s "http://localhost:3001/api/device/$DEVICE_KEY/status" | python3 -m json.tool 2>/dev/null || curl -s "http://localhost:3001/api/device/$DEVICE_KEY/status"

echo ""
echo ""
echo "=== End of Check ==="


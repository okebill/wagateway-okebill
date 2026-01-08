# 📝 WhatsApp Server Logging Documentation

## Log Files

Server WhatsApp menggunakan sistem logging yang terpisah untuk memudahkan debugging:

### 1. **whatsapp-server.log**
- **Lokasi**: `logs/whatsapp-server.log` dan `storage/logs/whatsapp-server.log`
- **Isi**: Semua log (INFO, SUCCESS, WARN, ERROR)
- **Gunakan untuk**: Monitoring aktivitas server secara umum

```bash
# View logs
tail -f logs/whatsapp-server.log

# View last 100 lines
tail -n 100 logs/whatsapp-server.log

# Search for specific device
grep "wa_ABC123" logs/whatsapp-server.log
```

### 2. **whatsapp-error.log** ⭐ NEW
- **Lokasi**: `logs/whatsapp-error.log` dan `storage/logs/whatsapp-error.log`
- **Isi**: Hanya ERROR dan WARNING
- **Gunakan untuk**: Debugging masalah dan troubleshooting

```bash
# View error logs
tail -f logs/whatsapp-error.log

# View with helper script
./view-error-log.sh

# View last 100 errors
./view-error-log.sh 100

# Count errors
grep -c "\[ERROR\]" logs/whatsapp-error.log
grep -c "\[WARN\]" logs/whatsapp-error.log
```

## Log Levels

| Level | Deskripsi | Warna | Contoh |
|-------|-----------|-------|--------|
| `INFO` | Informasi umum | Default | Connection requests, status checks |
| `SUCCESS` | Operasi berhasil | Hijau | Client connected, QR generated |
| `WARN` | Peringatan | Kuning | Timeout warnings, retry attempts |
| `ERROR` | Error/kesalahan | Merah | Connection failed, API errors |

## Helper Scripts

### 1. `./check-whatsapp-server.sh`
Cek status server dan log files:
```bash
./check-whatsapp-server.sh
```

Output:
- ✅ Process status
- 📝 Log files info
- 🌐 Health check
- 📁 Directories

### 2. `./view-error-log.sh [lines]`
View error logs dengan statistik:
```bash
# View last 50 errors (default)
./view-error-log.sh

# View last 100 errors
./view-error-log.sh 100
```

Output:
- ❌ Total errors
- ⚠️  Total warnings
- 📝 Last N lines

### 3. `./start-whatsapp-server-background.sh`
Start server dengan info log files:
```bash
./start-whatsapp-server-background.sh
```

## Common Use Cases

### 🔍 Debugging Connection Issues
```bash
# Monitor errors in real-time
tail -f logs/whatsapp-error.log | grep "CONNECTED"

# Check authentication errors
grep "auth_failure" logs/whatsapp-error.log

# Check client initialization
grep "Initializing WhatsApp client" logs/whatsapp-server.log
```

### 📊 Statistics
```bash
# Count errors by type
echo "Errors: $(grep -c '\[ERROR\]' logs/whatsapp-error.log)"
echo "Warnings: $(grep -c '\[WARN\]' logs/whatsapp-error.log)"

# Find most common errors
grep "\[ERROR\]" logs/whatsapp-error.log | cut -d' ' -f4- | sort | uniq -c | sort -rn | head -10
```

### 🧹 Log Management
```bash
# Clear logs (keep file)
> logs/whatsapp-server.log
> logs/whatsapp-error.log

# Rotate logs (backup and clear)
mv logs/whatsapp-server.log logs/whatsapp-server.log.$(date +%Y%m%d)
mv logs/whatsapp-error.log logs/whatsapp-error.log.$(date +%Y%m%d)
touch logs/whatsapp-server.log
touch logs/whatsapp-error.log

# Archive old logs
tar -czf logs-backup-$(date +%Y%m%d).tar.gz logs/*.log.*
```

## Integration dengan Laravel

Log files juga tersimpan di Laravel's `storage/logs`:
- `storage/logs/whatsapp-server.log`
- `storage/logs/whatsapp-error.log`

Ini memudahkan integrasi dengan sistem logging Laravel yang sudah ada.

## Tips

💡 **Gunakan `whatsapp-error.log` untuk debugging** - Lebih fokus dan mudah dibaca

💡 **Monitor real-time saat connect device**:
```bash
tail -f logs/whatsapp-server.log | grep "QR\|connected\|authenticated"
```

💡 **Filter by device key**:
```bash
DEVICE_KEY="wa_ABC123"
grep "$DEVICE_KEY" logs/whatsapp-server.log | tail -50
```

💡 **Check last errors quickly**:
```bash
./view-error-log.sh 10
```

## Troubleshooting

### Log files tidak muncul?
1. Pastikan directory `logs/` ada: `mkdir -p logs`
2. Pastikan permission: `chmod 755 logs`
3. Restart server: `./restart-whatsapp-server.sh`

### Error log terlalu besar?
```bash
# Rotate manually
./rotate-logs.sh  # jika ada script ini

# Atau manual:
> logs/whatsapp-error.log
```

### Ingin log lebih detail?
Edit `whatsapp-server.js` dan tambahkan lebih banyak `log()` calls di bagian yang ingin di-monitor.


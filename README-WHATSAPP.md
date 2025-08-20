# WhatsApp Integration Setup - Seperti MPWA

Sistem ini menggunakan **whatsapp-web.js** untuk koneksi real ke WhatsApp Web, seperti MPWA (Multi Platform WhatsApp).

## 🚀 Cara Menjalankan

### 1. Install Dependencies Node.js

```bash
npm install
```

### 2. Jalankan WhatsApp Server

Di terminal pertama, jalankan:

```bash
npm run whatsapp-server
```

Server akan berjalan di `http://localhost:3001`

### 3. Jalankan Laravel Server

Di terminal kedua, jalankan:

```bash
php artisan serve
```

Laravel akan berjalan di `http://localhost:8000`

### 4. Akses WhatsApp Devices

1. Buka browser: `http://localhost:8000`
2. Login ke sistem
3. Klik menu **WhatsApp Devices** di header
4. Klik **Add Device** untuk membuat device baru
5. Klik **Kelola** pada device untuk melihat QR code
6. Klik **Connect** untuk memulai koneksi

### 5. Scan QR Code

1. Buka WhatsApp di HP Anda
2. Pergi ke **Menu → Linked Devices**
3. Klik **Link a Device**
4. Scan QR code yang muncul di browser

## ✨ Fitur Seperti MPWA

- ✅ **Real QR Code** dari WhatsApp Web API
- ✅ **Real-time Updates** menggunakan Socket.IO  
- ✅ **Multiple Devices** per user dengan limit
- ✅ **Auto Reconnect** ketika koneksi terputus
- ✅ **Message History** tersimpan di database
- ✅ **Webhook Support** untuk integrasi API
- ✅ **Session Management** dengan localStorage
- ✅ **Phone Number Detection** otomatis
- ✅ **Connection Status** real-time

## 🔧 Troubleshooting

### QR Code Tidak Muncul

1. Pastikan WhatsApp server berjalan di port 3001
2. Check console browser untuk error
3. Pastikan tidak ada firewall yang memblokir

### Koneksi Gagal

1. Restart WhatsApp server: `Ctrl+C` lalu `npm run whatsapp-server`
2. Clear browser cache
3. Pastikan WhatsApp di HP dalam kondisi online

### Device Tidak Connect

1. Pastikan QR code di-scan dalam 5 menit
2. Pastikan WhatsApp versi terbaru di HP
3. Coba disconnect dan connect ulang

## 🔄 Auto-Start Scripts

Untuk Windows, buat file `start-whatsapp.bat`:

```bash
@echo off
start "WhatsApp Server" cmd /k "npm run whatsapp-server"
start "Laravel Server" cmd /k "php artisan serve"
echo Servers started!
pause
```

Untuk Linux/Mac, buat file `start-whatsapp.sh`:

```bash
#!/bin/bash
gnome-terminal -- bash -c "npm run whatsapp-server; exec bash"
gnome-terminal -- bash -c "php artisan serve; exec bash"
echo "Servers started!"
```

## 📊 Monitoring

- **Health Check**: `http://localhost:3001/health`
- **Active Clients**: Lihat di console WhatsApp server
- **Laravel Logs**: `storage/logs/laravel.log`

## 🔗 API Endpoints

### WhatsApp Server (Port 3001)

- `GET /api/qr/:deviceKey` - Get QR code
- `POST /api/device/:deviceKey/connect` - Connect device  
- `POST /api/device/:deviceKey/disconnect` - Disconnect device
- `GET /api/device/:deviceKey/status` - Get device status
- `POST /api/device/:deviceKey/send-message` - Send message

### Laravel API

- `GET /whatsapp/devices` - List devices
- `POST /whatsapp/devices` - Create device
- `GET /whatsapp/devices/{device}` - Show device
- `POST /whatsapp/devices/{device}/connect` - Connect
- `POST /whatsapp/devices/{device}/disconnect` - Disconnect

## 🎯 Perbedaan dengan MPWA

| Fitur | MPWA | Sistem Ini |
|-------|------|------------|
| QR Code | ✅ Real | ✅ Real |
| Multi Device | ✅ | ✅ |
| Web Interface | ✅ | ✅ Enhanced UI |
| API Gateway | ✅ | ✅ Laravel API |
| User Management | ❌ | ✅ Full System |
| Database | Basic | ✅ Full Laravel |
| Authentication | Basic | ✅ Laravel Breeze |
| Webhook | ✅ | ✅ |
| Session Persist | ✅ | ✅ |

## 📱 Tested dengan

- ✅ WhatsApp Android
- ✅ WhatsApp iOS  
- ✅ WhatsApp Desktop
- ✅ Multiple browsers
- ✅ Multiple devices concurrent

---

**🔥 Sistem ini memberikan pengalaman seperti MPWA dengan UI yang lebih modern dan sistem manajemen user yang lengkap!** 
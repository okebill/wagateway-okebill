# 📱 WhatsApp Gateway

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel">
    <img src="https://img.shields.io/badge/Node.js-18.x-339933?style=for-the-badge&logo=node.js" alt="Node.js">
    <img src="https://img.shields.io/badge/WhatsApp-Web_API-25D366?style=for-the-badge&logo=whatsapp" alt="WhatsApp">
    <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License">
</p>

<p align="center">
    <strong>🚀 Production-Ready WhatsApp Gateway with Laravel & Node.js</strong><br>
    Complete WhatsApp Web API integration with multi-device support, contact management, and MPWA compatibility.
</p>

---

## ✨ Features

### 🔐 **Admin Management**
- **Admin-only Access**: Secure user management restricted to administrators
- **User Approval System**: New registrations require admin approval
- **Role-based Permissions**: Admin and User role separation
- **User Statistics**: Complete dashboard with user analytics

### 📱 **WhatsApp Integration**
- **Real WhatsApp Connection**: Direct integration with WhatsApp Web API
- **Multi-Device Support**: Manage multiple WhatsApp devices
- **QR Code Authentication**: Real-time QR code generation and scanning
- **Contact Synchronization**: Automatic contact and group sync
- **Message Sending**: Send messages to individuals and groups

### 🔄 **MPWA Compatibility**
- **100% MPWA Compatible**: Identical API endpoints and response formats
- **GET/POST Support**: Flexible API method support
- **Phone Number Normalization**: Automatic Indonesian phone number formatting
- **Mikrotik Integration**: Ready-to-use PPP notification scripts

### 🛠 **Advanced Features**
- **Contact Management**: Full contact list with search and pagination
- **Group Support**: Complete WhatsApp group integration
- **API Documentation**: Built-in API info with example requests
- **Session Management**: Persistent WhatsApp sessions
- **Real-time Updates**: Socket.IO for live status updates

---

## 📋 Requirements

### System Requirements
- **PHP**: 8.1 or higher
- **Node.js**: 18.x or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.3+)
- **Composer**: Latest version
- **NPM**: Latest version

### Server Requirements
- **Memory**: Minimum 1GB RAM (2GB recommended)
- **Storage**: Minimum 2GB free space
- **Network**: Stable internet connection for WhatsApp Web API

---

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/your-username/whatsapp-gateway.git
cd whatsapp-gateway
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:
```env
APP_NAME="WhatsApp Gateway"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_gateway
DB_USERNAME=root
DB_PASSWORD=

WHATSAPP_PORT=3001
```

### 5. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE whatsapp_gateway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Create admin user
php artisan db:seed --class=AdminUserSeeder
```

### 6. Start Services
```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: WhatsApp Server
node whatsapp-server.js
```

### 7. Access Application
- **Web Interface**: http://localhost:8000
- **Admin Login**: adminwa@localhost.com / adminwa

---

## ⚙️ Configuration

### Admin User
Default admin credentials:
- **Email**: `adminwa@localhost.com`
- **Password**: `adminwa`

> ⚠️ **Security**: Change admin password after first login!

### WhatsApp Server
The Node.js server runs on port 3001 and handles:
- WhatsApp Web API connections
- QR code generation
- Real-time messaging
- Contact synchronization

---

## 📖 Usage

### 1. Admin Dashboard
- Access user management at `/users`
- Approve new user registrations at `/users-pending-approval`
- Monitor system statistics and user activities

### 2. WhatsApp Device Management
- Add new devices at `/whatsapp/devices`
- Scan QR codes for authentication
- Send test messages and sync contacts
- View API credentials and documentation

### 3. Contact Management
- Automatic contact synchronization
- Search and filter contacts/groups
- Pagination options (50-2000 entries per page)
- Send messages directly from contact list

---

## 🔌 API Documentation

### MPWA Compatible Endpoints

#### Send Message
```bash
# POST Method (JSON)
POST /send-message
Content-Type: application/json

{
    "api_key": "your_device_key",
    "sender": "628123456789",
    "number": "628987654321",
    "message": "Hello World"
}

# GET Method (URL Parameters)
GET /send-message?api_key=your_device_key&sender=628123456789&number=628987654321&message=Hello%20World
```

#### Phone Number Formats
The system automatically normalizes phone numbers:
- `0812345678` → `62812345678`
- `812345678` → `62812345678`
- `62812345678` → `62812345678` (no change)

#### Group Messages
Send to WhatsApp groups using group ID:
```bash
POST /send-message
{
    "api_key": "your_device_key",
    "sender": "628123456789", 
    "number": "120363XXXXXXXXX@g.us",
    "message": "Hello Group"
}
```

### Response Format
```json
{
    "success": true,
    "message": "Message sent successfully",
    "data": {
        "message_id": "messageId",
        "timestamp": 1640995200,
        "to": "628987654321@c.us",
        "sender": "628123456789"
    }
}
```

---

## 🌐 Deployment

### aaPanel Deployment
This application is **100% compatible with aaPanel**. Use the included deployment script:

```bash
chmod +x deploy-aapanel.sh
sudo bash deploy-aapanel.sh
```

The script automatically:
- Configures Nginx and PHP-FPM
- Sets up process management with PM2
- Configures SSL certificates
- Sets proper file permissions
- Optimizes for production

### Production Checklist
- [ ] Change admin password
- [ ] Configure firewall (ports 80, 443, 3001)
- [ ] Set up SSL certificates
- [ ] Configure backup strategy
- [ ] Monitor system resources
- [ ] Set up log rotation

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards for PHP
- Use ESLint configuration for JavaScript
- Write descriptive commit messages
- Add tests for new features
- Update documentation as needed

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🆘 Support

### Documentation
- [Installation Guide](docs/installation.md)
- [API Reference](docs/api.md)
- [Deployment Guide](docs/deployment.md)

### Community
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/your-username/whatsapp-gateway/issues)
- 💡 **Feature Requests**: [GitHub Discussions](https://github.com/your-username/whatsapp-gateway/discussions)
- ❓ **Questions**: [Stack Overflow](https://stackoverflow.com/questions/tagged/whatsapp-gateway)

### Security
If you discover a security vulnerability, please send an email to security@yoursite.com. All security vulnerabilities will be promptly addressed.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework
- [whatsapp-web.js](https://github.com/pedroslopez/whatsapp-web.js) - WhatsApp Web API
- [Socket.IO](https://socket.io) - Real-time communication
- [Tailwind CSS](https://tailwindcss.com) - CSS framework

---

<p align="center">
    <strong>Made with ❤️ for the developer community</strong><br>
    <a href="https://github.com/your-username/whatsapp-gateway">⭐ Star this project if you find it helpful!</a>
</p>

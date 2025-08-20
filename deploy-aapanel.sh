#!/bin/bash

# WhatsApp Gateway Deployment Script for aaPanel
# Usage: bash deploy-aapanel.sh

echo "🚀 Starting WhatsApp Gateway deployment on aaPanel..."

# Variables
DOMAIN="yourdomain.com"
PROJECT_PATH="/www/wwwroot/$DOMAIN"
NODE_PATH="/www/server/nodejs/v16/bin"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root or with sudo"
    exit 1
fi

# Step 1: Setup directories and permissions
print_status "Setting up directories and permissions..."
cd $PROJECT_PATH
mkdir -p logs
mkdir -p wa-sessions
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views

chown -R www:www $PROJECT_PATH
chmod -R 755 $PROJECT_PATH
chmod -R 775 $PROJECT_PATH/storage
chmod -R 775 $PROJECT_PATH/bootstrap/cache
chmod -R 775 $PROJECT_PATH/wa-sessions

# Step 2: Install PHP dependencies
print_status "Installing PHP dependencies..."
/usr/bin/composer install --optimize-autoloader --no-dev

# Step 3: Install Node.js dependencies
print_status "Installing Node.js dependencies..."
export PATH=$NODE_PATH:$PATH
npm install --production

# Step 4: Install PM2 globally
print_status "Installing PM2 process manager..."
npm install -g pm2

# Step 5: Setup environment
print_status "Setting up environment configuration..."
if [ ! -f .env ]; then
    cp .env.production .env
    print_warning "Please edit .env file with your database and domain settings"
fi

# Step 6: Laravel setup
print_status "Setting up Laravel..."
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 7: Database migration (only if .env is configured)
if grep -q "DB_DATABASE=" .env && ! grep -q "your_database_name" .env; then
    print_status "Running database migrations..."
    php artisan migrate --force
else
    print_warning "Database not configured. Please setup .env and run: php artisan migrate"
fi

# Step 8: Start WhatsApp server with PM2
print_status "Starting WhatsApp server..."
pm2 start ecosystem.config.js --env production
pm2 save
pm2 startup

# Step 9: Setup Nginx configuration
print_status "Setting up Nginx configuration..."
cat > /www/server/panel/vhost/nginx/$DOMAIN.conf << 'EOF'
server {
    listen 80;
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/yourdomain.com/public;
    
    #SSL-START SSL Settings
    ssl_certificate /www/server/panel/vhost/cert/yourdomain.com/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    #SSL-END
    
    #ERROR-PAGE-START Error page configuration
    error_page 404 /404.html;
    error_page 502 /502.html;
    #ERROR-PAGE-END
    
    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-81.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
        include pathinfo.conf;
    }
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)$ {
        return 404;
    }
    
    location ~ \.log$ {
        return 404;
    }
    
    # WhatsApp Server Proxy (optional - for API calls)
    location /ws {
        proxy_pass http://127.0.0.1:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
    
    access_log /www/wwwlogs/yourdomain.com.log;
    error_log /www/wwwlogs/yourdomain.com.error.log;
}
EOF

# Replace domain placeholders
sed -i "s/yourdomain.com/$DOMAIN/g" /www/server/panel/vhost/nginx/$DOMAIN.conf

# Step 10: Restart services
print_status "Restarting services..."
systemctl reload nginx

# Step 11: Setup firewall
print_status "Configuring firewall..."
if command -v ufw &> /dev/null; then
    ufw allow 80
    ufw allow 443
    ufw allow 3001
    print_status "Firewall configured (UFW)"
elif command -v firewalld &> /dev/null; then
    firewall-cmd --permanent --add-port=80/tcp
    firewall-cmd --permanent --add-port=443/tcp
    firewall-cmd --permanent --add-port=3001/tcp
    firewall-cmd --reload
    print_status "Firewall configured (FirewallD)"
fi

# Step 12: Final status check
print_status "Checking services status..."
systemctl status nginx | head -3
pm2 status

print_status "🎉 Deployment completed!"
echo ""
echo "📋 Next Steps:"
echo "1. Configure your domain DNS to point to this server"
echo "2. Edit .env file with your database settings"
echo "3. Run: php artisan migrate (if not done automatically)"
echo "4. Access your site: https://$DOMAIN"
echo "5. Access API: https://$DOMAIN/send-message"
echo ""
echo "📊 Monitoring:"
echo "- PM2 Dashboard: pm2 monit"
echo "- Logs: pm2 logs whatsapp-gateway"
echo "- Laravel Logs: tail -f storage/logs/laravel.log"
echo ""
echo "🔧 Management Commands:"
echo "- Restart WhatsApp: pm2 restart whatsapp-gateway"
echo "- View Status: pm2 status"
echo "- Clear Cache: php artisan cache:clear" 
#!/bin/bash

##############################################################################
# FastrackGPS - Complete Server Installation Script
# Ubuntu 20.04/22.04 LTS Compatible
##############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration variables
DOMAIN=${1:-"fastrackgps.local"}
DB_ROOT_PASS=${2:-$(openssl rand -base64 32)}
DB_USER="fastrackgps"
DB_PASS=$(openssl rand -base64 32)
DB_NAME="fastrackgps"
APP_USER="fastrackgps"
APP_DIR="/var/www/fastrackgps"
LOG_FILE="/tmp/fastrackgps-install.log"

echo -e "${BLUE}##############################################################################"
echo -e "# FastrackGPS - Complete Server Installation"
echo -e "# Domain: $DOMAIN"
echo -e "# Installation Log: $LOG_FILE"
echo -e "##############################################################################${NC}"

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root. Run as a user with sudo privileges."
fi

# Check OS compatibility
if ! grep -q "Ubuntu" /etc/os-release; then
    warning "This script is optimized for Ubuntu. Other distributions may require modifications."
fi

##############################################################################
# STEP 1: System Update and Basic Packages
##############################################################################

log "Step 1: Updating system packages..."
sudo apt update && sudo apt upgrade -y

log "Installing essential packages..."
sudo apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release \
    htop \
    nano \
    fail2ban \
    ufw \
    supervisor \
    cron \
    logrotate

##############################################################################
# STEP 2: PHP 8.1+ Installation
##############################################################################

log "Step 2: Installing PHP 8.1..."
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
    php8.1 \
    php8.1-fpm \
    php8.1-cli \
    php8.1-common \
    php8.1-mysql \
    php8.1-zip \
    php8.1-gd \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-json \
    php8.1-intl \
    php8.1-readline \
    php8.1-opcache

# Configure PHP
log "Configuring PHP..."
sudo tee /etc/php/8.1/fpm/conf.d/99-fastrackgps.ini > /dev/null <<EOF
; FastrackGPS PHP Configuration
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_vars = 3000
date.timezone = America/Sao_Paulo

; Security Settings
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Session Security
session.cookie_lifetime = 0
session.cookie_secure = 1
session.cookie_httponly = 1
session.use_strict_mode = 1
session.cookie_samesite = "Strict"

; OPcache Configuration
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
EOF

# Create PHP log directory
sudo mkdir -p /var/log/php
sudo chown www-data:www-data /var/log/php

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
sudo systemctl enable php8.1-fpm

##############################################################################
# STEP 3: Composer Installation
##############################################################################

log "Step 3: Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version

##############################################################################
# STEP 4: Node.js Installation
##############################################################################

log "Step 4: Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verify installation
node --version
npm --version

##############################################################################
# STEP 5: MySQL 8.0 Installation
##############################################################################

log "Step 5: Installing MySQL 8.0..."
sudo apt install -y mysql-server mysql-client

# Secure MySQL installation
log "Securing MySQL installation..."
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_ROOT_PASS';"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.user WHERE User='';"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "DROP DATABASE IF EXISTS test;"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "FLUSH PRIVILEGES;"

# Create application database and user
log "Creating application database..."
sudo mysql -u root -p"$DB_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -u root -p"$DB_ROOT_PASS" -e "FLUSH PRIVILEGES;"

# Configure MySQL
sudo tee /etc/mysql/mysql.conf.d/99-fastrackgps.cnf > /dev/null <<EOF
[mysqld]
# FastrackGPS MySQL Configuration
max_connections = 200
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 1
innodb_lock_wait_timeout = 50

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Security
skip-name-resolve
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO
EOF

sudo systemctl restart mysql
sudo systemctl enable mysql

##############################################################################
# STEP 6: Nginx Installation and Configuration
##############################################################################

log "Step 6: Installing and configuring Nginx..."
sudo apt install -y nginx

# Create FastrackGPS site configuration
sudo tee /etc/nginx/sites-available/fastrackgps > /dev/null <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    
    # Redirect HTTP to HTTPS
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $DOMAIN www.$DOMAIN;
    
    root $APP_DIR/public;
    index index.php index.html index.htm;
    
    # SSL Configuration (placeholder - will be configured with Let's Encrypt)
    ssl_certificate /etc/ssl/certs/ssl-cert-snakeoil.pem;
    ssl_certificate_key /etc/ssl/private/ssl-cert-snakeoil.key;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;
    
    # Main location
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /composer\. {
        deny all;
    }
    
    location ~ /\.env {
        deny all;
    }
    
    # API endpoints with special handling
    location /api/ {
        try_files \$uri \$uri/ /index.php?\$query_string;
        
        # CORS headers for API
        add_header Access-Control-Allow-Origin "*" always;
        add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization" always;
        
        if (\$request_method = OPTIONS) {
            return 204;
        }
    }
    
    # WebSocket proxy (for real-time features)
    location /ws {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
    
    # Logs
    access_log /var/log/nginx/fastrackgps_access.log;
    error_log /var/log/nginx/fastrackgps_error.log;
}
EOF

# Enable site and disable default
sudo ln -sf /etc/nginx/sites-available/fastrackgps /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test nginx configuration
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx

##############################################################################
# STEP 7: Application User and Directory Setup
##############################################################################

log "Step 7: Setting up application user and directories..."

# Create application user
if ! id "$APP_USER" &>/dev/null; then
    sudo useradd -r -s /bin/bash -d "$APP_DIR" -m "$APP_USER"
fi

# Create application directory structure
sudo mkdir -p "$APP_DIR"/{public,storage/{logs,cache,sessions,uploads},database/backups}

# Set permissions
sudo chown -R "$APP_USER:www-data" "$APP_DIR"
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage"

##############################################################################
# STEP 8: SSL Certificate with Let's Encrypt
##############################################################################

log "Step 8: Installing Let's Encrypt SSL certificate..."
sudo apt install -y certbot python3-certbot-nginx

# Only attempt SSL if domain is not local
if [[ "$DOMAIN" != *".local" ]] && [[ "$DOMAIN" != "localhost" ]]; then
    log "Obtaining SSL certificate for $DOMAIN..."
    sudo certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos --email "admin@$DOMAIN" || warning "SSL certificate setup failed. You can run 'sudo certbot --nginx' manually later."
else
    warning "Skipping SSL certificate for local domain: $DOMAIN"
fi

##############################################################################
# STEP 9: Redis Installation (for caching and sessions)
##############################################################################

log "Step 9: Installing Redis..."
sudo apt install -y redis-server

# Configure Redis
sudo tee /etc/redis/redis.conf.d/99-fastrackgps.conf > /dev/null <<EOF
# FastrackGPS Redis Configuration
maxmemory 128mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
EOF

sudo systemctl restart redis-server
sudo systemctl enable redis-server

##############################################################################
# STEP 10: Firewall Configuration
##############################################################################

log "Step 10: Configuring firewall..."
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow essential services
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306 comment 'MySQL'

# Enable firewall
sudo ufw --force enable

##############################################################################
# STEP 11: Fail2Ban Configuration
##############################################################################

log "Step 11: Configuring Fail2Ban..."
sudo tee /etc/fail2ban/jail.d/fastrackgps.conf > /dev/null <<EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true

[nginx-noscript]
enabled = true

[nginx-badbots]
enabled = true

[nginx-noproxy]
enabled = true

[php-url-fopen]
enabled = true
EOF

sudo systemctl restart fail2ban
sudo systemctl enable fail2ban

##############################################################################
# STEP 12: System Services and Monitoring
##############################################################################

log "Step 12: Setting up system services..."

# Create systemd service for WebSocket server
sudo tee /etc/systemd/system/fastrackgps-websocket.service > /dev/null <<EOF
[Unit]
Description=FastrackGPS WebSocket Server
After=network.target

[Service]
Type=simple
User=$APP_USER
WorkingDirectory=$APP_DIR
ExecStart=/usr/bin/php $APP_DIR/bin/websocket-server.php
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

# Create systemd service for GPS data processor
sudo tee /etc/systemd/system/fastrackgps-gps-processor.service > /dev/null <<EOF
[Unit]
Description=FastrackGPS GPS Data Processor
After=network.target mysql.service

[Service]
Type=simple
User=$APP_USER
WorkingDirectory=$APP_DIR
ExecStart=/usr/bin/php $APP_DIR/bin/gps-processor.php
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

# Enable services (will start after application deployment)
sudo systemctl daemon-reload

##############################################################################
# STEP 13: Cron Jobs Setup
##############################################################################

log "Step 13: Setting up cron jobs..."
sudo tee /etc/cron.d/fastrackgps > /dev/null <<EOF
# FastrackGPS Cron Jobs
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

# Process expired GPS commands every 5 minutes
*/5 * * * * $APP_USER cd $APP_DIR && php bin/console gps:process-expired-commands

# Clean old GPS positions daily at 2 AM
0 2 * * * $APP_USER cd $APP_DIR && php bin/console gps:clean-old-positions

# Generate daily reports at 3 AM
0 3 * * * $APP_USER cd $APP_DIR && php bin/console reports:generate-daily

# Database backup daily at 1 AM
0 1 * * * root $APP_DIR/scripts/maintenance/backup-database.sh

# System cleanup weekly
0 4 * * 0 root $APP_DIR/scripts/maintenance/system-cleanup.sh
EOF

##############################################################################
# STEP 14: Log Rotation
##############################################################################

log "Step 14: Setting up log rotation..."
sudo tee /etc/logrotate.d/fastrackgps > /dev/null <<EOF
$APP_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    copytruncate
    su $APP_USER www-data
}

$APP_DIR/storage/logs/gps/*.log {
    hourly
    missingok
    rotate 168
    compress
    delaycompress
    notifempty
    copytruncate
    su $APP_USER www-data
}
EOF

##############################################################################
# STEP 15: System Monitoring
##############################################################################

log "Step 15: Installing system monitoring tools..."
sudo apt install -y htop iotop nethogs

# Install and configure Netdata for monitoring
bash <(curl -Ss https://my-netdata.io/kickstart.sh) --dont-wait --disable-telemetry

##############################################################################
# FINAL STEPS: Save Configuration
##############################################################################

log "Saving installation configuration..."
sudo tee "$APP_DIR/.install-config" > /dev/null <<EOF
# FastrackGPS Installation Configuration
# Generated on: $(date)

DOMAIN=$DOMAIN
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
DB_ROOT_PASS=$DB_ROOT_PASS
APP_USER=$APP_USER
APP_DIR=$APP_DIR
INSTALL_DATE=$(date)
PHP_VERSION=$(php --version | head -n1)
NGINX_VERSION=$(nginx -v 2>&1)
MYSQL_VERSION=$(mysql --version)
EOF

sudo chown "$APP_USER:$APP_USER" "$APP_DIR/.install-config"
sudo chmod 600 "$APP_DIR/.install-config"

##############################################################################
# INSTALLATION COMPLETE
##############################################################################

echo -e "${GREEN}"
echo "##############################################################################"
echo "# FastrackGPS Server Installation Complete!"
echo "##############################################################################"
echo ""
echo "📋 INSTALLATION SUMMARY:"
echo "  Domain: $DOMAIN"
echo "  App Directory: $APP_DIR"
echo "  Database: $DB_NAME"
echo "  Database User: $DB_USER"
echo ""
echo "🔐 CREDENTIALS (SAVE THESE SECURELY):"
echo "  MySQL Root Password: $DB_ROOT_PASS"
echo "  MySQL App Password: $DB_PASS"
echo ""
echo "📁 CONFIGURATION FILES:"
echo "  Nginx Config: /etc/nginx/sites-available/fastrackgps"
echo "  PHP Config: /etc/php/8.1/fpm/conf.d/99-fastrackgps.ini"
echo "  MySQL Config: /etc/mysql/mysql.conf.d/99-fastrackgps.cnf"
echo "  Install Config: $APP_DIR/.install-config"
echo ""
echo "🚀 NEXT STEPS:"
echo "  1. Deploy FastrackGPS application to $APP_DIR"
echo "  2. Run database migration script"
echo "  3. Configure environment variables"
echo "  4. Start application services"
echo ""
echo "📝 LOG FILE: $LOG_FILE"
echo "##############################################################################"
echo -e "${NC}"

# Save credentials to file
echo "MySQL Root Password: $DB_ROOT_PASS" | sudo tee /root/mysql-credentials.txt > /dev/null
echo "MySQL App Password: $DB_PASS" | sudo tee -a /root/mysql-credentials.txt > /dev/null
sudo chmod 600 /root/mysql-credentials.txt

log "Installation completed successfully!"
log "Credentials saved to /root/mysql-credentials.txt"
log "System ready for FastrackGPS application deployment"
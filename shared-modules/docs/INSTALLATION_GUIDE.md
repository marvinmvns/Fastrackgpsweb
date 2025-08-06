# FastrackGPS - Complete Installation Guide

This guide provides step-by-step instructions for installing and configuring the modernized FastrackGPS system on Ubuntu 20.04/22.04 LTS.

## 🚀 Quick Start

For a fully automated installation, run:

```bash
# Download and run the server setup script
wget https://raw.githubusercontent.com/your-repo/fastrackgps/main/scripts/install/server-setup.sh
chmod +x server-setup.sh
./server-setup.sh yourdomain.com
```

## 📋 Prerequisites

- **Operating System:** Ubuntu 20.04 or 22.04 LTS
- **RAM:** Minimum 2GB (4GB+ recommended)
- **Storage:** Minimum 20GB SSD
- **Domain:** Valid domain name pointing to your server IP
- **User:** Non-root user with sudo privileges

## 🔧 Manual Installation

If you prefer manual installation or need to customize the setup:

### Step 1: System Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release htop nano \
    fail2ban ufw supervisor cron logrotate
```

### Step 2: PHP 8.1+ Installation

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-cli php8.1-common \
    php8.1-mysql php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl \
    php8.1-xml php8.1-bcmath php8.1-json php8.1-intl php8.1-readline \
    php8.1-opcache

# Configure PHP
sudo nano /etc/php/8.1/fpm/conf.d/99-fastrackgps.ini
```

**PHP Configuration:**
```ini
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

; OPcache Configuration
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1
```

### Step 3: Composer Installation

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### Step 4: MySQL 8.0 Installation

```bash
# Install MySQL
sudo apt install -y mysql-server mysql-client

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

**MySQL Commands:**
```sql
CREATE DATABASE fastrackgps CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fastrackgps'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON fastrackgps.* TO 'fastrackgps'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5: Nginx Installation

```bash
# Install Nginx
sudo apt install -y nginx

# Create site configuration
sudo nano /etc/nginx/sites-available/fastrackgps
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/fastrackgps/public;
    index index.php index.html;
    
    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Assets caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # WebSocket proxy
    location /ws {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    # Deny access to sensitive files
    location ~ /\. { deny all; }
    location ~ /composer\. { deny all; }
    location ~ /\.env { deny all; }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/fastrackgps /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### Step 6: SSL Certificate

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Step 7: Redis Installation

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
```

**Redis Configuration:**
```conf
maxmemory 128mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server
```

## 📁 Application Deployment

### Step 1: Download Application

```bash
# Create application directory
sudo mkdir -p /var/www/fastrackgps
cd /var/www/fastrackgps

# Clone or download application files
# (Replace with your actual repository or file source)
sudo git clone https://github.com/your-repo/fastrackgps.git .

# Or upload files manually to this directory
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install Node.js dependencies (if needed)
npm install
npm run build
```

### Step 3: Set Permissions

```bash
# Create application user
sudo useradd -r -s /bin/bash -d /var/www/fastrackgps -m fastrackgps

# Set directory permissions
sudo chown -R fastrackgps:www-data /var/www/fastrackgps
sudo chmod -R 755 /var/www/fastrackgps
sudo chmod -R 775 /var/www/fastrackgps/storage
sudo chmod -R 775 /var/www/fastrackgps/database/backups
```

### Step 4: Environment Configuration

```bash
# Copy environment configuration
cd /var/www/fastrackgps
sudo cp .env.example .env
sudo nano .env
```

**Update .env file with your settings:**
```env
APP_NAME=FastrackGPS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_SECRET=your-generated-secret-key

DB_HOST=localhost
DB_PORT=3306
DB_NAME=fastrackgps
DB_USER=fastrackgps
DB_PASS=your-database-password

GOOGLE_MAPS_API_KEY=your-google-maps-api-key
```

### Step 5: Database Setup

```bash
# Run database setup script
sudo chmod +x scripts/database/setup-database.sh
sudo ./scripts/database/setup-database.sh
```

## 🔧 System Services

### Configure System Services

```bash
# GPS Processor Service
sudo systemctl enable fastrackgps-gps-processor
sudo systemctl start fastrackgps-gps-processor

# WebSocket Service
sudo systemctl enable fastrackgps-websocket
sudo systemctl start fastrackgps-websocket
```

### Configure Firewall

```bash
# Configure UFW firewall
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw --force enable
```

### Configure Fail2ban

```bash
# Create Fail2ban configuration
sudo nano /etc/fail2ban/jail.d/fastrackgps.conf
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true

[nginx-noscript]
enabled = true
```

```bash
sudo systemctl restart fail2ban
sudo systemctl enable fail2ban
```

## 🔄 Automated Maintenance

### Setup Cron Jobs

```bash
# Edit crontab
sudo crontab -e
```

**Add these cron jobs:**
```cron
# Process expired GPS commands every 5 minutes
*/5 * * * * /var/www/fastrackgps/bin/console gps:process-expired-commands

# Clean old GPS positions daily at 2 AM
0 2 * * * /var/www/fastrackgps/bin/console gps:clean-old-positions

# Database backup daily at 1 AM
0 1 * * * /var/www/fastrackgps/scripts/maintenance/backup-database.sh

# System cleanup weekly
0 4 * * 0 /var/www/fastrackgps/scripts/maintenance/system-cleanup.sh
```

### Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/fastrackgps
```

```conf
/var/www/fastrackgps/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    copytruncate
    su fastrackgps www-data
}
```

## ✅ Installation Verification

### Test Installation

```bash
# Check service status
sudo systemctl status nginx php8.1-fpm mysql redis-server

# Check FastrackGPS services
sudo systemctl status fastrackgps-gps-processor fastrackgps-websocket

# Test database connection
mysql -u fastrackgps -p fastrackgps -e "SELECT VERSION();"

# Check logs
tail -f /var/www/fastrackgps/storage/logs/app.log
```

### Access Web Interface

1. Open your browser and navigate to `https://yourdomain.com`
2. You should see the FastrackGPS login page
3. Use the default admin credentials:
   - Email: `admin@fastrackgps.com`
   - Password: `password` (change this immediately!)

## 🔐 Security Hardening

### Change Default Passwords

```bash
# Change default admin password through web interface
# Update database passwords
# Generate new APP_SECRET in .env file
```

### Update System

```bash
# Enable automatic security updates
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

### Monitor System

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs
```

## 🚨 Troubleshooting

### Common Issues

**1. Permission Errors:**
```bash
sudo chown -R fastrackgps:www-data /var/www/fastrackgps
sudo chmod -R 775 /var/www/fastrackgps/storage
```

**2. Database Connection Issues:**
```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u fastrackgps -p -h localhost fastrackgps
```

**3. PHP-FPM Issues:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check PHP error logs
sudo tail -f /var/log/php/error.log
```

**4. Nginx Issues:**
```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log
```

### Log Locations

- **Application Logs:** `/var/www/fastrackgps/storage/logs/`
- **Nginx Logs:** `/var/log/nginx/`
- **PHP Logs:** `/var/log/php/`
- **MySQL Logs:** `/var/log/mysql/`
- **System Logs:** `journalctl -u service-name`

## 📚 Additional Resources

- **Documentation:** `/var/www/fastrackgps/docs/`
- **API Documentation:** `https://yourdomain.com/api/docs`
- **Support:** Create issues on GitHub
- **Configuration Files:** All stored in `/var/www/fastrackgps/config/`

## 🔄 Updates and Maintenance

### Updating the Application

```bash
# Backup current installation
sudo /var/www/fastrackgps/scripts/maintenance/backup-database.sh

# Download updates
cd /var/www/fastrackgps
sudo git pull origin main

# Update dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Run any database migrations
sudo ./scripts/database/migrate.sh

# Restart services
sudo systemctl restart nginx php8.1-fpm fastrackgps-*
```

### Regular Maintenance

The system includes automated maintenance scripts that run via cron:

- **Daily:** Database backups, log rotation, cache cleanup
- **Weekly:** System cleanup, security updates
- **Monthly:** Full system health check

Monitor these via the logs and health reports generated in `/var/www/fastrackgps/storage/logs/`.

---

**FastrackGPS** - Modern GPS Tracking System
*Complete installation and configuration guide*
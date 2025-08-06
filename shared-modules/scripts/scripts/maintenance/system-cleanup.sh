#!/bin/bash

##############################################################################
# FastrackGPS - System Cleanup and Maintenance Script
# Performs routine maintenance tasks to keep the system healthy
##############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="${APP_DIR:-/var/www/fastrackgps}"
LOG_FILE="/tmp/fastrackgps-cleanup.log"
RETENTION_DAYS=30

echo -e "${BLUE}##############################################################################"
echo -e "# FastrackGPS - System Cleanup and Maintenance"
echo -e "# Date: $(date)"
echo -e "# Log: $LOG_FILE"
echo -e "##############################################################################${NC}"

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}" | tee -a "$LOG_FILE"
}

##############################################################################
# STEP 1: Log Cleanup
##############################################################################

log "Step 1: Cleaning up log files..."

# Clean application logs older than retention period
if [[ -d "$APP_DIR/storage/logs" ]]; then
    log "Cleaning application logs older than $RETENTION_DAYS days"
    find "$APP_DIR/storage/logs" -name "*.log" -mtime +$RETENTION_DAYS -delete
    
    # Compress old logs
    find "$APP_DIR/storage/logs" -name "*.log" -mtime +7 -exec gzip {} \;
    
    # Clean compressed logs older than retention period
    find "$APP_DIR/storage/logs" -name "*.log.gz" -mtime +$RETENTION_DAYS -delete
fi

# Clean system logs
log "Cleaning system logs"
journalctl --vacuum-time=30d
logrotate -f /etc/logrotate.conf

# Clean nginx logs
if [[ -d "/var/log/nginx" ]]; then
    find /var/log/nginx -name "*.log" -mtime +$RETENTION_DAYS -delete
    find /var/log/nginx -name "*.log.*.gz" -mtime +$RETENTION_DAYS -delete
fi

# Clean PHP logs  
if [[ -d "/var/log/php" ]]; then
    find /var/log/php -name "*.log" -mtime +$RETENTION_DAYS -delete
fi

# Clean MySQL logs
if [[ -d "/var/log/mysql" ]]; then
    find /var/log/mysql -name "*.log" -mtime +$RETENTION_DAYS -delete
fi

##############################################################################
# STEP 2: Cache Cleanup
##############################################################################

log "Step 2: Cleaning up cache files..."

# Clean application cache
if [[ -d "$APP_DIR/storage/cache" ]]; then
    log "Cleaning application cache"
    find "$APP_DIR/storage/cache" -type f -mtime +7 -delete
    find "$APP_DIR/storage/cache" -type d -empty -delete
fi

# Clean template cache
if [[ -d "$APP_DIR/storage/cache/templates" ]]; then
    log "Cleaning template cache"
    find "$APP_DIR/storage/cache/templates" -name "*.php" -mtime +7 -delete
fi

# Clean Redis cache if running
if pgrep redis-server > /dev/null; then
    log "Flushing Redis cache"
    redis-cli FLUSHDB
fi

# Clean system package cache
log "Cleaning system package cache"
apt-get autoremove -y
apt-get autoclean

##############################################################################
# STEP 3: Temporary Files Cleanup
##############################################################################

log "Step 3: Cleaning temporary files..."

# Clean system temp files
log "Cleaning system temporary files"
find /tmp -type f -mtime +7 -delete 2>/dev/null || true
find /var/tmp -type f -mtime +7 -delete 2>/dev/null || true

# Clean application temp files
if [[ -d "$APP_DIR/storage/temp" ]]; then
    log "Cleaning application temporary files"
    find "$APP_DIR/storage/temp" -type f -mtime +1 -delete
fi

# Clean upload temp files
if [[ -d "$APP_DIR/storage/uploads/temp" ]]; then
    log "Cleaning upload temporary files"
    find "$APP_DIR/storage/uploads/temp" -type f -mtime +1 -delete
fi

##############################################################################
# STEP 4: Database Maintenance
##############################################################################

log "Step 4: Database maintenance..."

# Load database configuration
if [[ -f "$APP_DIR/.database-config" ]]; then
    source "$APP_DIR/.database-config"
    
    log "Running database cleanup procedures"
    
    # Run database cleanup script if it exists
    if [[ -f "$APP_DIR/scripts/maintenance/database-cleanup.sh" ]]; then
        "$APP_DIR/scripts/maintenance/database-cleanup.sh"
    fi
    
    # Optimize database tables
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        OPTIMIZE TABLE usuarios, bem, posicao_gps, alerta, cerca_virtual, comando_gps, pagamento;
    " || warning "Database optimization failed"
    
else
    warning "Database configuration not found, skipping database maintenance"
fi

##############################################################################
# STEP 5: Session Cleanup
##############################################################################

log "Step 5: Cleaning up sessions..."

# Clean PHP sessions
if [[ -d "/var/lib/php/sessions" ]]; then
    log "Cleaning PHP sessions older than 7 days"
    find /var/lib/php/sessions -name "sess_*" -mtime +7 -delete
fi

# Clean application sessions
if [[ -d "$APP_DIR/storage/sessions" ]]; then
    log "Cleaning application sessions older than 7 days"
    find "$APP_DIR/storage/sessions" -type f -mtime +7 -delete
fi

##############################################################################
# STEP 6: Security Updates Check
##############################################################################

log "Step 6: Checking for security updates..."

# Update package lists
apt-get update -qq

# Check for security updates
SECURITY_UPDATES=$(apt list --upgradable 2>/dev/null | grep -c "security" || echo "0")

if [[ $SECURITY_UPDATES -gt 0 ]]; then
    warning "$SECURITY_UPDATES security updates available"
    log "Installing security updates automatically"
    
    # Install security updates
    unattended-upgrades -d || warning "Failed to install security updates"
else
    log "No security updates available"
fi

##############################################################################
# STEP 7: Service Health Check
##############################################################################

log "Step 7: Checking service health..."

# Check critical services
SERVICES=("nginx" "php8.1-fpm" "mysql" "redis-server")

for service in "${SERVICES[@]}"; do
    if systemctl is-active --quiet "$service"; then
        log "Service $service is running"
    else
        error "Service $service is not running"
        log "Attempting to restart $service"
        systemctl restart "$service" || error "Failed to restart $service"
    fi
done

# Check custom FastrackGPS services
CUSTOM_SERVICES=("fastrackgps-websocket" "fastrackgps-gps-processor")

for service in "${CUSTOM_SERVICES[@]}"; do
    if systemctl is-active --quiet "$service"; then
        log "Service $service is running"
    else
        warning "Service $service is not running (may not be enabled yet)"
    fi
done

##############################################################################
# STEP 8: Disk Space Check
##############################################################################

log "Step 8: Checking disk space..."

# Check disk usage
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')

if [[ $DISK_USAGE -gt 90 ]]; then
    error "Disk usage is critical: ${DISK_USAGE}%"
elif [[ $DISK_USAGE -gt 80 ]]; then
    warning "Disk usage is high: ${DISK_USAGE}%"
else
    log "Disk usage is normal: ${DISK_USAGE}%"
fi

# Show disk usage breakdown
log "Disk usage breakdown:"
du -sh "$APP_DIR"/{storage,database} 2>/dev/null || true

##############################################################################
# STEP 9: Backup Verification
##############################################################################

log "Step 9: Verifying backups..."

# Check database backups
BACKUP_DIR="$APP_DIR/database/backups"
if [[ -d "$BACKUP_DIR" ]]; then
    RECENT_BACKUPS=$(find "$BACKUP_DIR" -name "*.sql.gz" -mtime -1 | wc -l)
    
    if [[ $RECENT_BACKUPS -gt 0 ]]; then
        log "Recent database backups found: $RECENT_BACKUPS"
    else
        warning "No recent database backups found"
    fi
    
    # Clean old backups
    log "Cleaning old backups (keeping last $RETENTION_DAYS days)"
    find "$BACKUP_DIR" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
else
    warning "Backup directory not found: $BACKUP_DIR"
fi

##############################################################################
# STEP 10: System Monitoring
##############################################################################

log "Step 10: System monitoring..."

# Check system load
LOAD_AVG=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
log "System load average: $LOAD_AVG"

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.1f", $3/$2 * 100.0)}')
log "Memory usage: ${MEMORY_USAGE}%"

# Check running processes
PROCESS_COUNT=$(ps aux | wc -l)
log "Running processes: $PROCESS_COUNT"

# Check network connections
CONNECTIONS=$(netstat -an | grep ESTABLISHED | wc -l)
log "Established connections: $CONNECTIONS"

##############################################################################
# STEP 11: Generate Health Report
##############################################################################

log "Step 11: Generating health report..."

REPORT_FILE="$APP_DIR/storage/logs/health-report-$(date +%Y%m%d).txt"
cat > "$REPORT_FILE" << EOF
FastrackGPS System Health Report
Generated: $(date)

=== SYSTEM INFO ===
Hostname: $(hostname)
Uptime: $(uptime)
Disk Usage: ${DISK_USAGE}%
Memory Usage: ${MEMORY_USAGE}%
Load Average: $LOAD_AVG

=== SERVICES STATUS ===
EOF

for service in "${SERVICES[@]}" "${CUSTOM_SERVICES[@]}"; do
    if systemctl is-active --quiet "$service"; then
        echo "$service: Running" >> "$REPORT_FILE"
    else
        echo "$service: Stopped" >> "$REPORT_FILE"
    fi
done

cat >> "$REPORT_FILE" << EOF

=== RECENT BACKUPS ===
$(find "$BACKUP_DIR" -name "*.sql.gz" -mtime -7 2>/dev/null | sort -r | head -5 || echo "No recent backups found")

=== LOG SUMMARY ===
Application Logs: $(find "$APP_DIR/storage/logs" -name "*.log" 2>/dev/null | wc -l) files
System Log Size: $(journalctl --disk-usage | awk '{print $7}')

=== SECURITY UPDATES ===
Available Updates: $SECURITY_UPDATES

EOF

log "Health report generated: $REPORT_FILE"

##############################################################################
# CLEANUP COMPLETE
##############################################################################

echo -e "${GREEN}"
echo "##############################################################################"
echo "# FastrackGPS System Cleanup Complete!"
echo "##############################################################################"
echo ""
echo "📋 CLEANUP SUMMARY:"
echo "  ✅ Log files cleaned and compressed"
echo "  ✅ Cache files cleared"
echo "  ✅ Temporary files removed"
echo "  ✅ Database maintenance performed"
echo "  ✅ Security updates checked"
echo "  ✅ Service health verified"
echo "  ✅ Disk space monitored"
echo "  ✅ Backup verification completed"
echo ""
echo "📊 SYSTEM STATUS:"
echo "  Disk Usage: ${DISK_USAGE}%"
echo "  Memory Usage: ${MEMORY_USAGE}%"
echo "  Security Updates: $SECURITY_UPDATES available"
echo ""
echo "📝 REPORTS:"
echo "  Cleanup Log: $LOG_FILE"
echo "  Health Report: $REPORT_FILE"
echo "##############################################################################"
echo -e "${NC}"

log "System cleanup completed successfully!"
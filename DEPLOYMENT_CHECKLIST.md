# Donation Eligibility System - Deployment & Verification Checklist

## ✅ Pre-Deployment Verification

### 1. Code Quality
- [ ] All tests passing: `php artisan test`
- [ ] No PHP errors: `php -l` on all files
- [ ] Code standards: PSR-12 compliant
- [ ] No console errors in browser dev tools
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities

### 2. Database
- [ ] All migrations run successfully
- [ ] Tables exist with correct schema
- [ ] Foreign keys configured
- [ ] Indexes created for performance
- [ ] Sample data seeded (if applicable)
- [ ] Backup created before migration

### 3. Environment Configuration
- [ ] `.env` file configured correctly
- [ ] Database credentials correct
- [ ] Mail driver configured
- [ ] Queue driver configured
- [ ] Cache driver configured
- [ ] Log level appropriate (info/debug)

### 4. Dependencies
- [ ] Composer dependencies installed: `composer install --no-dev`
- [ ] NPM dependencies installed: `npm install`
- [ ] Assets compiled: `npm run production`
- [ ] No deprecated packages in use
- [ ] All packages are latest stable versions

### 5. API Endpoints
- [ ] All 7 endpoints accessible and tested
- [ ] Request validation working correctly
- [ ] Response structure correct and documented
- [ ] Error responses appropriate
- [ ] Status codes correct (200, 201, 400, 404, 422, 500)

### 6. Authentication & Authorization
- [ ] Auth middleware working
- [ ] Policy checks enforced
- [ ] Admin-only routes protected
- [ ] Public routes accessible without auth
- [ ] Token authentication working (if API)

### 7. Views & UI
- [ ] All 5 views render without errors
- [ ] CSS loads correctly
- [ ] Charts display properly
- [ ] Forms submit successfully
- [ ] Responsive design works on mobile
- [ ] Print layout works

### 8. Notifications
- [ ] Email configuration correct
- [ ] Notification classes instantiate
- [ ] Event listeners registered
- [ ] Queue worker can process jobs
- [ ] Test email sent successfully

### 9. Security
- [ ] CSRF tokens present on forms
- [ ] SQL injection tests passed
- [ ] XSS protection verified
- [ ] Rate limiting configured
- [ ] Sensitive data encrypted
- [ ] No hardcoded secrets in code

### 10. Performance
- [ ] Page load time < 2 seconds
- [ ] Database queries optimized (no N+1)
- [ ] Assets minified and cached
- [ ] Indexes created on large tables
- [ ] Query caching implemented
- [ ] Session handling optimized

---

## 🚀 Deployment Steps

### Step 1: Prepare Server
```bash
# Clone repository
git clone <repo-url>
cd blood-banking

# Install system dependencies
sudo apt-get update
sudo apt-get install php7.4 php7.4-mysql php7.4-xml composer

# Create application user
sudo useradd -m -d /var/www/blood-banking app
```

### Step 2: Install Application
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Install front-end dependencies
npm install

# Compile assets
npm run production
```

### Step 3: Configure Environment
```bash
# Create .env file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R app:app storage bootstrap/cache
```

### Step 4: Database Setup
```bash
# Run migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed

# Verify data
php artisan tinker
# >>> Donor::count()  <- Should show records
```

### Step 5: Cache & Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 6: Setup Queue Processing
```bash
# Create queue worker service
sudo nano /etc/systemd/system/blood-banking-queue.service
```

Add content:
```ini
[Unit]
Description=Blood Banking Queue Worker
After=network.target

[Service]
User=app
Group=www-data
WorkingDirectory=/var/www/blood-banking
ExecStart=/usr/bin/php /var/www/blood-banking/artisan queue:work
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Start service:
```bash
sudo systemctl enable blood-banking-queue
sudo systemctl start blood-banking-queue
```

### Step 7: Setup Web Server (Nginx)
```bash
sudo nano /etc/nginx/sites-available/blood-banking
```

Add content:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    root /var/www/blood-banking/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/blood-banking /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 8: SSL Certificate (Let's Encrypt)
```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### Step 9: Monitoring & Logging
```bash
# View application logs
tail -f storage/logs/laravel.log

# Monitor queue
php artisan queue:monitor

# Check system status
php artisan tinker
# >>> config('app.debug')
# >>> env('APP_ENV')
```

---

## 🧪 Post-Deployment Testing

### 1. Smoke Tests
```bash
# Check application health
curl https://yourdomain.com

# Check API endpoint
curl -X GET https://yourdomain.com/api/donation-eligibility/dashboard \
  -H "Authorization: Bearer TOKEN"

# Check database connection
php artisan tinker
# >>> DB::connection()->getPdo()
```

### 2. Functional Tests
```bash
# Run full test suite
php artisan test

# Test specific feature
php artisan test tests/Feature/DonationEligibilityControllerTest.php
```

### 3. Performance Tests
```bash
# Check page load time
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com

# Run load test (using Apache Bench)
ab -n 100 -c 10 https://yourdomain.com/
```

### 4. Security Tests
```bash
# Check headers
curl -I https://yourdomain.com

# Test SQL injection prevention
curl "https://yourdomain.com/api/donors?search='; DROP TABLE donors; --"
# Should return validation error, not execute

# Test XSS prevention
curl -X POST https://yourdomain.com/api/donors \
  -d 'name=<script>alert("xss")</script>'
# Should escape or reject
```

### 5. Notification Tests
```bash
# Test email sending
php artisan tinker
# >>> Mail::to('test@example.com')->send(new DonorDeferralNotification(...))
```

---

## 📊 Health Monitoring

### Create Health Check Endpoint
```php
// routes/api.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::get('test') ? 'working' : 'not working',
        'queue' => Queue::size() . ' jobs',
        'timestamp' => now(),
    ]);
});
```

### Monitoring Commands
```bash
# Check queue length
php artisan queue:monitor --max=100

# View failed jobs
php artisan queue:failed

# Monitor processes
ps aux | grep "queue:work"

# Check disk space
df -h

# Check memory usage
free -h

# Check error logs
tail -f /var/log/syslog
```

---

## 🔄 Backup & Recovery

### Automated Backups
```bash
# Create daily backup script
sudo nano /usr/local/bin/backup-blood-banking.sh
```

Script content:
```bash
#!/bin/bash
BACKUP_DIR="/backups/blood-banking"
DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
mysqldump -u root -p blood_banking > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/blood-banking

# Keep only last 7 days
find $BACKUP_DIR -mtime +7 -delete
```

Schedule with cron:
```bash
# Run daily at 2 AM
0 2 * * * /usr/local/bin/backup-blood-banking.sh
```

### Recovery Steps
```bash
# Restore database
mysql -u root -p blood_banking < backup.sql

# Restore files
tar -xzf backup.tar.gz -C /

# Clear cache
php artisan cache:clear
php artisan view:clear
```

---

## 📋 Deployment Verification Checklist

### Pre-Launch
- [ ] Database migrated successfully
- [ ] All tests passing
- [ ] Environment configured
- [ ] SSL certificate installed
- [ ] Backups created
- [ ] Monitoring configured
- [ ] Log rotation setup
- [ ] Email service tested

### Go-Live
- [ ] Domain points to correct server
- [ ] Health check endpoint responds
- [ ] All API endpoints working
- [ ] Authentication functional
- [ ] Notifications sending
- [ ] Performance acceptable
- [ ] No errors in logs
- [ ] Team notified

### Post-Launch
- [ ] Monitor error logs for 24 hours
- [ ] Check queue processing
- [ ] Verify backup execution
- [ ] Monitor server resources
- [ ] Test notification delivery
- [ ] Check API response times
- [ ] Verify user access
- [ ] Document issues found

---

## 🚨 Rollback Procedure

If critical issues found:

```bash
# 1. Stop queue worker
sudo systemctl stop blood-banking-queue

# 2. Revert to previous migration
php artisan migrate:rollback

# 3. Switch to previous code version
git checkout previous_tag

# 4. Clear cache
php artisan cache:clear

# 5. Restart services
sudo systemctl restart php7.4-fpm
sudo systemctl restart nginx

# 6. Restore from backup if needed
mysql -u root -p blood_banking < backup.sql

# 7. Notify team
# Send incident report to stakeholders
```

---

## 📱 Maintenance Schedule

| Task | Frequency | Time |
|------|-----------|------|
| Update dependencies | Monthly | 2 AM |
| Database optimization | Weekly | Sunday 2 AM |
| Log rotation | Daily | Daily |
| Security patches | As needed | ASAP |
| Full backup | Daily | 2 AM |
| Performance review | Weekly | Monday 8 AM |
| Security audit | Quarterly | End of quarter |
| Code review | Per commit | N/A |

---

## 📞 Emergency Contacts

| Role | Name | Phone | Email |
|------|------|-------|-------|
| System Admin | - | - | - |
| Database Admin | - | - | - |
| DevOps Lead | - | - | - |
| Support Manager | - | - | - |

---

## 📝 Incident Response

### Critical Issue (System Down)
1. Alert all team members
2. Check system logs
3. Check queue status
4. Review recent deployments
5. Consider rollback
6. Notify stakeholders
7. Document issue
8. Post-mortem after resolution

### Performance Issue
1. Check database performance
2. Review slow queries
3. Check queue length
4. Check server resources
5. Review recent code changes
6. Optimize queries if needed
7. Monitor improvements

### Data Issue
1. Check database integrity
2. Verify backups
3. Identify affected data
4. Plan recovery
5. Execute recovery
6. Verify data correctness
7. Prevent future issues

---

## ✅ Final Verification

Before marking as "Production Ready":

```bash
# Run all checks
php artisan test --coverage

# Verify all endpoints
curl -s "https://yourdomain.com/api/donation-eligibility/dashboard" | jq

# Check database health
php artisan tinker
# >>> DB::select('SELECT COUNT(*) as count FROM donors')

# Monitor logs
tail -f storage/logs/laravel.log

# Verify notifications
php artisan queue:work --once

# Check performance
php artisan tinker
# >>> DB::enableQueryLog(); 
# >>> Donor::with('donationRecords')->get();
# >>> DB::getQueryLog()
```

---

## 🎯 Sign-Off

System is ready for production when:
- [ ] All tests passing
- [ ] All endpoints verified
- [ ] Performance acceptable
- [ ] Security audit passed
- [ ] Backups working
- [ ] Monitoring active
- [ ] Team trained
- [ ] Documentation complete

**Deployment Status**: ✅ READY FOR PRODUCTION

**Date**: ________________  
**Approved By**: ________________  
**Verified By**: ________________

---

**Last Updated**: January 2024  
**Version**: 1.0.0

#!/bin/bash
set -e

# ============================================================
# AI Token Dashboard - Deployment Script for aaPanel VPS
# ============================================================
# Usage:
#   1. SSH ke VPS
#   2. Upload script ini atau clone repo dulu
#   3. Edit variabel di bawah
#   4. chmod +x deploy.sh && ./deploy.sh
# ============================================================

# ===== EDIT VARIABEL INI =====
DOMAIN="aimurah.my.id"
SITE_DIR="/www/wwwroot/${DOMAIN}"
DB_NAME="ai_token_dashboard"
DB_USER="aitoken"
DB_PASS="GANTI_PASSWORD_DATABASE_ANDA"
DB_ROOT_PASS="GANTI_PASSWORD_ROOT_MYSQL_AAPANEL"
ADMIN_EMAIL="wahidilasp@gmail.com"
ADMIN_PASS="GANTI_PASSWORD_ADMIN"
ENOWXAI_API_KEY="enx-61b80f54548c7d42d22256755ac3b0bc67d4911cd21d6e689114b058437f254b"
ENOWXAI_BASE_URL="http://43.133.141.45:1434/v1"
GO_PROXY_PORT="8080"
# ==============================

echo "============================================"
echo "  AI Token Dashboard - Deployment"
echo "  Domain: ${DOMAIN}"
echo "============================================"
echo ""

# ---- Step 1: Check prerequisites ----
echo "[1/12] Checking prerequisites..."
command -v nginx >/dev/null 2>&1 || { echo "ERROR: Nginx not installed. Install via aaPanel first."; exit 1; }
command -v mysql >/dev/null 2>&1 || { echo "ERROR: MySQL not installed. Install via aaPanel first."; exit 1; }
command -v php >/dev/null 2>&1 || { echo "ERROR: PHP not installed. Install via aaPanel first."; exit 1; }
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "  Nginx: OK"
echo "  MySQL: OK"
echo "  PHP: ${PHP_VER}"

# ---- Step 2: Install Node.js if needed ----
echo ""
echo "[2/12] Checking Node.js..."
if ! command -v node >/dev/null 2>&1; then
    echo "  Installing Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - >/dev/null 2>&1
    apt-get install -y nodejs >/dev/null 2>&1
fi
echo "  Node: $(node -v)"
echo "  NPM: $(npm -v)"

# ---- Step 3: Install Go if needed ----
echo ""
echo "[3/12] Checking Go..."
if ! command -v go >/dev/null 2>&1; then
    echo "  Installing Go 1.22.5..."
    wget -q https://go.dev/dl/go1.22.5.linux-amd64.tar.gz -O /tmp/go.tar.gz
    tar -C /usr/local -xzf /tmp/go.tar.gz
    rm /tmp/go.tar.gz
    export PATH=$PATH:/usr/local/go/bin
    echo 'export PATH=$PATH:/usr/local/go/bin' >> /etc/profile
fi
export PATH=$PATH:/usr/local/go/bin
echo "  Go: $(go version | awk '{print $3}')"

# ---- Step 4: Install Composer if needed ----
echo ""
echo "[4/12] Checking Composer..."
if ! command -v composer >/dev/null 2>&1; then
    echo "  Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1
fi
echo "  Composer: $(composer --version 2>&1 | head -1)"

# ---- Step 5: Clone/update repository ----
echo ""
echo "[5/12] Setting up project files..."
if [ -d "${SITE_DIR}/.git" ]; then
    echo "  Git repo exists, pulling latest..."
    git config --global --add safe.directory "${SITE_DIR}"
    cd "${SITE_DIR}"
    git pull origin main
else
    echo "  Cloning repository..."
    mkdir -p "${SITE_DIR}"
    cd "${SITE_DIR}"
    # Remove default aaPanel files
    find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.user.ini' -exec rm -rf {} + 2>/dev/null || true
    git clone https://github.com/Wahidila/aiproxy.git .
fi

# ---- Step 6: Create database ----
echo ""
echo "[6/12] Setting up database..."
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
mysql -u root -p"${DB_ROOT_PASS}" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>/dev/null
mysql -u root -p"${DB_ROOT_PASS}" -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null
echo "  Database: ${DB_NAME} (user: ${DB_USER})"

# ---- Step 7: Configure Laravel .env ----
echo ""
echo "[7/12] Configuring Laravel..."
cd "${SITE_DIR}"
cp .env.example .env

# Update .env values
sed -i "s|APP_NAME=Laravel|APP_NAME=\"AI Token Dashboard\"|" .env
sed -i "s|APP_ENV=local|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|" .env
sed -i "s|APP_URL=http://localhost|APP_URL=https://${DOMAIN}|" .env
sed -i "s|DB_DATABASE=laravel|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|DB_USERNAME=root|DB_USERNAME=${DB_USER}|" .env
sed -i "s|DB_PASSWORD=|DB_PASSWORD=${DB_PASS}|" .env

# Add EnowxAI config if not present
if ! grep -q "ENOWXAI_BASE_URL" .env; then
    cat >> .env << EOF

# EnowxAI Configuration
ENOWXAI_BASE_URL=${ENOWXAI_BASE_URL}
ENOWXAI_API_KEY=${ENOWXAI_API_KEY}
ENOWXAI_DASHBOARD_URL=http://43.133.141.45:1435

# Token Limits
FREE_CREDIT_AMOUNT=100000
MIN_TOPUP_AMOUNT=10000
EOF
else
    sed -i "s|ENOWXAI_BASE_URL=.*|ENOWXAI_BASE_URL=${ENOWXAI_BASE_URL}|" .env
    sed -i "s|ENOWXAI_API_KEY=.*|ENOWXAI_API_KEY=${ENOWXAI_API_KEY}|" .env
fi

# Install PHP dependencies
echo "  Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# Generate app key
php artisan key:generate --force --quiet

# Install Node dependencies & build
echo "  Building frontend assets..."
npm install --silent 2>/dev/null
npm run build 2>/dev/null

# Run migrations & seed
echo "  Running migrations..."
php artisan migrate --force --quiet
php artisan db:seed --force --quiet

# Storage link
php artisan storage:link --force --quiet 2>/dev/null || true

# Cache config
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet

echo "  Laravel configured successfully"

# ---- Step 8: Set permissions ----
echo ""
echo "[8/12] Setting permissions..."
chown -R www:www "${SITE_DIR}"
chmod -R 755 "${SITE_DIR}"
chmod -R 775 "${SITE_DIR}/storage" "${SITE_DIR}/bootstrap/cache"
echo "  Permissions set"

# ---- Step 9: Build Golang proxy ----
echo ""
echo "[9/12] Building Golang proxy..."
cd "${SITE_DIR}/golang-proxy"

# Configure .env
cp .env.example .env
sed -i "s|DB_PASSWORD=|DB_PASSWORD=${DB_PASS}|" .env
sed -i "s|DB_USERNAME=root|DB_USERNAME=${DB_USER}|" .env
sed -i "s|ENOWXAI_API_KEY=.*|ENOWXAI_API_KEY=${ENOWXAI_API_KEY}|" .env
sed -i "s|ENOWXAI_BASE_URL=.*|ENOWXAI_BASE_URL=${ENOWXAI_BASE_URL}|" .env
sed -i "s|PORT=.*|PORT=${GO_PROXY_PORT}|" .env

# Build
go mod download
CGO_ENABLED=0 go build -ldflags="-s -w" -o ai-token-proxy .
echo "  Golang proxy built: $(ls -lh ai-token-proxy | awk '{print $5}')"

# ---- Step 10: Setup systemd service ----
echo ""
echo "[10/12] Setting up systemd service..."
cat > /etc/systemd/system/ai-token-proxy.service << EOF
[Unit]
Description=AI Token Proxy (Golang)
After=network.target mysql.service

[Service]
Type=simple
User=www
Group=www
WorkingDirectory=${SITE_DIR}/golang-proxy
ExecStart=${SITE_DIR}/golang-proxy/ai-token-proxy
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable ai-token-proxy --quiet
systemctl restart ai-token-proxy
sleep 2

if systemctl is-active --quiet ai-token-proxy; then
    echo "  Golang proxy: RUNNING"
else
    echo "  WARNING: Golang proxy failed to start. Check: journalctl -u ai-token-proxy"
fi

# ---- Step 11: Configure Nginx ----
echo ""
echo "[11/12] Configuring Nginx reverse proxy..."

# Find PHP-FPM socket
PHP_SOCK=$(find /tmp /www/server/php -name "php-cgi-*.sock" -o -name "php-fpm.sock" 2>/dev/null | head -1)
if [ -z "$PHP_SOCK" ]; then
    PHP_SOCK="/tmp/php-cgi-81.sock"
fi
echo "  PHP-FPM socket: ${PHP_SOCK}"

# Backup existing config
NGINX_CONF="/www/server/panel/vhost/nginx/${DOMAIN}.conf"
if [ -f "$NGINX_CONF" ]; then
    cp "$NGINX_CONF" "${NGINX_CONF}.bak"
fi

# Write new config
cat > "$NGINX_CONF" << NGINXEOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${SITE_DIR}/public;
    index index.php;

    # API routes -> Golang proxy
    location /api/v1/ {
        rewrite ^/api(/v1/.*)\$ \$1 break;
        proxy_pass http://127.0.0.1:${GO_PROXY_PORT};
        proxy_http_version 1.1;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header Connection '';
        proxy_buffering off;
        proxy_cache off;
        chunked_transfer_encoding on;
        proxy_read_timeout 300s;
        proxy_send_timeout 300s;
    }

    # Laravel
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)\$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    access_log /www/wwwlogs/${DOMAIN}.log;
    error_log /www/wwwlogs/${DOMAIN}.error.log;
}
NGINXEOF

nginx -t 2>/dev/null && nginx -s reload
echo "  Nginx configured and reloaded"

# ---- Step 12: Setup cron ----
echo ""
echo "[12/12] Setting up cron scheduler..."
CRON_LINE="* * * * * cd ${SITE_DIR} && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "artisan schedule:run"; echo "$CRON_LINE") | crontab -
echo "  Cron scheduler added"

# ---- Step 13: Set admin password ----
echo ""
echo "[BONUS] Setting admin password..."
cd "${SITE_DIR}"
php artisan tinker --execute="
\$u = \App\Models\User::where('email','${ADMIN_EMAIL}')->first();
if (!\$u) {
    \$u = \App\Models\User::create(['name'=>'Admin','email'=>'${ADMIN_EMAIL}','password'=>Hash::make('${ADMIN_PASS}'),'role'=>'admin']);
    echo 'Admin created: ${ADMIN_EMAIL}';
} else {
    \$u->password = Hash::make('${ADMIN_PASS}');
    \$u->role = 'admin';
    \$u->save();
    echo 'Admin updated: ${ADMIN_EMAIL}';
}
" 2>/dev/null

# ---- Done ----
echo ""
echo "============================================"
echo "  DEPLOYMENT COMPLETE!"
echo "============================================"
echo ""
echo "  Website:  https://${DOMAIN}"
echo "  Admin:    ${ADMIN_EMAIL}"
echo "  API URL:  https://${DOMAIN}/api/v1"
echo ""
echo "  Next steps:"
echo "  1. Setup SSL di aaPanel > Website > ${DOMAIN} > SSL > Let's Encrypt"
echo "  2. Login ke https://${DOMAIN}/login"
echo "  3. Upload QRIS image di Admin > Settings"
echo "  4. Test API: curl https://${DOMAIN}/api/v1/health"
echo ""
echo "  Logs:"
echo "  - Laravel: tail -f ${SITE_DIR}/storage/logs/laravel.log"
echo "  - Golang:  journalctl -u ai-token-proxy -f"
echo "============================================"

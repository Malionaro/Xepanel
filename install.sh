#!/bin/bash

# --- FilePanel Ultra-Setup Script (Linux) ---
echo "🌟 Starting FULL FilePanel Installation..."

# 1. Root Check
if [ "$EUID" -ne 0 ]; then 
  echo "❌ Error: Please run with sudo (sudo bash install.sh)"
  exit 1
fi

# Directory Check - Ensure we are in the panel directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: 'artisan' file not found!"
    echo "   Please make sure you are in the FilePanel project folder."
    echo "   Use 'cd /path/to/panel' before running this script."
    exit 1
fi

# Determine the actual user who ran sudo
if [ -n "$SUDO_USER" ]; then
    REAL_USER="$SUDO_USER"
else
    REAL_USER=$(logname 2>/dev/null || echo "$USER")
fi
if [ -z "$REAL_USER" ] || [ "$REAL_USER" = "root" ]; then
    REAL_USER="root"
fi

echo "👤 Installing for user: $REAL_USER"

# 2. System Update & Dependencies
echo "📦 Installing system dependencies..."
if [ -f /etc/arch-release ]; then
    echo "Distro: Arch Linux"
    pacman -Syu --noconfirm
    pacman -S --noconfirm php php-sqlite php-gd php-intl php-curl php-mbstring php-xml composer nodejs npm unzip git curl
elif [ -f /etc/debian_version ] || grep -q -i ubuntu /etc/os-release; then
    echo "Distro: Debian/Ubuntu"
    export DEBIAN_FRONTEND=noninteractive
    apt-get update -y
    apt-get install -y software-properties-common curl ca-certificates gnupg unzip git

    # Add NodeSource repo for Node.js 20
    mkdir -p /etc/apt/keyrings
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg --yes
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

    # Add Ondrej PPA for PHP (if available)
    if command -v add-apt-repository >/dev/null 2>&1; then
        add-apt-repository -y ppa:ondrej/php || true
    fi

    apt-get update -y
    # Install PHP 8.2 and dependencies (without docker.io to prevent containerd conflicts)
    apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-sqlite3 php8.2-gd php8.2-intl php8.2-curl php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-zip nodejs
    
    # Force default PHP CLI to the version we just installed
    if command -v update-alternatives >/dev/null 2>&1; then
        update-alternatives --set php /usr/bin/php8.2 || true
    fi
else
    echo "⚠️ Unknown OS. Trying to install basic packages..."
    apt-get install -y php nodejs npm unzip git || yum install -y php nodejs npm unzip git
fi

# Fallback for Composer
if ! command -v composer >/dev/null 2>&1; then
    echo "📦 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# 3. Docker setup
echo "🐳 Setting up Docker..."
if ! command -v docker >/dev/null 2>&1; then
    echo "   Installing Docker via official script..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
fi

systemctl start docker || true
systemctl enable docker || true
chmod 666 /var/run/docker.sock 2>/dev/null || true
if [ "$REAL_USER" != "root" ]; then
    usermod -aG docker "$REAL_USER" || true
fi

# Check if essential commands are available before continuing
for cmd in php composer npm; do
    if ! command -v $cmd >/dev/null 2>&1; then
        echo "❌ Error: $cmd could not be installed. Please check your system's package manager."
        exit 1
    fi
done

# 4. Webserver Prep
echo "⚙️ Initializing Panel Environment..."
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "⚠️ .env.example not found. An empty .env will be created."
        touch .env
    fi
fi

mkdir -p storage/app/private/services storage/app/private/eggs storage/app/private/docker
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
mkdir -p database

touch database/database.sqlite

# 5. Composer & Laravel
echo "📦 Installing PHP Dependencies (Composer)..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

echo "🔑 Generating Application Key..."
php artisan key:generate --force

echo "🗄️ Running Migrations..."
php artisan migrate --force

echo "🥚 Seeding default eggs..."
php artisan tinker --execute="\App\Models\Egg::seedDefaults();"

# 6. Frontend
echo "🎨 Installing Node Dependencies & Building Frontend..."
npm install
npm run build

# 7. Permissions
echo "🔒 Setting up correct file permissions..."
# Give ownership back to the real user so they can edit files easily
chown -R "$REAL_USER:$REAL_USER" .

# Ensure Laravel's writable directories are accessible by the webserver and CLI
chmod -R 777 storage bootstrap/cache database
chmod 666 database/database.sqlite
if [ -f storage/app/users.json ]; then
    chmod 666 storage/app/users.json
fi

# 8. Create Admin User
echo ""
echo "-------------------------------------------------------"
read -p "❓ Do you want to create an Admin User now? (y/n): " create_user
if [[ "$create_user" =~ ^[Yy]$ ]]; then
    read -p "   Enter Email: " admin_email
    read -p "   Enter Name: " admin_name
    read -p "   Enter Password: " admin_pass
    
    cat << 'EOF' > create_admin.php
<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = $argv[1];
$name = $argv[2];
$pass = $argv[3];

$u = new \App\Models\FileUser();
$u->id = uniqid();
$u->email = $email;
$u->name = $name;
$u->password = \Illuminate\Support\Facades\Hash::make($pass);
$u->role = 'admin';
$u->save();
EOF

    # Run the PHP script with arguments to safely handle spaces and special chars
    if [ "$REAL_USER" != "root" ]; then
        sudo -u "$REAL_USER" php create_admin.php "$admin_email" "$admin_name" "$admin_pass"
    else
        php create_admin.php "$admin_email" "$admin_name" "$admin_pass"
    fi
    
    rm create_admin.php
    echo "✅ Admin user created successfully!"
fi

echo ""
echo "🎉 EVERYTHING INSTALLED!"
echo "-------------------------------------------------------"
echo "Start the panel manually for development:"
echo "   php artisan serve"
echo "URL: http://127.0.0.1:8000"
echo ""
echo "Note: If running behind a webserver (Nginx/Apache), point the document root to the 'public' folder."
echo "-------------------------------------------------------"

#!/bin/bash

# --- FilePanel Ultra-Setup Script (Linux) ---
echo "🌟 Starting FULL FilePanel Installation..."

# 1. Root Check
if [ "$EUID" -ne 0 ]; then 
  echo "❌ Error: Please run with sudo (sudo bash install.sh)"
  exit 1
fi

# 2. System Update & Dependencies
if [ -f /etc/arch-release ]; then
    echo "📦 Arch Linux detected. Installing all packages..."
    pacman -Syu --noconfirm
    pacman -S --noconfirm php php-sqlite php-gd php-intl php-curl php-mbstring php-xml composer nodejs npm docker unzip git
elif [ -f /etc/debian_version ]; then
    echo "📦 Debian/Ubuntu detected. Adding PHP repository and installing..."
    apt update && apt install -y software-properties-common
    add-apt-repository -y ppa:ondrej/php
    apt update
    apt install -y php8.2 php8.2-cli php8.2-common php8.2-sqlite3 php8.2-gd php8.2-intl php8.2-curl php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-zip composer nodejs npm docker.io unzip git
else
    echo "⚠️ Unknown OS. Trying to install basic packages..."
    apt install -y php composer nodejs npm docker unzip git || yum install -y php composer nodejs npm docker unzip git
fi

# 3. Docker setup
echo "🐳 Setting up Docker..."
systemctl start docker
systemctl enable docker
chmod 666 /var/run/docker.sock
usermod -aG docker $SUDO_USER

# 4. Webserver Prep
echo "⚙️ Initializing Panel..."
[ ! -f .env ] && cp .env.example .env
mkdir -p storage/app/private/services storage/app/private/eggs storage/app/private/docker
mkdir -p storage/framework/{sessions,views,cache}
touch database/database.sqlite

# 5. Composer & Laravel
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan tinker --execute="\App\Models\Egg::seedDefaults();"

# 6. Frontend
npm install
npm run build

# 7. Permissions
chown -R $SUDO_USER:$SUDO_USER .
chmod -R 775 storage bootstrap/cache
chmod 777 database/database.sqlite

# 8. Create Admin User
echo ""
read -p "❓ Do you want to create an Admin User now? (y/n): " create_user
if [ "$create_user" = "y" ]; then
    read -p "   Enter Email: " admin_email
    read -p "   Enter Name: " admin_name
    read -p "   Enter Password: " admin_pass
    php artisan tinker --execute="\$u = new \App\Models\FileUser(); \$u->id = uniqid(); \$u->email = '$admin_email'; \$u->name = '$admin_name'; \$u->password = \Illuminate\Support\Facades\Hash::make('$admin_pass'); \$u->role = 'admin'; \$u->save();"
    echo "✅ Admin user created!"
fi

echo ""
echo "🎉 EVERYTHING INSTALLED!"
echo "-------------------------------------------------------"
echo "Start the panel: php artisan serve"
echo "URL: http://127.0.0.1:8000"
echo "-------------------------------------------------------"

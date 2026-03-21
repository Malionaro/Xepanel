#!/bin/bash
# install.sh - Automated installer for the FilePanel project

set -e

# Ensure script is run as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run this script as root (sudo ./install.sh)"
  exit 1
fi

echo "--- Starting Automated Installation ---"

# Step 1: Update System Packages
echo "[1/6] Updating system packages..."
apt-get update -y
apt-get upgrade -y

# Step 2: Install Base Dependencies and PHP 8.2
echo "[2/6] Installing PHP, Curl, Git, and other dependencies..."
apt-get install -y software-properties-common curl git wget unzip acl

# Add Ondrej PHP repository for latest PHP versions
add-apt-repository -y ppa:ondrej/php
apt-get update -y

apt-get install -y php8.2 php8.2-cli php8.2-common php8.2-fpm php8.2-mysql php8.2-zip \
  php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-sqlite3

# Step 3: Install Composer
echo "[3/6] Installing Composer..."
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
    >&2 echo 'ERROR: Invalid composer installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Step 4: Install Node.js & NPM
echo "[4/6] Installing Node.js..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Step 5: Install Docker
echo "[5/6] Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
else
    echo "Docker is already installed."
fi

# Add the actual user (not root) to the docker group
REAL_USER=${SUDO_USER:-$(whoami)}
usermod -aG docker "$REAL_USER"
echo "Added user $REAL_USER to docker group. You may need to log out and log back in."

# Step 6: Setup Laravel Panel
echo "[6/6] Setting up project..."

# Change ownership to the real user so composer doesn't complain
chown -R "$REAL_USER":"$REAL_USER" .

# Install PHP dependencies
sudo -u "$REAL_USER" composer install --no-dev --optimize-autoloader || composer install --no-dev --optimize-autoloader

# Set up environment file
if [ ! -f .env ]; then
    sudo -u "$REAL_USER" cp .env.example .env
fi

# Set up SQLite database default
if [ ! -f database/database.sqlite ]; then
    sudo -u "$REAL_USER" touch database/database.sqlite
fi

# Generate app key, run migrations
sudo -u "$REAL_USER" php artisan key:generate --force
sudo -u "$REAL_USER" php artisan migrate --force

# Install Node dependencies and build frontend assets
sudo -u "$REAL_USER" npm install
sudo -u "$REAL_USER" npm run build

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
setfacl -R -m u:www-data:rwx -m u:"$REAL_USER":rwx storage bootstrap/cache
setfacl -dR -m u:www-data:rwx -m u:"$REAL_USER":rwx storage bootstrap/cache

echo "--- Installation Complete! ---"
echo "You can now serve your application using:"
echo "php artisan serve"
echo "Or configure your web server (Nginx/Apache) to serve the public/ directory."
echo "Note: To run docker-related services, you may need to 'su - $REAL_USER' or reboot for docker group changes to apply."

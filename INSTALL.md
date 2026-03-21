# FilePanel Installation Guide

Welcome to the FilePanel standalone setup guide. We provide an automated installation script (`install.sh`) to seamlessly configure your system and panel dependencies on Ubuntu/Debian.

## Prerequisites

- A fresh Ubuntu 20.04/22.04/24.04 or Debian 11/12 machine.
- `root` access (or `sudo` privileges).
- An active internet connection.

## 1. Download the Panel

First, clone the panel repository to your server:

```bash
git clone https://github.com/Malionaro/Xepanel.git /opt/panel
cd /opt/panel
```
*(If you have already downloaded the panel via ZIP or FTP, simply navigate to the panel's directory instead).*

## 2. Run the Installer

Make sure the installer script is marked as executable, and then execute it with `sudo`:

```bash
chmod +x install.sh
sudo ./install.sh
```

**What the installer does:**
- Installs necessary system packages and updates them.
- Installs PHP 8.2 and all required PHP extensions.
- Installs Composer (PHP Package Manager).
- Installs Node.js & npm, downloading and building required frontend assets.
- Installs Docker (required for isolated game servers) and adds the current user to the `docker` group.
- Configures Laravel environment (`.env`), database (`database.sqlite`), and generates required keys.
- Fixes directory permissions for the `storage` and `bootstrap/cache` folders.

## 3. Post-Installation Steps

### 3.1. Re-login for Docker Permissions
Because the installer adds your current user to the `docker` group, you must log out and log back into your SSH session for these group permissions to take effect. If you skip this, the panel won't be able to communicate with the Docker daemon!

```bash
su - $USER
```

### 3.2. Set Up the First Administrator User
Once installed, you must register the initial administrator account. Open your database or create a user via Artisan Tinker:

```bash
php artisan tinker
```
Inside the interactive shell, run:
```php
$user = new \App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('your_secure_password');
$user->role = 'admin';
$user->save();
exit;
```

### 3.3. Serve the Application
You can temporarily test the panel by running the built-in development server:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
Then navigate to `http://<your-server-ip>:8000` in your web browser.

For production, we strongly recommend configuring **Nginx** or **Apache** to serve the `/public` directory of the application securely.

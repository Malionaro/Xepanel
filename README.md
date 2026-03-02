# 🚀 Xepanel

A lightweight, high-performance, and **file-based** service management panel. Control your Docker containers and host processes with ease—no SQL database required.

![Xepanel Preview](https://via.placeholder.com/1200x600?text=Xepanel+Modern+Service+Management)

## ✨ Features

- 🐳 **Full Docker Support**: Deploy, monitor, and manage containers with custom ports, volumes, and networks.
- ⚙️ **Host Process Support**: Run Node.js, Python, or Java scripts directly on your host using systemd-like management.
- 📁 **No Database Needed**: All data (Users, Services, Settings) is stored in optimized JSON files. Portable and fast.
- 📊 **Real-time Analytics**: Beautiful, live-updating charts for CPU and RAM usage powered by Chart.js.
- 🔐 **Enhanced Security**: 
    - Google 2FA (TOTP) support for every account.
    - Fine-grained API Keys for automation.
    - Resource Quotas (Limit RAM, CPU, and Instance count per user).
- 🥚 **Smart Templates (Eggs)**:
    - Pre-configured templates for Minecraft (Paper/Spigot), Python Bots, MariaDB, Redis, and more.
    - Automatic dependency installation (pip, npm, composer).
- 📂 **Rich File Manager**: Integrated web-based editor with upload, zip/unzip, and secure path protection.
- 📋 **Activity Logs**: Full audit trail of every action performed on the panel.
- 🌑 **Modern UI**: Clean, dark-mode first design with responsive mobile support.

## 🛠️ Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Docker (optional, for container support)

### Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/YOUR_USERNAME/Xepanel.git
   cd Xepanel
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install && npm run build
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Set permissions:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

5. Start the panel:
   ```bash
   php artisan serve
   ```

Default admin login: Access the panel and create your first user via the registration/setup flow.

## 🥚 Included Eggs (Templates)
Xepanel comes with "Batteries Included":
- **Minecraft Server**: Automatic EULA acceptance and version management.
- **Python Script**: Auto-installs `requirements.txt` via pip.
- **Discord Bot**: Specialized for discord.py/nextcord.
- **MariaDB/Redis**: One-click database deployment.
- **Node.js**: Auto-installs `package.json` dependencies.

## 📄 License
This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---
Built with ❤️ for developers who love simplicity.

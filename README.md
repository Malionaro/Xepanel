# 🚀 FilePanel - High-End Service Orchestration

A futuristically designed, high-performance service management panel. Control your infrastructure with a cinematic UI—optimized for Game Servers, Bots, and Web Apps.

![FilePanel Preview](https://via.placeholder.com/1200x600?text=FilePanel+Cinematic+Management)

## ✨ Premium Features

- 🎨 **High-End UI/UX**: Cinematic dark-mode design with glassmorphism, glowing borders, and fluid spring-animations (4K Dribbble-inspired aesthetic).
- 🌓 **White-Labeling**: Fully personalizable via UI. Change brand colors (Accent), Panel Names, and Icons with a single click.
- 🐳 **Full Docker Orchestration**: Deploy containers with custom ports, volumes, and networks. Specialized for Minecraft, FiveM, and App Hosting.
- 🗄️ **Integrated DB Management**: Automatic provisioning of MySQL/MariaDB databases and users for your services.
- 📁 **Cloud-Scale File Manager**: Integrated high-performance editor (Ace), path protection, and secure infrastructure streams.
- 📊 **Real-time Telemetry**: Live-updating resource gauges and 24h performance history graphs for CPU & RAM.
- 🔐 **Hardened Security**: 
    - Google 2FA (TOTP) hardware-grade protection.
    - Encrypted API tokens for CI/CD integration.
    - Intelligent resource quotas and isolation.
- 🔄 **Auto-Update Engine**: Integrated GitHub sync. Check for updates and apply core patches directly from the web interface.
- 📁 **JSON-Driven Core**: Lightning-fast, file-based data storage. No SQL database required for the panel itself.

## 🛠️ Rapid Deployment

### Prerequisites
- PHP 8.2 or 8.3
- Composer
- Docker (for containerized instances)
- Git (for the Auto-Update engine)

### Setup
1. **Initialise Cluster**:
   ```bash
   git clone https://github.com/malo/panel.git
   cd panel
   ```

2. **Install Dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Authorise Permissions**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

5. **Launch Protocol**:
   ```bash
   php artisan serve
   ```

## 🔄 Auto-Update Protocol
To enable the integrated update engine:
1. Navigate to **Administration > Panel Settings**.
2. Configure your **GitHub Repository** (e.g. `malo/panel`).
3. (Optional) Provide a **GitHub Access Token** for private repos or higher rate limits.
4. Click **"Check for Updates"** to synchronise with the main branch.

## 🥚 Evolution (Templates)
Standard protocols included:
- **Minecraft Ecosystem**: Paper, Spigot, Fabric, BungeeCord.
- **GTA V Roleplay**: FiveM (Linux optimized).
- **Web Infrastructure**: Nginx (Alpine), PHP 8.2 (Apache).
- **Bot Environments**: Node.js 20, Python 3.11 (Auto-pip/npm support).

## 📄 Registry
This project is licensed under the **MIT License**.

---
*Elevating Infrastructure Management to an Art Form.*

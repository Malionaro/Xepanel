@echo off
setlocal enabledelayedexpansion

:: --- FilePanel Ultra-Setup Script (Windows) ---
echo 🌟 Starting FULL FilePanel Initialization...

:: 1. Check/Install System Dependencies via Winget
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo 📦 PHP not found. Installing via Winget...
    winget install -e --id PHP.PHP.8.2
    echo ⚠️ Please RESTART this script after installation completes.
    pause
    exit /b
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo 📦 Node.js not found. Installing via Winget...
    winget install -e --id OpenJS.NodeJS
    echo ⚠️ Please RESTART this script after installation completes.
    pause
    exit /b
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo 📦 Composer not found. Installing via Winget...
    winget install -e --id PHP.Composer
    echo ⚠️ Please RESTART this script after installation completes.
    pause
    exit /b
)

:: 2. Application Init
echo ⚙️ Initializing Environment...
if not exist .env (
    copy .env.example .env
    echo ✅ Created .env file
)

if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo ✅ Created database file
)

mkdir storage\app\private\services 2>nul
mkdir storage\app\private\eggs 2>nul
mkdir storage\app\private\docker 2>nul

:: 3. Install Dependencies
echo 🚚 Installing PHP packages...
call composer install --no-interaction

echo 🚚 Installing Node packages...
call npm install

:: 4. Setup Laravel
echo 🔧 Configuring Database...
call php artisan key:generate --force
call php artisan migrate --force
call php artisan tinker --execute="\App\Models\Egg::seedDefaults();"

:: 5. Build Assets
echo 🎨 Building UI assets...
call npm run build

:: 6. Create Admin User
echo.
set /p create_user="❓ Do you want to create an Admin User now? (y/n): "
if /i "%create_user%"=="y" (
    set /p admin_email="   Enter Email: "
    set /p admin_name="   Enter Name: "
    set /p admin_pass="   Enter Password: "
    call php artisan tinker --execute="$u = new \App\Models\FileUser(); $u->id = uniqid(); $u->email = '!admin_email!'; $u->name = '!admin_name!'; $u->password = \Illuminate\Support\Facades\Hash::make('!admin_pass!'); $u->role = 'admin'; $u->save();"
    echo ✅ Admin user created!
)

echo.
echo 🎉 EVERYTHING INSTALLED SUCCESSFULLY!
echo -------------------------------------------------------
echo Start the panel: php artisan serve
echo URL: http://127.0.0.1:8000
echo -------------------------------------------------------
pause

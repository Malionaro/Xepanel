param(
    [switch]$Native,
    [switch]$Docker
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

function Has-Command($Name) {
    return $null -ne (Get-Command $Name -ErrorAction SilentlyContinue)
}

if ($Docker -or ((-not $Native) -and (Has-Command "docker"))) {
    Write-Host "Starting Xepanel with Docker Compose..."
    docker compose up --build
    exit $LASTEXITCODE
}

Write-Host "Docker was not found. Starting native Laravel development stack instead..."

if (-not (Test-Path ".env") -and (Test-Path ".env.example")) {
    Copy-Item ".env.example" ".env"
}

if (-not (Test-Path "database/database.sqlite")) {
    New-Item -ItemType File -Path "database/database.sqlite" -Force | Out-Null
}

if (-not (Test-Path "vendor")) {
    composer install
}

if (-not (Test-Path "node_modules")) {
    npm install
}

php artisan key:generate --force
php artisan migrate --force

Write-Host "Starting Laravel server, queue worker, and Vite in separate PowerShell windows..."
Start-Process powershell -WindowStyle Normal -ArgumentList "-NoExit", "-Command", "cd '$root'; php artisan serve --host=127.0.0.1 --port=8000"
Start-Process powershell -WindowStyle Normal -ArgumentList "-NoExit", "-Command", "cd '$root'; php artisan queue:listen --tries=1 --timeout=0"
Start-Process powershell -WindowStyle Normal -ArgumentList "-NoExit", "-Command", "cd '$root'; npm run dev -- --host 127.0.0.1"

Write-Host ""
Write-Host "Xepanel is starting:"
Write-Host "  Panel: http://127.0.0.1:8000"
Write-Host "  Vite:  http://127.0.0.1:5173"

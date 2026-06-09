# Xepanel Docker Development

This stack runs the panel as a reproducible Docker development environment.

## Services

- `web`: nginx reverse proxy on `APP_PORT` or `8080`
- `app`: Laravel PHP-FPM runtime
- `queue`: Laravel queue worker
- `scheduler`: Laravel scheduler worker, including `panel:monitor`
- `vite`: Vite development server on `VITE_PORT` or `5173`

The app, queue, and scheduler containers mount `/var/run/docker.sock` so Xepanel can manage customer containers through the Docker Engine API.

## Start

```bash
docker compose up --build
```

On Windows you can use the project starter. It uses Docker when Docker is available and falls back to the native Laravel development stack when Docker is missing:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\start.ps1
```

Open:

```text
http://localhost:8080
```

The entrypoint creates `.env` from `.env.example`, creates `database/database.sqlite`, generates `APP_KEY`, runs migrations, and prepares writable Laravel directories.

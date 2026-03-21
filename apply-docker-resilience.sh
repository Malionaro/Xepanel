#!/bin/bash

# Ensure this is run as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run this script as root (sudo bash apply-docker-resilience.sh)"
  exit 1
fi

echo "--- 1. Configuring Docker Daemon (live-restore & logging limits) ---"
cat <<EOF > /etc/docker/daemon.json
{
  "live-restore": true,
  "max-concurrent-downloads": 3,
  "max-concurrent-uploads": 3,
  "default-shm-size": "64M",
  "log-driver": "json-file",
  "log-opts": {
    "max-size": "50m",
    "max-file": "3"
  }
}
EOF

echo "Reloading Docker daemon..."
systemctl reload docker
echo "Docker daemon configuration updated."

echo "--- 2. Starting Autoheal Container ---"
if [ ! "$(docker ps -a -q -f name=^autoheal$)" ]; then
    docker run -d \
      --name autoheal \
      --restart=always \
      -e AUTOHEAL_CONTAINER_LABEL=autoheal \
      -v /var/run/docker.sock:/var/run/docker.sock \
      willfarrell/autoheal
    echo "Autoheal container started."
else
    echo "Autoheal container already exists."
fi

echo "--- Done! ---"
echo "Your Docker Host is now hardened."

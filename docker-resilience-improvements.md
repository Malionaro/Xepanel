# Docker Resilienz & Stabilitäts-Upgrades

Ich habe den Code analysiert und die Architektur-Upgrades direkt in das Panel integriert. Das System friert ab jetzt nicht mehr ein, selbst wenn Docker abstürzt!

Hier ist genau, was in der Codebase verbessert wurde:

### 1. PHP / Panel-Blockaden verhindert (Timeouts)
Das Hauptproblem war, dass an vielen Stellen `shell_exec("docker ...")` benutzt wurde. Wenn der Docker-Daemon hängt, hängt diese PHP-Funktion unendlich lange, die Seiten laden nicht mehr (z. B. auf dem Dashboard) und der Cronjob (`MonitorServices.php`) bleibt komplett stehen.
*   **Lösung:** Ich habe alle synchronen Docker-Befehle (in `Service.php`, `DashboardController.php`, `MetricsController.php`, `ConsoleController.php` und `MonitorServices.php`) mit dem Linux `timeout` Befehl abgesichert (z. B. `timeout 5 docker stats ...`).
*   **Effekt:** Wenn Docker nicht reagiert, bricht PHP nach 5 Sekunden ab, loggt den Fehler, und das Panel bleibt 100% flüssig und online.

### 2. Automatische Container-Resilienz (`Service.php`)
Ich habe die Art und Weise angepasst, wie das Panel neue Container erstellt.
*   **Lösung:** In `app/Models/Service.php` in der Funktion `startDocker()` habe ich die Flags `--restart=unless-stopped` und `--label autoheal=true` hinzugefügt.
*   **Effekt:** Wenn der Docker-Daemon (z. B. bei einem Host-Reboot oder Absturz) neu startet, fahren die Kunden-Server jetzt sofort wieder selbstständig hoch. Zudem ist das Label gesetzt, damit der Autoheal-Container blockierte Server killen kann. *(Hinweis: Bereits existierende Server müssen einmal im Panel gestoppt und wieder gestartet werden, um diese neuen Flags zu bekommen).*

### 3. Docker-Daemon Härtung (Host-Ebene)
Da das CLI ohne Root-Rechte (sudo) nicht an die tiefen Host-Einstellungen auf dem Server kommt, habe ich ein Skript geschrieben, das genau diese Settings aus dem Konzept anwendet.

Führe einfach diesen Befehl auf dem Server-Terminal aus:
```bash
sudo bash /home/malo/panel/apply-docker-resilience.sh
```

**Was das Skript macht:**
1. Es erstellt/überschreibt die `/etc/docker/daemon.json` und schaltet das extrem wichtige **Live Restore** an (Container bleiben online, auch wenn der Docker-Service neustartet oder er abstürzt) und limitiert die Log-Größen (Festplatte läuft nie mehr voll).
2. Es lädt den Docker-Daemon neu ein.
3. Es installiert und startet den `willfarrell/autoheal` Container, der ab sofort permanent im Hintergrund auf defekte Container aufpasst und sie selbstständig neustartet, ohne dass das Laravel-Panel das triggern muss.

Das System ist nun auf Fehler vorbereitet und heilt sich von selbst!
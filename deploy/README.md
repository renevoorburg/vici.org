# Vici.org Production Deployment

Deze map bevat configuratiebestanden voor productiedeployment van vici.org.

## Bestanden

- `nginx-proxy.conf` - Nginx reverse proxy configuratie
- `robots.txt` - Robots.txt voor de proxy
- `vici-docker.service` - Systemd service voor Docker containers
- `docker-compose.prod.yml` - Productie Docker Compose configuratie
- `.env.prod.example` - Environment template voor productie

## Installatie

### 1. Bestanden kopiëren

```bash
# Kopieer deployment bestanden
sudo cp deploy/nginx-proxy.conf /etc/nginx/sites-available/vici.org
sudo ln -s /etc/nginx/sites-available/vici.org /etc/nginx/sites-enabled/

# Kopieer robots.txt naar proxy
sudo mkdir -p /etc/nginx/robots
sudo cp deploy/robots.txt /etc/nginx/robots/robots.txt

# Kopieer systemd service
sudo cp deploy/vici-docker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable vici-docker.service
```

### 2. Environment opzetten

```bash
# Kopieer en configureer environment
cp deploy/.env.prod.example .env.prod
# Edit .env.prod met productie waarden
```

### 3. Applicatie installeren

```bash
# Maak directories
sudo mkdir -p /opt/vici.org
sudo mkdir -p /opt/vici.org/logs
sudo mkdir -p /opt/vici.org/backups

# Kopieer applicatie bestanden
sudo cp -r . /opt/vici.org/
sudo chown -R root:root /opt/vici.org
```

### 4. SSL Certificaten (Let's Encrypt)

```bash
# Installeer certbot
sudo apt install certbot python3-certbot-nginx

# Genereer certificaten
sudo certbot --nginx -d vici.org -d www.vici.org -d tiles.vici.org -d images.vici.org
```

### 5. Starten

```bash
# Start Docker containers
sudo systemctl start vici-docker.service

# Controleer status
sudo systemctl status vici-docker.service
docker-compose -f docker-compose.prod.yml ps
```

## Productie verschillen met development

### Docker Compose
- **Ports**: 8080 i.p.v. 80 (nginx proxy handled extern)
- **Volumes**: Read-only mounts voor security
- **Health checks**: Monitoring van container status
- **Backups**: Aparte volume voor database backups

### Nginx Proxy
- **SSL**: HTTPS met Let's Encrypt
- **Security headers**: X-Frame-Options, CSP, etc.
- **Caching**: Statische files 1 jaar cache
- **Compression**: Gzip voor static content
- **Rate limiting**: Optioneel toe te voegen

### Systemd Service
- **Auto-restart**: Containers starten automatisch bij reboot
- **Health monitoring**: Service monitoring
- **Security**: Restricted permissions

## Onderhoud

### Updates

```bash
# Stop containers
sudo systemctl stop vici-docker.service

# Update code
cd /opt/vici.org
git pull origin main

# Herbuild containers
docker-compose -f docker-compose.prod.yml build

# Start containers
sudo systemctl start vici-docker.service
```

### Backups

```bash
# Database backup
docker exec vici-mysql mysqldump -u root -p vici > /opt/vici.org/backups/vici-$(date +%Y%m%d).sql

# Restore backup
docker exec -i vici-mysql mysql -u root -p vici < /opt/vici.org/backups/vici-20231201.sql
```

### Monitoring

```bash
# Container status
docker-compose -f docker-compose.prod.yml ps

# Logs
docker-compose -f docker-compose.prod.yml logs -f

# Service logs
sudo journalctl -u vici-docker.service -f
```

## Port configuratie

Poorten zijn configureerbaar via `.env.prod`:
- `NGINX_PORT=8080` - host poort voor de nginx/php container
- `DB_PORT=3306` - host poort voor MariaDB

Let op: de `upstream` poort in `deploy/nginx-proxy.conf` moet overeenkomen met `NGINX_PORT`.

## Security Notes

- Database alleen bereikbaar via localhost (3306)
- File mounts read-only waar mogelijk
- Environment variables niet in git
- SSL/TLS verplicht voor productie
- Regular security updates voor Docker images

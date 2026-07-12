# Vici.org Docker Setup

Deze setup draait vici.org volledig in Docker met nginx, PHP-FPM en MariaDB.

## Quick Start

1. Kopieer de environment file:
   ```bash
   cp .env.docker .env
   ```

2. Pas `.env` aan, vooral `VICIBASE` en poorten:
   ```bash
   # Voorbeeld met poort 8080
   NGINX_PORT=8080
   DB_PORT=3306
   VICIBASE=http://localhost:8080
   ```

3. Start de containers:
   ```bash
   docker-compose up -d
   ```

4. Open http://localhost:8080 in je browser

## Containers

- **php**: nginx + PHP-FPM 7.4 met supervisord
- **mysql**: MariaDB 10.6 met data volume

## Database

Eerste keer start importeert automatisch:
- `db/vici.sql.gz` → `vici` database
- `db/geo.sql.gz` → `geo` database

Data wordt bewaard in Docker volume `mysql_data`.

## Environment Variables

Pas `.env` aan voor je configuratie:
- `DB_HOST=mysql` - database host
- `DB_MAIN=vici` - hoofd database naam
- `DB_GEO=geo` - geo database naam
- `DB_USER=vici` - database gebruiker
- `DB_PASS=vici` - database wachtwoord
- `VICIBASE=http://localhost` - base URL (pas poort aan indien nodig)
- `VICITOKEN=your-secret-token` - API token
- `EXTSECRET=your-ext-secret` - external secret
- `NGINX_PORT=80` - host poort voor nginx
- `DB_PORT=3306` - host poort voor MariaDB
- `MYSQL_ROOT_PASSWORD=rootpassword` - MariaDB root wachtwoord
- `CUST1-9` (optioneel) - extra API tokens
- `UA1-9` (optioneel) - user-agent prefixes

## Development

- PHP code: `public/` directory (gemount als volume)
- Nginx config: `nginx/default.conf`
- Database dumps: `db/` directory

## Stoppen

```bash
docker-compose down
```

## Database reset

```bash
docker-compose down -v  # verwijdert data volume
docker-compose up -d    # herimporteert databases
```

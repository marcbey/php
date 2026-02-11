# PHP PDO CRUD Demo (MariaDB)

Kurztest-Demo ohne Framework: CRUD-Operationen mit PDO, OOP, HTML/CSS.

## Voraussetzungen

- PHP 8.3+
- MariaDB 10.6+

## Setup

1. `.env.example` nach `.env` kopieren und DB-Zugangsdaten anpassen.
2. Datenbank anlegen und Schema + Seeds importieren:

```bash
mysql -u root -p -e "CREATE DATABASE demo_crud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p demo_crud < schema.sql
mysql -u root -p demo_crud < seed.sql
```

3. Lokalen Server starten:

```bash
php -S localhost:8000 -t public
```

App aufrufen: `http://localhost:8000`

## Features

- CRUD für `tasks` und `projects`
- PDO mit Prepared Statements (SQL-Injection-Schutz)
- Input-Validierung + Output-Escaping (XSS-Schutz)
- CSRF-Token + Same-Origin-Check
- Security-Header (CSP, `X-Frame-Options`, `X-Content-Type-Options`)
- Kein serverseitiger URL-Fetch-Flow aus User-Input (SSRF-by-design vermieden)
- Simple MVC-Struktur (ohne Framework)

## Struktur

- `public/` Front Controller + Assets
- `src/` OOP Code
- `schema.sql` / `seed.sql`

# Local Setup Guide

This guide provides a minimal local setup for running Zippd.

### Prerequisites

- Docker Desktop (with Docker Compose)
- Node.js 20+ and npm (for frontend dev workflow)

### 1) Start services

From the repository root:

```bash
docker compose up -d --build
```

This starts:

- SQL Server at `localhost:11433`
- Laravel app at `http://localhost:8000`

### 2) Configure environment

```bash
cp laravel-app/.env.example laravel-app/.env
```

The default `.env.example` values are already set for Docker:

- `DB_CONNECTION=sqlsrv`
- `DB_HOST=db`
- `DB_PORT=1433`
- `DB_DATABASE=zippd_db`
- `DB_USERNAME=SA`
- `DB_PASSWORD=Zippd@12345678`

### 3) Install dependencies

```bash
docker compose exec app composer install
cd laravel-app
npm install
cd ..
```

### 4) Create the database

```bash
docker compose exec db /opt/mssql-tools18/bin/sqlcmd -S localhost -U SA -P "Zippd@12345678" -Q "IF DB_ID('zippd_db') IS NULL CREATE DATABASE zippd_db;" -C
```

### 5) Run migrations

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 6) Optional demo data

Run `database/seed/seeders.sql` against `zippd_db` using SSMS or Azure Data Studio.

### 7) Frontend development (optional)

In a separate terminal:

```bash
cd laravel-app
npm run dev
```

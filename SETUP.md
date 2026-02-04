# ERP System - Setup Guide

This guide will help you set up the ERP system for development.

## Prerequisites

- **Docker & Docker Compose** (recommended) OR
- **PHP 8.2+**, **Composer**, **Node.js 18+**, **PostgreSQL 15+**, **Redis 7+**

## Quick Start with Docker (Recommended)

### 1. Clone the Repository

```bash
git clone https://github.com/kasunvimarshana/erp.git
cd erp
```

### 2. Set Up Backend Environment

```bash
cd backend
cp .env.docker.example .env
php artisan key:generate
cd ..
```

### 3. Set Up Frontend Environment

```bash
cd frontend
cp .env.example .env
cd ..
```

### 4. Start All Services

```bash
docker-compose up -d
```

This will start:
- PostgreSQL (port 5432)
- Redis (port 6379)
- Backend API (port 8000)
- Frontend (port 3000)
- MailHog (port 8025 for UI, 1025 for SMTP)

### 5. Run Migrations

```bash
docker-compose exec backend php artisan migrate --seed
```

### 6. Access the Application

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api/documentation (coming soon)
- **MailHog**: http://localhost:8025

## Manual Setup (Without Docker)

### Backend Setup

1. **Install Dependencies**

```bash
cd backend
composer install
```

2. **Configure Environment**

```bash
cp .env.example .env
# Edit .env and configure your database connection
php artisan key:generate
```

3. **Run Migrations**

```bash
php artisan migrate --seed
```

4. **Start Development Server**

```bash
php artisan serve
```

The backend API will be available at `http://localhost:8000`

### Frontend Setup

1. **Install Dependencies**

```bash
cd frontend
npm install
```

2. **Configure Environment**

```bash
cp .env.example .env
# Edit .env if needed
```

3. **Start Development Server**

```bash
npm run dev
```

The frontend will be available at `http://localhost:3000`

## Development Commands

### Backend

```bash
# Run tests
php artisan test

# Run code style checks
./vendor/bin/phpstan analyze

# Clear all caches
php artisan optimize:clear

# Create a new migration
php artisan make:migration create_table_name

# Create a new model
php artisan make:model ModelName

# Create a new controller
php artisan make:controller ControllerName

# Create a new seeder
php artisan make:seeder SeederName
```

### Frontend

```bash
# Run tests
npm run test:unit

# Run linter
npm run lint

# Build for production
npm run build

# Preview production build
npm run preview
```

## Project Structure

```
erp/
├── backend/              # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Repositories/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   └── tests/
├── frontend/             # Vue.js frontend
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/
│   │   ├── router/
│   │   └── assets/
│   └── tests/
├── docker/               # Docker configuration
│   ├── Dockerfile.backend
│   └── Dockerfile.frontend
└── docker-compose.yml
```

## Module Structure

Modules will be organized under `backend/modules/`:

```
modules/
├── iam/                  # Identity & Access Management
├── inventory/            # Inventory Management
├── crm/                  # Customer Relationship Management
├── sales/                # Sales Management
└── procurement/          # Procurement Management
```

Each module follows the structure:

```
module_name/
├── src/
│   ├── Controllers/
│   ├── Services/
│   ├── Repositories/
│   ├── Models/
│   └── Events/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
└── module.json
```

## Testing

### Backend Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter=TestClassName
```

### Frontend Tests

```bash
# Run unit tests
npm run test:unit

# Run with coverage
npm run test:coverage

# Run E2E tests
npm run test:e2e
```

## Troubleshooting

### Docker Issues

**Containers won't start:**
```bash
docker-compose down
docker-compose up -d --build
```

**Database connection errors:**
```bash
docker-compose restart postgres
docker-compose logs postgres
```

### Backend Issues

**500 errors:**
- Check `.env` file is configured correctly
- Ensure `APP_KEY` is generated
- Check file permissions on `storage/` and `bootstrap/cache/`

```bash
chmod -R 775 storage bootstrap/cache
```

**Database connection errors:**
- Verify PostgreSQL is running
- Check database credentials in `.env`
- Test connection: `php artisan migrate:status`

### Frontend Issues

**Build errors:**
```bash
rm -rf node_modules package-lock.json
npm install
```

**API connection errors:**
- Verify `VITE_API_URL` in `.env`
- Check backend is running
- Check browser console for CORS errors

## Next Steps

1. Read the [Architecture Documentation](./ARCHITECTURE.md)
2. Review the [Implementation Guide](./IMPLEMENTATION_GUIDE.md)
3. Check the [Module System Documentation](./MODULE_SYSTEM.md)
4. Start developing your first module!

## Support

- **Documentation**: See the `docs/` directory
- **Issues**: https://github.com/kasunvimarshana/erp/issues
- **Discussions**: https://github.com/kasunvimarshana/erp/discussions

## License

This project is open-sourced software licensed under the MIT license.

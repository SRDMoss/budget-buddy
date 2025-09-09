# Budget Buddy API

This is the backend of Budget Buddy, built with PHP 8 and MySQL.

## Structure
- **app/**: Core classes, controllers, models
- **config/**: Configuration files (DB, app settings)
- **public/**: Entry point (`index.php`) and static files
- **scripts/**: Database migrations and seeds
- **storage/**: Logs, uploads
- **tests/**: PHPUnit tests

## Setup
```bash
composer install
cp .env.example .env
php -S localhost:8000 -t public

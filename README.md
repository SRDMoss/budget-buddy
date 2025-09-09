# Budget Buddy

A simple, modern budgeting app with a PHP API and a React front-end. Track income and expenses, categorize transactions, and see clean visual summaries.

## Tech Stack
- **Backend:** PHP 8+, MySQL (PDO, PSR-4 autoload, Composer)
- **Frontend:** React (Vite), TypeScript (optional), modern component patterns
- **Testing (planned):** PHPUnit (API), Jest/RTL + Playwright (web)

## Features (MVP)
- Auth (register/login/logout)
- Categories (CRUD)
- Transactions (CRUD, filters, search, sort)
- Dashboard with monthly totals + category breakdown
- CSV export

## Getting Started

### Prerequisites
- PHP 8.2+
- MySQL 8 (or MariaDB)
- Node.js 20+
- Composer (for PHP dependencies)

### Setup
```
# API
cd api
cp .env.example .env           # create your local env file
composer install

# Web
cd ../web
npm install
```

## Running (dev)

- API: configure your web server to serve api/public/ (or use PHP’s built-in server for dev)
- Web: npm run dev (Vite dev server)

### Environment Variables

- API: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, APP_ENV, APP_DEBUG
- Web: VITE_API_BASE_URL

### Folder Structure

budget-buddy/
├─ api/
│  ├─ public/           # index.php (front controller)
│  ├─ app/              # Core, Controllers, Models
│  ├─ config/
│  ├─ scripts/          # migrations, seeds
│  └─ storage/          # logs, uploads
├─ web/
│  ├─ src/              # components, pages, hooks, api
│  └─ public/
├─ docs/
├─ .editorconfig
├─ .gitignore
├─ LICENSE
└─ README.md



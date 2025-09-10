# PHP Employer Impression Checklist

This covers the **non-functional** pieces that make a PHP project look professional.

---

## 🗂️ Project Structure & Organization
- [ ] Clear directory layout (`app/`, `config/`, `public/`, `tests/`, `storage/`)
- [ ] PSR-4 namespaces with Composer autoloading
- [ ] Reusable helpers; no duplicate code
- [ ] Separation of concerns (controllers, models, core utilities)

---

## ⚙️ Environment & Config
- [ ] `.env` file for secrets/config
- [ ] `.env.example` committed for reference
- [ ] Config files (e.g., `config/database.php`, `config/app.php`) read env vars
- [ ] No hard-coded credentials

---

## 🛡️ Security Practices
- [ ] Passwords hashed with `password_hash()` and checked with `password_verify()`
- [ ] PDO prepared statements for all queries
- [ ] CSRF tokens for forms and state-changing requests
- [ ] Secure session cookies (HttpOnly, Secure, SameSite)
- [ ] Input validation & sanitization
- [ ] Escaped output for HTML (XSS protection)

---

## 🧪 Testing & Quality
- [ ] PHPUnit tests (unit + integration where sensible)
- [ ] PSR-12 code style enforced
- [ ] Linter/fixer configured (PHP-CS-Fixer or PHP_CodeSniffer)
- [ ] Type hints and docblocks where applicable

---

## 📝 Documentation
- [ ] `README.md` includes:
  - [ ] Project description and purpose
  - [ ] Setup instructions
  - [ ] Example `.env` file
  - [ ] API routes and examples
- [ ] Architecture diagram (in `/docs`)
- [ ] API reference (Markdown or Swagger/OpenAPI)

---

## 🔄 Database Discipline
- [ ] Migration scripts (`/scripts/migrations`)
- [ ] Seed data for demo/test users
- [ ] Foreign keys & indexes where appropriate

---

## 🚀 Deployment Readiness
- [ ] `.gitignore` excludes sensitive/build artifacts (`.env`, `vendor/`, `node_modules/`)
- [ ] Error handling differs for dev vs prod (no stack traces in prod)
- [ ] Logging enabled (e.g., `/storage/logs/app.log`)
- [ ] Security headers set (CSP, `X-Content-Type-Options: nosniff`, `X-Frame-Options`, `Referrer-Policy`)

---

## 📦 Modern PHP Practices
- [ ] Composer autoloading (`vendor/autoload.php`)
- [ ] Namespaced classes; avoid manual `require` chains
- [ ] Avoid globals; prefer dependency injection
- [ ] Small, focused classes and functions

---

## 🔄 Version Control Hygiene
- [ ] Descriptive, atomic commits
- [ ] Clean `main` branch; features in separate branches
- [ ] No sensitive files committed (`.env`, `vendor/`, `node_modules/`)

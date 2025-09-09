# Project Plan – Budget App (PHP API + React Frontend)

## Build Steps

1. **X Repo setup**
   - Init git, add `.editorconfig`, `.gitignore`, `README.md`, `LICENSE`.
   - Create `api/` (PHP) and `web/` (React) folders.
   - Optional: add `docker-compose.yml` for MySQL + PHP.

2. **X Environment & config**
   - `.env` files for API and React (`API_BASE_URL`, DB creds).
   - Config loader in PHP.

3. **X Database**
   - Schema: `users`, `categories`, `transactions`.
   - Migration + seed scripts.

4. **X PHP API bootstrap**
   - Front controller + router.
   - Core: DB (PDO), Auth, CSRF, JSON responder.

5. **X Security baseline**
   - Secure session cookies.
   - Security headers (CSP, nosniff, frame-options, referrer-policy).
   - Locked CORS to React origin.
   - Input validation helpers.

6. **X Auth module**
   - Endpoints: register, login, logout, me.
   - Password hashing, sessions/JWT.

7. **X Categories module**
   - CRUD endpoints.

8. **X Transactions module**
   - CRUD endpoints (filters: month, category, type).

9. **X Reports module**
   - Monthly totals, category breakdown.

10. **X Error handling & logging**
    - Central exception handler.
    - Structured logs, hide stack traces.

11. **X React app scaffold**
    - Vite + React project structure.

12. **X Auth UI**
    - Login/register forms.
    - Auth context/hook.

13. **X Categories UI**
    - Table + modals.

14. **X Transactions UI**
    - Table with filters + modals.

15. **X Dashboard UI**
    - Summary cards + charts.

16. **Client–API integration**
    - API client with interceptors.
    - Central error handler.

17. **Accessibility & UX polish**
    - Keyboard navigation, ARIA, focus states.

18. **Testing**
    - API unit + integration tests.
    - React component + E2E tests.

19. **Build & deploy**
    - API → PHP host.
    - React → Netlify/Vercel.

20. **Docs & demo**
    - README, setup, endpoints, screenshots.
    - Seed script + demo user.

---

## Portfolio Polish (Impressive Features)

A. **Security**
   - HTTPS-only, secure cookies, CSP, strict CORS, CSRF tokens, rate-limited login.

B. **Testing**
   - Unit, integration, component, and E2E tests.

C. **Clean API design**
   - Consistent JSON, status codes, pagination, `/api/v1` prefix.

D. **Accessibility**
   - ARIA, contrast, keyboard navigation.

E. **Performance**
   - DB indexes, server pagination, React code-splitting.

F. **DX (Developer Experience)**
   - `.env.example`, scripts, seed data, linting/formatting.

G. **Observability**
   - Structured logs, request IDs, API latency metrics.

H. **Error UX**
   - Friendly errors, empty/loading states, 404/500 pages.

I. **Docs & Screenshots**
   - README with architecture diagram + screenshots.

J. **CI/CD**
   - GitHub Actions for lint/test + auto-deploy.

K. **Seeded demo mode**
   - Demo user with sample transactions.

L. **Data export**
   - CSV export for transactions/reports.

M. **Budgets**
   - Category budgets with visual progress bars.

N. **Audit trail**
   - Track edits/deletes for accountability.

O. **i18n readiness**
   - String maps, date/number formatting.

P. **Roles**
   - Owner vs. member roles.

Q. **Mobile polish**
   - Responsive layout, touch-friendly forms.

R. **Onboarding/help**
   - Tooltips, onboarding checklist.

S. **Migrations**
   - Versioned DB migration runner.

T. **Demo video**
   - 90-second walkthrough.

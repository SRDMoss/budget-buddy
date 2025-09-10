# Budget Buddy – Functional/Content Checklist

This covers the **features and behaviors** the app should implement (what it *does*), independent of implementation details.

---

## 👤 Authentication & Accounts
- [ ] Register with email + password
- [ ] Login/logout flows (with “remember me” optional)
- [ ] Password reset (request + token + change)
- [ ] “Me” endpoint / profile page (email, display name)
- [ ] Session timeout + re-auth on sensitive actions

---

## 🏷️ Categories
- [ ] CRUD categories (name, color)
- [ ] Category icons/colors shown consistently in UI
- [ ] Prevent duplicate names (per user)
- [ ] Merge category (move transactions + delete old)
- [ ] Archive category (hide from pickers but keep history)

---

## 💸 Transactions
- [ ] Create income/expense with: amount, date, category, note, payee
- [ ] Edit/delete transactions
- [ ] List view with:
  - [ ] Filters: date range, category, type (income/expense), min/max
  - [ ] Sort by date, amount, category
  - [ ] Search by payee/note
  - [ ] Pagination or infinite scroll
- [ ] Bulk actions (delete, recategorize)
- [ ] Running balance (optional toggle)

---

## 📊 Dashboard & Reports
- [ ] Summary cards: income, expenses, net for selected period
- [ ] Category breakdown (pie/donut)
- [ ] Monthly totals (bar/line) across the year
- [ ] Top categories / payees
- [ ] Export chart data (CSV)

---

## 🎯 Budgets (Per Category / Monthly)
- [ ] Set monthly budget per category
- [ ] Show used vs remaining (progress bar)
- [ ] Over-budget warning (color + toast)
- [ ] Carryover option (unused budget rolls forward)

---

## 🔁 Recurring Items (Nice-to-have)
- [ ] Define recurring transactions (amount, category, frequency, next run date)
- [ ] Auto-post on schedule (or generate pending entries to confirm)
- [ ] Pause/Resume recurrence
- [ ] Recurrence history

---

## ⬆️⬇️ Import / Export
- [ ] CSV import (mapping columns: date, amount, category, payee, note)
- [ ] Dry-run preview with error report (invalid dates, missing categories)
- [ ] CSV export (all transactions or filtered set)
- [ ] Duplicate detection on import (hash or similar)

---

## 🔔 Notifications (Optional)
- [ ] Over-budget alert (per category threshold)
- [ ] Large transaction alert (configurable threshold)
- [ ] Email or in-app toasts (user preference)

---

## 👥 Sharing (Optional)
- [ ] Invite collaborator by email
- [ ] Roles: owner, editor (can add/edit), viewer (read-only)
- [ ] Activity log (who changed what, when)

---

## ⚙️ Settings
- [ ] Currency + locale (date/number formatting)
- [ ] First day of week / month start rules
- [ ] Default date range on load (e.g., “This month”)
- [ ] Timezone selection (if needed)
- [ ] Data export / account deletion (GDPR-friendly)

---

## 🧭 UX States & Flows
- [ ] Empty states with helpful CTAs (no transactions, no categories)
- [ ] Loading states (skeletons/spinners)
- [ ] Error states with retry
- [ ] Confirm dialogs for destructive actions (delete, merge)
- [ ] Undo snackbar (where safe: e.g., delete transaction)

---

## ♿ Accessibility (Functional)
- [ ] Forms fully operable via keyboard
- [ ] Screen-reader labels for form fields & charts (aria-label/desc)
- [ ] Focus management on modals and route changes

---

## 🔐 Security (Functional behaviors)
- [ ] Only the owner/collaborators can access their data (no IDOR)
- [ ] Server-side validation errors presented inline in forms
- [ ] Rate-limit login attempts (user feedback on lockout)
- [ ] Logout everywhere (invalidate other sessions)

---

## 🧪 Demo & Seed Data
- [ ] Seed script creates demo user with realistic categories and 3–6 months of transactions
- [ ] Demo walkthrough: add transaction → dashboard updates → export CSV

---

## 🧵 API Surface (Minimum Endpoints)
- [ ] `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`
- [ ] `GET/POST/PATCH/DELETE /categories`
- [ ] `GET/POST/PATCH/DELETE /transactions`
- [ ] `GET /reports/summary?from=YYYY-MM-DD&to=YYYY-MM-DD`
- [ ] `GET /reports/category-breakdown?month=YYYY-MM`
- [ ] `POST /import/transactions`, `GET /export/transactions.csv`
- [ ] (Optional) `GET/POST /budgets` per category, `GET /reports/budgets?month=YYYY-MM`

---

## ✅ Acceptance Criteria (MVP Cut)
- [ ] A user can sign up, log in, and stay authenticated across pages
- [ ] User can add/edit/delete transactions and categories
- [ ] Dashboard shows correct totals for selected month
- [ ] Category breakdown and monthly totals charts match underlying data
- [ ] Transactions list filters and search work together correctly
- [ ] CSV export downloads filtered results
- [ ] All data is scoped to the authenticated user (no cross-user leaks)

---

## 🚀 Stretch Goals (High-Impact for Portfolio)
- [ ] Budgets with progress bars and overage warnings
- [ ] Recurring transactions (auto or confirm)
- [ ] Collaborator sharing with roles
- [ ] Advanced reports (trendlines, savings rate, cashflow forecast)
- [ ] Mobile-friendly PWA (installable, offline view of last month)

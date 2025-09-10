# JS/React Employer Impression Checklist

This covers the **non-functional** pieces that make a React project look professional.

---

## 🗂️ Project Structure & Conventions
- [ ] Clear src layout (`src/components`, `src/pages`, `src/hooks`, `src/lib`, `src/styles`, `src/assets`)
- [ ] Absolute imports / path aliases (e.g., `@/components/...`)
- [ ] Consistent naming: `PascalCase` for components, `camelCase` for files/functions
- [ ] Centralized barrel exports where sensible (avoid deep import paths)

---

## ⚙️ Tooling & Environment
- [ ] Vite (or Next.js) configured; fast dev + production build
- [ ] `.env` / `.env.local` for secrets & config (never committed)
- [ ] Separate `development` vs `production` configs (API base URLs, logging)
- [ ] Scripts in `package.json` for dev, build, preview, test, lint, type-check

---

## 🧩 Type Safety (recommended)
- [ ] TypeScript configured (`tsconfig.json`)
- [ ] Strict mode on (`"strict": true`)
- [ ] Types for API responses and app models (no `any` soup)

---

## 🎯 State & Data Layer
- [ ] Lightweight state mgmt (Context/Zustand/Redux Toolkit) with clear boundaries
- [ ] Data fetching with React Query/RTK Query (caching, retries, loading/error states)
- [ ] API client abstraction (fetch/axios wrapper, interceptors, typed endpoints)

---

## 🧪 Testing & Quality
- [ ] Unit/component tests (Jest + React Testing Library) for key components/hooks
- [ ] E2E tests (Playwright or Cypress) for core flows (auth, CRUD happy path)
- [ ] ESLint (airbnb or recommended + React hooks rules)
- [ ] Prettier for formatting; `.editorconfig` present
- [ ] Husky + lint-staged to lint/test on commit (optional but polished)

---

## ♿ Accessibility (a11y)
- [ ] Semantic HTML; forms/labels/roles correct
- [ ] Keyboard navigation & focus outlines managed
- [ ] Color contrast meets WCAG AA
- [ ] ARIA only when necessary; no div-spans for interactive elements

---

## 🎨 UI/UX Polish
- [ ] Design tokens / variables for spacing, colors, typography
- [ ] Reusable UI primitives (Button, Modal, Input, Select, Table)
- [ ] Empty, loading, and error states for all data views
- [ ] Responsive layouts; mobile-first checks

---

## 📈 Performance
- [ ] Code-splitting via `import()` for pages/heavy modules
- [ ] Memoization: `React.memo`, `useMemo`, `useCallback` where needed
- [ ] Image optimization (dimensions, lazy loading)
- [ ] Bundle analysis (e.g., `rollup-plugin-visualizer`); avoid heavy deps

---

## 🔐 Security (Front-end)
- [ ] No secrets in client bundle; use env for public keys only
- [ ] Safe rendering: escape user input, avoid `dangerouslySetInnerHTML`
- [ ] Proper auth handling (httpOnly cookies or token storage strategy explained)
- [ ] Stri

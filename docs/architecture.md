
---

### `/docs/architecture.md`
```markdown
# Budget Buddy – Architecture Overview

## Overview
Budget Buddy is a full-stack budgeting app with:
- **Backend (api/):** PHP 8 + MySQL (RESTful API)
- **Frontend (web/):** React + Vite (consumes the API)

## Flow
1. User interacts with React app.
2. React calls PHP API endpoints.
3. PHP API queries MySQL and responds with JSON.
4. React displays results in UI.

## Future Enhancements
- Add budgets per category
- Import/export CSV
- Optional recurring transactions

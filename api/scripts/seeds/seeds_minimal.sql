-- Assumes you're using the same DB / collation as the migration
-- USE budgetbuddy;

-- Demo user (password: password123 — replace with a real hash in production)
INSERT INTO users (email, password_hash, display_name)
VALUES
  ('demo@budgetbuddy.local', '$2y$10$6X0m4rF1sVq9tA7uGm0j4OxJY7w0qgKrxS5o5M6sPZ2p3cAD2b9Ju', 'Demo User');

-- Grab the inserted user id
SET @uid := LAST_INSERT_ID();

-- Categories
INSERT INTO categories (user_id, name, color_hex) VALUES
  (@uid, 'Rent',       '#6B7280'),
  (@uid, 'Groceries',  '#10B981'),
  (@uid, 'Dining',     '#F59E0B'),
  (@uid, 'Utilities',  '#3B82F6'),
  (@uid, 'Salary',     '#8B5CF6');

-- Some transactions (current month)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note)
SELECT
  @uid,
  c.id,
  'expense',
  125.43, 'USD',
  DATE_FORMAT(CURDATE(), '%Y-%m-05'),
  'Whole Foods', 'Weekly groceries'
FROM categories c WHERE c.user_id=@uid AND c.name='Groceries';

INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note)
SELECT
  @uid,
  c.id,
  'expense',
  48.20, 'USD',
  DATE_FORMAT(CURDATE(), '%Y-%m-08'),
  'Chipotle', 'Lunch with friend'
FROM categories c WHERE c.user_id=@uid AND c.name='Dining';

INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note)
SELECT
  @uid,
  c.id,
  'income',
  4200.00, 'USD',
  DATE_FORMAT(CURDATE(), '%Y-%m-01'),
  'Acme Corp', 'Monthly salary'
FROM categories c WHERE c.user_id=@uid AND c.name='Salary';

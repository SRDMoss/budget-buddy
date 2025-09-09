-- 900_seed_demo.sql
-- Budget Buddy demo data: 6 categories, Jan–Aug 2025, with ups/downs

START TRANSACTION;

-- set your user id
SET @uid := 1;

-- categories (unique per user by name)
INSERT INTO categories (user_id, name, color_hex, is_archived, created_at)
VALUES
(@uid, 'Rent',       '#EF4444', 0, NOW()),
(@uid, 'Utilities',  '#3B82F6', 0, NOW()),
(@uid, 'Groceries',  '#10B981', 0, NOW()),
(@uid, 'Dining',     '#F59E0B', 0, NOW()),
(@uid, 'Salary',     '#22C55E', 0, NOW()),
(@uid, 'Car Repair', '#7C3AED', 0, NOW())
ON DUPLICATE KEY UPDATE color_hex = VALUES(color_hex);

-- capture category ids
SELECT @cat_rent      := id FROM categories WHERE user_id=@uid AND name='Rent'       LIMIT 1;
SELECT @cat_utils     := id FROM categories WHERE user_id=@uid AND name='Utilities'  LIMIT 1;
SELECT @cat_groceries := id FROM categories WHERE user_id=@uid AND name='Groceries'  LIMIT 1;
SELECT @cat_dining    := id FROM categories WHERE user_id=@uid AND name='Dining'     LIMIT 1;
SELECT @cat_salary    := id FROM categories WHERE user_id=@uid AND name='Salary'     LIMIT 1;
SELECT @cat_carrepair := id FROM categories WHERE user_id=@uid AND name='Car Repair' LIMIT 1;

-- optional: clear any prior demo rows for 2025
DELETE FROM transactions
WHERE user_id=@uid AND txn_date BETWEEN '2025-01-01' AND '2025-12-31';

-- JAN 2025 (net +1240)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-01-01','Employer','January salary',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-01-03','Landlord','Rent Jan',NOW()),
(@uid,@cat_utils,    'expense', 140.00,'USD','2025-01-10','Power Co','Utilities Jan',NOW()),
(@uid,@cat_groceries,'expense', 420.00,'USD','2025-01-12','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 310.00,'USD','2025-01-20','Restaurants','Dining out',NOW()),
(@uid,@cat_groceries,'expense', 390.00,'USD','2025-01-27','Market','Stock-up',NOW());

-- FEB 2025 (net +1720)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-02-01','Employer','February salary',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-02-03','Landlord','Rent Feb',NOW()),
(@uid,@cat_utils,    'expense', 160.00,'USD','2025-02-10','Power Co','Utilities Feb',NOW()),
(@uid,@cat_groceries,'expense', 460.00,'USD','2025-02-14','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 160.00,'USD','2025-02-18','Restaurants','Dining out',NOW());

-- MAR 2025 (net -180)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-03-01','Employer','March salary',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-03-03','Landlord','Rent Mar',NOW()),
(@uid,@cat_utils,    'expense', 175.00,'USD','2025-03-10','Power Co','Utilities Mar',NOW()),
(@uid,@cat_groceries,'expense', 520.00,'USD','2025-03-12','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 420.00,'USD','2025-03-16','Restaurants','Dining out',NOW()),
(@uid,@cat_carrepair,'expense',1565.00,'USD','2025-03-22','Auto Shop','Brake + tires',NOW());

-- APR 2025 (net -530)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 3200.00,'USD','2025-04-01','Employer','April salary (PTO)',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-04-03','Landlord','Rent Apr',NOW()),
(@uid,@cat_utils,    'expense', 165.00,'USD','2025-04-10','Power Co','Utilities Apr',NOW()),
(@uid,@cat_groceries,'expense', 580.00,'USD','2025-04-12','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 360.00,'USD','2025-04-17','Restaurants','Dining out',NOW()),
(@uid,@cat_carrepair,'expense',1125.00,'USD','2025-04-24','Auto Shop','Suspension',NOW());

-- MAY 2025 (net +1795)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-05-01','Employer','May salary',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-05-03','Landlord','Rent May',NOW()),
(@uid,@cat_utils,    'expense', 150.00,'USD','2025-05-10','Power Co','Utilities May',NOW()),
(@uid,@cat_groceries,'expense', 520.00,'USD','2025-05-13','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 235.00,'USD','2025-05-20','Restaurants','Dining out',NOW()),
(@uid,NULL,          'income',  200.00,'USD','2025-05-28','Rebate','Cashback/credit',NOW());

-- JUN 2025 (net -40)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 3800.00,'USD','2025-06-01','Employer','June salary (unpaid leave)',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-06-03','Landlord','Rent Jun',NOW()),
(@uid,@cat_utils,    'expense', 180.00,'USD','2025-06-10','Power Co','Utilities Jun',NOW()),
(@uid,@cat_groceries,'expense', 610.00,'USD','2025-06-12','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 400.00,'USD','2025-06-18','Restaurants','Dining out',NOW()),
(@uid,@cat_carrepair,'expense',1150.00,'USD','2025-06-26','Auto Shop','AC repair',NOW());

-- JUL 2025 (net +1445)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-07-01','Employer','July salary',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-07-03','Landlord','Rent Jul',NOW()),
(@uid,@cat_utils,    'expense', 145.00,'USD','2025-07-10','Power Co','Utilities Jul',NOW()),
(@uid,@cat_groceries,'expense', 520.00,'USD','2025-07-13','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 390.00,'USD','2025-07-21','Restaurants','Dining out',NOW());

-- AUG 2025 (net +1605)
INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
(@uid,@cat_salary,   'income', 4200.00,'USD','2025-08-01','Employer','August salary (bonus)',NOW()),
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-08-03','Landlord','Rent Aug',NOW()),
(@uid,@cat_utils,    'expense', 155.00,'USD','2025-08-10','Power Co','Utilities Aug',NOW()),
(@uid,@cat_groceries,'expense', 520.00,'USD','2025-08-12','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense', 420.00,'USD','2025-08-22','Restaurants','Dining out',NOW());

-- Get category ids for this user (created by the earlier seed)
SELECT @cat_rent      := id FROM categories WHERE user_id=@uid AND name='Rent'       LIMIT 1;
SELECT @cat_utils     := id FROM categories WHERE user_id=@uid AND name='Utilities'  LIMIT 1;
SELECT @cat_groceries := id FROM categories WHERE user_id=@uid AND name='Groceries'  LIMIT 1;
SELECT @cat_dining    := id FROM categories WHERE user_id=@uid AND name='Dining'     LIMIT 1;
SELECT @cat_salary    := id FROM categories WHERE user_id=@uid AND name='Salary'     LIMIT 1;
SELECT @cat_carrepair := id FROM categories WHERE user_id=@uid AND name='Car Repair' LIMIT 1;

-- Optional: stop if any essential categories are missing
-- (Uncomment to enforce)
-- SELECT IF(@cat_salary IS NULL OR @cat_rent IS NULL OR @cat_utils IS NULL OR @cat_groceries IS NULL OR @cat_dining IS NULL, 
--   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='One or more categories missing for this user', 0);

-- Clean just this month for this user to avoid duplicates while iterating
DELETE FROM transactions
WHERE user_id=@uid AND txn_date BETWEEN '2025-09-01' AND '2025-09-30';

-- ======== SEPTEMBER 2025 ========
-- Net is modestly positive but with a dip from car repair

INSERT INTO transactions (user_id, category_id, type, amount, currency, txn_date, payee, note, created_at) VALUES
-- Income
(@uid,@cat_salary,   'income', 4000.00,'USD','2025-09-01','Employer','September salary',NOW()),

-- Fixed costs
(@uid,@cat_rent,     'expense',1500.00,'USD','2025-09-03','Landlord','Rent Sep',NOW()),
(@uid,@cat_utils,    'expense', 150.00,'USD','2025-09-10','Power Co','Utilities Sep',NOW()),

-- Variable: groceries + dining
(@uid,@cat_groceries,'expense', 180.00,'USD','2025-09-05','Market','Groceries',NOW()),
(@uid,@cat_dining,   'expense',  82.00,'USD','2025-09-07','Cafe','Brunch',NOW()),
(@uid,@cat_groceries,'expense',  95.00,'USD','2025-09-12','Market','Top-up',NOW()),
(@uid,@cat_dining,   'expense',  65.00,'USD','2025-09-14','Restaurant','Dinner out',NOW()),

-- One-time: car repair (creates a noticeable dip)
(@uid,@cat_carrepair,'expense', 620.00,'USD','2025-09-08','Auto Shop','Battery + alignment',NOW());

COMMIT;

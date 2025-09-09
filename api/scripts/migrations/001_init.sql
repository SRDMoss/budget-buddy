-- Create database (run once if not already created)
-- CREATE DATABASE IF NOT EXISTS budgetbuddy
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_0900_ai_ci; -- If MySQL 8+
-- USE budgetbuddy;

-- If you're on MySQL < 8 or MariaDB, use utf8mb4_unicode_ci instead of 0900_ai_ci.
SET NAMES utf8mb4;

-- USERS
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name  VARCHAR(100) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CATEGORIES (scoped per user)
CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  name        VARCHAR(100) NOT NULL,
  color_hex   CHAR(7) NULL, -- e.g. #FF9900
  is_archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_categories_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT uq_categories_user_name UNIQUE (user_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TRANSACTIONS
CREATE TABLE IF NOT EXISTS transactions (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  category_id  INT UNSIGNED NULL,
  type         ENUM('income','expense') NOT NULL,
  amount       DECIMAL(12,2) NOT NULL,
  currency     CHAR(3) NOT NULL DEFAULT 'USD',
  txn_date     DATE NOT NULL,
  payee        VARCHAR(160) NULL,
  note         TEXT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tx_user      FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
  CONSTRAINT fk_tx_category  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Helpful indexes
CREATE INDEX idx_tx_user_date    ON transactions (user_id, txn_date);
CREATE INDEX idx_tx_user_type    ON transactions (user_id, type);
CREATE INDEX idx_tx_user_amount  ON transactions (user_id, amount);
CREATE INDEX idx_tx_category     ON transactions (category_id);

ALTER TABLE transactions
  ADD INDEX idx_txn_user_date (user_id, txn_date),
  ADD INDEX idx_txn_user_cat  (user_id, category_id);

-- Migration: 001_add_users_created_at.sql
-- Idempotent: adds created_at column to `users` table if it does not exist
USE biowell_insurance;

SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'created_at'
);

SET @sql := IF(@col_exists = 0,
  'ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
  'SELECT "created_at_already_exists"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

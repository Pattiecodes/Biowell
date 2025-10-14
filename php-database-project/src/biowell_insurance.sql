-- BioWell Insurance Company Database Schema
-- Save this file as biowell_insurance.sql and import using phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS biowell_insurance;
USE biowell_insurance;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    avatar VARCHAR(255),
    role ENUM('admin', 'agent') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Agents table
CREATE TABLE IF NOT EXISTS agents (
    user_id INT PRIMARY KEY,
    acct_num VARCHAR(100),
    license_num VARCHAR(100),
    commission_rate DECIMAL(5,2),
    approved TINYINT(1) DEFAULT 0,
    assigned_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Agent Applications table
CREATE TABLE IF NOT EXISTS agent_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insurance Products table
CREATE TABLE IF NOT EXISTS insurance_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    issuing_company VARCHAR(255),
    description TEXT,
    premium DECIMAL(10,2),
    coverage TEXT,
    eligibility TEXT
);

-- Add default products: VIP and Regular

-- Add default products

-- Seed products: VIP and Regular

-- Insurance Applications table
-- Ensure insurance_application table has client_name, client_address, client_email columns
-- Add default products: VIP and Regular
-- Insert default products (idempotent)
INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility)
SELECT 'VIP', 'BioWell', 'VIP insurance product - PHP 2,500.00/month', 2500.00, 'Full coverage', 'All clients'
WHERE NOT EXISTS (SELECT 1 FROM insurance_products WHERE name = 'VIP' AND issuing_company = 'BioWell');

INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility)
SELECT 'Regular', 'BioWell', 'Regular insurance product - PHP 600.00/month', 600.00, 'Standard coverage', 'All clients'
WHERE NOT EXISTS (SELECT 1 FROM insurance_products WHERE name = 'Regular' AND issuing_company = 'BioWell');

CREATE TABLE IF NOT EXISTS insurance_application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    agent_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_address VARCHAR(255) NOT NULL,
    client_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    application_code VARCHAR(255) NULL,
    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES insurance_products(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(user_id) ON DELETE CASCADE
);

-- Ensure unique index for application_code (if column exists)
-- Ensure unique index for application_code (idempotent)
-- Use information_schema to check whether the index already exists, then create it if missing.
SET @idx_count := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS s
    WHERE s.TABLE_SCHEMA = DATABASE()
        AND s.TABLE_NAME = 'insurance_application'
        AND s.INDEX_NAME = 'ux_application_code'
);
SET @create_idx_sql := IF(@idx_count = 0, 'ALTER TABLE insurance_application ADD UNIQUE KEY ux_application_code (application_code(255))', 'SELECT "ux_application_code_exists"');
PREPARE create_idx_stmt FROM @create_idx_sql;
EXECUTE create_idx_stmt;
DEALLOCATE PREPARE create_idx_stmt;

-- Tickets table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    message TEXT,
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    type VARCHAR(50),
    created_by_user_id INT NOT NULL,
    client_id INT,
    agent_id INT,
    admin_id INT,
    insurance_application_id INT,
    company VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(user_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(user_id) ON DELETE SET NULL,
    FOREIGN KEY (insurance_application_id) REFERENCES insurance_application(id) ON DELETE SET NULL
);

-- Ticket Responses table
CREATE TABLE ticket_response (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    responder_user_id INT NOT NULL,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (responder_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
        category ENUM('news', 'promo', 'update') NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ensure 'event' exists in posts.category enum. If not, alter the column to add it.
SET @col_type := (
    SELECT COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'category'
);
-- If column exists and 'event' not present, alter the enum
SET @needs_event := IF(@col_type IS NOT NULL AND LOCATE("'event'", @col_type) = 0, 1, 0);
SET @alter_sql := IF(@needs_event = 1, "ALTER TABLE posts MODIFY category ENUM('news','promo','update','event') NOT NULL", 'SELECT "enum_ok"');
PREPARE alter_stmt FROM @alter_sql;
EXECUTE alter_stmt;
DEALLOCATE PREPARE alter_stmt;

-- Post Comments table
CREATE TABLE post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ensure application_code column exists in insurance_application and create unique index if missing
-- This block is idempotent and safe to run multiple times.
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insurance_application'
      AND COLUMN_NAME = 'application_code'
);

SET @alter_col_sql := IF(@col_exists = 0,
    'ALTER TABLE insurance_application ADD COLUMN application_code VARCHAR(255) NULL',
    'SELECT "application_code_already_exists"'
);

PREPARE stmt_col FROM @alter_col_sql;
EXECUTE stmt_col;
DEALLOCATE PREPARE stmt_col;

-- Ensure unique index on application_code (prefix 191 for utf8mb4 safety)
SET @idx_exists := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insurance_application'
      AND INDEX_NAME = 'ux_application_code'
);

SET @create_idx_sql := IF(@idx_exists = 0,
    'ALTER TABLE insurance_application ADD UNIQUE KEY ux_application_code (application_code(191))',
    'SELECT "ux_application_code_already_exists"'
);

PREPARE stmt_idx FROM @create_idx_sql;
EXECUTE stmt_idx;
-- BioWell Insurance Company Database Schema
-- Save this file as biowell_insurance.sql and import using phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS biowell_insurance;
USE biowell_insurance;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    avatar VARCHAR(255),
    role ENUM('admin', 'agent') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Agents table
CREATE TABLE IF NOT EXISTS agents (
    user_id INT PRIMARY KEY,
    acct_num VARCHAR(100),
    license_num VARCHAR(100),
    commission_rate DECIMAL(5,2),
    approved TINYINT(1) DEFAULT 0,
    assigned_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Agent Applications table
CREATE TABLE IF NOT EXISTS agent_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insurance Products table
CREATE TABLE IF NOT EXISTS insurance_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    issuing_company VARCHAR(255),
    description TEXT,
    premium DECIMAL(10,2),
    coverage TEXT,
    eligibility TEXT
);

-- Add default products: VIP and Regular

-- Add default products

-- Seed products: VIP and Regular

-- Insurance Applications table
-- Ensure insurance_application table has client_name, client_address, client_email columns
-- Add default products: VIP and Regular
-- Insert default products (idempotent)
INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility)
SELECT 'VIP', 'BioWell', 'VIP insurance product - PHP 2,500.00/month', 2500.00, 'Full coverage', 'All clients'
WHERE NOT EXISTS (SELECT 1 FROM insurance_products WHERE name = 'VIP' AND issuing_company = 'BioWell');

INSERT INTO insurance_products (name, issuing_company, description, premium, coverage, eligibility)
SELECT 'Regular', 'BioWell', 'Regular insurance product - PHP 600.00/month', 600.00, 'Standard coverage', 'All clients'
WHERE NOT EXISTS (SELECT 1 FROM insurance_products WHERE name = 'Regular' AND issuing_company = 'BioWell');

CREATE TABLE IF NOT EXISTS insurance_application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    agent_id INT NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_address VARCHAR(255) NOT NULL,
    client_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    application_code VARCHAR(255) NULL,
    application_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES insurance_products(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(user_id) ON DELETE CASCADE
);

-- Ensure unique index for application_code (if column exists)
-- Ensure unique index for application_code (idempotent)
-- Use information_schema to check whether the index already exists, then create it if missing.
SET @idx_count := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS s
    WHERE s.TABLE_SCHEMA = DATABASE()
        AND s.TABLE_NAME = 'insurance_application'
        AND s.INDEX_NAME = 'ux_application_code'
);
SET @create_idx_sql := IF(@idx_count = 0, 'ALTER TABLE insurance_application ADD UNIQUE KEY ux_application_code (application_code(255))', 'SELECT "ux_application_code_exists"');
PREPARE create_idx_stmt FROM @create_idx_sql;
EXECUTE create_idx_stmt;
DEALLOCATE PREPARE create_idx_stmt;

-- Tickets table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    message TEXT,
    status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    type VARCHAR(50),
    created_by_user_id INT NOT NULL,
    client_id INT,
    agent_id INT,
    admin_id INT,
    insurance_application_id INT,
    company VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(user_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(user_id) ON DELETE SET NULL,
    FOREIGN KEY (insurance_application_id) REFERENCES insurance_application(id) ON DELETE SET NULL
);

-- Ticket Responses table
CREATE TABLE ticket_response (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    responder_user_id INT NOT NULL,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (responder_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
        category ENUM('news', 'promo', 'update') NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ensure 'event' exists in posts.category enum. If not, alter the column to add it.
SET @col_type := (
    SELECT COLUMN_TYPE FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'category'
);
-- If column exists and 'event' not present, alter the enum
SET @needs_event := IF(@col_type IS NOT NULL AND LOCATE("'event'", @col_type) = 0, 1, 0);
SET @alter_sql := IF(@needs_event = 1, "ALTER TABLE posts MODIFY category ENUM('news','promo','update','event') NOT NULL", 'SELECT "enum_ok"');
PREPARE alter_stmt FROM @alter_sql;
EXECUTE alter_stmt;
DEALLOCATE PREPARE alter_stmt;

-- Post Comments table
CREATE TABLE post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Ensure application_code column exists in insurance_application and create unique index if missing
-- This block is idempotent and safe to run multiple times.
SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insurance_application'
      AND COLUMN_NAME = 'application_code'
);

SET @alter_col_sql := IF(@col_exists = 0,
    'ALTER TABLE insurance_application ADD COLUMN application_code VARCHAR(255) NULL',
    'SELECT "application_code_already_exists"'
);

PREPARE stmt_col FROM @alter_col_sql;
EXECUTE stmt_col;
DEALLOCATE PREPARE stmt_col;

-- Ensure unique index on application_code (prefix 191 for utf8mb4 safety)
SET @idx_exists := (
    SELECT COUNT(1)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'insurance_application'
      AND INDEX_NAME = 'ux_application_code'
);

SET @create_idx_sql := IF(@idx_exists = 0,
    'ALTER TABLE insurance_application ADD UNIQUE KEY ux_application_code (application_code(191))',
    'SELECT "ux_application_code_already_exists"'
);

PREPARE stmt_idx FROM @create_idx_sql;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

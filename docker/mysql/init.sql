-- Initialize database for Siroko Cart
-- This file is executed when MySQL container starts for the first time
-- User and database are created automatically by MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE env vars

-- Set default charset and collation for main database
ALTER DATABASE siroko_cart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create test database for unit tests
CREATE DATABASE IF NOT EXISTS siroko_cart_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
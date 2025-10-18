-- Initialize database for Siroko Cart
-- This file is executed when MySQL container starts for the first time

-- Create database if not exists (already created by environment variable)
USE siroko_cart;

-- Set default charset and collation
ALTER DATABASE siroko_cart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
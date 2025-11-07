-- Create vidhyaguru_db database and copy data from camuscore_db
-- Run this script in phpMyAdmin SQL tab

-- Step 1: Create the new database
CREATE DATABASE IF NOT EXISTS `vidhyaguru_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 2: Use the new database
USE `vidhyaguru_db`;

-- Step 3: Copy table structure and data from camuscore_db

-- Copy users table
CREATE TABLE IF NOT EXISTS `users` LIKE `camuscore_db`.`users`;
INSERT INTO `users` SELECT * FROM `camuscore_db`.`users`;

-- Copy applications table (if exists)
CREATE TABLE IF NOT EXISTS `applications` LIKE `camuscore_db`.`applications`;
INSERT INTO `applications` SELECT * FROM `camuscore_db`.`applications`;

-- Copy admin_users table (if exists)
CREATE TABLE IF NOT EXISTS `admin_users` LIKE `camuscore_db`.`admin_users`;
INSERT INTO `admin_users` SELECT * FROM `camuscore_db`.`admin_users`;

-- Add any additional tables as needed
-- Uncomment and modify the following lines for other tables:

-- CREATE TABLE IF NOT EXISTS `table_name` LIKE `camuscore_db`.`table_name`;
-- INSERT INTO `table_name` SELECT * FROM `camuscore_db`.`table_name`;

-- Verification queries (optional - run these to check if data was copied)
-- SELECT COUNT(*) as user_count FROM users;
-- SELECT COUNT(*) as application_count FROM applications;
-- SELECT COUNT(*) as admin_count FROM admin_users;

-- Show all tables in the new database
SHOW TABLES;

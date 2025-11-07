-- Complete script to create vidhyaguru_db and copy from camuscore_db
-- Copy this entire script and run it in phpMyAdmin SQL tab

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS `vidhyaguru_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 2: Use the new database
USE `vidhyaguru_db`;

-- Step 3: Copy all tables from camuscore_db
-- This will copy the structure and data of all tables

-- Users table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` LIKE `camuscore_db`.`users`;
INSERT INTO `users` SELECT * FROM `camuscore_db`.`users`;

-- Applications table
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` LIKE `camuscore_db`.`applications`;
INSERT INTO `applications` SELECT * FROM `camuscore_db`.`applications`;

-- Admin users table
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` LIKE `camuscore_db`.`admin_users`;
INSERT INTO `admin_users` SELECT * FROM `camuscore_db`.`admin_users`;

-- Copy any other tables that might exist
-- Add more tables as needed following the same pattern

-- Verification queries
SELECT 'users' as table_name, COUNT(*) as row_count FROM users
UNION ALL
SELECT 'applications' as table_name, COUNT(*) as row_count FROM applications
UNION ALL
SELECT 'admin_users' as table_name, COUNT(*) as row_count FROM admin_users;

-- Show all tables in the new database
SHOW TABLES;

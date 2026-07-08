-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `nikahnama_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nikahnama_db`;

-- Drop tables if they exist to start fresh
DROP TABLE IF EXISTS `nikahnama`;
DROP TABLE IF EXISTS `users`;

-- Create users table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) NOT NULL,
  `role` ENUM('admin', 'registrar') DEFAULT 'registrar',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create nikahnama table
CREATE TABLE `nikahnama` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `certificate_no` VARCHAR(50) UNIQUE NOT NULL,
  `registration_no` VARCHAR(50) UNIQUE NOT NULL,
  `marriage_date` DATE NOT NULL,
  `marriage_time` TIME NOT NULL,
  `marriage_place` TEXT NOT NULL,
  `mahr_amount` DECIMAL(15,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'BDT',
  `mahr_status` ENUM('paid', 'due', 'partially_paid') NOT NULL,
  
  -- Bride Info
  `bride_name` VARCHAR(150) NOT NULL,
  `bride_father` VARCHAR(150) NOT NULL,
  `bride_mother` VARCHAR(150) NOT NULL,
  `bride_birth` DATE NOT NULL,
  `bride_nid` VARCHAR(50) DEFAULT NULL,
  `bride_passport` VARCHAR(50) DEFAULT NULL,
  `bride_phone` VARCHAR(30) NOT NULL,
  `bride_address` TEXT NOT NULL,
  
  -- Groom Info
  `groom_name` VARCHAR(150) NOT NULL,
  `groom_father` VARCHAR(150) NOT NULL,
  `groom_mother` VARCHAR(150) NOT NULL,
  `groom_birth` DATE NOT NULL,
  `groom_nid` VARCHAR(50) DEFAULT NULL,
  `groom_passport` VARCHAR(50) DEFAULT NULL,
  `groom_phone` VARCHAR(30) NOT NULL,
  `groom_address` TEXT NOT NULL,
  
  -- Wali Info (optional, but typically required or N/A)
  `wali_name` VARCHAR(150) DEFAULT NULL,
  
  -- Registrar Info
  `registrar_name` VARCHAR(150) NOT NULL,
  `registrar_license` VARCHAR(100) NOT NULL,
  `registrar_phone` VARCHAR(30) NOT NULL,
  `registrar_address` TEXT NOT NULL,
  
  -- Witness Info
  `witness1_name` VARCHAR(150) NOT NULL,
  `witness1_nid` VARCHAR(50) NOT NULL,
  `witness2_name` VARCHAR(150) NOT NULL,
  `witness2_nid` VARCHAR(50) NOT NULL,
  
  -- General Info
  `notes` TEXT DEFAULT NULL,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default admin user (username: admin, password: admin123)
INSERT INTO `users` (`username`, `password`, `fullname`, `role`) VALUES
('admin', '$2y$10$IriuGDloH/z1dmimyy0xV.sBsM/7I7JMI1d2ZlOI.7/1fsjo11aJW', 'Administrator', 'admin');

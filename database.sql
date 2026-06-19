-- ============================================
-- Student Information Management System
-- Database Setup Script
-- Course: CP 222 - Open Source Technologies
-- ============================================

CREATE DATABASE IF NOT EXISTS student_mgmt_db;
USE student_mgmt_db;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    school_level ENUM('Primary', 'Secondary') NOT NULL,
    class_name VARCHAR(20) NOT NULL,
    school_name VARCHAR(100) NOT NULL,
    region VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    parent_email VARCHAR(100),
    address TEXT,
    enrollment_date DATE NOT NULL,
    status ENUM('Active', 'Inactive', 'Graduated', 'Transferred') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users Table (User Management Module - mandatory)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Teacher', 'Viewer') DEFAULT 'Viewer',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Audit Log Table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: Admin@1234)
-- Run setup.php after importing this SQL to set the correct password hash
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@school.ac.tz', 'PLACEHOLDER', 'System Administrator', 'Admin')
ON DUPLICATE KEY UPDATE username = username;

-- Sample student data
INSERT INTO students (registration_number, first_name, last_name, date_of_birth, gender, school_level, class_name, school_name, region, district, parent_name, parent_phone, enrollment_date) VALUES
('PS-2024-001', 'Amina', 'Hassan', '2015-03-12', 'Female', 'Primary', 'Standard 4', 'Mwangaza Primary School', 'Dar es Salaam', 'Kinondoni', 'Hassan Juma', '0712345678', '2020-01-15'),
('PS-2024-002', 'Juma', 'Mwangi', '2014-07-22', 'Male', 'Primary', 'Standard 5', 'Mwangaza Primary School', 'Dar es Salaam', 'Kinondoni', 'Mwangi Otieno', '0723456789', '2019-01-10'),
('SS-2024-001', 'Fatuma', 'Ally', '2009-11-05', 'Female', 'Secondary', 'Form 3', 'Kibaha Secondary School', 'Pwani', 'Kibaha', 'Ally Hamis', '0734567890', '2022-01-20'),
('SS-2024-002', 'Mohamed', 'Salum', '2008-04-18', 'Male', 'Secondary', 'Form 4', 'Kibaha Secondary School', 'Pwani', 'Kibaha', 'Salum Omar', '0745678901', '2021-01-15'),
('PS-2024-003', 'Grace', 'Kimaro', '2016-09-30', 'Female', 'Primary', 'Standard 3', 'Umoja Primary School', 'Kilimanjaro', 'Moshi Urban', 'Kimaro Peter', '0756789012', '2021-01-12')
ON DUPLICATE KEY UPDATE registration_number = registration_number;

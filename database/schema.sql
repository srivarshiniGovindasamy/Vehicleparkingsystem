<<<<<<< HEAD
CREATE DATABASE IF NOT EXISTS vehicle_parking_system;
USE vehicle_parking_system;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    token VARCHAR(80) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS vehicle_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parking_slots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slot_number VARCHAR(50) NOT NULL UNIQUE,
    lane_name VARCHAR(80) NULL,
    status ENUM('AVAILABLE', 'OCCUPIED', 'MAINTENANCE') NOT NULL DEFAULT 'AVAILABLE',
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parking_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    receipt_number VARCHAR(80) NOT NULL UNIQUE,
    vehicle_category_id INT UNSIGNED NOT NULL,
    parking_slot_id INT UNSIGNED NULL,
    vehicle_type VARCHAR(80) NOT NULL,
    vehicle_company VARCHAR(120) NULL,
    registration_number VARCHAR(40) NOT NULL,
    owner_name VARCHAR(120) NOT NULL,
    owner_contact VARCHAR(20) NOT NULL,
    status ENUM('IN', 'EXITED') NOT NULL DEFAULT 'IN',
    notes TEXT NULL,
    entry_time DATETIME NOT NULL,
    exit_time DATETIME NULL,
    parked_minutes INT UNSIGNED NULL,
    parked_hours DECIMAL(10,2) NULL,
    parking_charge DECIMAL(10,2) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_parking_records_category FOREIGN KEY (vehicle_category_id) REFERENCES vehicle_categories(id),
    CONSTRAINT fk_parking_records_slot FOREIGN KEY (parking_slot_id) REFERENCES parking_slots(id) ON DELETE SET NULL
);

INSERT INTO admins (name, username, email, phone, password)
SELECT 'System Admin', 'admin', 'admin@vpms.local', '9876543210', '$2b$12$hS9Vd5he.GE5TISiXgk8NO2uA9Iirk.n5lMtXGwRwPrp40fOwakOm'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');

INSERT INTO vehicle_categories (name, description, hourly_rate)
SELECT 'Two Wheeler', 'Motorcycles and scooters', 10.00
WHERE NOT EXISTS (SELECT 1 FROM vehicle_categories WHERE name = 'Two Wheeler');

INSERT INTO vehicle_categories (name, description, hourly_rate)
SELECT 'Four Wheeler', 'Cars, SUVs, and vans', 20.00
WHERE NOT EXISTS (SELECT 1 FROM vehicle_categories WHERE name = 'Four Wheeler');

INSERT INTO parking_slots (slot_number, lane_name, status, remarks)
SELECT 'A1', 'Lane A', 'AVAILABLE', 'Near gate'
WHERE NOT EXISTS (SELECT 1 FROM parking_slots WHERE slot_number = 'A1');

INSERT INTO parking_slots (slot_number, lane_name, status, remarks)
SELECT 'A2', 'Lane A', 'AVAILABLE', 'Near gate'
WHERE NOT EXISTS (SELECT 1 FROM parking_slots WHERE slot_number = 'A2');
=======
CREATE TABLE IF NOT EXISTS vehicle (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_no TEXT NOT NULL,
    owner_name TEXT,
    contact_no TEXT,
    vehicle_type TEXT,
    parking_slot TEXT,
    entry_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    exit_time DATETIME,
    parking_fee INTEGER DEFAULT 0
);
>>>>>>> origin/database

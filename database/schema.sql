CREATE TABLE IF NOT EXISTS admin (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS vehicle (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vehicle_no TEXT NOT NULL,
    owner_name TEXT,
    contact_no TEXT,
    vehicle_type TEXT,
    entry_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    exit_time DATETIME,
    parking_fee INTEGER DEFAULT 0
);
import sqlite3

# 🔹 Database Connection
def get_db():
    conn = sqlite3.connect("database.db")
    conn.row_factory = sqlite3.Row
    return conn


# 🔹 Add Vehicle (INSERT)
def add_vehicle(vehicle_no, owner, contact, vtype, slot):
    conn = get_db()
    conn.execute("""
        INSERT INTO vehicle (vehicle_no, owner_name, contact_no, vehicle_type, parking_slot)
        VALUES (?, ?, ?, ?, ?)
    """, (vehicle_no, owner, contact, vtype, slot))
    conn.commit()
    conn.close()


# 🔹 Get All Vehicles (SELECT)
def get_vehicles():
    conn = get_db()
    data = conn.execute("SELECT * FROM vehicle").fetchall()
    conn.close()
    return data


# 🔹 Exit Vehicle (UPDATE)
def exit_vehicle(vehicle_id, fee):
    conn = get_db()
    conn.execute("""
        UPDATE vehicle
        SET exit_time = CURRENT_TIMESTAMP,
            parking_fee = ?
        WHERE id = ?
    """, (fee, vehicle_id))
    conn.commit()
    conn.close()


# 🔹 Total Revenue (OPTIONAL)
def get_total_revenue():
    conn = get_db()
    result = conn.execute("SELECT SUM(parking_fee) FROM vehicle").fetchone()
    conn.close()
    return result[0]
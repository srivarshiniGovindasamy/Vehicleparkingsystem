import sqlite3

conn = sqlite3.connect("database.db")

with open("database/schema.sql") as f:
    conn.executescript(f.read())

conn.close()

print("Database created successfully!")
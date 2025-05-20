import pymysql
import openpyxl
from openpyxl import Workbook

conn = pymysql.connect(
    host='localhost',
    user='root',
    password='',
    database='myDB'
)

cursor = conn.cursor()
cursor.execute("SELECT * FROM formation")
rows = cursor.fetchall()
columns = [desc[0] for desc in cursor.description]

wb = Workbook()
ws = wb.active
ws.append(columns)
for row in rows:
    ws.append(row)

wb.save("formation_export.xlsx")
conn.close()

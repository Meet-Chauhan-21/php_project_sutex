@echo off
echo Creating vidhyaguru_db database and copying data from camuscore_db
echo.

REM Navigate to MySQL bin directory
cd /d "C:\xampp\mysql\bin"

REM Create the database
echo Creating vidhyaguru_db database...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS vidhyaguru_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Dump camuscore_db and import to vidhyaguru_db
echo Copying data from camuscore_db to vidhyaguru_db...
mysqldump -u root -p camuscore_db > temp_dump.sql
mysql -u root -p vidhyaguru_db < temp_dump.sql

REM Clean up
del temp_dump.sql

echo.
echo Database copy completed!
echo.
pause

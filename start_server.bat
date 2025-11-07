@echo off
echo ==================================================
echo          VidyaGuru College Website
echo ==================================================
echo.
echo The website is served through XAMPP Apache server
echo Make sure XAMPP Apache and MySQL are running
echo.
echo Access the website at:
echo - Main Site: http://localhost:8080/phpwebsite/
echo - Admin Panel: http://localhost:8080/phpwebsite/admin.html
echo - Test Auth: http://localhost:8080/phpwebsite/test_auth.html
echo - Test Endpoints: http://localhost:8080/phpwebsite/test_port_8080.html
echo.
echo IMPORTANT: Apache is running on PORT 8080 (not 80)
echo (IIS is using port 80)
echo.
echo Admin Credentials:
echo - Username: mohit@gmail.com
echo - Password: Mohit123
echo.
echo Press any key to open the website...
pause
start http://localhost:8080/phpwebsite/

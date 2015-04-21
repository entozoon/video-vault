@echo off

echo.
echo Starting MySQL
net start wampmysqld64

echo.
echo Apache and MySQL should be running
E:\wamp\bin\apache\apache2.4.9\bin\httpd.exe

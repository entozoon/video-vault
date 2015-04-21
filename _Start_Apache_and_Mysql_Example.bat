@echo off

echo.
echo Stopping MySQL
net stop wampmysqld64

echo.
echo Starting MySQL
net start wampmysqld64

echo.
echo Stopping Apache
E:\wamp\bin\apache\apache2.4.9\bin\httpd.exe -n wampapache64 -k stop

echo.
echo Apache and MySQL should be running
echo (this might need to run as administrator)
E:\wamp\bin\apache\apache2.4.9\bin\httpd.exe
pause
::E:\wamp\bin\apache\apache2.4.9\bin\httpd.exe -n wampapache64 -k start

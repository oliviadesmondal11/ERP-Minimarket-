@echo off
setlocal
cd /d "%~dp0"

echo ===================================================================
echo     ERP MINIMARKET PT RANTAU MART RIAU - LOCALHOST SERVER
echo ===================================================================
echo.

REM Deteksi lokasi PHP
set PHP_BIN=php
if exist "C:\xampp\php\php.exe" (
    set PHP_BIN=C:\xampp\php\php.exe
)

echo [1/2] Menyiapkan server PHP (%PHP_BIN%)...
echo [2/2] Server siap diakses!
echo.
echo -------------------------------------------------------------------
echo  Buka browser Anda dan akses tautan berikut:
echo  👉 http://localhost:8000
echo -------------------------------------------------------------------
echo.
echo Catatan: Tekan tombol Ctrl + C di jendela ini untuk mematikan server.
echo.

"%PHP_BIN%" -d extension=pdo_pgsql -d extension=pgsql -S localhost:8000 -t public

pause

@echo off
title Iniciar Sistema Dizany

cd /d C:\xampp\htdocs\dizany

:: Elimina symlink si ya existe (y está roto o incorrecto)
IF EXIST public\storage (
    rmdir public\storage
)

:: Crea el symlink
php artisan storage:link

:: Iniciar servidor de desarrollo
start cmd /k "php artisan serve"

:: Abrir el navegador después de 2 segundos
timeout /t 2 >nul
start http://localhost:8000/login

pause

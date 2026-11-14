@echo off
title Robert Hotel Lab 7
cd /d "%~dp0"
echo Starting Robert Hotel Lab 7...
echo Open http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause

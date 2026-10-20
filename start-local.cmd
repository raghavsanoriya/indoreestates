@echo off
if exist "%~dp0php.ini" set "PHPRC=%~dp0php.ini"
cd /d "%~dp0public"
php -S 127.0.0.1:8003 "%~dp0vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"

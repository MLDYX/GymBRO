@echo off
setlocal

cd /d "%~dp0"

echo ==========================================
echo           GymBRO - Start lokalny
echo ==========================================
echo.

where php >nul 2>nul
if errorlevel 1 (
    echo [BLAD] PHP nie jest dostepne w PATH.
    echo Zainstaluj PHP albo dodaj je do PATH.
    pause
    exit /b 1
)

php -m | findstr /i "mongodb" >nul
if errorlevel 1 (
    echo [BLAD] Brakuje rozszerzenia PHP: ext-mongodb
    echo.
    echo Dla Twojego PHP pobierz plik DLL:
    echo PHP 8.5 TS x64 ^(mongodb for Windows^)
    echo https://pecl.php.net/package/mongodb/2.2.1/windows
    echo.
    echo Potem:
    echo 1. skopiuj php_mongodb.dll do folderu ext w PHP
    echo 2. dodaj w php.ini linie: extension=mongodb
    echo 3. otworz terminal ponownie
    echo 4. uruchom ten plik jeszcze raz
    echo.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    if exist "composer.phar" (
        echo [INFO] Brakuje vendor/autoload.php
        echo [INFO] Uruchamiam instalacje zaleznosci...
        php composer.phar install
        if errorlevel 1 (
            echo.
            echo [BLAD] Nie udalo sie zainstalowac zaleznosci.
            pause
            exit /b 1
        )
    ) else (
        echo [UWAGA] Nie znaleziono vendor/autoload.php ani composer.phar
        echo Najpierw uruchom:
        echo php composer.phar install
        pause
        exit /b 1
    )
)

call :ensure_mongo_server

echo.
echo [INFO] Upewnij sie, ze PostgreSQL i MongoDB sa uruchomione
echo [INFO] oraz ze dane zostaly juz zaladowane do baz.
echo.
echo [INFO] Start serwera: http://localhost:8000
start "" http://localhost:8000
php -S localhost:8000 -t public

endlocal
goto :eof

:ensure_mongo_server
set "MONGOD_EXE="
for %%M in ("mongod.exe") do set "MONGOD_EXE=%%~$PATH:M"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\8.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\8.0\bin\mongod.exe"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\7.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\7.0\bin\mongod.exe"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\6.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\6.0\bin\mongod.exe"

sc query MongoDB >nul 2>nul
if not errorlevel 1 (
    for /f "tokens=3" %%S in ('sc query MongoDB ^| findstr STATE') do set "MONGO_STATE=%%S"
    if /i not "!MONGO_STATE!"=="RUNNING" (
        echo [INFO] Uruchamiam usluge MongoDB...
        net start MongoDB >nul 2>nul
    )
    exit /b 0
)

if defined MONGOD_EXE (
    echo [INFO] Wykryto mongod.exe, ale brak skonfigurowanej uslugi MongoDB.
    echo [INFO] W razie potrzeby uruchom MongoDB recznie.
    exit /b 0
)

echo [UWAGA] Nie znaleziono serwera MongoDB ^(mongod/uslugi MongoDB^).
exit /b 0

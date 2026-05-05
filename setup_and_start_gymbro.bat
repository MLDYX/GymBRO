@echo off
setlocal EnableDelayedExpansion

cd /d "%~dp0"

echo ==========================================
echo      GymBRO - Pelny setup i start
echo ==========================================
echo.

call :find_php
if errorlevel 1 goto :fail

call :check_mongodb_extension
if errorlevel 1 goto :fail

call :install_dependencies
if errorlevel 1 goto :fail

call :find_psql
if errorlevel 1 goto :fail

call :setup_postgres
if errorlevel 1 goto :fail

call :ensure_mongo_server

call :find_mongosh
if errorlevel 1 (
    echo [UWAGA] Nie znaleziono mongosh. Pomijam seed MongoDB.
    echo [UWAGA] Jesli chcesz miec dane testowe MongoDB, zainstaluj MongoDB Shell.
    echo.
) else (
    call :setup_mongo
    if errorlevel 1 goto :fail
)

echo [INFO] Start aplikacji pod adresem http://localhost:8000
start "" http://localhost:8000
php -S localhost:8000 -t public
goto :eof

:find_php
where php >nul 2>nul
if errorlevel 1 (
    echo [BLAD] PHP nie jest dostepne w PATH.
    exit /b 1
)
exit /b 0

:check_mongodb_extension
php -m | findstr /i "mongodb" >nul
if errorlevel 1 (
    echo [BLAD] Brakuje rozszerzenia PHP: ext-mongodb
    echo [INFO] Zainstaluj php_mongodb.dll i dodaj extension=mongodb w php.ini
    exit /b 1
)
exit /b 0

:install_dependencies
if exist "vendor\autoload.php" (
    echo [INFO] Zaleznosci PHP sa juz zainstalowane.
    echo.
    exit /b 0
)

if exist "composer.phar" (
    echo [INFO] Instalacja zaleznosci przez composer.phar...
    php composer.phar install
    if errorlevel 1 (
        echo [BLAD] Composer nie zainstalowal zaleznosci.
        exit /b 1
    )
    echo.
    exit /b 0
)

where composer >nul 2>nul
if errorlevel 1 (
    echo [BLAD] Nie znaleziono composer.phar ani komendy composer.
    exit /b 1
)

echo [INFO] Instalacja zaleznosci przez composer...
composer install
if errorlevel 1 (
    echo [BLAD] Composer nie zainstalowal zaleznosci.
    exit /b 1
)
echo.
exit /b 0

:ensure_mongo_server
set "MONGOD_EXE="
for %%M in ("mongod.exe") do set "MONGOD_EXE=%%~$PATH:M"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\8.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\8.0\bin\mongod.exe"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\7.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\7.0\bin\mongod.exe"
if not defined MONGOD_EXE if exist "C:\Program Files\MongoDB\Server\6.0\bin\mongod.exe" set "MONGOD_EXE=C:\Program Files\MongoDB\Server\6.0\bin\mongod.exe"

sc query MongoDB >nul 2>nul
if not errorlevel 1 (
    for /f "tokens=3" %%S in ('sc query MongoDB ^| findstr STATE') do set "MONGO_STATE=%%S"
    if /i "!MONGO_STATE!"=="RUNNING" (
        echo [INFO] Usluga MongoDB juz dziala.
        echo.
        exit /b 0
    )

    echo [INFO] Uruchamiam usluge MongoDB...
    net start MongoDB
    if errorlevel 1 (
        echo [UWAGA] Nie udalo sie uruchomic uslugi MongoDB.
        echo [UWAGA] Aplikacja uruchomi sie bez danych MongoDB.
        echo.
        exit /b 0
    )

    echo.
    exit /b 0
)

if defined MONGOD_EXE (
    echo [UWAGA] Wykryto mongod.exe, ale brak uslugi MongoDB.
    echo [UWAGA] Skonfiguruj usluge MongoDB albo uruchom serwer recznie.
    echo.
    exit /b 0
)

echo [UWAGA] Nie znaleziono instalacji serwera MongoDB.
echo [UWAGA] Aplikacja uruchomi sie bez danych MongoDB.
echo.
exit /b 0

:find_psql
set "PSQL_EXE="
for %%P in ("psql.exe") do set "PSQL_EXE=%%~$PATH:P"
if defined PSQL_EXE exit /b 0

if exist "C:\Program Files\PostgreSQL\18\bin\psql.exe" set "PSQL_EXE=C:\Program Files\PostgreSQL\18\bin\psql.exe"
if exist "C:\Program Files\PostgreSQL\17\bin\psql.exe" set "PSQL_EXE=C:\Program Files\PostgreSQL\17\bin\psql.exe"
if exist "C:\Program Files\PostgreSQL\16\bin\psql.exe" set "PSQL_EXE=C:\Program Files\PostgreSQL\16\bin\psql.exe"
if exist "C:\Program Files\PostgreSQL\15\bin\psql.exe" set "PSQL_EXE=C:\Program Files\PostgreSQL\15\bin\psql.exe"

if not defined PSQL_EXE (
    echo [BLAD] Nie znaleziono psql.exe.
    exit /b 1
)

exit /b 0

:setup_postgres
echo [INFO] Konfiguracja PostgreSQL
set "PGHOST=localhost"
set "PGPORT=5432"
set "PGUSER=postgres"
set "PGDATABASE=gymbro_db"

set /p PGHOST=Host PostgreSQL [localhost]: 
if "%PGHOST%"=="" set "PGHOST=localhost"

set /p PGPORT=Port PostgreSQL [5432]: 
if "%PGPORT%"=="" set "PGPORT=5432"

set /p PGUSER=Uzytkownik PostgreSQL [postgres]: 
if "%PGUSER%"=="" set "PGUSER=postgres"

set /p PGPASSWORD=Haslo PostgreSQL [postgres]: 
if "%PGPASSWORD%"=="" set "PGPASSWORD=postgres"

echo [INFO] Sprawdzam polaczenie z PostgreSQL...
"%PSQL_EXE%" -h "%PGHOST%" -p "%PGPORT%" -U "%PGUSER%" -d postgres -c "SELECT version();"
if errorlevel 1 (
    echo [BLAD] Nie udalo sie polaczyc z PostgreSQL.
    echo [INFO] Najczestsza przyczyna: zly login lub haslo.
    exit /b 1
)

echo [INFO] Tworze baze gymbro_db, jesli nie istnieje...
"%PSQL_EXE%" -h "%PGHOST%" -p "%PGPORT%" -U "%PGUSER%" -d postgres -tc "SELECT 1 FROM pg_database WHERE datname='gymbro_db';" | findstr /r /c:"1" >nul
if errorlevel 1 (
    "%PSQL_EXE%" -h "%PGHOST%" -p "%PGPORT%" -U "%PGUSER%" -d postgres -c "CREATE DATABASE gymbro_db;"
    if errorlevel 1 (
        echo [BLAD] Nie udalo sie utworzyc bazy gymbro_db.
        exit /b 1
    )
)

echo [INFO] Laduje schemat PostgreSQL...
"%PSQL_EXE%" -h "%PGHOST%" -p "%PGPORT%" -U "%PGUSER%" -d gymbro_db -f database\postgres_schema.sql
if errorlevel 1 (
    echo [BLAD] Nie udalo sie zaladowac schematu PostgreSQL.
    exit /b 1
)

echo [INFO] Laduje dane testowe PostgreSQL...
"%PSQL_EXE%" -h "%PGHOST%" -p "%PGPORT%" -U "%PGUSER%" -d gymbro_db -f database\postgres_seed.sql
if errorlevel 1 (
    echo [BLAD] Nie udalo sie zaladowac danych PostgreSQL.
    exit /b 1
)

echo.
exit /b 0

:find_mongosh
set "MONGOSH_EXE="
for %%M in ("mongosh.exe") do set "MONGOSH_EXE=%%~$PATH:M"
if defined MONGOSH_EXE exit /b 0

if exist "C:\Program Files\MongoDB\mongosh\bin\mongosh.exe" set "MONGOSH_EXE=C:\Program Files\MongoDB\mongosh\bin\mongosh.exe"
if exist "%LocalAppData%\Programs\mongosh\mongosh.exe" set "MONGOSH_EXE=%LocalAppData%\Programs\mongosh\mongosh.exe"

if not defined MONGOSH_EXE exit /b 1
exit /b 0

:setup_mongo
echo [INFO] Laduje dane testowe MongoDB...
"%MONGOSH_EXE%" database\mongo_seed.js
if errorlevel 1 (
    echo [BLAD] Nie udalo sie zaladowac danych MongoDB.
    exit /b 1
)
echo.
exit /b 0

:fail
echo.
echo [BLAD] Setup zostal przerwany.
pause
exit /b 1

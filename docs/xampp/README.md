# GymBRO na XAMPP

To jest zalecany lokalny setup dla GymBRO na Windows.
Nie uzywamy tutaj `php -S`, tylko Apache z XAMPP.

## Docelowy uklad

- projekt: `C:\dev\GymBRO`
- Apache i PHP: XAMPP
- PostgreSQL: osobna instalacja
- MongoDB: osobna instalacja

Adres aplikacji:

```text
http://localhost/gymbro
```

## Krok 1. Przenies projekt poza OneDrive

Najlepiej:

```text
C:\dev\GymBRO
```

## Krok 2. Dodaj konfiguracje Apache

Otworz:

```text
C:\xampp\apache\conf\httpd.conf
```

Na koncu pliku dodaj:

```apache
Include "conf/extra/apache-gymbro.conf"
```

## Krok 3. Skopiuj plik konfiguracyjny GymBRO

Skopiuj:

```text
docs/xampp/apache-gymbro.conf
```

do:

```text
C:\xampp\apache\conf\extra\apache-gymbro.conf
```

## Krok 4. Popraw dane w `apache-gymbro.conf`

Sprawdz:

- sciezke do projektu
- uzytkownika PostgreSQL
- haslo PostgreSQL

Najwazniejsze linie:

```apache
Alias /gymbro "C:/dev/GymBRO/public"
SetEnv PGHOST localhost
SetEnv PGPORT 5432
SetEnv PGDATABASE gymbro_db
SetEnv PGUSER postgres
SetEnv PGPASSWORD CHANGE_ME
SetEnv MONGODB_URI mongodb://localhost:27017
SetEnv MONGODB_DB gymbro_mongo
```

## Krok 5. Wlacz wymagane rozszerzenia PHP

Otworz:

```text
C:\xampp\php\php.ini
```

Upewnij sie, ze masz:

```ini
extension=openssl
extension=curl
extension=pgsql
extension=pdo_pgsql
extension=mongodb
```

Przydatne ustawienia:

```ini
upload_max_filesize = 20M
post_max_size = 24M
max_execution_time = 60
```

## Krok 6. Zrestartuj Apache

W XAMPP:

1. `Stop`
2. `Start`

## Krok 7. Otworz aplikacje

```text
http://localhost/gymbro
```

## Uwagi

- XAMPP sluzy tutaj tylko do Apache i PHP
- MySQL z XAMPP nie jest uzywany
- PostgreSQL i MongoDB dzialaja osobno jako lokalne uslugi

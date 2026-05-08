# GymBRO

GymBRO to aplikacja webowa napisana w czystym PHP na przedmiot "Bazy danych".
Projekt laczy PostgreSQL i MongoDB w jednej, realnie dzialajacej aplikacji dla
paczek znajomych chodzacych na silownie.

Uzytkownicy moga:

- zalozyc konto i przejsc onboarding
- uzupelnic profil i wyniki startowe
- dodawac znajomych
- tworzyc plany treningowe
- umawiac wspolne treningi
- zapisywac wykonane treningi
- sledzic progres
- komentowac plany

## Technologie

- PHP 8.x
- PostgreSQL
- MongoDB
- PDO
- oficjalna biblioteka `mongodb/mongodb`
- Bootstrap 5
- Chart.js
- HTML, CSS, JavaScript
- XAMPP jako lokalny Apache/PHP na Windows

## Dlaczego sa dwie bazy danych

### PostgreSQL

PostgreSQL przechowuje dane relacyjne, ktore wymagaja:

- kluczy obcych
- spojnosci
- relacji miedzy rekordami
- wygodnych JOIN-ow

W GymBRO sa to:

- `users`
- `user_profiles`
- `friendships`
- `gyms`
- `exercises`
- `workout_events`
- `workout_event_participants`
- `training_plans`
- `training_plan_days`
- `training_plan_exercises`

### MongoDB

MongoDB przechowuje dane dokumentowe i elastyczne, gdzie liczba pol
lub zagniezdzen moze byc rozna dla roznych wpisow.

W GymBRO sa to:

- `workout_logs`
- `progress_measurements`
- `plan_comments`
- `notifications`
- `activity_logs`

Najlepszy przyklad sensu MongoDB to `workout_logs`, bo jeden dokument moze
zawierac wiele cwiczen, a kazde cwiczenie wiele serii bez dokladania
dodatkowych tabel relacyjnych.

## Gdzie sa wszystkie operacje na bazach

Po centralizacji warstwy danych najwazniejsze pliki do pokazania prowadzacemu to:

- `database/postgres.php`
- `database/mongo.php`

To tam sa wszystkie glowne operacje na PostgreSQL i MongoDB.
Repozytoria w `app/repositories/` sa juz tylko cienka warstwa wywolujaca
funkcje z tych dwoch plikow.

## Materialy do obrony

Najwazniejsze pliki do prezentacji:

- `docs/obrona-bazy-danych.md`
- `docs/postgresql-erd.md`
- `docs/mongodb-struktura.md`
- `docs/pokaz-checklista.md`
- `docs/queries/postgresql_demo.sql`
- `docs/queries/mongodb_demo.js`

## Rozszerzone dane demo

Aktualne seedy tworza wiekszy, realistyczny zestaw danych:

- okolo 20 uzytkownikow
- okolo miesiaca aktywnosci
- znajomosci, wydarzenia, plany, komentarze, progres i logi treningowe

Po imporcie aplikacja wyglada jak system, z ktorego grupa znajomych
korzysta juz od kilku tygodni.

## Wymagania

Potrzebujesz:

- Windows
- XAMPP
- PostgreSQL
- pgAdmin
- MongoDB Community Server
- mongosh
- PHP z rozszerzeniami:
  - `openssl`
  - `curl`
  - `pgsql`
  - `pdo_pgsql`
  - `mongodb`

## Zalecana lokalizacja projektu

Projekt trzymaj poza OneDrive, najlepiej tutaj:

```text
C:\dev\GymBRO
```

## Najwazniejsze pliki konfiguracyjne

- Apache: `docs/xampp/apache-gymbro.conf`
- Instrukcja XAMPP: `docs/xampp/README.md`
- PostgreSQL schema: `database/postgres_schema.sql`
- PostgreSQL seed: `database/postgres_seed.sql`
- MongoDB seed: `database/mongo_seed.js`

## Co pobrac

### 1. XAMPP

Pobierz i zainstaluj XAMPP:

`https://www.apachefriends.org/`

### 2. PostgreSQL

Pobierz i zainstaluj PostgreSQL:

`https://www.postgresql.org/download/windows/`

### 3. MongoDB Community Server

Pobierz i zainstaluj MongoDB Community Server:

`https://www.mongodb.com/try/download/community`

### 4. MongoDB Shell

Pobierz `mongosh`:

`https://www.mongodb.com/try/download/shell`

## Jak przygotowac XAMPP

### 1. Skopiuj projekt

Przenies projekt do:

```text
C:\dev\GymBRO
```

### 2. Skonfiguruj Apache

Otworz:

```text
C:\xampp\apache\conf\httpd.conf
```

Na koncu dopisz:

```apache
Include "conf/extra/apache-gymbro.conf"
```

Nastepnie skopiuj plik:

```text
docs/xampp/apache-gymbro.conf
```

do:

```text
C:\xampp\apache\conf\extra\apache-gymbro.conf
```

### 3. Edytuj `apache-gymbro.conf`

Sprawdz lub popraw:

- sciezke do projektu
- login do PostgreSQL
- haslo do PostgreSQL

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

### 4. Wlacz rozszerzenia PHP w XAMPP

Otworz:

```text
C:\xampp\php\php.ini
```

Upewnij sie, ze sa wlaczone:

```ini
extension=openssl
extension=curl
extension=pgsql
extension=pdo_pgsql
extension=mongodb
```

Przydatne limity:

```ini
upload_max_filesize = 20M
post_max_size = 24M
max_execution_time = 60
```

Po zmianach zrestartuj Apache w XAMPP.

## Jak przygotowac bazy danych

### PostgreSQL - utworzenie bazy

Mozesz to zrobic w `pgAdmin`:

1. Otworz `pgAdmin`
2. Zaloguj sie do serwera
3. Kliknij prawym na `Databases`
4. Wybierz `Create -> Database`
5. Wpisz:

```text
gymbro_db
```

### PostgreSQL - schema i seed

Najprosciej przez `psql`:

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d gymbro_db -f C:\dev\GymBRO\database\postgres_schema.sql
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d gymbro_db -f C:\dev\GymBRO\database\postgres_seed.sql
```

Albo przez `pgAdmin -> Query Tool`:

1. Otworz `database/postgres_schema.sql`
2. Uruchom plik
3. Otworz `database/postgres_seed.sql`
4. Uruchom plik

### MongoDB - seed

Uruchom:

```powershell
mongosh
```

W srodku `mongosh` wpisz:

```javascript
load("C:/dev/GymBRO/database/mongo_seed.js")
```

Po poprawnym imporcie powinien pojawic sie komunikat:

```text
GymBRO MongoDB seed inserted into gymbro_mongo
```

## Jak uruchomic aplikacje

1. Wlacz `Apache` w XAMPP
2. Upewnij sie, ze dziala PostgreSQL
3. Upewnij sie, ze dziala MongoDB
4. Otworz:

```text
http://localhost/gymbro
```

## Przydatne komendy

### Start PostgreSQL

```powershell
net start postgresql-x64-18
```

### Start MongoDB

```powershell
net start MongoDB
```

### Sprawdzenie PostgreSQL

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d gymbro_db -c "SELECT 1;"
```

### Sprawdzenie MongoDB

```powershell
mongosh --eval "db.adminCommand({ ping: 1 })"
```

### Szybkie sprawdzenie liczby danych

```powershell
& "C:\Program Files\PostgreSQL\18\bin\psql.exe" -U postgres -d gymbro_db -c "SELECT COUNT(*) AS users_count FROM users;"
mongosh --eval "db.getSiblingDB('gymbro_mongo').workout_logs.countDocuments()"
```

## Konta testowe

Wszystkie konta maja haslo:

```text
password123
```

Polecane konta do prezentacji:

- `jan@gymbro.local`
- `piotr@gymbro.local`
- `natalia@gymbro.local`
- `damian@gymbro.local`
- `ewa@gymbro.local`

Pozostale konta:

- `anna@gymbro.local`
- `kasia@gymbro.local`
- `marek@gymbro.local`
- `olga@gymbro.local`
- `tomasz@gymbro.local`
- `julia@gymbro.local`
- `kamil@gymbro.local`
- `zuzanna@gymbro.local`
- `michal@gymbro.local`
- `karolina@gymbro.local`
- `pawel@gymbro.local`
- `alicja@gymbro.local`
- `sebastian@gymbro.local`
- `monika@gymbro.local`
- `bartosz@gymbro.local`

## Co pokazac prowadzacemu

Najkrotsza sensowna kolejnosc:

1. Logowanie na konto demo
2. Dashboard
3. Znajomi
4. Wydarzenia
5. Plany
6. Log treningowy
7. Progres
8. PostgreSQL schema i query
9. MongoDB seed i query
10. `database/postgres.php` i `database/mongo.php`

## Co jeszcze mozna rozbudowac

- edycje i usuwanie workout logow
- edycje i usuwanie pomiarow progresu
- oznaczanie powiadomien jako przeczytane z UI
- upload i kadrowanie avatara
- paginacje list
- filtrowanie wydarzen po miescie i silowni
- eksport planow i logow

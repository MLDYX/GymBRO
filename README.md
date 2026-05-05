# GymBRO

GymBRO to aplikacja webowa napisana w czystym PHP, przygotowana jako projekt na przedmiot „Bazy danych”. Aplikacja łączy klasyczny model relacyjny w PostgreSQL z dokumentowym modelem MongoDB i pozwala zarządzać planami treningowymi, wydarzeniami, dziennikiem treningów oraz progressem użytkownika.

## Technologie

- PHP 8.x
- PostgreSQL
- MongoDB
- PDO
- oficjalna biblioteka `mongodb/mongodb`
- Bootstrap 5 z CDN
- Chart.js z CDN
- HTML, CSS, trochę JavaScript

## Dlaczego dwie bazy danych

PostgreSQL został użyty do danych relacyjnych, które wymagają spójności, relacji i kluczy obcych, takich jak użytkownicy, profile, znajomości, siłownie, ćwiczenia, wydarzenia treningowe oraz plany treningowe.

MongoDB został użyty do danych dokumentowych i elastycznych, których struktura może być różna dla różnych użytkowników, takich jak dzienniki treningowe, serie ćwiczeń, pomiary progresu, komentarze, powiadomienia i historia aktywności. Dzięki temu jeden wpis treningowy może zawierać dowolną liczbę ćwiczeń i serii bez tworzenia wielu dodatkowych tabel relacyjnych.

## Najważniejsze funkcje

- rejestracja i logowanie użytkowników
- dashboard z danymi z PostgreSQL i MongoDB
- edycja profilu
- system znajomych
- CRUD siłowni
- CRUD ćwiczeń
- wydarzenia treningowe z uczestnikami
- plany treningowe z dniami i ćwiczeniami
- komentarze planów zapisane w MongoDB
- dziennik treningowy w MongoDB
- pomiary progresu i wykresy Chart.js

## Struktura projektu

```text
GymBRO/
├── public/
├── app/
├── database/
├── composer.json
└── README.md
```

## Wymagania

- PHP 8.x z rozszerzeniami `pdo_pgsql` i `mongodb`
- Composer
- PostgreSQL
- MongoDB

## Instalacja krok po kroku

## Najprostsze uruchomienie na Windows

Jeśli chcesz po prostu uruchomić projekt lokalnie bez zabawy w zaawansowaną konfigurację, użyj tej wersji:

### 1. Zainstaluj 4 rzeczy

- PHP 8.x
- Composer
- PostgreSQL
- MongoDB

### 2. W katalogu projektu zainstaluj bibliotekę PHP

Jeśli Composer działa u Ciebie jako plik `composer.phar`, użyj:

```powershell
php composer.phar install
```

Jeśli masz normalnie komendę `composer`, użyj:

```powershell
composer install
```

### 3. Utwórz bazę PostgreSQL

Najprościej przez **pgAdmin**:

- otwórz `pgAdmin`
- zaloguj się do lokalnego serwera PostgreSQL
- kliknij prawym na `Databases`
- wybierz `Create -> Database`
- wpisz nazwę: `gymbro_db`

### 4. Wgraj schemat i dane PostgreSQL

Najprościej także przez **pgAdmin**:

- wybierz bazę `gymbro_db`
- otwórz `Query Tool`
- wczytaj plik [database/postgres_schema.sql](/c:/Users/wikto/OneDrive/Pulpit/GymBRO/database/postgres_schema.sql)
- uruchom go
- potem wczytaj [database/postgres_seed.sql](/c:/Users/wikto/OneDrive/Pulpit/GymBRO/database/postgres_seed.sql)
- uruchom go

### 5. Wgraj dane do MongoDB

Najprościej przez `mongosh`:

```powershell
mongosh
```

Potem w konsoli:

```javascript
load("database/mongo_seed.js")
```

Jeśli `mongosh` nie działa, zainstaluj MongoDB Shell albo użyj MongoDB Compass.

### 6. Uruchom aplikację

W katalogu projektu:

```powershell
php -S localhost:8000 -t public
```

Potem otwórz w przeglądarce:

```text
http://localhost:8000
```

## Minimalny zestaw komend

Jeśli wszystko jest już zainstalowane, wystarczą Ci zwykle tylko te komendy:

```powershell
php composer.phar install
mongosh
php -S localhost:8000 -t public
```

Resztę dla PostgreSQL możesz zrobić w `pgAdmin`, bez wpisywania `psql`.

### 1. Sklonuj lub otwórz projekt

Przejdź do katalogu projektu:

```bash
cd GymBRO
```

### 2. Zainstaluj Composer

Jeżeli nie masz Composera:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Na Windows możesz też pobrać oficjalny instalator z:

`https://getcomposer.org/`

### 3. Zainstaluj zależności PHP

```bash
composer install
```

Projekt używa tylko jednej biblioteki zewnętrznej:

```bash
composer require mongodb/mongodb
```

### 4. Utwórz bazę PostgreSQL

Zaloguj się do PostgreSQL i utwórz bazę:

```bash
psql -U postgres -h localhost -p 5432
```

W konsoli `psql`:

```sql
CREATE DATABASE gymbro_db;
```

### 5. Uruchom schemat PostgreSQL

```bash
psql -U postgres -d gymbro_db -f database/postgres_schema.sql
```

### 6. Uruchom seedy PostgreSQL

```bash
psql -U postgres -d gymbro_db -f database/postgres_seed.sql
```

### 7. Utwórz i zasil MongoDB

Uruchom `mongosh`:

```bash
mongosh
```

Następnie:

```javascript
load("database/mongo_seed.js")
```

Skrypt utworzy dane w bazie `gymbro_mongo`.

### 8. Skonfiguruj połączenia

Domyślne ustawienia są zapisane w:

- `app/config/postgres.php`
- `app/config/mongodb.php`

Domyślne wartości:

- PostgreSQL: `host=localhost`, `port=5432`, `dbname=gymbro_db`, `user=postgres`, `password=postgres`
- MongoDB: `mongodb://localhost:27017`, baza `gymbro_mongo`

Możesz je nadpisać zmiennymi środowiskowymi:

- `PGHOST`
- `PGPORT`
- `PGDATABASE`
- `PGUSER`
- `PGPASSWORD`
- `MONGODB_URI`
- `MONGODB_DB`

### 9. Uruchom aplikację lokalnie

Najprościej przez wbudowany serwer PHP:

```bash
php -S localhost:8000 -t public
```

Potem otwórz:

`http://localhost:8000`

## Dane testowe

Przykładowe konto:

- e-mail: `jan@gymbro.local`
- hasło: `password123`

Pozostałe konta testowe:

- `anna@gymbro.local`
- `piotr@gymbro.local`
- `kasia@gymbro.local`
- `marek@gymbro.local`

Wszystkie mają hasło:

`password123`

## Komendy do uruchomienia

```bash
composer install
psql -U postgres -d gymbro_db -f database/postgres_schema.sql
psql -U postgres -d gymbro_db -f database/postgres_seed.sql
mongosh
load("database/mongo_seed.js")
php -S localhost:8000 -t public
```

## Prostsza wersja komend na Twoim Windows

Jeżeli `composer` nie działa jako komenda globalna, użyj:

```powershell
php composer.phar install
```

Jeżeli `psql` nie działa jako komenda globalna, wgraj pliki SQL przez `pgAdmin` zamiast przez terminal.

## Co można dalej rozbudować

- reset hasła i weryfikację e-mail
- paginację list użytkowników, planów i ćwiczeń
- filtrowanie wydarzeń po mieście i siłowni
- upload zdjęcia profilowego
- oznaczanie powiadomień jako przeczytane z poziomu UI
- edycję i usuwanie wpisów treningowych oraz pomiarów
- średnie ocen planów i ranking najpopularniejszych planów
- pełniejsze logowanie aktywności użytkownika

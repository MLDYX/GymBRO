# GymBRO na XAMPP

To jest zalecany lokalny setup dla GymBRO zamiast `php -S`.

## Zalecana lokalizacja projektu

Przenies projekt poza OneDrive, na przyklad:

```text
C:\dev\GymBRO
```

## Co zostaje osobno

XAMPP sluzy tutaj tylko do:

- Apache
- PHP

Projekt dalej korzysta z:

- PostgreSQL
- MongoDB

Nie uzywamy MySQL z XAMPP.

## Konfiguracja Apache

1. Skopiuj projekt do `C:\dev\GymBRO`.
2. Otworz konfiguracje Apache w XAMPP.
3. Dodaj zawartosc pliku:

```text
docs/xampp/apache-gymbro.conf
```

4. Jesli projekt nie lezy w `C:\dev\GymBRO`, popraw sciezke w aliasie i `<Directory>`.

Plik ustawia:

- `Alias /gymbro`
- `APP_BASE_PATH=/gymbro`
- zmienne srodowiskowe dla PostgreSQL i MongoDB

## Konfiguracja PHP w XAMPP

W `php.ini` z XAMPP wlacz:

```ini
extension=openssl
extension=curl
extension=pgsql
extension=pdo_pgsql
extension=mongodb
```

Ustaw tez limity uploadu:

```ini
upload_max_filesize = 20M
post_max_size = 24M
max_execution_time = 60
```

## Jak odpalac projekt

1. Uruchom Apache w XAMPP Control Panel.
2. Upewnij sie, ze PostgreSQL dziala.
3. Upewnij sie, ze MongoDB dziala.
4. Otworz:

```text
http://localhost/gymbro
```

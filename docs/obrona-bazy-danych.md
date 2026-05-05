# GymBRO - obrona baz danych

Ten plik jest praktyczna sciaga do pokazania projektu na przedmiocie "Bazy danych".
Zaklada, ze aplikacja juz dziala lokalnie, a dane z `database/postgres_seed.sql` i
`database/mongo_seed.js` sa wgrane.

## Co pokazac prowadzacemu

W GymBRO sa dwie bazy danych:

- PostgreSQL przechowuje dane relacyjne i uporzadkowane:
  - uzytkownicy
  - profile
  - znajomosci
  - silownie
  - cwiczenia
  - wydarzenia treningowe
  - plany treningowe
- MongoDB przechowuje dane dokumentowe i elastyczne:
  - wykonane treningi
  - pomiary progresu
  - komentarze do planow
  - powiadomienia
  - logi aktywnosci

## Dlaczego taki podzial

### PostgreSQL

PostgreSQL pasuje do danych, ktore:

- maja relacje miedzy soba
- wymagaja kluczy obcych
- musza byc spojne
- dobrze dzialaja w modelu tabelarycznym

Przyklady z projektu:

- `users` laczy sie z `user_profiles`
- `friendships` laczy dwoch uzytkownikow
- `workout_events` laczy tworce, silownie i uczestnikow
- `training_plans` maja dni, a dni maja cwiczenia

### MongoDB

MongoDB pasuje do danych, ktorych struktura jest bardziej elastyczna.

Najlepszy przyklad to `workout_logs`:

- jeden trening moze miec rozna liczbe cwiczen
- jedno cwiczenie moze miec rozna liczbe serii
- seria ma wlasna wage i liczbe powtorzen

W relacyjnej bazie trzeba byloby do tego zrobic kilka dodatkowych tabel.
W MongoDB wszystko miesci sie wygodnie w jednym dokumencie.

## Mapowanie funkcji aplikacji do baz

| Funkcja w aplikacji | PostgreSQL | MongoDB |
| --- | --- | --- |
| Rejestracja i logowanie | `users`, `user_profiles` | `activity_logs` po rejestracji |
| Profil i onboarding | `user_profiles` | startowe wyniki w `progress_measurements` |
| Znajomi | `friendships`, `users` | `notifications`, `activity_logs` |
| Silownie | `gyms` | - |
| Cwiczenia | `exercises` | - |
| Wydarzenia treningowe | `workout_events`, `workout_event_participants` | `notifications`, `activity_logs` |
| Plany treningowe | `training_plans`, `training_plan_days`, `training_plan_exercises` | `plan_comments` |
| Dziennik treningowy | plan i silownia pobierane z PostgreSQL | `workout_logs` |
| Progres | - | `progress_measurements` |

## Scenariusz prezentacji 5-10 minut

### 1. Pokaz aplikacje

Powiedz:

> GymBRO to aplikacja dla znajomych z silowni. Uzytkownicy moga umawiac wspolne
> treningi, tworzyc plany, zapisywac wykonane treningi i sledzic progres.

Kliknij:

1. Dashboard
2. Spotkania
3. Plany
4. Treningi / Progres

Cel: pokazac, ze aplikacja faktycznie korzysta z danych z obu baz.

### 2. Pokaz PostgreSQL

Otworz:

- `database/postgres_schema.sql`
- `docs/postgresql-erd.md`
- `docs/queries/postgresql_demo.sql`

Powiedz:

> PostgreSQL trzyma dane relacyjne, bo tutaj sa klucze obce, powiazania i
> kontrola spojnosci danych.

Potem pokaz:

1. tabele `users` i `user_profiles`
2. tabele `friendships`
3. tabele `workout_events` i `workout_event_participants`
4. tabele `training_plans`, `training_plan_days`, `training_plan_exercises`

Uruchom 2-3 zapytania z `postgresql_demo.sql`.

### 3. Pokaz MongoDB

Otworz:

- `database/mongo_seed.js`
- `docs/mongodb-struktura.md`
- `docs/queries/mongodb_demo.js`

Powiedz:

> MongoDB trzyma dane dokumentowe, gdzie struktura moze byc rozna dla roznych
> rekordow, szczegolnie przy wykonanych treningach i seriach.

Potem pokaz:

1. kolekcje `workout_logs`
2. kolekcje `progress_measurements`
3. kolekcje `plan_comments`
4. kolekcje `notifications`
5. kolekcje `activity_logs`

Uruchom 2-3 query z `mongodb_demo.js`.

### 4. Najwazniejsza odpowiedz dla prowadzacego

Jesli padnie pytanie "dlaczego dwie bazy?", odpowiedz:

> PostgreSQL wybralem do danych mocno relacyjnych i spojnych, a MongoDB do danych
> elastycznych i zagniezdzonych. Najlepiej widac to po logach treningowych, gdzie
> jeden dokument moze zawierac wiele cwiczen i wiele serii bez rozbijania tego na
> dodatkowe tabele.

## Co warto powiedziec o jakosci projektu

- w PostgreSQL sa klucze obce
- sa ograniczenia `CHECK`
- sa indeksy na czesto uzywanych polach
- dane testowe sa przygotowane do pokazu
- MongoDB nie dubluje modelu relacyjnego, tylko obsluguje dane elastyczne
- aplikacja praktycznie wykorzystuje obie bazy, a nie tylko "ma je podlaczone"

## Minimalny zestaw plikow do otwarcia na obronie

1. `README.md`
2. `database/postgres_schema.sql`
3. `database/mongo_seed.js`
4. `docs/postgresql-erd.md`
5. `docs/mongodb-struktura.md`
6. `docs/queries/postgresql_demo.sql`
7. `docs/queries/mongodb_demo.js`

# GymBRO - model relacyjny PostgreSQL

Ten opis odpowiada aktualnemu schematowi z `database/postgres_schema.sql`.

## Najwazniejsze encje

- `users` - podstawowe dane logowania
- `user_profiles` - rozszerzone dane profilu 1:1 z uzytkownikiem
- `friendships` - relacja znajomych i zaproszen
- `gyms` - lista silowni dodanych przez uzytkownikow
- `exercises` - lista cwiczen dodanych przez uzytkownikow
- `workout_events` - wspolne treningi umawiane przez uzytkownikow
- `workout_event_participants` - uczestnicy wydarzen
- `training_plans` - plany treningowe
- `training_plan_days` - dni w planie
- `training_plan_exercises` - cwiczenia przypisane do dnia planu

## Pseudo-diagram relacji

```text
users
  1 -> 1  user_profiles
  1 -> n  gyms
  1 -> n  exercises
  1 -> n  training_plans
  1 -> n  workout_events
  n -> n  workout_events przez workout_event_participants
  n -> n  users przez friendships

training_plans
  1 -> n  training_plan_days

training_plan_days
  1 -> n  training_plan_exercises

training_plan_exercises
  n -> 1  exercises

workout_events
  n -> 1  gyms
  1 -> n  workout_event_participants
```

## Kluczowe relacje do omowienia

### 1. `users` i `user_profiles`

To relacja 1:1.
Kazdy uzytkownik ma jeden profil.

Przyklad:

- `users` przechowuje `name`, `email`, `password_hash`
- `user_profiles` przechowuje `height_cm`, `weight_kg`, `goal`, `training_level`

To rozdzielenie ma sens, bo dane logowania i dane profilowe maja inna role.

### 2. `friendships`

To tabela relacyjna opisujaca znajomosci i zaproszenia.

Najwazniejsze pola:

- `requester_id`
- `receiver_id`
- `status`

Dzieki temu mozna latwo odroznic:

- zaproszenie oczekujace
- znajomosc zaakceptowana
- zaproszenie odrzucone

### 3. `workout_events` i `workout_event_participants`

To klasyczna relacja wiele-do-wielu:

- jedno wydarzenie ma wielu uczestnikow
- jeden uzytkownik moze uczestniczyc w wielu wydarzeniach

Dlatego potrzebna jest tabela posrednia `workout_event_participants`.

### 4. `training_plans`, `training_plan_days`, `training_plan_exercises`

Plan treningowy jest rozbity relacyjnie:

- plan ma wiele dni
- kazdy dzien ma wiele cwiczen
- kazde cwiczenie jest powiazane z tabela `exercises`

To pozwala dobrze trzymac uporzadkowane dane:

- kolejnosc dni
- kolejnosc cwiczen
- serie
- powtorzenia
- przerwy
- notatki

## Integralnosc i bezpieczenstwo danych

W schemacie sa uzyte:

- `PRIMARY KEY`
- `FOREIGN KEY`
- `UNIQUE`
- `CHECK`
- `ON DELETE CASCADE`
- `ON DELETE SET NULL`

To oznacza, ze baza pilnuje spojnosci, a nie tylko sama aplikacja.

## Indeksy

W projekcie sa indeksy na polach:

- `users(email)`
- `workout_events(event_date)`
- `training_plans(user_id)`
- `training_plans(visibility)`
- `friendships(requester_id)`
- `friendships(receiver_id)`

Mozna powiedziec prowadzacemu, ze te pola sa sensowne do indeksowania, bo sa
uzywane przy logowaniu, filtrowaniu, listowaniu i wyszukiwaniu powiazanych danych.

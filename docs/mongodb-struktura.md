# GymBRO - struktura MongoDB

Baza `gymbro_mongo` sluzy do danych dokumentowych i elastycznych.

## Kolekcje

### 1. `workout_logs`

Ta kolekcja trzyma wykonane treningi uzytkownika.

Przyklad dokumentu:

```javascript
{
  user_id: 1,
  training_date: "2026-01-20",
  plan_id: 1,
  gym_id: 1,
  duration_minutes: 75,
  mood: "dobry",
  notes: "Dobry trening, progres na klatce.",
  exercises: [
    {
      exercise_id: 1,
      name: "Wyciskanie sztangi lezac",
      sets: [
        { weight: 80, reps: 8 },
        { weight: 82.5, reps: 6 },
        { weight: 85, reps: 5 }
      ]
    }
  ],
  created_at: "2026-01-20 18:30:00"
}
```

To jest najlepszy argument za MongoDB w tym projekcie.
Jeden dokument moze przechowac:

- wiele cwiczen
- w kazdym cwiczeniu wiele serii
- rozne wartosci wagi i powtorzen

Bez MongoDB trzeba byloby robic dodatkowe tabele typu:

- workout_logs
- workout_log_exercises
- workout_log_sets

### 2. `progress_measurements`

Ta kolekcja trzyma pomiary progresu.

Przykladowe typy:

- `body_weight`
- `bench_press`
- `squat`
- `deadlift`
- `arm_circumference`
- `chest_circumference`
- `waist_circumference`

Kazdy wpis to jeden pomiar z data, wartoscia, jednostka i notatka.

### 3. `plan_comments`

Ta kolekcja trzyma komentarze do planow treningowych.

Kazdy dokument zawiera:

- `plan_id`
- `user_id`
- `user_name`
- `content`
- `rating`
- `created_at`

To dobre miejsce na MongoDB, bo komentarze sa proste, niezalezne i latwe do
przechowywania jako osobne dokumenty.

### 4. `notifications`

Kolekcja z powiadomieniami dla uzytkownika.

Kazdy dokument zawiera:

- `user_id`
- `type`
- `title`
- `content`
- `is_read`
- `created_at`

To dane typowo aplikacyjne, czesto dopisywane i czytane jako lista.

### 5. `activity_logs`

Kolekcja historii aktywnosci uzytkownika.

Kazdy dokument zawiera:

- `user_id`
- `action`
- `details`
- `created_at`

Pole `details` jest elastyczne. Dla roznych akcji moze miec inna zawartosc, na
przyklad:

```javascript
{ plan_id: 1, plan_name: "Push Pull Legs" }
```

albo:

```javascript
{ training_date: "2026-01-20" }
```

I to jest kolejny przypadek, gdzie dokumentowy model bardzo dobrze pasuje.

## Dlaczego MongoDB ma sens w GymBRO

MongoDB nie sluzy tu do wszystkiego. Jest uzyty tam, gdzie struktura danych jest:

- bardziej zmienna
- zagniezdzona
- mniej relacyjna
- wygodna do przechowywania jako dokument

Najmocniejsze przyklady:

- logi treningowe
- serie w cwiczeniach
- logi aktywnosci
- powiadomienia

## Co powiedziec prowadzacemu

Jesli padnie pytanie, dlaczego komentarze albo powiadomienia nie sa w PostgreSQL,
mozna odpowiedziec:

> Te dane nie wymagaja tutaj skomplikowanych relacji i wygodnie dzialaja jako
> lekkie dokumenty. Najwazniejsze jest jednak to, ze MongoDB w projekcie obsluguje
> tez dane zagniezdzone, przede wszystkim logi treningowe z cwiczeniami i seriami.

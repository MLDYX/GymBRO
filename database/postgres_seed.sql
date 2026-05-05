-- Dane testowe dla GymBRO
-- Haslo wszystkich kont: password123

TRUNCATE TABLE
    training_plan_exercises,
    training_plan_days,
    workout_event_participants,
    workout_events,
    training_plans,
    exercises,
    gyms,
    friendships,
    user_profiles,
    users
RESTART IDENTITY CASCADE;

INSERT INTO users (name, email, password_hash, created_at) VALUES
('Jan Kowalski', 'jan@gymbro.local', '$2a$10$kypbnGGCpJ7UQlysnqzJG.6H.dUewn7UPVWA3Ip.E.8U4jlVnFNnu', '2026-01-05 10:00:00'),
('Anna Nowak', 'anna@gymbro.local', '$2a$10$kypbnGGCpJ7UQlysnqzJG.6H.dUewn7UPVWA3Ip.E.8U4jlVnFNnu', '2026-01-06 11:00:00'),
('Piotr Zielinski', 'piotr@gymbro.local', '$2a$10$kypbnGGCpJ7UQlysnqzJG.6H.dUewn7UPVWA3Ip.E.8U4jlVnFNnu', '2026-01-07 12:00:00'),
('Katarzyna Wisniewska', 'kasia@gymbro.local', '$2a$10$kypbnGGCpJ7UQlysnqzJG.6H.dUewn7UPVWA3Ip.E.8U4jlVnFNnu', '2026-01-08 13:00:00'),
('Marek Lewandowski', 'marek@gymbro.local', '$2a$10$kypbnGGCpJ7UQlysnqzJG.6H.dUewn7UPVWA3Ip.E.8U4jlVnFNnu', '2026-01-09 14:00:00');

INSERT INTO user_profiles (user_id, age, height_cm, weight_kg, training_level, training_experience, goal, bio, avatar_path, onboarding_completed, updated_at) VALUES
(1, 24, 182, 84.50, 'sredniozaawansowany', '1-2 lata', 'masa miesniowa', 'Lubi trening silowy i klasyczne split plany.', NULL, TRUE, '2026-01-10 09:00:00'),
(2, 22, 168, 61.00, 'poczatkujacy', 'ponizej roku', 'redukcja', 'Dopiero buduje nawyk regularnych treningow.', NULL, TRUE, '2026-01-10 09:05:00'),
(3, 27, 176, 77.30, 'zaawansowany', '3+ lata', 'sila', 'Skupiony na trojboju i wynikach w bojach.', NULL, TRUE, '2026-01-10 09:10:00'),
(4, 25, 170, 64.20, 'sredniozaawansowany', '1-2 lata', 'sprawnosc', 'Laczy trening na silowni z bieganiem.', NULL, TRUE, '2026-01-10 09:15:00'),
(5, 29, 188, 92.10, 'zaawansowany', '3+ lata', 'rekompozycja', 'Lubi trening typu push pull legs.', NULL, TRUE, '2026-01-10 09:20:00');

INSERT INTO friendships (requester_id, receiver_id, status, created_at) VALUES
(1, 2, 'accepted', '2026-01-12 08:00:00'),
(1, 3, 'accepted', '2026-01-12 08:05:00'),
(2, 4, 'pending', '2026-01-13 10:00:00'),
(5, 1, 'pending', '2026-01-14 11:00:00'),
(3, 4, 'rejected', '2026-01-15 12:00:00'),
(4, 5, 'accepted', '2026-01-16 13:00:00');

INSERT INTO gyms (user_id, name, city, address, description, created_at) VALUES
(1, 'Iron Temple', 'Warszawa', 'ul. Sportowa 12', 'Duza silownia z wolnymi ciezarami.', '2026-01-11 08:00:00'),
(2, 'Power House', 'Krakow', 'ul. Fitness 5', 'Nowoczesna strefa cardio i silowa.', '2026-01-11 08:15:00'),
(3, 'Atlas Club', 'Wroclaw', 'ul. Treningowa 8', 'Miejsce dla osob trenujacych pod sile.', '2026-01-11 08:30:00'),
(4, 'Lift Lab', 'Poznan', 'ul. Zdrowa 17', 'Przyjazna atmosfera i zajecia grupowe.', '2026-01-11 08:45:00'),
(5, 'Barbell Spot', 'Gdansk', 'ul. Morska 9', 'Silownia z platformami i rackami.', '2026-01-11 09:00:00');

INSERT INTO exercises (user_id, name, muscle_group, equipment, description, created_at) VALUES
(1, 'Wyciskanie sztangi lezac', 'klatka piersiowa', 'sztanga', 'Klasyczne cwiczenie na klatke.', '2026-01-11 10:00:00'),
(1, 'Przysiad ze sztanga', 'nogi', 'sztanga', 'Podstawowe cwiczenie wielostawowe.', '2026-01-11 10:05:00'),
(1, 'Martwy ciag klasyczny', 'plecy', 'sztanga', 'Buduje sile tylnej tasmy.', '2026-01-11 10:10:00'),
(2, 'Wioslowanie sztanga', 'plecy', 'sztanga', 'Ruch przyciagania w opadzie.', '2026-01-11 10:15:00'),
(2, 'Wyciskanie zolnierskie', 'barki', 'sztanga', 'Pionowe wyciskanie nad glowe.', '2026-01-11 10:20:00'),
(2, 'Uginanie ramion z hantlami', 'biceps', 'hantle', 'Izolacja ramion.', '2026-01-11 10:25:00'),
(3, 'Prostowanie nog na maszynie', 'nogi', 'maszyna', 'Akcent na czworoglowe.', '2026-01-11 10:30:00'),
(3, 'Uginanie nog lezac', 'dwuglowe ud', 'maszyna', 'Akcent na tyl uda.', '2026-01-11 10:35:00'),
(3, 'Sciaganie drazka do klatki', 'plecy', 'wyciag', 'Podstawowe cwiczenie na najszersze.', '2026-01-11 10:40:00'),
(4, 'Rozpietki na bramie', 'klatka piersiowa', 'brama', 'Praca w przywiedzeniu ramion.', '2026-01-11 10:45:00'),
(4, 'Unoszenie bokiem', 'barki', 'hantle', 'Boczny akton barkow.', '2026-01-11 10:50:00'),
(4, 'Plank', 'core', 'masa ciala', 'Stabilizacja tulowia.', '2026-01-11 10:55:00'),
(5, 'Wspiecia na lydki stojac', 'lydki', 'maszyna', 'Trening lydek.', '2026-01-11 11:00:00'),
(5, 'Hip thrust', 'posladki', 'sztanga', 'Mocny akcent na posladki.', '2026-01-11 11:05:00'),
(5, 'Dipy na poreczach', 'triceps', 'porecze', 'Trening tricepsa i klatki.', '2026-01-11 11:10:00');

INSERT INTO training_plans (user_id, name, description, level, goal, visibility, created_at) VALUES
(1, 'Push Pull Legs', 'Plan trzydniowy ukierunkowany na hipertrofie.', 'sredniozaawansowany', 'masa', 'public', '2026-01-18 09:00:00'),
(2, 'Full Body Start', 'Prosty plan FBW dla poczatkujacych.', 'poczatkujacy', 'nauka techniki', 'public', '2026-01-18 09:10:00'),
(3, 'Power Build', 'Polaczenie silowych bojow i dodatkow.', 'zaawansowany', 'sila', 'private', '2026-01-18 09:20:00'),
(5, 'Lean Recomp', 'Rozpiska pod recomposition i duza objetosc.', 'sredniozaawansowany', 'rekompozycja', 'public', '2026-01-18 09:30:00');

INSERT INTO training_plan_days (plan_id, name, day_order) VALUES
(1, 'Push', 1),
(1, 'Pull', 2),
(1, 'Legs', 3),
(2, 'FBW A', 1),
(2, 'FBW B', 2),
(3, 'Lower Strength', 1),
(3, 'Upper Strength', 2),
(4, 'Recomp Day 1', 1),
(4, 'Recomp Day 2', 2);

INSERT INTO training_plan_exercises (day_id, exercise_id, sets, reps, rest_seconds, notes, exercise_order) VALUES
(1, 1, 4, '6-8', 120, 'Glowny boj dnia.', 1),
(1, 5, 4, '8-10', 90, 'Pełny zakres ruchu.', 2),
(1, 15, 3, '8-12', 75, 'Kontrolowane tempo.', 3),
(2, 4, 4, '8-10', 90, 'Trzymaj neutralny kregoslup.', 1),
(2, 9, 4, '10-12', 75, 'Skup sie na pracy plecow.', 2),
(2, 6, 3, '10-12', 60, 'Bez bujania tulowiem.', 3),
(3, 2, 5, '5-8', 150, 'Ciezko technicznie.', 1),
(3, 7, 3, '12-15', 60, 'Dobijanie czworoglowych.', 2),
(3, 13, 4, '12-15', 45, 'Pauza w gorze.', 3),
(4, 2, 3, '8', 120, 'Przysiad jako baza.', 1),
(4, 1, 3, '8', 120, 'Stabilny tor ruchu.', 2),
(4, 12, 3, '45s', 45, 'Stabilizacja.', 3),
(5, 3, 3, '5', 180, 'Technika przede wszystkim.', 1),
(5, 10, 3, '12', 60, 'Napiecie miesniowe.', 2),
(6, 2, 5, '3-5', 180, 'Dzien silowy dolu.', 1),
(6, 14, 4, '8', 120, 'Mocny lockout.', 2),
(7, 1, 5, '3-5', 180, 'Dzien silowy gory.', 1),
(7, 4, 4, '6', 120, 'Stabilny chwyt.', 2),
(8, 1, 4, '10', 90, 'Objętość klatki.', 1),
(8, 10, 3, '15', 60, 'Domkniecie pracy klatki.', 2),
(9, 2, 4, '10', 120, 'Tempo 3-1-1.', 1),
(9, 14, 4, '12', 90, 'Akcent na posladki.', 2);

INSERT INTO workout_events (creator_id, gym_id, title, description, event_date, start_time, max_participants, status, created_at) VALUES
(1, 1, 'Push day po pracy', 'Trening klatki i barkow.', '2026-05-10', '18:00', 3, 'planned', '2026-05-01 12:00:00'),
(2, 2, 'Poranny FBW', 'Lekki trening dla poczatkujacych.', '2026-05-12', '07:30', 4, 'planned', '2026-05-02 08:00:00'),
(3, 3, 'Wieczorny squat session', 'Cięższa jednostka pod nogi.', '2026-05-14', '19:00', 3, 'planned', '2026-05-03 09:00:00'),
(5, 5, 'Recomp team workout', 'Objętościowy trening całego ciała.', '2026-05-16', '17:00', 5, 'planned', '2026-05-04 10:00:00');

INSERT INTO workout_event_participants (event_id, user_id, joined_at) VALUES
(1, 2, '2026-05-02 12:00:00'),
(1, 3, '2026-05-02 12:30:00'),
(2, 1, '2026-05-03 07:00:00'),
(2, 4, '2026-05-03 07:10:00'),
(3, 1, '2026-05-03 18:00:00'),
(4, 2, '2026-05-04 11:00:00'),
(4, 4, '2026-05-04 11:10:00');

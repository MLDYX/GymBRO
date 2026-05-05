-- GymBRO - query demo do PostgreSQL
-- Uruchamiaj w bazie: gymbro_db

-- 1. Uzytkownicy z profilami.
-- Pokazuje relacje 1:1 i podstawowe dane profilu.
SELECT
    u.id,
    u.name,
    u.email,
    up.training_level,
    up.training_experience,
    up.goal,
    up.onboarding_completed
FROM users u
LEFT JOIN user_profiles up ON up.user_id = u.id
ORDER BY u.id;

-- 2. Znajomosci i zaproszenia.
-- Pokazuje tabele relacyjna miedzy uzytkownikami.
SELECT
    f.id,
    requester.name AS requester,
    receiver.name AS receiver,
    f.status,
    f.created_at
FROM friendships f
JOIN users requester ON requester.id = f.requester_id
JOIN users receiver ON receiver.id = f.receiver_id
ORDER BY f.created_at;

-- 3. Nadchodzace wydarzenia z tworca i silownia.
-- Dobry przyklad klasycznego JOIN.
SELECT
    e.id,
    e.title,
    e.event_date,
    e.start_time,
    creator.name AS creator_name,
    g.name AS gym_name,
    e.max_participants,
    e.status
FROM workout_events e
JOIN users creator ON creator.id = e.creator_id
LEFT JOIN gyms g ON g.id = e.gym_id
WHERE e.event_date >= DATE '2026-05-10'
ORDER BY e.event_date, e.start_time;

-- 4. Uczestnicy wydarzen.
-- Pokazuje relacje wiele-do-wielu przez tabele posrednia.
SELECT
    e.title,
    u.name AS participant_name,
    wep.joined_at
FROM workout_event_participants wep
JOIN workout_events e ON e.id = wep.event_id
JOIN users u ON u.id = wep.user_id
ORDER BY e.id, wep.joined_at;

-- 5. Publiczne plany treningowe.
-- Przyklad filtrowania po polu, na ktorym jest indeks.
SELECT
    tp.id,
    tp.name,
    tp.level,
    tp.goal,
    tp.visibility,
    u.name AS author_name
FROM training_plans tp
JOIN users u ON u.id = tp.user_id
WHERE tp.visibility = 'public'
ORDER BY tp.created_at;

-- 6. Rozpiska konkretnego planu: dni i cwiczenia.
-- Pokazuje trojstopniowa strukture plan -> dzien -> cwiczenie.
SELECT
    tp.name AS plan_name,
    tpd.day_order,
    tpd.name AS day_name,
    tpe.exercise_order,
    e.name AS exercise_name,
    tpe.sets,
    tpe.reps,
    tpe.rest_seconds
FROM training_plans tp
JOIN training_plan_days tpd ON tpd.plan_id = tp.id
JOIN training_plan_exercises tpe ON tpe.day_id = tpd.id
JOIN exercises e ON e.id = tpe.exercise_id
WHERE tp.id = 1
ORDER BY tpd.day_order, tpe.exercise_order;

-- 7. Ile osob jest zapisanych na kazde wydarzenie.
-- Przyklad grupowania danych.
SELECT
    e.id,
    e.title,
    e.max_participants,
    COUNT(wep.user_id) AS participants_count,
    e.max_participants - COUNT(wep.user_id) AS free_slots
FROM workout_events e
LEFT JOIN workout_event_participants wep ON wep.event_id = e.id
GROUP BY e.id, e.title, e.max_participants
ORDER BY e.id;

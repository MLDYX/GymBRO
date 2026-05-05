const database = db.getSiblingDB('gymbro_mongo');

database.workout_logs.deleteMany({});
database.progress_measurements.deleteMany({});
database.plan_comments.deleteMany({});
database.notifications.deleteMany({});
database.activity_logs.deleteMany({});

database.workout_logs.insertMany([
  {
    user_id: 1,
    training_date: '2026-01-20',
    plan_id: 1,
    gym_id: 1,
    duration_minutes: 75,
    mood: 'dobry',
    notes: 'Dobry trening, progres na klatce.',
    exercises: [
      { exercise_id: 1, name: 'Wyciskanie sztangi lezac', sets: [{ weight: 80, reps: 8 }, { weight: 82.5, reps: 6 }, { weight: 85, reps: 5 }] },
      { exercise_id: 5, name: 'Wyciskanie zolnierskie', sets: [{ weight: 45, reps: 8 }, { weight: 45, reps: 8 }] }
    ],
    created_at: '2026-01-20 18:30:00'
  },
  {
    user_id: 1,
    training_date: '2026-01-23',
    plan_id: 1,
    gym_id: 1,
    duration_minutes: 80,
    mood: 'bardzo dobry',
    notes: 'Mocny push day.',
    exercises: [
      { exercise_id: 1, name: 'Wyciskanie sztangi lezac', sets: [{ weight: 82.5, reps: 8 }, { weight: 85, reps: 6 }] }
    ],
    created_at: '2026-01-23 18:35:00'
  },
  {
    user_id: 2,
    training_date: '2026-01-21',
    plan_id: 2,
    gym_id: 2,
    duration_minutes: 55,
    mood: 'ok',
    notes: 'Pierwszy pelny trening FBW.',
    exercises: [
      { exercise_id: 2, name: 'Przysiad ze sztanga', sets: [{ weight: 40, reps: 8 }, { weight: 40, reps: 8 }] },
      { exercise_id: 12, name: 'Plank', sets: [{ weight: 0, reps: 45 }, { weight: 0, reps: 45 }] }
    ],
    created_at: '2026-01-21 07:40:00'
  },
  {
    user_id: 2,
    training_date: '2026-01-25',
    plan_id: 2,
    gym_id: 2,
    duration_minutes: 60,
    mood: 'dobry',
    notes: 'Coraz lepsza technika.',
    exercises: [
      { exercise_id: 1, name: 'Wyciskanie sztangi lezac', sets: [{ weight: 32.5, reps: 8 }, { weight: 35, reps: 8 }] }
    ],
    created_at: '2026-01-25 08:15:00'
  },
  {
    user_id: 3,
    training_date: '2026-01-22',
    plan_id: 3,
    gym_id: 3,
    duration_minutes: 90,
    mood: 'dobry',
    notes: 'Silowy squat session.',
    exercises: [
      { exercise_id: 2, name: 'Przysiad ze sztanga', sets: [{ weight: 150, reps: 5 }, { weight: 155, reps: 4 }] },
      { exercise_id: 3, name: 'Martwy ciag klasyczny', sets: [{ weight: 190, reps: 3 }] }
    ],
    created_at: '2026-01-22 20:00:00'
  },
  {
    user_id: 3,
    training_date: '2026-01-29',
    plan_id: 3,
    gym_id: 3,
    duration_minutes: 92,
    mood: 'swietny',
    notes: 'Nowy rekord w przysiadzie.',
    exercises: [
      { exercise_id: 2, name: 'Przysiad ze sztanga', sets: [{ weight: 160, reps: 3 }, { weight: 165, reps: 2 }] }
    ],
    created_at: '2026-01-29 20:10:00'
  },
  {
    user_id: 4,
    training_date: '2026-01-19',
    plan_id: 4,
    gym_id: 4,
    duration_minutes: 65,
    mood: 'spokojny',
    notes: 'Lekkie barki i core.',
    exercises: [
      { exercise_id: 11, name: 'Unoszenie bokiem', sets: [{ weight: 8, reps: 15 }, { weight: 8, reps: 15 }] },
      { exercise_id: 12, name: 'Plank', sets: [{ weight: 0, reps: 60 }] }
    ],
    created_at: '2026-01-19 17:20:00'
  },
  {
    user_id: 4,
    training_date: '2026-01-27',
    plan_id: 4,
    gym_id: 4,
    duration_minutes: 68,
    mood: 'dobry',
    notes: 'Dobre czucie miesniowe.',
    exercises: [
      { exercise_id: 10, name: 'Rozpietki na bramie', sets: [{ weight: 25, reps: 12 }, { weight: 25, reps: 12 }] }
    ],
    created_at: '2026-01-27 17:25:00'
  },
  {
    user_id: 5,
    training_date: '2026-01-24',
    plan_id: 4,
    gym_id: 5,
    duration_minutes: 78,
    mood: 'bardzo dobry',
    notes: 'Mocna objetosc na nogi.',
    exercises: [
      { exercise_id: 2, name: 'Przysiad ze sztanga', sets: [{ weight: 110, reps: 10 }, { weight: 110, reps: 9 }] },
      { exercise_id: 14, name: 'Hip thrust', sets: [{ weight: 140, reps: 10 }, { weight: 145, reps: 8 }] }
    ],
    created_at: '2026-01-24 18:10:00'
  },
  {
    user_id: 5,
    training_date: '2026-01-31',
    plan_id: 4,
    gym_id: 5,
    duration_minutes: 82,
    mood: 'dobry',
    notes: 'Udany tydzien treningowy.',
    exercises: [
      { exercise_id: 1, name: 'Wyciskanie sztangi lezac', sets: [{ weight: 90, reps: 8 }, { weight: 92.5, reps: 6 }] }
    ],
    created_at: '2026-01-31 18:25:00'
  }
]);

database.progress_measurements.insertMany([
  { user_id: 1, date: '2026-01-10', type: 'body_weight', value: 84.8, unit: 'kg', note: 'Pomiar rano.', created_at: '2026-01-10 08:00:00' },
  { user_id: 1, date: '2026-01-17', type: 'body_weight', value: 84.5, unit: 'kg', note: 'Lekki spadek.', created_at: '2026-01-17 08:00:00' },
  { user_id: 1, date: '2026-01-24', type: 'body_weight', value: 84.9, unit: 'kg', note: 'Stabilizacja.', created_at: '2026-01-24 08:00:00' },
  { user_id: 1, date: '2026-01-31', type: 'bench_press', value: 95, unit: 'kg', note: 'Nowe 1RM.', created_at: '2026-01-31 08:00:00' },
  { user_id: 2, date: '2026-01-10', type: 'body_weight', value: 62.0, unit: 'kg', note: 'Start redukcji.', created_at: '2026-01-10 08:05:00' },
  { user_id: 2, date: '2026-01-17', type: 'body_weight', value: 61.4, unit: 'kg', note: 'Pierwszy tydzien.', created_at: '2026-01-17 08:05:00' },
  { user_id: 2, date: '2026-01-24', type: 'waist_circumference', value: 73, unit: 'cm', note: 'Mniejszy obwod pasa.', created_at: '2026-01-24 08:05:00' },
  { user_id: 2, date: '2026-01-31', type: 'body_weight', value: 60.9, unit: 'kg', note: 'Dalszy spadek.', created_at: '2026-01-31 08:05:00' },
  { user_id: 3, date: '2026-01-10', type: 'squat', value: 180, unit: 'kg', note: 'Bazowy rekord.', created_at: '2026-01-10 08:10:00' },
  { user_id: 3, date: '2026-01-17', type: 'deadlift', value: 220, unit: 'kg', note: 'Solidna seria.', created_at: '2026-01-17 08:10:00' },
  { user_id: 3, date: '2026-01-24', type: 'bench_press', value: 120, unit: 'kg', note: 'Technicznie czysto.', created_at: '2026-01-24 08:10:00' },
  { user_id: 3, date: '2026-01-31', type: 'squat', value: 185, unit: 'kg', note: 'Poprawa wyniku.', created_at: '2026-01-31 08:10:00' },
  { user_id: 4, date: '2026-01-10', type: 'body_weight', value: 64.5, unit: 'kg', note: 'Początek miesiąca.', created_at: '2026-01-10 08:15:00' },
  { user_id: 4, date: '2026-01-17', type: 'arm_circumference', value: 29, unit: 'cm', note: 'Pomiar po treningu.', created_at: '2026-01-17 08:15:00' },
  { user_id: 4, date: '2026-01-24', type: 'chest_circumference', value: 88, unit: 'cm', note: 'Regularny monitoring.', created_at: '2026-01-24 08:15:00' },
  { user_id: 4, date: '2026-01-31', type: 'body_weight', value: 64.0, unit: 'kg', note: 'Stabilnie.', created_at: '2026-01-31 08:15:00' },
  { user_id: 5, date: '2026-01-10', type: 'body_weight', value: 92.5, unit: 'kg', note: 'Pomiar startowy.', created_at: '2026-01-10 08:20:00' },
  { user_id: 5, date: '2026-01-17', type: 'body_weight', value: 91.9, unit: 'kg', note: 'Lekki spadek wagi.', created_at: '2026-01-17 08:20:00' },
  { user_id: 5, date: '2026-01-24', type: 'waist_circumference', value: 88, unit: 'cm', note: 'Mniejszy pas.', created_at: '2026-01-24 08:20:00' },
  { user_id: 5, date: '2026-01-31', type: 'body_weight', value: 91.2, unit: 'kg', note: 'Kolejny tydzien progresu.', created_at: '2026-01-31 08:20:00' }
]);

database.plan_comments.insertMany([
  { plan_id: 1, user_id: 2, user_name: 'Anna Nowak', content: 'Bardzo dobry plan pod mase.', rating: 5, created_at: '2026-01-20 19:00:00' },
  { plan_id: 1, user_id: 3, user_name: 'Piotr Zielinski', content: 'Dobra struktura i sensowna objetosc.', rating: 4, created_at: '2026-01-21 19:10:00' },
  { plan_id: 1, user_id: 4, user_name: 'Katarzyna Wisniewska', content: 'Czytelny i prosty plan.', rating: 5, created_at: '2026-01-22 19:20:00' },
  { plan_id: 2, user_id: 1, user_name: 'Jan Kowalski', content: 'Dobry start dla nowych osob.', rating: 4, created_at: '2026-01-23 19:30:00' },
  { plan_id: 2, user_id: 5, user_name: 'Marek Lewandowski', content: 'Dodalbym wiecej pracy na plecy.', rating: 3, created_at: '2026-01-24 19:40:00' },
  { plan_id: 3, user_id: 1, user_name: 'Jan Kowalski', content: 'Bardzo mocny akcent silowy.', rating: 5, created_at: '2026-01-25 19:50:00' },
  { plan_id: 3, user_id: 5, user_name: 'Marek Lewandowski', content: 'Plan dla kogos z baza treningowa.', rating: 4, created_at: '2026-01-26 20:00:00' },
  { plan_id: 4, user_id: 2, user_name: 'Anna Nowak', content: 'Podoba mi sie duza objetosc.', rating: 4, created_at: '2026-01-27 20:10:00' },
  { plan_id: 4, user_id: 3, user_name: 'Piotr Zielinski', content: 'Sensowna progresja i priorytety.', rating: 5, created_at: '2026-01-28 20:20:00' },
  { plan_id: 4, user_id: 4, user_name: 'Katarzyna Wisniewska', content: 'Fajny plan na recomposition.', rating: 4, created_at: '2026-01-29 20:30:00' }
]);

database.notifications.insertMany([
  { user_id: 1, type: 'event_joined', title: 'Nowy uczestnik treningu', content: 'Anna dolaczyla do Twojego treningu.', is_read: false, created_at: '2026-01-20 19:10:00' },
  { user_id: 1, type: 'friend_request', title: 'Nowe zaproszenie', content: 'Marek wyslal Ci zaproszenie do znajomych.', is_read: false, created_at: '2026-01-21 19:15:00' },
  { user_id: 2, type: 'friendship_accepted', title: 'Zaproszenie zaakceptowane', content: 'Jan zaakceptowal zaproszenie.', is_read: true, created_at: '2026-01-22 19:20:00' },
  { user_id: 2, type: 'event_joined', title: 'Nowy uczestnik treningu', content: 'Jan dolaczyl do porannego FBW.', is_read: false, created_at: '2026-01-23 19:25:00' },
  { user_id: 3, type: 'plan_comment', title: 'Nowy komentarz planu', content: 'Jan skomentowal Twoj plan.', is_read: true, created_at: '2026-01-24 19:30:00' },
  { user_id: 3, type: 'event_joined', title: 'Nowy uczestnik treningu', content: 'Jan dolaczyl do squat session.', is_read: false, created_at: '2026-01-25 19:35:00' },
  { user_id: 4, type: 'friend_request', title: 'Nowe zaproszenie', content: 'Anna wyslala Ci zaproszenie do znajomych.', is_read: false, created_at: '2026-01-26 19:40:00' },
  { user_id: 4, type: 'event_joined', title: 'Nowy uczestnik treningu', content: 'Marek dolaczyl do treningu.', is_read: true, created_at: '2026-01-27 19:45:00' },
  { user_id: 5, type: 'plan_comment', title: 'Nowy komentarz planu', content: 'Piotr ocenil Twoj plan na 5/5.', is_read: false, created_at: '2026-01-28 19:50:00' },
  { user_id: 5, type: 'friend_request', title: 'Nowe zaproszenie', content: 'Katarzyna wyslala Ci zaproszenie.', is_read: false, created_at: '2026-01-29 19:55:00' }
]);

database.activity_logs.insertMany([
  { user_id: 1, action: 'created_training_plan', details: { plan_id: 1, plan_name: 'Push Pull Legs' }, created_at: '2026-01-18 14:15:00' },
  { user_id: 1, action: 'joined_event', details: { event_id: 2, title: 'Poranny FBW' }, created_at: '2026-01-20 14:20:00' },
  { user_id: 1, action: 'created_workout_log', details: { training_date: '2026-01-20' }, created_at: '2026-01-20 18:35:00' },
  { user_id: 2, action: 'created_training_plan', details: { plan_id: 2, plan_name: 'Full Body Start' }, created_at: '2026-01-18 15:00:00' },
  { user_id: 2, action: 'sent_friend_request', details: { receiver_id: 4, receiver_name: 'Katarzyna Wisniewska' }, created_at: '2026-01-19 09:00:00' },
  { user_id: 2, action: 'created_workout_log', details: { training_date: '2026-01-21' }, created_at: '2026-01-21 07:50:00' },
  { user_id: 3, action: 'created_training_plan', details: { plan_id: 3, plan_name: 'Power Build' }, created_at: '2026-01-18 16:00:00' },
  { user_id: 3, action: 'created_workout_log', details: { training_date: '2026-01-22' }, created_at: '2026-01-22 20:05:00' },
  { user_id: 3, action: 'commented_plan', details: { plan_id: 1, rating: 4 }, created_at: '2026-01-21 19:12:00' },
  { user_id: 4, action: 'created_workout_log', details: { training_date: '2026-01-19' }, created_at: '2026-01-19 17:25:00' },
  { user_id: 4, action: 'accepted_friend_request', details: { friendship_id: 3 }, created_at: '2026-01-26 10:00:00' },
  { user_id: 4, action: 'commented_plan', details: { plan_id: 4, rating: 4 }, created_at: '2026-01-29 20:35:00' },
  { user_id: 5, action: 'created_training_plan', details: { plan_id: 4, plan_name: 'Lean Recomp' }, created_at: '2026-01-18 17:00:00' },
  { user_id: 5, action: 'created_workout_log', details: { training_date: '2026-01-24' }, created_at: '2026-01-24 18:20:00' },
  { user_id: 5, action: 'commented_plan', details: { plan_id: 2, rating: 3 }, created_at: '2026-01-24 19:45:00' }
]);

print('GymBRO MongoDB seed inserted into gymbro_mongo');

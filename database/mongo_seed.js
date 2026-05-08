const database = db.getSiblingDB('gymbro_mongo');

database.workout_logs.deleteMany({});
database.progress_measurements.deleteMany({});
database.plan_comments.deleteMany({});
database.notifications.deleteMany({});
database.activity_logs.deleteMany({});

const exerciseCatalog = {
  1: 'Wyciskanie sztangi lezac',
  2: 'Przysiad ze sztanga',
  3: 'Martwy ciag klasyczny',
  4: 'Wioslowanie sztanga',
  5: 'Wyciskanie zolnierskie',
  6: 'Uginanie ramion z hantlami',
  7: 'Prostowanie nog na maszynie',
  8: 'Uginanie nog lezac',
  9: 'Sciaganie drazka do klatki',
  10: 'Rozpietki na bramie',
  11: 'Unoszenie bokiem',
  12: 'Plank',
  13: 'Wspiecia na lydki stojac',
  14: 'Hip thrust',
  15: 'Dipy na poreczach',
  16: 'Wyciskanie hantli skos dodatni',
  17: 'RDL ze sztanga',
  18: 'Przyciaganie linki siedziac',
  19: 'Face pull',
  20: 'Wykroki chodzone',
  21: 'Podciaganie nachwytem',
  22: 'Prostowanie ramion na wyciagu',
  23: 'Uginanie na modlitewniku',
  24: 'Leg press',
  25: 'Bulgarian split squat',
  26: 'Chest supported row',
  27: 'Ab wheel',
  28: 'Farmer walk'
};

const workoutTemplates = {
  upper_push: [
    { exercise_id: 1, key: 'bench', ratio: 0.80, reps: [8, 6] },
    { exercise_id: 16, key: 'db_press', ratio: 1.00, reps: [10, 8] },
    { exercise_id: 11, key: 'lateral', ratio: 1.00, reps: [15, 15] }
  ],
  upper_pull: [
    { exercise_id: 4, key: 'row', ratio: 0.95, reps: [8, 8] },
    { exercise_id: 9, key: 'lat_pull', ratio: 1.00, reps: [10, 10] },
    { exercise_id: 21, key: 'body', ratio: 0, reps: [8, 7], bodyweight: true }
  ],
  lower_strength: [
    { exercise_id: 2, key: 'squat', ratio: 0.82, reps: [6, 4] },
    { exercise_id: 17, key: 'deadlift', ratio: 0.65, reps: [8, 8] },
    { exercise_id: 13, key: 'calves', ratio: 1.00, reps: [15, 15] }
  ],
  beginner_a: [
    { exercise_id: 24, key: 'leg_press', ratio: 0.85, reps: [12, 12] },
    { exercise_id: 16, key: 'db_press', ratio: 1.00, reps: [10, 10] },
    { exercise_id: 12, key: 'body', ratio: 0, reps: [45, 45], bodyweight: true }
  ],
  beginner_b: [
    { exercise_id: 2, key: 'squat', ratio: 0.48, reps: [8, 8] },
    { exercise_id: 18, key: 'cable_row', ratio: 1.00, reps: [12, 12] },
    { exercise_id: 27, key: 'body', ratio: 0, reps: [8, 8], bodyweight: true }
  ],
  beginner_c: [
    { exercise_id: 14, key: 'hip', ratio: 0.75, reps: [10, 10] },
    { exercise_id: 5, key: 'overhead', ratio: 0.85, reps: [8, 8] },
    { exercise_id: 20, key: 'lunges', ratio: 1.00, reps: [10, 10] }
  ],
  glutes: [
    { exercise_id: 14, key: 'hip', ratio: 0.90, reps: [10, 8] },
    { exercise_id: 25, key: 'split_squat', ratio: 1.00, reps: [10, 10] },
    { exercise_id: 20, key: 'lunges', ratio: 1.00, reps: [12, 12] }
  ],
  runner: [
    { exercise_id: 12, key: 'body', ratio: 0, reps: [50, 45], bodyweight: true },
    { exercise_id: 20, key: 'lunges', ratio: 1.00, reps: [12, 12] },
    { exercise_id: 27, key: 'body', ratio: 0, reps: [10, 8], bodyweight: true }
  ],
  power_upper: [
    { exercise_id: 1, key: 'bench', ratio: 0.85, reps: [5, 4] },
    { exercise_id: 4, key: 'row', ratio: 1.00, reps: [6, 6] },
    { exercise_id: 5, key: 'overhead', ratio: 0.90, reps: [6, 6] }
  ],
  power_lower: [
    { exercise_id: 2, key: 'squat', ratio: 0.86, reps: [5, 4] },
    { exercise_id: 3, key: 'deadlift', ratio: 0.82, reps: [4, 3] },
    { exercise_id: 24, key: 'leg_press', ratio: 1.00, reps: [10, 10] }
  ],
  balanced_upper: [
    { exercise_id: 10, key: 'cable_fly', ratio: 1.00, reps: [12, 12] },
    { exercise_id: 18, key: 'cable_row', ratio: 1.00, reps: [12, 12] },
    { exercise_id: 11, key: 'lateral', ratio: 1.00, reps: [15, 15] }
  ],
  balanced_lower: [
    { exercise_id: 24, key: 'leg_press', ratio: 0.95, reps: [12, 10] },
    { exercise_id: 14, key: 'hip', ratio: 0.85, reps: [10, 10] },
    { exercise_id: 12, key: 'body', ratio: 0, reps: [45, 45], bodyweight: true }
  ],
  strength_support: [
    { exercise_id: 2, key: 'squat', ratio: 0.70, reps: [6, 6] },
    { exercise_id: 16, key: 'db_press', ratio: 1.00, reps: [8, 8] },
    { exercise_id: 13, key: 'calves', ratio: 1.00, reps: [15, 15] }
  ]
};

const users = [
  { id: 1, name: 'Jan Kowalski', planId: 1, gymId: 1, dates: ['2026-04-10', '2026-04-22', '2026-05-05'], templates: ['upper_push', 'upper_pull', 'lower_strength'], bodyWeight: [84.6, 84.9, 85.2], metrics: { bench: 95, squat: 140, deadlift: 175, row: 75, db_press: 32, lateral: 10, lat_pull: 68, calves: 65, overhead: 55 } },
  { id: 2, name: 'Anna Nowak', planId: 2, gymId: 2, dates: ['2026-04-11', '2026-04-20', '2026-05-03'], templates: ['beginner_a', 'beginner_b', 'beginner_c'], bodyWeight: [61.2, 60.8, 60.4], metrics: { leg_press: 90, db_press: 10, squat: 35, cable_row: 28, hip: 50, overhead: 18, lunges: 6 } },
  { id: 3, name: 'Piotr Zielinski', planId: 3, gymId: 3, dates: ['2026-04-12', '2026-04-24', '2026-05-06'], templates: ['power_lower', 'power_upper', 'power_lower'], bodyWeight: [79.4, 79.1, 79.2], metrics: { bench: 125, squat: 185, deadlift: 225, row: 95, overhead: 72, leg_press: 260 } },
  { id: 4, name: 'Katarzyna Wisniewska', planId: 6, gymId: 4, dates: ['2026-04-10', '2026-04-23', '2026-05-04'], templates: ['runner', 'strength_support', 'balanced_upper'], bodyWeight: [64.4, 64.1, 64.0], metrics: { squat: 70, db_press: 16, calves: 28, cable_fly: 17, cable_row: 35, lateral: 5, lunges: 8 } },
  { id: 5, name: 'Marek Lewandowski', planId: 4, gymId: 5, dates: ['2026-04-13', '2026-04-26', '2026-05-07'], templates: ['balanced_upper', 'balanced_lower', 'power_upper'], bodyWeight: [92.4, 91.9, 91.6], metrics: { cable_fly: 28, cable_row: 60, lateral: 12, leg_press: 220, hip: 170, bench: 110, row: 85, overhead: 65 } },
  { id: 6, name: 'Olga Duda', planId: 2, gymId: 6, dates: ['2026-04-09', '2026-04-18', '2026-05-02'], templates: ['beginner_a', 'beginner_b', 'beginner_c'], bodyWeight: [59.2, 58.9, 58.7], metrics: { leg_press: 85, db_press: 9, squat: 30, cable_row: 26, hip: 45, overhead: 16, lunges: 5 } },
  { id: 7, name: 'Tomasz Wozniak', planId: 6, gymId: 1, dates: ['2026-04-12', '2026-04-21', '2026-05-01'], templates: ['runner', 'strength_support', 'runner'], bodyWeight: [79.5, 79.2, 78.9], metrics: { squat: 85, db_press: 18, calves: 32, lunges: 10 } },
  { id: 8, name: 'Natalia Kaczmarek', planId: 5, gymId: 7, dates: ['2026-04-11', '2026-04-25', '2026-05-06'], templates: ['glutes', 'balanced_lower', 'glutes'], bodyWeight: [64.1, 63.9, 63.8], metrics: { hip: 120, split_squat: 14, lunges: 10, leg_press: 160 } },
  { id: 9, name: 'Damian Szymanski', planId: 3, gymId: 8, dates: ['2026-04-14', '2026-04-27', '2026-05-07'], templates: ['power_upper', 'power_lower', 'power_upper'], bodyWeight: [88.2, 88.3, 88.4], metrics: { bench: 135, squat: 190, deadlift: 230, row: 100, overhead: 75, leg_press: 280 } },
  { id: 10, name: 'Julia Mazur', planId: 8, gymId: 2, dates: ['2026-04-09', '2026-04-19', '2026-05-04'], templates: ['beginner_a', 'beginner_b', 'beginner_c'], bodyWeight: [57.6, 57.2, 56.9], metrics: { leg_press: 95, db_press: 8, squat: 32, cable_row: 25, hip: 48, overhead: 15, lunges: 5 } },
  { id: 11, name: 'Kamil Pawlak', planId: 7, gymId: 10, dates: ['2026-04-12', '2026-04-24', '2026-05-05'], templates: ['upper_push', 'balanced_lower', 'upper_pull'], bodyWeight: [85.4, 85.8, 86.2], metrics: { bench: 102, db_press: 34, lateral: 11, leg_press: 210, hip: 140, row: 78, lat_pull: 72 } },
  { id: 12, name: 'Zuzanna Grabowska', planId: 10, gymId: 7, dates: ['2026-04-10', '2026-04-22', '2026-05-04'], templates: ['balanced_upper', 'balanced_lower', 'glutes'], bodyWeight: [62.9, 62.5, 62.1], metrics: { cable_fly: 20, cable_row: 38, lateral: 6, leg_press: 130, hip: 95, split_squat: 10, lunges: 7 } },
  { id: 13, name: 'Michal Jablonski', planId: 9, gymId: 9, dates: ['2026-04-13', '2026-04-26', '2026-05-07'], templates: ['power_upper', 'upper_pull', 'power_upper'], bodyWeight: [84.8, 84.6, 84.7], metrics: { bench: 130, row: 88, overhead: 68, lat_pull: 78 } },
  { id: 14, name: 'Karolina Czarnecka', planId: 8, gymId: 4, dates: ['2026-04-11', '2026-04-23', '2026-05-05'], templates: ['beginner_a', 'balanced_upper', 'beginner_b'], bodyWeight: [65.8, 65.5, 65.4], metrics: { leg_press: 100, db_press: 10, cable_fly: 16, cable_row: 28, lateral: 4, squat: 35 } },
  { id: 15, name: 'Pawel Nowicki', planId: 7, gymId: 5, dates: ['2026-04-12', '2026-04-25', '2026-05-07'], templates: ['upper_push', 'balanced_lower', 'upper_pull'], bodyWeight: [82.1, 81.8, 81.5], metrics: { bench: 92, db_press: 30, lateral: 8, leg_press: 190, hip: 130, row: 70, lat_pull: 65 } },
  { id: 16, name: 'Alicja Ostrowska', planId: 8, gymId: 6, dates: ['2026-04-09', '2026-04-21', '2026-05-03'], templates: ['beginner_a', 'runner', 'beginner_b'], bodyWeight: [58.6, 58.2, 57.9], metrics: { leg_press: 88, db_press: 9, squat: 28, cable_row: 24, lunges: 5 } },
  { id: 17, name: 'Sebastian Krupa', planId: 9, gymId: 10, dates: ['2026-04-15', '2026-04-28', '2026-05-07'], templates: ['power_lower', 'power_upper', 'power_lower'], bodyWeight: [93.5, 93.2, 93.3], metrics: { bench: 145, squat: 205, deadlift: 245, row: 102, overhead: 78, leg_press: 300 } },
  { id: 18, name: 'Monika Sobczak', planId: 6, gymId: 4, dates: ['2026-04-10', '2026-04-20', '2026-05-02'], templates: ['runner', 'balanced_upper', 'strength_support'], bodyWeight: [66.4, 66.2, 66.0], metrics: { cable_fly: 18, cable_row: 34, lateral: 5, squat: 65, db_press: 15, calves: 30, lunges: 8 } },
  { id: 19, name: 'Bartosz Malinowski', planId: 8, gymId: 8, dates: ['2026-04-11', '2026-04-22', '2026-05-06'], templates: ['beginner_b', 'beginner_c', 'beginner_a'], bodyWeight: [71.9, 72.3, 72.8], metrics: { squat: 45, cable_row: 32, hip: 62, overhead: 22, lunges: 7, leg_press: 120, db_press: 14 } },
  { id: 20, name: 'Ewa Michalak', planId: 10, gymId: 1, dates: ['2026-04-12', '2026-04-24', '2026-05-07'], templates: ['balanced_upper', 'glutes', 'balanced_lower'], bodyWeight: [68.3, 67.8, 67.2], metrics: { cable_fly: 22, cable_row: 40, lateral: 6, hip: 105, split_squat: 12, lunges: 8, leg_press: 145 } }
];

const progressDates = ['2026-04-10', '2026-04-24', '2026-05-07'];

const extraProgress = {
  1: [
    { type: 'bench_press', values: [92.5, 95], dates: ['2026-04-21', '2026-05-07'], unit: 'kg', note: 'Staly progres na klatce.' },
    { type: 'squat', values: [135, 140], dates: ['2026-04-21', '2026-05-07'], unit: 'kg', note: 'Technika coraz pewniejsza.' }
  ],
  2: [
    { type: 'waist_circumference', values: [74, 72], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Pas stopniowo spada.' }
  ],
  3: [
    { type: 'squat', values: [180, 185], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Mocny progres pod zawody.' },
    { type: 'deadlift', values: [220, 225], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Martwy trzymany w ryzach.' }
  ],
  4: [
    { type: 'arm_circumference', values: [29, 29.5], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Maly, ale staly progres.' }
  ],
  5: [
    { type: 'bench_press', values: [107.5, 110], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Gora ciala idzie do przodu.' }
  ],
  7: [
    { type: 'waist_circumference', values: [84, 82], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Lepsza forma pod bieganie.' }
  ],
  8: [
    { type: 'arm_circumference', values: [28.5, 29], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Calkiem rowny progres.' }
  ],
  9: [
    { type: 'bench_press', values: [132.5, 135], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Powerbuilding robi swoje.' },
    { type: 'squat', values: [187.5, 190], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Coraz mocniejsze nogi.' }
  ],
  11: [
    { type: 'chest_circumference', values: [106, 107], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Masa rosnie spokojnie.' }
  ],
  12: [
    { type: 'waist_circumference', values: [75, 73], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Redukcja idzie zgodnie z planem.' }
  ],
  13: [
    { type: 'bench_press', values: [127.5, 130], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Bench meetup dobrze wplynal na wynik.' }
  ],
  15: [
    { type: 'body_weight', values: [82.1, 81.8], dates: ['2026-04-17', '2026-05-01'], unit: 'kg', note: 'Lekki spadek przy dobrej sile.' }
  ],
  17: [
    { type: 'deadlift', values: [240, 245], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Solidne wejscie w koncowke cyklu.' },
    { type: 'squat', values: [200, 205], dates: ['2026-04-24', '2026-05-07'], unit: 'kg', note: 'Przysiad trzyma poziom.' }
  ],
  20: [
    { type: 'waist_circumference', values: [79, 77], dates: ['2026-04-24', '2026-05-07'], unit: 'cm', note: 'Wyrazna poprawa w pasie.' }
  ]
};

const notesByTemplate = {
  upper_push: 'Mocny trening pchajacy, dobry kontakt z klatka i barkami.',
  upper_pull: 'Spokojna jednostka na plecy i grzbiet.',
  lower_strength: 'Dzien nog z ciezszym ruchem glownym.',
  beginner_a: 'Prosty trening na wejscie i budowanie regularnosci.',
  beginner_b: 'Czysta technika i brak spiny na ciezar.',
  beginner_c: 'Coraz pewniejsze ruchy i lepsze tempo.',
  glutes: 'Dobry trening dolu i czucie posladka.',
  runner: 'Krotkie wsparcie dla biegania i stabilizacji.',
  power_upper: 'Silowy upper z naciskiem na wynik.',
  power_lower: 'Ciezsza jednostka silowa pod nogi i tylna tasme.',
  balanced_upper: 'Objeciowy upper bez przesady z ciezarem.',
  balanced_lower: 'Dol ciala z balansem miedzy sila a objetoscia.',
  strength_support: 'Wsparcie silowe bez zajezdzania regeneracji.'
};

const publicPlans = [
  { id: 1, name: 'Push Pull Legs Pro', user_id: 1 },
  { id: 2, name: 'Full Body Start', user_id: 2 },
  { id: 4, name: 'Lean Recomp', user_id: 5 },
  { id: 5, name: 'Strong Legs and Glutes', user_id: 8 },
  { id: 6, name: 'Biegacz plus Silownia', user_id: 7 },
  { id: 7, name: 'Upper Lower Base', user_id: 11 },
  { id: 8, name: 'Beginner Shape Up', user_id: 14 },
  { id: 10, name: 'Summer Cut Balance', user_id: 20 }
];

const userNames = Object.fromEntries(users.map((user) => [user.id, user.name]));

function roundWeight(value) {
  return Math.round(value * 2) / 2;
}

function weightForEntry(user, entry, sessionIndex) {
  if (entry.bodyweight) {
    return 0;
  }

  const source = user.metrics[entry.key] || 0;
  const progression = 1 + sessionIndex * 0.025;
  return roundWeight(source * entry.ratio * progression);
}

function buildSets(user, entry, sessionIndex) {
  return entry.reps.map((reps, repIndex) => ({
    weight: weightForEntry(user, entry, sessionIndex) + (entry.bodyweight ? 0 : repIndex * 2.5),
    reps: reps
  }));
}

function buildWorkoutLog(user, date, templateName, sessionIndex) {
  const template = workoutTemplates[templateName];
  const createdAtTime = sessionIndex === 0 ? '18:20:00' : (sessionIndex === 1 ? '19:05:00' : '18:45:00');
  const mood = sessionIndex === 2 ? 'bardzo dobry' : (sessionIndex === 1 ? 'dobry' : 'ok');

  return {
    user_id: user.id,
    training_date: date,
    plan_id: user.planId,
    gym_id: user.gymId,
    duration_minutes: 55 + (sessionIndex * 8) + (user.id % 4) * 4,
    mood: mood,
    notes: notesByTemplate[templateName],
    exercises: template.map((entry) => ({
      exercise_id: entry.exercise_id,
      name: exerciseCatalog[entry.exercise_id],
      sets: buildSets(user, entry, sessionIndex)
    })),
    created_at: `${date} ${createdAtTime}`
  };
}

const workoutLogs = [];
users.forEach((user) => {
  user.templates.forEach((templateName, index) => {
    workoutLogs.push(buildWorkoutLog(user, user.dates[index], templateName, index));
  });
});

const progressMeasurements = [];
users.forEach((user) => {
  user.bodyWeight.forEach((value, index) => {
    progressMeasurements.push({
      user_id: user.id,
      date: progressDates[index],
      type: 'body_weight',
      value: value,
      unit: 'kg',
      note: index === 0 ? 'Punkt startowy miesiaca.' : (index === 1 ? 'Srodek miesiaca.' : 'Koncowka miesiaca.'),
      created_at: `${progressDates[index]} 07:${String(10 + user.id).padStart(2, '0')}:00`
    });
  });

  (extraProgress[user.id] || []).forEach((entry) => {
    entry.values.forEach((value, index) => {
      progressMeasurements.push({
        user_id: user.id,
        date: entry.dates[index],
        type: entry.type,
        value: value,
        unit: entry.unit,
        note: entry.note,
        created_at: `${entry.dates[index]} 08:${String(5 + user.id).padStart(2, '0')}:00`
      });
    });
  });
});

const planComments = [
  { plan_id: 1, user_id: 2, content: 'Bardzo czytelny plan pod mase i regularny progres.', rating: 5, created_at: '2026-04-14 19:00:00' },
  { plan_id: 1, user_id: 5, content: 'Dobry balans miedzy bojem glownym a dodatkami.', rating: 4, created_at: '2026-04-18 19:20:00' },
  { plan_id: 1, user_id: 11, content: 'Na spokojnie da sie trzymac ten uklad przez dluzszy czas.', rating: 5, created_at: '2026-04-27 20:10:00' },
  { plan_id: 2, user_id: 6, content: 'Dobrze rozpisany start dla nowych osob.', rating: 5, created_at: '2026-04-15 18:40:00' },
  { plan_id: 2, user_id: 10, content: 'Wlasnie taki prosty plan byl mi potrzebny na poczatek.', rating: 4, created_at: '2026-04-21 18:20:00' },
  { plan_id: 4, user_id: 1, content: 'Fajny plan, kiedy chce sie utrzymac forme i sile naraz.', rating: 4, created_at: '2026-04-16 20:00:00' },
  { plan_id: 4, user_id: 12, content: 'Dobra objetosc i sensowne tempo progresu.', rating: 5, created_at: '2026-04-24 20:05:00' },
  { plan_id: 4, user_id: 15, content: 'Najbardziej podoba mi sie prostota calego ukladu.', rating: 4, created_at: '2026-05-02 20:10:00' },
  { plan_id: 5, user_id: 4, content: 'Super plan na dol ciala i mocne czucie ruchu.', rating: 5, created_at: '2026-04-17 19:05:00' },
  { plan_id: 5, user_id: 20, content: 'Bardzo czytelny split pod nogi i posladki.', rating: 4, created_at: '2026-04-29 19:10:00' },
  { plan_id: 6, user_id: 18, content: 'Dobrze laczy bieganie z silownia bez przesady.', rating: 5, created_at: '2026-04-18 18:30:00' },
  { plan_id: 6, user_id: 4, content: 'Fajny uklad dla kogos, kto nie chce ciagle dokladac kolejnego dnia.', rating: 4, created_at: '2026-05-01 18:35:00' },
  { plan_id: 7, user_id: 5, content: 'Klasyka, ale zrobiona porzadnie i bez kombinowania.', rating: 5, created_at: '2026-04-20 20:40:00' },
  { plan_id: 7, user_id: 1, content: 'Dobry plan dla kogos, kto lubi prosty rytm tygodnia.', rating: 4, created_at: '2026-05-03 20:45:00' },
  { plan_id: 8, user_id: 2, content: 'Przyjazny plan dla poczatkujacych i bez chaosu.', rating: 5, created_at: '2026-04-23 18:10:00' },
  { plan_id: 8, user_id: 16, content: 'Dobrze, ze nie ma tu za duzo cwiczen na raz.', rating: 4, created_at: '2026-05-05 18:15:00' },
  { plan_id: 10, user_id: 12, content: 'Dobrze sie sprawdza przy redukcji i trzymaniu rytmu.', rating: 4, created_at: '2026-04-26 19:50:00' },
  { plan_id: 10, user_id: 8, content: 'Maly plan, ale bardzo praktyczny na zajety tydzien.', rating: 4, created_at: '2026-05-06 19:55:00' }
].map((comment) => ({
  ...comment,
  user_name: userNames[comment.user_id]
}));

const notifications = [
  { user_id: 1, type: 'event_joined', title: 'Nowi uczestnicy wydarzenia', content: 'Anna, Marek i Ewa dolaczyli do Push po pracy.', is_read: false, created_at: '2026-05-04 08:30:00' },
  { user_id: 2, type: 'friendship_accepted', title: 'Zaproszenie zaakceptowane', content: 'Julia zaakceptowala Twoje zaproszenie do znajomych.', is_read: true, created_at: '2026-04-08 08:20:00' },
  { user_id: 3, type: 'event_joined', title: 'Squat and coffee sie zapelnia', content: 'Damian i Sebastian potwierdzili udzial.', is_read: false, created_at: '2026-05-04 09:25:00' },
  { user_id: 4, type: 'friend_request', title: 'Nowe zaproszenie do znajomych', content: 'Tomasz wyslal Ci zaproszenie.', is_read: false, created_at: '2026-05-02 08:02:00' },
  { user_id: 5, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Pawel zostawil komentarz do Lean Recomp.', is_read: true, created_at: '2026-05-02 20:12:00' },
  { user_id: 6, type: 'event_joined', title: 'Nowy uczestnik wydarzenia', content: 'Anna zapisala sie na Poranny FBW.', is_read: true, created_at: '2026-05-04 08:32:00' },
  { user_id: 7, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Monika ocenila Biegacz plus Silownia na 5/5.', is_read: false, created_at: '2026-04-18 18:32:00' },
  { user_id: 8, type: 'event_joined', title: 'Glutes session ma chetnych', content: 'Kasia i Ewa dolaczyly do wydarzenia.', is_read: false, created_at: '2026-05-04 09:45:00' },
  { user_id: 9, type: 'friendship_accepted', title: 'Zaproszenie zaakceptowane', content: 'Jan jest teraz w Twojej paczce znajomych.', is_read: true, created_at: '2026-04-06 09:18:00' },
  { user_id: 10, type: 'friend_request', title: 'Nowe zaproszenie do znajomych', content: 'Pawel wyslal Ci zaproszenie do znajomych.', is_read: false, created_at: '2026-05-02 09:12:00' },
  { user_id: 11, type: 'event_joined', title: 'Upper lower ekipa rosnie', content: 'Tomasz, Pawel i Monika potwierdzili udzial.', is_read: false, created_at: '2026-05-04 11:05:00' },
  { user_id: 12, type: 'plan_comment', title: 'Nowy komentarz do planu', content: 'Ewa napisala opinie o Summer Cut Balance.', is_read: true, created_at: '2026-05-06 20:00:00' },
  { user_id: 13, type: 'event_joined', title: 'Bench meetup', content: 'Jan, Piotr, Damian i Kamil dolaczyli do spotkania.', is_read: false, created_at: '2026-05-04 10:45:00' },
  { user_id: 14, type: 'friend_request', title: 'Nowe zaproszenie do znajomych', content: 'Marek wyslal Ci zaproszenie.', is_read: false, created_at: '2026-05-02 08:32:00' },
  { user_id: 15, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Marek skomentowal Upper Lower Base.', is_read: true, created_at: '2026-04-20 20:42:00' },
  { user_id: 16, type: 'friendship_accepted', title: 'Zaproszenie zaakceptowane', content: 'Karolina zaakceptowala zaproszenie do znajomych.', is_read: true, created_at: '2026-04-12 13:08:00' },
  { user_id: 17, type: 'event_joined', title: 'Sobotni trojboj', content: 'Piotr, Damian i Michal potwierdzili udzial.', is_read: false, created_at: '2026-05-04 11:45:00' },
  { user_id: 18, type: 'event_joined', title: 'Cardio plus core', content: 'Kasia, Alicja i Ewa dolaczyly do wydarzenia.', is_read: false, created_at: '2026-05-04 12:12:00' },
  { user_id: 19, type: 'friend_request', title: 'Nowe zaproszenie do znajomych', content: 'Anna nie odpowiedziala jeszcze na Twoje zaproszenie.', is_read: false, created_at: '2026-05-02 08:42:00' },
  { user_id: 20, type: 'event_joined', title: 'Masz weekendowa ekipe', content: 'Jan i Natalia zapisali sie na wspolny trening.', is_read: true, created_at: '2026-05-04 09:55:00' },
  { user_id: 1, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Kamil ocenil Push Pull Legs Pro na 5/5.', is_read: false, created_at: '2026-04-27 20:12:00' },
  { user_id: 8, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Katarzyna pochwalila Strong Legs and Glutes.', is_read: true, created_at: '2026-04-17 19:07:00' },
  { user_id: 2, type: 'plan_comment', title: 'Nowa opinia o planie', content: 'Olga skomentowala Full Body Start.', is_read: false, created_at: '2026-04-15 18:42:00' },
  { user_id: 11, type: 'friend_request', title: 'Nowe zaproszenie do znajomych', content: 'Ewa wyslala Ci zaproszenie do znajomych.', is_read: false, created_at: '2026-05-02 08:22:00' }
];

const activityLogs = [];

publicPlans.forEach((plan, index) => {
  activityLogs.push({
    user_id: plan.user_id,
    action: 'created_training_plan',
    details: { plan_id: plan.id, plan_name: plan.name },
    created_at: `2026-04-${String(9 + index).padStart(2, '0')} 14:${String(10 + index).padStart(2, '0')}:00`
  });
});

workoutLogs.forEach((log) => {
  activityLogs.push({
    user_id: log.user_id,
    action: 'created_workout_log',
    details: { training_date: log.training_date, plan_id: log.plan_id },
    created_at: log.created_at
  });
});

[
  { user_id: 2, action: 'sent_friend_request', details: { receiver_id: 20, receiver_name: 'Ewa Michalak' }, created_at: '2026-04-07 08:18:00' },
  { user_id: 4, action: 'sent_friend_request', details: { receiver_id: 7, receiver_name: 'Tomasz Wozniak' }, created_at: '2026-05-02 08:00:00' },
  { user_id: 8, action: 'created_event', details: { event_id: 4, title: 'Glutes session' }, created_at: '2026-05-03 11:30:00' },
  { user_id: 11, action: 'created_event', details: { event_id: 6, title: 'Upper lower ekipa' }, created_at: '2026-05-03 11:50:00' },
  { user_id: 13, action: 'created_event', details: { event_id: 5, title: 'Bench meetup' }, created_at: '2026-05-03 11:40:00' },
  { user_id: 17, action: 'created_event', details: { event_id: 7, title: 'Sobotni trojboj' }, created_at: '2026-05-03 12:00:00' },
  { user_id: 18, action: 'created_event', details: { event_id: 8, title: 'Cardio plus core' }, created_at: '2026-05-03 12:10:00' },
  { user_id: 1, action: 'joined_event', details: { event_id: 5, title: 'Bench meetup' }, created_at: '2026-05-04 10:10:00' },
  { user_id: 3, action: 'joined_event', details: { event_id: 7, title: 'Sobotni trojboj' }, created_at: '2026-05-04 11:20:00' },
  { user_id: 9, action: 'joined_event', details: { event_id: 3, title: 'Squat and coffee' }, created_at: '2026-05-04 09:20:00' },
  { user_id: 12, action: 'commented_plan', details: { plan_id: 4, rating: 5 }, created_at: '2026-04-24 20:05:00' },
  { user_id: 18, action: 'commented_plan', details: { plan_id: 6, rating: 5 }, created_at: '2026-04-18 18:32:00' }
].forEach((entry) => activityLogs.push(entry));

progressMeasurements
  .filter((entry) => entry.type !== 'body_weight')
  .slice(0, 18)
  .forEach((entry) => {
    activityLogs.push({
      user_id: entry.user_id,
      action: 'added_progress_measurement',
      details: { type: entry.type, value: entry.value },
      created_at: entry.created_at
    });
  });

database.workout_logs.insertMany(workoutLogs);
database.progress_measurements.insertMany(progressMeasurements);
database.plan_comments.insertMany(planComments);
database.notifications.insertMany(notifications);
database.activity_logs.insertMany(activityLogs);

print('GymBRO MongoDB seed inserted into gymbro_mongo');

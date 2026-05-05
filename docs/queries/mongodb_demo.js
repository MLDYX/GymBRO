// GymBRO - query demo do MongoDB
// Uruchom w mongosh:
// load("docs/queries/mongodb_demo.js")

const database = db.getSiblingDB('gymbro_mongo');

print('\n1. Wszystkie kolekcje w bazie gymbro_mongo');
printjson(database.getCollectionNames());

print('\n2. Ostatnie logi treningowe uzytkownika 1');
print('Pokazuje dokumenty z zagniezdzonymi cwiczeniami i seriami.');
printjson(
  database.workout_logs
    .find({ user_id: 1 }, { user_id: 1, training_date: 1, mood: 1, exercises: 1, _id: 0 })
    .sort({ training_date: -1 })
    .limit(2)
    .toArray()
);

print('\n3. Tylko logi, w ktorych wystepuje cwiczenie "Przysiad ze sztanga"');
print('Przyklad filtrowania po polu zagniezdzonym w tablicy documents.');
printjson(
  database.workout_logs
    .find({ 'exercises.name': 'Przysiad ze sztanga' }, { user_id: 1, training_date: 1, exercises: 1, _id: 0 })
    .toArray()
);

print('\n4. Pomiary progresu typu body_weight dla uzytkownika 1');
print('Pokazuje proste filtrowanie dokumentow po typie i uzytkowniku.');
printjson(
  database.progress_measurements
    .find({ user_id: 1, type: 'body_weight' }, { _id: 0 })
    .sort({ date: 1 })
    .toArray()
);

print('\n5. Komentarze do planu 1');
print('Pokazuje komentarze zapisane poza PostgreSQL.');
printjson(
  database.plan_comments
    .find({ plan_id: 1 }, { _id: 0 })
    .sort({ created_at: 1 })
    .toArray()
);

print('\n6. Nieprzeczytane powiadomienia uzytkownika 1');
print('Przyklad danych aplikacyjnych dobrze pasujacych do modelu dokumentowego.');
printjson(
  database.notifications
    .find({ user_id: 1, is_read: false }, { _id: 0 })
    .sort({ created_at: -1 })
    .toArray()
);

print('\n7. Ostatnie aktywnosci uzytkownika 3');
printjson(
  database.activity_logs
    .find({ user_id: 3 }, { _id: 0 })
    .sort({ created_at: -1 })
    .limit(3)
    .toArray()
);

print('\n8. Agregacja: srednia ocena dla kazdego planu');
print('To jest prosty przyklad aggregate() do pokazania na obronie.');
printjson(
  database.plan_comments.aggregate([
    {
      $group: {
        _id: '$plan_id',
        average_rating: { $avg: '$rating' },
        comments_count: { $sum: 1 }
      }
    },
    { $sort: { _id: 1 } }
  ]).toArray()
);

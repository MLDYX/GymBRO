# GymBRO - checklista przed pokazem

## 1. Srodowisko

- Apache w XAMPP dziala
- PostgreSQL dziala
- MongoDB dziala
- aplikacja otwiera sie pod `http://localhost/gymbro`

## 2. Dane

- `postgres_seed.sql` jest wgrany
- `mongo_seed.js` jest wgrany
- konto demo loguje sie poprawnie
- dashboard nie jest pusty

## 3. Konto do pokazu

Najbezpieczniej zaczac od:

- login: `jan@gymbro.local`
- haslo: `password123`

Dodatkowe konta do pokazania roznych scenariuszy:

- `piotr@gymbro.local`
- `natalia@gymbro.local`
- `damian@gymbro.local`
- `ewa@gymbro.local`

## 4. Ekrany do klikniecia

Kolejnosc, ktora dobrze wyglada na zywo:

1. Dashboard
2. Znajomi
3. Spotkania
4. Plany
5. Dziennik treningowy
6. Progres
7. Profil

## 5. Co otworzyc obok aplikacji

- `pgAdmin`
- `mongosh` albo MongoDB Compass
- `docs/queries/postgresql_demo.sql`
- `docs/queries/mongodb_demo.js`
- `database/postgres.php`
- `database/mongo.php`

## 6. Co powiedziec w 30 sekund

GymBRO to aplikacja dla znajomych z silowni.
PostgreSQL obsluguje dane relacyjne, takie jak uzytkownicy, profile, znajomosci,
wydarzenia i plany.
MongoDB obsluguje dane elastyczne, takie jak logi treningowe, serie cwiczen,
progres, komentarze, powiadomienia i aktywnosci.

## 7. Najwazniejsze query do pokazania

PostgreSQL:

- uzytkownicy z profilami
- wydarzenia z uczestnikami
- plan -> dni -> cwiczenia

MongoDB:

- workout log z cwiczeniami i seriami
- progres dla jednego uzytkownika
- agregacja sredniej oceny planow

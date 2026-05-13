<?php

declare(strict_types=1);

/**
 * Tworzy i zwraca wspolne polaczenie PDO do PostgreSQL.
 */
function postgres_connection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('PGHOST') ?: 'localhost';
    $port = getenv('PGPORT') ?: '5432';
    $dbname = getenv('PGDATABASE') ?: 'gymbro_db';
    $user = getenv('PGUSER') ?: 'postgres';
    $password = getenv('PGPASSWORD') ?: 'postgres';

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);

    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}

/**
 * Tworzy nowe konto uzytkownika i zwraca jego ID.
 */
function postgres_create_user(string $name, string $email, string $passwordHash): int
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash) RETURNING id'
    );
    $statement->execute([
        'name' => $name,
        'email' => mb_strtolower($email),
        'password_hash' => $passwordHash,
    ]);

    return (int) $statement->fetchColumn();
}

/**
 * Pobiera uzytkownika po adresie e-mail.
 */
function postgres_find_user_by_email(string $email): ?array
{
    $statement = postgres_connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => mb_strtolower($email)]);
    $user = $statement->fetch();

    return $user ?: null;
}

/**
 * Pobiera jednego uzytkownika razem z danymi z profilu.
 */
function postgres_find_user_by_id(int $id): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT u.*, up.age, up.height_cm, up.weight_kg, up.training_level, up.training_experience, up.goal, up.bio, up.avatar_path, up.onboarding_completed
         FROM users u
         LEFT JOIN user_profiles up ON up.user_id = u.id
         WHERE u.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $user = $statement->fetch();

    return $user ?: null;
}

/**
 * Zwraca wszystkich innych uzytkownikow wraz ze skrotem profilu.
 */
function postgres_get_all_users_except(int $userId): array
{
    $statement = postgres_connection()->prepare(
        'SELECT u.id, u.name, u.email, up.training_level, up.training_experience, up.goal, up.bio, up.avatar_path
         FROM users u
         LEFT JOIN user_profiles up ON up.user_id = u.id
         WHERE u.id <> :user_id
         ORDER BY u.name ASC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Zaklada pusty profil dla nowego uzytkownika.
 */
function postgres_create_empty_profile(int $userId): void
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO user_profiles (user_id) VALUES (:user_id) ON CONFLICT (user_id) DO NOTHING'
    );
    $statement->execute(['user_id' => $userId]);
}

/**
 * Pobiera profil uzytkownika po jego ID.
 */
function postgres_find_profile_by_user_id(int $userId): ?array
{
    $statement = postgres_connection()->prepare('SELECT * FROM user_profiles WHERE user_id = :user_id LIMIT 1');
    $statement->execute(['user_id' => $userId]);
    $profile = $statement->fetch();

    return $profile ?: null;
}

/**
 * Aktualizuje dane profilu i stan onboardingu.
 */
function postgres_update_profile(int $userId, array $data): void
{
    $existingProfile = postgres_find_profile_by_user_id($userId) ?? [];
    $onboardingCompleted = array_key_exists('onboarding_completed', $data)
        ? (bool) $data['onboarding_completed']
        : (bool) ($existingProfile['onboarding_completed'] ?? false);

    $statement = postgres_connection()->prepare(
        'UPDATE user_profiles
         SET age = :age,
             height_cm = :height_cm,
             weight_kg = :weight_kg,
             training_level = :training_level,
             training_experience = :training_experience,
             goal = :goal,
             bio = :bio,
             avatar_path = :avatar_path,
             onboarding_completed = :onboarding_completed,
             updated_at = CURRENT_TIMESTAMP
         WHERE user_id = :user_id'
    );

    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':age', $data['age'] !== '' ? (int) $data['age'] : null, $data['age'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $statement->bindValue(':height_cm', $data['height_cm'] !== '' ? (int) $data['height_cm'] : null, $data['height_cm'] !== '' ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $statement->bindValue(':weight_kg', $data['weight_kg'] !== '' ? (string) $data['weight_kg'] : null, $data['weight_kg'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':training_level', trim((string) ($data['training_level'] ?? '')) ?: null, trim((string) ($data['training_level'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':training_experience', trim((string) ($data['training_experience'] ?? '')) ?: null, trim((string) ($data['training_experience'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':goal', trim((string) ($data['goal'] ?? '')) ?: null, trim((string) ($data['goal'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':bio', trim((string) ($data['bio'] ?? '')) ?: null, trim((string) ($data['bio'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':avatar_path', trim((string) ($data['avatar_path'] ?? '')) ?: null, trim((string) ($data['avatar_path'] ?? '')) !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $statement->bindValue(':onboarding_completed', $onboardingCompleted, PDO::PARAM_BOOL);
    $statement->execute();
}

/**
 * Oznacza onboarding uzytkownika jako zakonczony.
 */
function postgres_complete_onboarding(int $userId): void
{
    $statement = postgres_connection()->prepare(
        'UPDATE user_profiles SET onboarding_completed = TRUE, updated_at = CURRENT_TIMESTAMP WHERE user_id = :user_id'
    );
    $statement->execute(['user_id' => $userId]);
}

/**
 * Wysyla zaproszenie do znajomych, jesli nie ma jeszcze relacji.
 */
function postgres_send_friend_request(int $requesterId, int $receiverId): bool
{
    if ($requesterId === $receiverId || postgres_are_friends_or_pending($requesterId, $receiverId)) {
        return false;
    }

    $statement = postgres_connection()->prepare(
        'INSERT INTO friendships (requester_id, receiver_id, status)
         VALUES (:requester_id, :receiver_id, :status)'
    );

    return $statement->execute([
        'requester_id' => $requesterId,
        'receiver_id' => $receiverId,
        'status' => 'pending',
    ]);
}

/**
 * Akceptuje oczekujace zaproszenie do znajomych.
 */
function postgres_accept_friend_request(int $friendshipId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        "UPDATE friendships
         SET status = 'accepted'
         WHERE id = :id AND receiver_id = :user_id AND status = 'pending'"
    );
    $statement->execute(['id' => $friendshipId, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Odrzuca oczekujace zaproszenie do znajomych.
 */
function postgres_reject_friend_request(int $friendshipId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        "UPDATE friendships
         SET status = 'rejected'
         WHERE id = :id AND receiver_id = :user_id AND status = 'pending'"
    );
    $statement->execute(['id' => $friendshipId, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Zwraca liste zaakceptowanych znajomych uzytkownika.
 */
function postgres_get_friends(int $userId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT f.id, f.created_at, u.id AS user_id, u.name, u.email, up.training_level, up.goal, up.avatar_path
         FROM friendships f
         JOIN users u ON u.id = CASE WHEN f.requester_id = :user_id THEN f.receiver_id ELSE f.requester_id END
         LEFT JOIN user_profiles up ON up.user_id = u.id
         WHERE (f.requester_id = :user_id OR f.receiver_id = :user_id) AND f.status = 'accepted'
         ORDER BY u.name ASC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Zwraca zaproszenia przychodzace dla uzytkownika.
 */
function postgres_get_incoming_friend_requests(int $userId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT f.*, u.name, u.email
         FROM friendships f
         JOIN users u ON u.id = f.requester_id
         WHERE f.receiver_id = :user_id AND f.status = 'pending'
         ORDER BY f.created_at DESC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Zwraca zaproszenia wyslane przez uzytkownika.
 */
function postgres_get_outgoing_friend_requests(int $userId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT f.*, u.name, u.email
         FROM friendships f
         JOIN users u ON u.id = f.receiver_id
         WHERE f.requester_id = :user_id AND f.status = 'pending'
         ORDER BY f.created_at DESC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Sprawdza, czy miedzy dwoma osobami istnieje juz relacja.
 */
function postgres_are_friends_or_pending(int $userA, int $userB): bool
{
    $statement = postgres_connection()->prepare(
        'SELECT 1
         FROM friendships
         WHERE (requester_id = :user_a AND receiver_id = :user_b)
            OR (requester_id = :user_b AND receiver_id = :user_a)
         LIMIT 1'
    );
    $statement->execute([
        'user_a' => $userA,
        'user_b' => $userB,
    ]);

    return (bool) $statement->fetchColumn();
}

/**
 * Liczy zaakceptowanych znajomych danego uzytkownika.
 */
function postgres_count_friends(int $userId): int
{
    $statement = postgres_connection()->prepare(
        "SELECT COUNT(*) FROM friendships
         WHERE (requester_id = :user_id OR receiver_id = :user_id) AND status = 'accepted'"
    );
    $statement->execute(['user_id' => $userId]);

    return (int) $statement->fetchColumn();
}

/**
 * Pobiera jeden rekord znajomosci po jego ID.
 */
function postgres_find_friendship_by_id(int $id): ?array
{
    $statement = postgres_connection()->prepare('SELECT * FROM friendships WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $friendship = $statement->fetch();

    return $friendship ?: null;
}

/**
 * Zwraca same ID znajomych potrzebne np. do feedu aktywnosci.
 */
function postgres_get_friend_user_ids(int $userId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT CASE WHEN requester_id = :user_id THEN receiver_id ELSE requester_id END AS friend_user_id
         FROM friendships
         WHERE (requester_id = :user_id OR receiver_id = :user_id) AND status = 'accepted'"
    );
    $statement->execute(['user_id' => $userId]);

    return array_map(static fn(array $row): int => (int) $row['friend_user_id'], $statement->fetchAll());
}

/**
 * Zwraca wszystkie silownie razem z nazwa wlasciciela.
 */
function postgres_get_all_gyms(): array
{
    $statement = postgres_connection()->query(
        'SELECT g.*, u.name AS owner_name
         FROM gyms g
         LEFT JOIN users u ON u.id = g.user_id
         ORDER BY g.created_at DESC'
    );

    return $statement->fetchAll();
}

/**
 * Dodaje nowa silownie do PostgreSQL.
 */
function postgres_create_gym(int $userId, array $data): void
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO gyms (user_id, name, city, address, description)
         VALUES (:user_id, :name, :city, :address, :description)'
    );
    $statement->execute([
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'city' => trim((string) $data['city']),
        'address' => trim((string) $data['address']) ?: null,
        'description' => trim((string) $data['description']) ?: null,
    ]);
}

/**
 * Aktualizuje tylko silownie nalezaca do zalogowanego uzytkownika.
 */
function postgres_update_gym(int $id, int $userId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE gyms
         SET name = :name, city = :city, address = :address, description = :description
         WHERE id = :id AND user_id = :user_id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'city' => trim((string) $data['city']),
        'address' => trim((string) $data['address']) ?: null,
        'description' => trim((string) $data['description']) ?: null,
    ]);

    return $statement->rowCount() > 0;
}

/**
 * Usuwa silownie tylko wtedy, gdy nalezy do danego uzytkownika.
 */
function postgres_delete_gym(int $id, int $userId): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM gyms WHERE id = :id AND user_id = :user_id');
    $statement->execute(['id' => $id, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Pobiera jedna silownie razem z nazwa wlasciciela.
 */
function postgres_find_gym(int $id): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT g.*, u.name AS owner_name
         FROM gyms g
         LEFT JOIN users u ON u.id = g.user_id
         WHERE g.id = :id LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $gym = $statement->fetch();

    return $gym ?: null;
}

/**
 * Zwraca wszystkie cwiczenia razem z nazwa autora.
 */
function postgres_get_all_exercises(): array
{
    $statement = postgres_connection()->query(
        'SELECT e.*, u.name AS owner_name
         FROM exercises e
         LEFT JOIN users u ON u.id = e.user_id
         ORDER BY e.created_at DESC'
    );

    return $statement->fetchAll();
}

/**
 * Dodaje nowe cwiczenie do bazy relacyjnej.
 */
function postgres_create_exercise(int $userId, array $data): void
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO exercises (user_id, name, muscle_group, equipment, description)
         VALUES (:user_id, :name, :muscle_group, :equipment, :description)'
    );
    $statement->execute([
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'muscle_group' => trim((string) $data['muscle_group']),
        'equipment' => trim((string) $data['equipment']) ?: null,
        'description' => trim((string) $data['description']) ?: null,
    ]);
}

/**
 * Aktualizuje cwiczenie tylko wtedy, gdy nalezy do autora.
 */
function postgres_update_exercise(int $id, int $userId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE exercises
         SET name = :name, muscle_group = :muscle_group, equipment = :equipment, description = :description
         WHERE id = :id AND user_id = :user_id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'muscle_group' => trim((string) $data['muscle_group']),
        'equipment' => trim((string) $data['equipment']) ?: null,
        'description' => trim((string) $data['description']) ?: null,
    ]);

    return $statement->rowCount() > 0;
}

/**
 * Usuwa cwiczenie nalezace do danego uzytkownika.
 */
function postgres_delete_exercise(int $id, int $userId): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM exercises WHERE id = :id AND user_id = :user_id');
    $statement->execute(['id' => $id, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Pobiera jedno cwiczenie razem z nazwa autora.
 */
function postgres_find_exercise(int $id): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT e.*, u.name AS owner_name
         FROM exercises e
         LEFT JOIN users u ON u.id = e.user_id
         WHERE e.id = :id LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $exercise = $statement->fetch();

    return $exercise ?: null;
}

/**
 * Zwraca liste nadchodzacych wydarzen razem z liczba uczestnikow.
 */
function postgres_get_upcoming_events(): array
{
    $statement = postgres_connection()->query(
        "SELECT we.*, g.name AS gym_name, g.city AS gym_city, u.name AS creator_name,
                COALESCE(COUNT(wep.id), 0) AS participants_count
         FROM workout_events we
         LEFT JOIN gyms g ON g.id = we.gym_id
         JOIN users u ON u.id = we.creator_id
         LEFT JOIN workout_event_participants wep ON wep.event_id = we.id
         WHERE we.event_date >= CURRENT_DATE AND we.status = 'planned'
         GROUP BY we.id, g.name, g.city, u.name
         ORDER BY we.event_date ASC, we.start_time ASC"
    );

    return $statement->fetchAll();
}

/**
 * Tworzy nowe wydarzenie treningowe i zwraca jego ID.
 */
function postgres_create_event(int $creatorId, array $data): int
{
    $statement = postgres_connection()->prepare(
        "INSERT INTO workout_events
         (creator_id, gym_id, title, description, event_date, start_time, max_participants, status)
         VALUES (:creator_id, :gym_id, :title, :description, :event_date, :start_time, :max_participants, 'planned')
         RETURNING id"
    );
    $statement->execute([
        'creator_id' => $creatorId,
        'gym_id' => $data['gym_id'] !== '' ? (int) $data['gym_id'] : null,
        'title' => trim((string) $data['title']),
        'description' => trim((string) $data['description']) ?: null,
        'event_date' => $data['event_date'],
        'start_time' => $data['start_time'],
        'max_participants' => max(2, (int) $data['max_participants']),
    ]);

    return (int) $statement->fetchColumn();
}

/**
 * Pobiera pelne dane jednego wydarzenia wraz z uczestnikami i wolnymi miejscami.
 */
function postgres_find_event(int $id): ?array
{
    $statement = postgres_connection()->prepare(
        "SELECT we.*, g.name AS gym_name, g.city AS gym_city, g.address AS gym_address, u.name AS creator_name,
                COALESCE(COUNT(wep.id), 0) AS participants_count
         FROM workout_events we
         LEFT JOIN gyms g ON g.id = we.gym_id
         JOIN users u ON u.id = we.creator_id
         LEFT JOIN workout_event_participants wep ON wep.event_id = we.id
         WHERE we.id = :id
         GROUP BY we.id, g.name, g.city, g.address, u.name
         LIMIT 1"
    );
    $statement->execute(['id' => $id]);
    $event = $statement->fetch();

    if (!$event) {
        return null;
    }

    $event['participants'] = postgres_get_event_participants($id);
    $event['free_slots'] = max(0, (int) $event['max_participants'] - (int) $event['participants_count']);

    return $event;
}

/**
 * Daje mozliwosc dolaczenia do wydarzenia, jesli sa jeszcze miejsca.
 */
function postgres_join_event(int $eventId, int $userId): bool
{
    $event = postgres_find_event($eventId);
    if (!$event || (int) $event['free_slots'] <= 0) {
        return false;
    }

    if (postgres_is_event_participant($eventId, $userId)) {
        return false;
    }

    $statement = postgres_connection()->prepare(
        'INSERT INTO workout_event_participants (event_id, user_id) VALUES (:event_id, :user_id)'
    );

    return $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);
}

/**
 * Pozwala opuscic zapisane wydarzenie treningowe.
 */
function postgres_leave_event(int $eventId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        'DELETE FROM workout_event_participants WHERE event_id = :event_id AND user_id = :user_id'
    );
    $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Pobiera liste uczestnikow danego wydarzenia.
 */
function postgres_get_event_participants(int $eventId): array
{
    $statement = postgres_connection()->prepare(
        'SELECT u.id, u.name, u.email
         FROM workout_event_participants wep
         JOIN users u ON u.id = wep.user_id
         WHERE wep.event_id = :event_id
         ORDER BY u.name ASC'
    );
    $statement->execute(['event_id' => $eventId]);

    return $statement->fetchAll();
}

/**
 * Liczy nadchodzace wydarzenia utworzone przez uzytkownika.
 */
function postgres_count_upcoming_events_for_user(int $userId): int
{
    $statement = postgres_connection()->prepare(
        "SELECT COUNT(*)
         FROM workout_events
         WHERE creator_id = :user_id AND event_date >= CURRENT_DATE AND status = 'planned'"
    );
    $statement->execute(['user_id' => $userId]);

    return (int) $statement->fetchColumn();
}

/**
 * Zwraca najblizsze wydarzenie, z ktorym uzytkownik jest powiazany.
 */
function postgres_find_nearest_event_for_user(int $userId): ?array
{
    $statement = postgres_connection()->prepare(
        "SELECT we.*, g.name AS gym_name
         FROM workout_events we
         LEFT JOIN gyms g ON g.id = we.gym_id
         WHERE (we.creator_id = :user_id OR EXISTS (
                SELECT 1 FROM workout_event_participants wep
                WHERE wep.event_id = we.id AND wep.user_id = :user_id
         ))
           AND we.event_date >= CURRENT_DATE
           AND we.status = 'planned'
         ORDER BY we.event_date ASC, we.start_time ASC
         LIMIT 1"
    );
    $statement->execute(['user_id' => $userId]);
    $event = $statement->fetch();

    return $event ?: null;
}

/**
 * Zwraca wydarzenia wspolne dla dwoch uzytkownikow.
 */
function postgres_get_shared_events(int $userId, int $otherUserId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT DISTINCT we.id, we.title, we.event_date, we.start_time
         FROM workout_events we
         JOIN workout_event_participants p1 ON p1.event_id = we.id
         JOIN workout_event_participants p2 ON p2.event_id = we.id
         WHERE p1.user_id = :user_id AND p2.user_id = :other_user_id
         ORDER BY we.event_date ASC, we.start_time ASC
         LIMIT 5"
    );
    $statement->execute(['user_id' => $userId, 'other_user_id' => $otherUserId]);

    return $statement->fetchAll();
}

/**
 * Sprawdza, czy uzytkownik jest uczestnikiem wydarzenia.
 */
function postgres_is_event_participant(int $eventId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        'SELECT 1 FROM workout_event_participants WHERE event_id = :event_id AND user_id = :user_id LIMIT 1'
    );
    $statement->execute(['event_id' => $eventId, 'user_id' => $userId]);

    return (bool) $statement->fetchColumn();
}

/**
 * Tworzy nowy plan treningowy i zwraca jego ID.
 */
function postgres_create_training_plan(int $userId, array $data): int
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO training_plans (user_id, name, description, level, goal, visibility)
         VALUES (:user_id, :name, :description, :level, :goal, :visibility)
         RETURNING id'
    );
    $statement->execute([
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'description' => trim((string) $data['description']) ?: null,
        'level' => trim((string) $data['level']) ?: null,
        'goal' => trim((string) $data['goal']) ?: null,
        'visibility' => $data['visibility'] === 'public' ? 'public' : 'private',
    ]);

    return (int) $statement->fetchColumn();
}

/**
 * Aktualizuje plan treningowy wlasciciela.
 */
function postgres_update_training_plan(int $id, int $userId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE training_plans
         SET name = :name, description = :description, level = :level, goal = :goal, visibility = :visibility
         WHERE id = :id AND user_id = :user_id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => $userId,
        'name' => trim((string) $data['name']),
        'description' => trim((string) $data['description']) ?: null,
        'level' => trim((string) $data['level']) ?: null,
        'goal' => trim((string) $data['goal']) ?: null,
        'visibility' => $data['visibility'] === 'public' ? 'public' : 'private',
    ]);

    return $statement->rowCount() > 0;
}

/**
 * Usuwa plan tylko wtedy, gdy nalezy do zalogowanego uzytkownika.
 */
function postgres_delete_training_plan(int $id, int $userId): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM training_plans WHERE id = :id AND user_id = :user_id');
    $statement->execute(['id' => $id, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Pobiera plan treningowy razem z nazwa autora.
 */
function postgres_find_training_plan(int $id): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT tp.*, u.name AS author_name
         FROM training_plans tp
         JOIN users u ON u.id = tp.user_id
         WHERE tp.id = :id LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $plan = $statement->fetch();

    return $plan ?: null;
}

/**
 * Zwraca wszystkie plany stworzone przez jednego uzytkownika.
 */
function postgres_get_user_training_plans(int $userId): array
{
    $statement = postgres_connection()->prepare(
        'SELECT tp.*, COUNT(tpd.id) AS day_count
         FROM training_plans tp
         LEFT JOIN training_plan_days tpd ON tpd.plan_id = tp.id
         WHERE tp.user_id = :user_id
         GROUP BY tp.id
         ORDER BY tp.created_at DESC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Zwraca wszystkie publiczne plany widoczne dla innych uzytkownikow.
 */
function postgres_get_public_training_plans(): array
{
    $statement = postgres_connection()->query(
        "SELECT tp.*, u.name AS author_name, COUNT(tpd.id) AS day_count
         FROM training_plans tp
         JOIN users u ON u.id = tp.user_id
         LEFT JOIN training_plan_days tpd ON tpd.plan_id = tp.id
         WHERE tp.visibility = 'public'
         GROUP BY tp.id, u.name
         ORDER BY tp.created_at DESC"
    );

    return $statement->fetchAll();
}

/**
 * Zwraca publiczne plany konkretnego autora.
 */
function postgres_get_public_training_plans_by_user(int $userId): array
{
    $statement = postgres_connection()->prepare(
        "SELECT * FROM training_plans
         WHERE user_id = :user_id AND visibility = 'public'
         ORDER BY created_at DESC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Dodaje kolejny dzien do planu, jesli plan nalezy do autora.
 */
function postgres_add_training_plan_day(int $planId, int $userId, array $data): bool
{
    if (!postgres_user_owns_training_plan($planId, $userId)) {
        return false;
    }

    $statement = postgres_connection()->prepare(
        'INSERT INTO training_plan_days (plan_id, name, day_order)
         VALUES (:plan_id, :name, :day_order)'
    );

    return $statement->execute([
        'plan_id' => $planId,
        'name' => trim((string) $data['name']),
        'day_order' => max(1, (int) $data['day_order']),
    ]);
}

/**
 * Dodaje cwiczenie do dnia planu nalezacego do autora.
 */
function postgres_add_exercise_to_training_plan_day(int $dayId, int $userId, array $data): bool
{
    if (!postgres_user_owns_training_plan_day($dayId, $userId)) {
        return false;
    }

    $statement = postgres_connection()->prepare(
        'INSERT INTO training_plan_exercises
         (day_id, exercise_id, sets, reps, rest_seconds, notes, exercise_order)
         VALUES (:day_id, :exercise_id, :sets, :reps, :rest_seconds, :notes, :exercise_order)'
    );

    return $statement->execute([
        'day_id' => $dayId,
        'exercise_id' => (int) $data['exercise_id'],
        'sets' => max(1, (int) $data['sets']),
        'reps' => trim((string) $data['reps']),
        'rest_seconds' => $data['rest_seconds'] !== '' ? (int) $data['rest_seconds'] : null,
        'notes' => trim((string) $data['notes']) ?: null,
        'exercise_order' => max(1, (int) $data['exercise_order']),
    ]);
}

/**
 * Usuwa pojedyncze cwiczenie z planu nalezacego do autora.
 */
function postgres_delete_training_plan_exercise(int $exerciseEntryId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        'DELETE FROM training_plan_exercises tpe
         USING training_plan_days tpd, training_plans tp
         WHERE tpe.id = :id
           AND tpd.id = tpe.day_id
           AND tp.id = tpd.plan_id
           AND tp.user_id = :user_id'
    );
    $statement->execute(['id' => $exerciseEntryId, 'user_id' => $userId]);

    return $statement->rowCount() > 0;
}

/**
 * Pobiera plan, dni i cwiczenia w kolejnosci do wyswietlenia w UI.
 */
function postgres_get_training_plan_with_days_and_exercises(int $planId): ?array
{
    $plan = postgres_find_training_plan($planId);
    if (!$plan) {
        return null;
    }

    $dayStatement = postgres_connection()->prepare(
        'SELECT * FROM training_plan_days WHERE plan_id = :plan_id ORDER BY day_order ASC, id ASC'
    );
    $dayStatement->execute(['plan_id' => $planId]);
    $days = $dayStatement->fetchAll();

    $exerciseStatement = postgres_connection()->prepare(
        'SELECT tpe.*, e.name AS exercise_name, e.muscle_group, e.equipment
         FROM training_plan_exercises tpe
         JOIN exercises e ON e.id = tpe.exercise_id
         WHERE tpe.day_id = :day_id
         ORDER BY tpe.exercise_order ASC, tpe.id ASC'
    );

    foreach ($days as &$day) {
        $exerciseStatement->execute(['day_id' => $day['id']]);
        $day['exercises'] = $exerciseStatement->fetchAll();
    }
    unset($day);

    $plan['days'] = $days;

    return $plan;
}

/**
 * Sprawdza, czy uzytkownik moze wejsc do danego planu.
 */
function postgres_can_access_training_plan(int $planId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        "SELECT 1
         FROM training_plans
         WHERE id = :id AND (user_id = :user_id OR visibility = 'public')
         LIMIT 1"
    );
    $statement->execute(['id' => $planId, 'user_id' => $userId]);

    return (bool) $statement->fetchColumn();
}

/**
 * Liczy wszystkie plany nalezace do jednego uzytkownika.
 */
function postgres_count_training_plans_by_user(int $userId): int
{
    $statement = postgres_connection()->prepare('SELECT COUNT(*) FROM training_plans WHERE user_id = :user_id');
    $statement->execute(['user_id' => $userId]);

    return (int) $statement->fetchColumn();
}

/**
 * Sprawdza, czy plan nalezy do danego uzytkownika.
 */
function postgres_user_owns_training_plan(int $planId, int $userId): bool
{
    $statement = postgres_connection()->prepare('SELECT 1 FROM training_plans WHERE id = :id AND user_id = :user_id LIMIT 1');
    $statement->execute(['id' => $planId, 'user_id' => $userId]);

    return (bool) $statement->fetchColumn();
}

/**
 * Sprawdza, czy dzien planu nalezy do planu autora.
 */
function postgres_user_owns_training_plan_day(int $dayId, int $userId): bool
{
    $statement = postgres_connection()->prepare(
        'SELECT 1
         FROM training_plan_days tpd
         JOIN training_plans tp ON tp.id = tpd.plan_id
         WHERE tpd.id = :day_id AND tp.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute(['day_id' => $dayId, 'user_id' => $userId]);

    return (bool) $statement->fetchColumn();
}

/**
 * Zwraca wszystkich uzytkownikow razem z danymi profilu dla panelu admina.
 */
function postgres_get_all_users(): array
{
    $statement = postgres_connection()->query(
        'SELECT u.*, up.age, up.height_cm, up.weight_kg, up.training_level, up.training_experience, up.goal, up.bio, up.avatar_path, up.onboarding_completed
         FROM users u
         LEFT JOIN user_profiles up ON up.user_id = u.id
         ORDER BY u.created_at DESC, u.id DESC'
    );

    return $statement->fetchAll();
}

function postgres_update_user_account(int $id, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE users
         SET name = :name, email = :email
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $id,
        'name' => trim((string) ($data['name'] ?? '')),
        'email' => mb_strtolower(trim((string) ($data['email'] ?? ''))),
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_user(int $id): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM users WHERE id = :id');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0;
}

function postgres_count_users(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function postgres_count_friendships(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM friendships')->fetchColumn();
}

function postgres_count_gyms(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM gyms')->fetchColumn();
}

function postgres_count_exercises(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM exercises')->fetchColumn();
}

function postgres_count_events(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM workout_events')->fetchColumn();
}

function postgres_count_training_plans(): int
{
    return (int) postgres_connection()->query('SELECT COUNT(*) FROM training_plans')->fetchColumn();
}

function postgres_get_latest_users(int $limit): array
{
    $statement = postgres_connection()->prepare(
        'SELECT u.id, u.name, u.email, u.created_at, up.onboarding_completed
         FROM users u
         LEFT JOIN user_profiles up ON up.user_id = u.id
         ORDER BY u.created_at DESC, u.id DESC
         LIMIT :limit'
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

function postgres_get_pending_friend_requests_global(int $limit): array
{
    $statement = postgres_connection()->prepare(
        "SELECT f.id, f.created_at, requester.name AS requester_name, receiver.name AS receiver_name
         FROM friendships f
         JOIN users requester ON requester.id = f.requester_id
         JOIN users receiver ON receiver.id = f.receiver_id
         WHERE f.status = 'pending'
         ORDER BY f.created_at DESC
         LIMIT :limit"
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

function postgres_get_all_events_admin(): array
{
    $statement = postgres_connection()->query(
        "SELECT we.*, g.name AS gym_name, g.city AS gym_city, u.name AS creator_name,
                COALESCE(COUNT(wep.id), 0) AS participants_count
         FROM workout_events we
         LEFT JOIN gyms g ON g.id = we.gym_id
         JOIN users u ON u.id = we.creator_id
         LEFT JOIN workout_event_participants wep ON wep.event_id = we.id
         GROUP BY we.id, g.name, g.city, u.name
         ORDER BY we.event_date DESC, we.start_time DESC, we.id DESC"
    );

    return $statement->fetchAll();
}

function postgres_update_event_admin(int $id, array $data): bool
{
    $statement = postgres_connection()->prepare(
        "UPDATE workout_events
         SET creator_id = :creator_id,
             gym_id = :gym_id,
             title = :title,
             description = :description,
             event_date = :event_date,
             start_time = :start_time,
             max_participants = :max_participants,
             status = :status
         WHERE id = :id"
    );
    $statement->execute([
        'id' => $id,
        'creator_id' => (int) ($data['creator_id'] ?? 0),
        'gym_id' => ($data['gym_id'] ?? '') !== '' ? (int) $data['gym_id'] : null,
        'title' => trim((string) ($data['title'] ?? '')),
        'description' => trim((string) ($data['description'] ?? '')) ?: null,
        'event_date' => $data['event_date'] ?? null,
        'start_time' => $data['start_time'] ?? null,
        'max_participants' => max(2, (int) ($data['max_participants'] ?? 2)),
        'status' => in_array(($data['status'] ?? 'planned'), ['planned', 'completed', 'cancelled'], true)
            ? $data['status']
            : 'planned',
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_event_admin(int $id): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM workout_events WHERE id = :id');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0;
}

function postgres_get_all_training_plans_admin(): array
{
    $statement = postgres_connection()->query(
        'SELECT tp.*, u.name AS author_name, COUNT(tpd.id) AS day_count
         FROM training_plans tp
         JOIN users u ON u.id = tp.user_id
         LEFT JOIN training_plan_days tpd ON tpd.plan_id = tp.id
         GROUP BY tp.id, u.name
         ORDER BY tp.created_at DESC, tp.id DESC'
    );

    return $statement->fetchAll();
}

function postgres_update_training_plan_admin(int $id, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE training_plans
         SET user_id = :user_id,
             name = :name,
             description = :description,
             level = :level,
             goal = :goal,
             visibility = :visibility
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => (int) ($data['user_id'] ?? 0),
        'name' => trim((string) ($data['name'] ?? '')),
        'description' => trim((string) ($data['description'] ?? '')) ?: null,
        'level' => trim((string) ($data['level'] ?? '')) ?: null,
        'goal' => trim((string) ($data['goal'] ?? '')) ?: null,
        'visibility' => ($data['visibility'] ?? 'private') === 'public' ? 'public' : 'private',
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_training_plan_admin(int $id): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM training_plans WHERE id = :id');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0;
}

function postgres_add_training_plan_day_admin(int $planId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO training_plan_days (plan_id, name, day_order)
         VALUES (:plan_id, :name, :day_order)'
    );

    return $statement->execute([
        'plan_id' => $planId,
        'name' => trim((string) ($data['name'] ?? '')),
        'day_order' => max(1, (int) ($data['day_order'] ?? 1)),
    ]);
}

function postgres_find_training_plan_day(int $dayId): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT tpd.*, tp.user_id, tp.name AS plan_name
         FROM training_plan_days tpd
         JOIN training_plans tp ON tp.id = tpd.plan_id
         WHERE tpd.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $dayId]);
    $day = $statement->fetch();

    return $day ?: null;
}

function postgres_update_training_plan_day_admin(int $dayId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE training_plan_days
         SET name = :name, day_order = :day_order
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $dayId,
        'name' => trim((string) ($data['name'] ?? '')),
        'day_order' => max(1, (int) ($data['day_order'] ?? 1)),
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_training_plan_day_admin(int $dayId): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM training_plan_days WHERE id = :id');
    $statement->execute(['id' => $dayId]);

    return $statement->rowCount() > 0;
}

function postgres_add_exercise_to_training_plan_day_admin(int $dayId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'INSERT INTO training_plan_exercises
         (day_id, exercise_id, sets, reps, rest_seconds, notes, exercise_order)
         VALUES (:day_id, :exercise_id, :sets, :reps, :rest_seconds, :notes, :exercise_order)'
    );

    return $statement->execute([
        'day_id' => $dayId,
        'exercise_id' => (int) ($data['exercise_id'] ?? 0),
        'sets' => max(1, (int) ($data['sets'] ?? 1)),
        'reps' => trim((string) ($data['reps'] ?? '')),
        'rest_seconds' => ($data['rest_seconds'] ?? '') !== '' ? (int) $data['rest_seconds'] : null,
        'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
        'exercise_order' => max(1, (int) ($data['exercise_order'] ?? 1)),
    ]);
}

function postgres_find_training_plan_exercise(int $exerciseEntryId): ?array
{
    $statement = postgres_connection()->prepare(
        'SELECT tpe.*, tpd.plan_id, e.name AS exercise_name
         FROM training_plan_exercises tpe
         JOIN training_plan_days tpd ON tpd.id = tpe.day_id
         JOIN exercises e ON e.id = tpe.exercise_id
         WHERE tpe.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $exerciseEntryId]);
    $exercise = $statement->fetch();

    return $exercise ?: null;
}

function postgres_update_training_plan_exercise_admin(int $exerciseEntryId, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE training_plan_exercises
         SET exercise_id = :exercise_id,
             sets = :sets,
             reps = :reps,
             rest_seconds = :rest_seconds,
             notes = :notes,
             exercise_order = :exercise_order
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $exerciseEntryId,
        'exercise_id' => (int) ($data['exercise_id'] ?? 0),
        'sets' => max(1, (int) ($data['sets'] ?? 1)),
        'reps' => trim((string) ($data['reps'] ?? '')),
        'rest_seconds' => ($data['rest_seconds'] ?? '') !== '' ? (int) $data['rest_seconds'] : null,
        'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
        'exercise_order' => max(1, (int) ($data['exercise_order'] ?? 1)),
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_training_plan_exercise_admin(int $exerciseEntryId): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM training_plan_exercises WHERE id = :id');
    $statement->execute(['id' => $exerciseEntryId]);

    return $statement->rowCount() > 0;
}

function postgres_update_gym_admin(int $id, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE gyms
         SET user_id = :user_id,
             name = :name,
             city = :city,
             address = :address,
             description = :description
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => ($data['user_id'] ?? '') !== '' ? (int) $data['user_id'] : null,
        'name' => trim((string) ($data['name'] ?? '')),
        'city' => trim((string) ($data['city'] ?? '')),
        'address' => trim((string) ($data['address'] ?? '')) ?: null,
        'description' => trim((string) ($data['description'] ?? '')) ?: null,
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_gym_admin(int $id): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM gyms WHERE id = :id');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0;
}

function postgres_update_exercise_admin(int $id, array $data): bool
{
    $statement = postgres_connection()->prepare(
        'UPDATE exercises
         SET user_id = :user_id,
             name = :name,
             muscle_group = :muscle_group,
             equipment = :equipment,
             description = :description
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $id,
        'user_id' => ($data['user_id'] ?? '') !== '' ? (int) $data['user_id'] : null,
        'name' => trim((string) ($data['name'] ?? '')),
        'muscle_group' => trim((string) ($data['muscle_group'] ?? '')),
        'equipment' => trim((string) ($data['equipment'] ?? '')) ?: null,
        'description' => trim((string) ($data['description'] ?? '')) ?: null,
    ]);

    return $statement->rowCount() > 0;
}

function postgres_delete_exercise_admin(int $id): bool
{
    $statement = postgres_connection()->prepare('DELETE FROM exercises WHERE id = :id');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0;
}

<?php

declare(strict_types=1);

use MongoDB\BSON\ObjectId;

/**
 * Tworzy i sprawdza polaczenie z MongoDB.
 */
function mongo_state(): array
{
    static $state = null;

    if (is_array($state)) {
        return $state;
    }

    $uri = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
    $dbName = getenv('MONGODB_DB') ?: 'gymbro_mongo';

    if (!class_exists(MongoDB\Client::class)) {
        $state = [
            'client' => null,
            'database' => null,
            'uri' => $uri,
            'db_name' => $dbName,
            'available' => false,
            'error' => 'Brak biblioteki mongodb/mongodb lub rozszerzenia ext-mongodb.',
        ];

        return $state;
    }

    try {
        $client = new MongoDB\Client($uri);
        $database = $client->selectDatabase($dbName);
        $database->command(['ping' => 1])->toArray();

        $state = [
            'client' => $client,
            'database' => $database,
            'uri' => $uri,
            'db_name' => $dbName,
            'available' => true,
            'error' => null,
        ];
    } catch (Throwable $exception) {
        $state = [
            'client' => null,
            'database' => null,
            'uri' => $uri,
            'db_name' => $dbName,
            'available' => false,
            'error' => $exception->getMessage(),
        ];
    }

    return $state;
}

/**
 * Zwraca klienta MongoDB, jesli polaczenie jest dostepne.
 */
function mongo_client_connection(): ?MongoDB\Client
{
    $state = mongo_state();
    return $state['client'] instanceof MongoDB\Client ? $state['client'] : null;
}

/**
 * Zwraca wybrana baze MongoDB, jesli jest dostepna.
 */
function mongo_database_connection(): ?MongoDB\Database
{
    $state = mongo_state();
    return $state['database'] instanceof MongoDB\Database ? $state['database'] : null;
}

/**
 * Informuje, czy MongoDB jest teraz dostepne dla aplikacji.
 */
function mongo_database_available(): bool
{
    return (bool) (mongo_state()['available'] ?? false);
}

/**
 * Zwraca ostatni blad polaczenia z MongoDB.
 */
function mongo_database_error(): ?string
{
    $error = mongo_state()['error'] ?? null;
    return is_string($error) ? $error : null;
}

/**
 * Pomaga pobrac kolekcje tylko wtedy, gdy MongoDB dziala.
 */
function mongo_collection(string $name): ?MongoDB\Collection
{
    $database = mongo_database_connection();
    return $database ? $database->selectCollection($name) : null;
}

/**
 * Sprawdza bezpiecznie, czy tekst moze byc poprawnym MongoDB ObjectId.
 */
function mongo_is_valid_object_id(string $id): bool
{
    if (!preg_match('/^[a-f0-9]{24}$/i', $id)) {
        return false;
    }

    try {
        new ObjectId($id);
        return true;
    } catch (Throwable) {
        return false;
    }
}

/**
 * Zapisuje wykonany trening jako dokument z cwiczeniami i seriami.
 */
function mongo_create_workout_log(array $data): void
{
    $collection = mongo_collection('workout_logs');
    if (!$collection) {
        return;
    }

    $collection->insertOne($data);
}

/**
 * Zwraca wszystkie logi treningowe jednego uzytkownika.
 */
function mongo_get_workout_logs_by_user(int $userId): array
{
    $collection = mongo_collection('workout_logs');
    if (!$collection) {
        return [];
    }

    return $collection
        ->find(['user_id' => $userId], ['sort' => ['training_date' => -1, 'created_at' => -1]])
        ->toArray();
}

/**
 * Pobiera pojedynczy log treningowy nalezacy do uzytkownika.
 */
function mongo_get_workout_log(string $id, int $userId): ?object
{
    $collection = mongo_collection('workout_logs');
    if (!$collection || !mongo_is_valid_object_id($id)) {
        return null;
    }

    return $collection->findOne([
        '_id' => new ObjectId($id),
        'user_id' => $userId,
    ]);
}

/**
 * Liczy, ile logow treningowych ma dany uzytkownik.
 */
function mongo_count_workout_logs_by_user(int $userId): int
{
    $collection = mongo_collection('workout_logs');
    if (!$collection) {
        return 0;
    }

    return $collection->countDocuments(['user_id' => $userId]);
}

/**
 * Zwraca kilka najnowszych logow treningowych uzytkownika.
 */
function mongo_get_latest_workout_logs_by_user(int $userId, int $limit): array
{
    $collection = mongo_collection('workout_logs');
    if (!$collection) {
        return [];
    }

    return $collection
        ->find(['user_id' => $userId], ['sort' => ['training_date' => -1], 'limit' => $limit])
        ->toArray();
}

/**
 * Zapisuje pojedynczy pomiar progresu.
 */
function mongo_create_progress_measurement(array $data): void
{
    $collection = mongo_collection('progress_measurements');
    if (!$collection) {
        return;
    }

    $collection->insertOne($data);
}

/**
 * Zwraca pomiary progresu jednego uzytkownika z opcjonalnym filtrem typu.
 */
function mongo_get_progress_measurements_by_user(int $userId, ?string $type = null): array
{
    $collection = mongo_collection('progress_measurements');
    if (!$collection) {
        return [];
    }

    $filter = ['user_id' => $userId];
    if ($type) {
        $filter['type'] = $type;
    }

    return $collection->find($filter, ['sort' => ['date' => -1, 'created_at' => -1]])->toArray();
}

/**
 * Zwraca ostatnie pomiary progresu dla dashboardu i widokow list.
 */
function mongo_get_latest_progress_measurements_by_user(int $userId, int $limit): array
{
    $collection = mongo_collection('progress_measurements');
    if (!$collection) {
        return [];
    }

    return $collection
        ->find(['user_id' => $userId], ['sort' => ['date' => -1], 'limit' => $limit])
        ->toArray();
}

/**
 * Buduje proste dane do wykresu progresu.
 */
function mongo_get_progress_chart_data(int $userId, string $type): array
{
    $collection = mongo_collection('progress_measurements');
    if (!$collection) {
        return ['labels' => [], 'values' => []];
    }

    $documents = $collection
        ->find(['user_id' => $userId, 'type' => $type], ['sort' => ['date' => 1]])
        ->toArray();

    $labels = [];
    $values = [];
    foreach ($documents as $document) {
        $labels[] = $document->date;
        $values[] = (float) $document->value;
    }

    return ['labels' => $labels, 'values' => $values];
}

/**
 * Dodaje komentarz i ocene do planu treningowego.
 */
function mongo_add_plan_comment(int $planId, int $userId, string $userName, string $content, int $rating): void
{
    $collection = mongo_collection('plan_comments');
    if (!$collection) {
        return;
    }

    $collection->insertOne([
        'plan_id' => $planId,
        'user_id' => $userId,
        'user_name' => $userName,
        'content' => $content,
        'rating' => max(1, min(5, $rating)),
        'created_at' => now_string(),
    ]);
}

/**
 * Zwraca komentarze zapisane dla danego planu.
 */
function mongo_get_plan_comments(int $planId): array
{
    $collection = mongo_collection('plan_comments');
    if (!$collection) {
        return [];
    }

    return $collection->find(['plan_id' => $planId], ['sort' => ['created_at' => -1]])->toArray();
}

/**
 * Tworzy nowe powiadomienie dla uzytkownika.
 */
function mongo_create_notification(int $userId, string $type, string $title, string $content): void
{
    $collection = mongo_collection('notifications');
    if (!$collection) {
        return;
    }

    $collection->insertOne([
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'content' => $content,
        'is_read' => false,
        'created_at' => now_string(),
    ]);
}

/**
 * Zwraca najnowsze powiadomienia wybranego uzytkownika.
 */
function mongo_get_latest_notifications(int $userId, int $limit): array
{
    $collection = mongo_collection('notifications');
    if (!$collection) {
        return [];
    }

    return $collection
        ->find(['user_id' => $userId], ['sort' => ['created_at' => -1], 'limit' => $limit])
        ->toArray();
}

/**
 * Oznacza jedno powiadomienie jako przeczytane.
 */
function mongo_mark_notification_as_read(string $id, int $userId): bool
{
    $collection = mongo_collection('notifications');
    if (!$collection || !mongo_is_valid_object_id($id)) {
        return false;
    }

    $result = $collection->updateOne(
        ['_id' => new ObjectId($id), 'user_id' => $userId],
        ['$set' => ['is_read' => true]]
    );

    return $result->getModifiedCount() > 0;
}

/**
 * Zapisuje pojedyncza aktywnosc do historii uzytkownika.
 */
function mongo_create_activity_log(int $userId, string $action, array $details): void
{
    $collection = mongo_collection('activity_logs');
    if (!$collection) {
        return;
    }

    $collection->insertOne([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details,
        'created_at' => now_string(),
    ]);
}

/**
 * Zwraca ostatnie aktywnosci konkretnego uzytkownika.
 */
function mongo_get_latest_activity_logs(int $userId, int $limit): array
{
    $collection = mongo_collection('activity_logs');
    if (!$collection) {
        return [];
    }

    return $collection
        ->find(['user_id' => $userId], ['sort' => ['created_at' => -1], 'limit' => $limit])
        ->toArray();
}

/**
 * Zwraca wspolny feed aktywnosci dla kilku uzytkownikow.
 */
function mongo_get_latest_activity_logs_for_users(array $userIds, int $limit): array
{
    $collection = mongo_collection('activity_logs');
    if (!$collection || $userIds === []) {
        return [];
    }

    return $collection
        ->find(['user_id' => ['$in' => array_values($userIds)]], ['sort' => ['created_at' => -1], 'limit' => $limit])
        ->toArray();
}

function mongo_admin_count_documents(string $collectionName, array $filter = []): int
{
    $collection = mongo_collection($collectionName);
    if (!$collection) {
        return 0;
    }

    return $collection->countDocuments($filter);
}

function mongo_admin_list_documents(string $collectionName, array $filter = [], array $options = []): array
{
    $collection = mongo_collection($collectionName);
    if (!$collection) {
        return [];
    }

    return $collection->find($filter, $options)->toArray();
}

function mongo_admin_find_document(string $collectionName, string $id): ?object
{
    $collection = mongo_collection($collectionName);
    if (!$collection || !mongo_is_valid_object_id($id)) {
        return null;
    }

    return $collection->findOne(['_id' => new ObjectId($id)]);
}

function mongo_admin_create_document(string $collectionName, array $data): ?string
{
    $collection = mongo_collection($collectionName);
    if (!$collection) {
        return null;
    }

    $result = $collection->insertOne($data);
    $insertedId = $result->getInsertedId();

    return $insertedId ? (string) $insertedId : null;
}

function mongo_admin_update_document(string $collectionName, string $id, array $data): bool
{
    $collection = mongo_collection($collectionName);
    if (!$collection || !mongo_is_valid_object_id($id)) {
        return false;
    }

    $result = $collection->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $data]
    );

    return $result->getMatchedCount() > 0;
}

function mongo_admin_delete_document(string $collectionName, string $id): bool
{
    $collection = mongo_collection($collectionName);
    if (!$collection || !mongo_is_valid_object_id($id)) {
        return false;
    }

    $result = $collection->deleteOne(['_id' => new ObjectId($id)]);

    return $result->getDeletedCount() > 0;
}

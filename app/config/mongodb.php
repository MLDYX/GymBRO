<?php

declare(strict_types=1);

$uri = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
$dbName = getenv('MONGODB_DB') ?: 'gymbro_mongo';

if (!class_exists(MongoDB\Client::class)) {
    return [
        'client' => null,
        'database' => null,
        'uri' => $uri,
        'db_name' => $dbName,
        'available' => false,
        'error' => 'Brak biblioteki mongodb/mongodb lub rozszerzenia ext-mongodb.',
    ];
}

try {
    $client = new MongoDB\Client($uri);
    $database = $client->selectDatabase($dbName);
    $database->command(['ping' => 1])->toArray();
} catch (Throwable $exception) {
    return [
        'client' => null,
        'database' => null,
        'uri' => $uri,
        'db_name' => $dbName,
        'available' => false,
        'error' => $exception->getMessage(),
    ];
}

return [
    'client' => $client,
    'database' => $database,
    'uri' => $uri,
    'db_name' => $dbName,
    'available' => true,
    'error' => null,
];

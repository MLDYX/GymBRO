<?php

declare(strict_types=1);

function admin_navigation_links(): array
{
    return [
        ['/admin/dashboard.php', 'Dashboard'],
        ['/admin/users', 'Uzytkownicy'],
        ['/admin/gyms', 'Silownie'],
        ['/admin/exercises', 'Cwiczenia'],
        ['/admin/events', 'Wydarzenia'],
        ['/admin/plans', 'Plany'],
        ['/admin/logs', 'Logi'],
        ['/admin/progress', 'Progres'],
        ['/admin/comments', 'Komentarze'],
        ['/admin/notifications', 'Powiadomienia'],
        ['/admin/activity', 'Aktywnosc'],
    ];
}

function admin_pretty_json(mixed $value): string
{
    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
}

function admin_decode_json_array(string $json, string $fieldLabel, array &$errors): array
{
    $json = trim($json);
    if ($json === '') {
        return [];
    }

    try {
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $errors[] = 'Pole "' . $fieldLabel . '" musi zawierac poprawny JSON.';
        return [];
    }

    if (!is_array($decoded)) {
        $errors[] = 'Pole "' . $fieldLabel . '" musi byc tablica JSON.';
        return [];
    }

    return $decoded;
}

function admin_decode_json_object(string $json, string $fieldLabel, array &$errors): array
{
    $decoded = admin_decode_json_array($json, $fieldLabel, $errors);
    if ($decoded !== [] && admin_is_list_array($decoded)) {
        $errors[] = 'Pole "' . $fieldLabel . '" musi byc obiektem JSON.';
        return [];
    }

    return $decoded;
}

function admin_is_list_array(array $value): bool
{
    $expectedKey = 0;
    foreach ($value as $key => $_) {
        if ($key !== $expectedKey) {
            return false;
        }

        $expectedKey++;
    }

    return true;
}

function admin_bool_value(mixed $value): bool
{
    return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
}

function admin_value(object|array|null $source, string $key, mixed $default = ''): mixed
{
    if (is_array($source)) {
        return $source[$key] ?? $default;
    }

    if (is_object($source)) {
        return $source->{$key} ?? $default;
    }

    return $default;
}

function admin_format_datetime_local(?string $value): string
{
    if (!$value) {
        return '';
    }

    $normalized = str_replace(' ', 'T', trim($value));
    return strlen($normalized) >= 16 ? substr($normalized, 0, 16) : $normalized;
}

<?php

declare(strict_types=1);

function validate_required(array $data, array $fields): array
{
    $errors = [];
    foreach ($fields as $field => $label) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            $errors[$field] = 'Pole "' . $label . '" jest wymagane.';
        }
    }

    return $errors;
}

function validate_email_unique(?array $user): ?string
{
    return $user ? 'Uzytkownik o podanym adresie e-mail juz istnieje.' : null;
}

function validate_min_length(string $value, int $length, string $label): ?string
{
    return mb_strlen($value) < $length ? 'Pole "' . $label . "\" musi miec minimum {$length} znakow." : null;
}

function validate_numeric_range(mixed $value, string $label, ?float $min = null, ?float $max = null): ?string
{
    if ($value === '' || $value === null) {
        return null;
    }

    if (!is_numeric($value)) {
        return 'Pole "' . $label . '" musi byc liczba.';
    }

    $numeric = (float) $value;
    if ($min !== null && $numeric < $min) {
        return 'Pole "' . $label . "\" musi byc wieksze lub rowne {$min}.";
    }

    if ($max !== null && $numeric > $max) {
        return 'Pole "' . $label . "\" musi byc mniejsze lub rowne {$max}.";
    }

    return null;
}

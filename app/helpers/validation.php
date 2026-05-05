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

function validate_uploaded_image(array $file, int $maxBytes = 2_500_000): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Nie udalo sie wgrac zdjecia profilowego.';
    }

    if (($file['size'] ?? 0) > $maxBytes) {
        return 'Zdjecie profilowe jest zbyt duze.';
    }

    $allowed = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    $mime = detect_uploaded_image_mime($file);
    if ($mime === null) {
        return 'Nie udalo sie rozpoznac typu pliku zdjecia.';
    }

    if (!in_array($mime, $allowed, true)) {
        return 'Dozwolone sa tylko pliki JPG, PNG i WEBP.';
    }

    return null;
}

function detect_uploaded_image_mime(array $file): ?string
{
    $tmpName = $file['tmp_name'] ?? null;
    if (!$tmpName || !is_file($tmpName)) {
        return null;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $tmpName) ?: null;
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($tmpName);
        if (is_string($mime) && $mime !== '') {
            return $mime;
        }
    }

    if (function_exists('getimagesize')) {
        $imageInfo = @getimagesize($tmpName);
        if (is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])) {
            return $imageInfo['mime'];
        }
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

    return match ($extension) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        default => null,
    };
}

function uploaded_image_extension(array $file): string
{
    return match (detect_uploaded_image_mime($file)) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };
}

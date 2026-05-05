<?php

declare(strict_types=1);

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function render_flashes(): void
{
    foreach (get_flashes() as $flash) {
        $type = e($flash['type'] ?? 'info');
        $message = e($flash['message'] ?? '');
        echo "<div class=\"alert alert-{$type} alert-dismissible fade show shadow-sm\" role=\"alert\">";
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

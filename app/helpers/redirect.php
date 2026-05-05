<?php

declare(strict_types=1);

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function redirect_with_flash(string $path, string $type, string $message): void
{
    set_flash($type, $message);
    redirect($path);
}

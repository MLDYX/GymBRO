<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

logout_user();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
set_flash('success', 'Wylogowano pomyslnie.');
redirect('/index.php');

<?php

declare(strict_types=1);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= e(is_logged_in() ? url('/dashboard.php') : url('/index.php')) ?>">GymBRO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Przelacz nawigacje">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/dashboard.php']) ?>" href="<?= e(url('/dashboard.php')) ?>">Start</a></li>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/logs', '/progress']) ?>" href="<?= e(url('/logs/index.php')) ?>">Treningi</a></li>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/events']) ?>" href="<?= e(url('/events/index.php')) ?>">Spotkania</a></li>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/friendships', '/users']) ?>" href="<?= e(url('/friendships/index.php')) ?>">Znajomi</a></li>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/plans']) ?>" href="<?= e(url('/plans/index.php')) ?>">Plany</a></li>
                    <li class="nav-item"><a class="nav-link <?= nav_active(['/profile.php']) ?>" href="<?= e(url('/profile.php')) ?>">Profil</a></li>
                    <li class="nav-item"><a class="btn btn-primary rounded-pill px-3 ms-lg-2" href="<?= e(url('/logout.php')) ?>">Wyloguj</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('/index.php')) ?>">Start</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('/login.php')) ?>">Logowanie</a></li>
                    <li class="nav-item"><a class="btn btn-primary rounded-pill px-3 ms-lg-2" href="<?= e(url('/register.php')) ?>">Rejestracja</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

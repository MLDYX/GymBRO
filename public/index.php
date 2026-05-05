<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$pageTitle = 'Start';
require_once base_path('app/includes/header.php');
?>
<section class="hero">
    <div class="row align-items-center g-4">
        <div class="col-lg-6">
            <span class="hero-badge mb-3">Dla paczki, która ćwiczy razem</span>
            <h1 class="display-4 fw-bold section-title mb-3">GymBRO pomaga ogarnąć wspólne treningi bez miliona zakładek i chaosu.</h1>
            <p class="lead muted mb-4">Umawiaj wejścia na siłownię, zapisuj swoje treningi i trzymaj plany w jednym prostym miejscu, które faktycznie nadaje się dla znajomych.</p>
            <div class="d-flex flex-wrap gap-3">
                <a href="/login.php" class="btn btn-primary btn-lg px-4">Wejdź do aplikacji</a>
                <a href="/register.php" class="btn btn-outline-primary btn-lg px-4">Załóż konto</a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="hero-card p-4 p-lg-5">
                <p class="section-kicker mb-2">Jak działa GymBRO</p>
                <div class="stack-list">
                    <div class="mini-stat">
                        <div class="fw-semibold mb-1">1. Umawiasz trening</div>
                        <div class="meta-line">Wybierasz miejsce, godzinę i od razu widzisz, kto wpada.</div>
                    </div>
                    <div class="mini-stat">
                        <div class="fw-semibold mb-1">2. Wrzucasz wykonany trening</div>
                        <div class="meta-line">Zapisujesz serie, ciężary i krótki komentarz po treningu.</div>
                    </div>
                    <div class="mini-stat">
                        <div class="fw-semibold mb-1">3. Masz wszystko pod ręką</div>
                        <div class="meta-line">Plany, progres i aktywność znajomych są w jednym miejscu.</div>
                    </div>
                    <div class="stat-card p-4">
                        <p class="muted mb-2">Technicznie</p>
                        <h3 class="mb-0">PostgreSQL pilnuje relacji, a MongoDB trzyma elastyczne wpisy treningowe i aktywność.</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="feature-icon mb-3">A</div>
                <h3 class="h4">Spotkania zamiast chaosu</h3>
                <p class="muted mb-0">Widzisz najbliższe wspólne treningi, wolne miejsca i kto już się zapisał.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="feature-icon mb-3">B</div>
                <h3 class="h4">Treningi bez rozpraszaczy</h3>
                <p class="muted mb-0">Dodajesz wpisy i progres bez przebijania się przez zbędne sekcje techniczne.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 h-100">
                <div class="feature-icon mb-3">C</div>
                <h3 class="h4">Plany dla swojej paczki</h3>
                <p class="muted mb-0">Budujesz plan raz, a potem łatwo go pokazujesz i omawiasz ze znajomymi.</p>
            </div>
        </div>
    </div>
</section>
<?php require_once base_path('app/includes/footer.php'); ?>

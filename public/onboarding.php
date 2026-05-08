<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

if (!is_logged_in()) {
    redirect('/login.php');
}

$profileRepository = new ProfileRepository(pdo());
$progressRepository = new ProgressRepository(mongo_db());
$friendshipRepository = new FriendshipRepository(pdo());
$userRepository = new UserRepository(pdo());
$activityRepository = new ActivityLogRepository(mongo_db());

$step = max(1, min(3, (int) ($_GET['step'] ?? 1)));
$profile = $profileRepository->findByUserId(current_user_id()) ?? [];
$errors = [];
$experienceOptions = ['ponizej roku', '1-2 lata', '3+ lata'];
$levelOptions = ['poczatkujacy', 'sredniozaawansowany', 'zaawansowany'];

if (($profile['onboarding_completed'] ?? false) && $step === 1) {
        redirect('/dashboard.php');
}

if (is_post()) {
    if (request_exceeds_post_limit()) {
        redirect_with_flash(
            '/onboarding.php?step=' . $step,
            'danger',
            'Przeslany formularz jest za duzy. Zmniejsz rozmiar zdjecia profilowego i sproboj ponownie.'
        );
    }

    verify_csrf();
    $postedStep = (int) ($_POST['step'] ?? $step);

    if ($postedStep === 1) {
        $errors = validate_required($_POST, [
            'height_cm' => 'Wzrost',
            'weight_kg' => 'Waga',
            'training_level' => 'Poziom',
            'training_experience' => 'Staz treningowy',
            'goal' => 'Cel',
        ]);

        foreach ([
            'age' => ['Wiek', 0, 120],
            'height_cm' => ['Wzrost', 100, 250],
            'weight_kg' => ['Waga', 30, 400],
        ] as $field => [$label, $min, $max]) {
            if ($message = validate_numeric_range($_POST[$field] ?? null, $label, (float) $min, (float) $max)) {
                $errors[$field] = $message;
            }
        }

        if ($message = validate_uploaded_image($_FILES['avatar'] ?? [])) {
            $errors['avatar'] = $message;
        }

        $avatarPath = $profile['avatar_path'] ?? null;
        if (!$errors && isset($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $extension = uploaded_image_extension($_FILES['avatar']);
            $fileName = 'avatar_' . current_user_id() . '_' . time() . '.' . $extension;
            $target = public_path('uploads/avatars/' . $fileName);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                $avatarPath = '/uploads/avatars/' . $fileName;
            }
        }

        if (!$errors) {
            $profileRepository->update(current_user_id(), [
                'age' => $_POST['age'] ?? '',
                'height_cm' => $_POST['height_cm'] ?? '',
                'weight_kg' => $_POST['weight_kg'] ?? '',
                'training_level' => $_POST['training_level'] ?? '',
                'training_experience' => $_POST['training_experience'] ?? '',
                'goal' => $_POST['goal'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'avatar_path' => $avatarPath ?? '',
            ]);

            $profile = $profileRepository->findByUserId(current_user_id()) ?? [];
            $_POST = [];
            $step = 2;
        }
    } elseif ($postedStep === 2) {
        $measurementDate = trim((string) ($_POST['measurement_date'] ?? '')) ?: date('Y-m-d');
        $strengthMap = [
            'bench_press' => trim((string) ($_POST['bench_press'] ?? '')),
            'squat' => trim((string) ($_POST['squat'] ?? '')),
            'deadlift' => trim((string) ($_POST['deadlift'] ?? '')),
        ];

        foreach ($strengthMap as $type => $value) {
            if ($value === '') {
                continue;
            }

            $progressRepository->create([
                'user_id' => current_user_id(),
                'date' => $measurementDate,
                'type' => $type,
                'value' => (float) $value,
                'unit' => 'kg',
                'note' => 'Wynik startowy z onboardingu.',
                'created_at' => now_string(),
            ]);
        }

        if (array_filter($strengthMap, static fn(string $value): bool => $value !== '')) {
            $activityRepository->create(current_user_id(), 'added_progress_measurement', ['type' => 'onboarding_strength']);
        }

        $_POST = [];
        $step = 3;
    } elseif ($postedStep === 3) {
        $profileRepository->completeOnboarding(current_user_id());
        redirect_with_flash('/dashboard.php', 'success', 'Masz to. Profil startowy jest gotowy.');
    }
}

$discoverUsers = array_values(array_filter(
    $userRepository->allExcept(current_user_id()),
    static fn(array $user): bool => !$friendshipRepository->areFriendsOrPending(current_user_id(), (int) $user['id'])
));

$pageTitle = 'Onboarding';
$pageScripts = $step === 3 ? ['/assets/js/onboarding.js'] : [];
require_once base_path('app/includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card p-4 p-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <p class="section-kicker mb-2">Szybki start</p>
                    <h1 class="h3 mb-1">Dokoncz swoj profil w GymBRO</h1>
                    <p class="muted mb-0">Jeszcze chwila i wejdziesz do aplikacji z gotowym profilem pod wspolne treningi.</p>
                </div>
                <span class="badge text-bg-light">Krok <?= e((string) $step) ?>/3</span>
            </div>

            <?php if ($step === 1): ?>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="1">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Wiek</label>
                            <input type="number" name="age" class="form-control" value="<?= e((string) ($profile['age'] ?? ($_POST['age'] ?? ''))) ?>">
                            <?php if (isset($errors['age'])): ?><small class="text-danger"><?= e($errors['age']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Wzrost (cm)</label>
                            <input type="number" name="height_cm" class="form-control" value="<?= e((string) ($profile['height_cm'] ?? ($_POST['height_cm'] ?? ''))) ?>">
                            <?php if (isset($errors['height_cm'])): ?><small class="text-danger"><?= e($errors['height_cm']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Waga (kg)</label>
                            <input type="number" step="0.01" name="weight_kg" class="form-control" value="<?= e((string) ($profile['weight_kg'] ?? ($_POST['weight_kg'] ?? ''))) ?>">
                            <?php if (isset($errors['weight_kg'])): ?><small class="text-danger"><?= e($errors['weight_kg']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poziom</label>
                            <select name="training_level" class="form-select">
                                <option value="">Wybierz poziom</option>
                                <?php foreach ($levelOptions as $option): ?>
                                    <?php $selected = (($profile['training_level'] ?? ($_POST['training_level'] ?? '')) === $option) ? 'selected' : ''; ?>
                                    <option value="<?= e($option) ?>" <?= $selected ?>><?= e(ucfirst($option)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['training_level'])): ?><small class="text-danger"><?= e($errors['training_level']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Staz treningowy</label>
                            <select name="training_experience" class="form-select">
                                <option value="">Wybierz staz</option>
                                <?php foreach ($experienceOptions as $option): ?>
                                    <?php $selected = (($profile['training_experience'] ?? ($_POST['training_experience'] ?? '')) === $option) ? 'selected' : ''; ?>
                                    <option value="<?= e($option) ?>" <?= $selected ?>><?= e(ucfirst($option)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['training_experience'])): ?><small class="text-danger"><?= e($errors['training_experience']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Cel</label>
                            <input type="text" name="goal" class="form-control" value="<?= e((string) ($profile['goal'] ?? ($_POST['goal'] ?? ''))) ?>">
                            <?php if (isset($errors['goal'])): ?><small class="text-danger"><?= e($errors['goal']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Zdjecie profilowe</label>
                            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="form-control">
                            <small class="text-muted">Opcjonalne. JPG, PNG albo WEBP. Najlepiej do 2.5 MB.</small>
                            <?php if (isset($errors['avatar'])): ?><small class="text-danger d-block"><?= e($errors['avatar']) ?></small><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="4"><?= e((string) ($profile['bio'] ?? ($_POST['bio'] ?? ''))) ?></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-4" type="submit">Dalej</button>
                </form>
            <?php elseif ($step === 2): ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="step" value="2">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Data pomiaru</label>
                            <input type="date" name="measurement_date" class="form-control" value="<?= old('measurement_date', date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="small text-muted">Ten krok jest opcjonalny. Mozesz wpisac tylko to, co chcesz na start.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Wyciskanie (kg)</label>
                            <input type="number" step="0.5" name="bench_press" class="form-control" value="<?= old('bench_press') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Przysiad (kg)</label>
                            <input type="number" step="0.5" name="squat" class="form-control" value="<?= old('squat') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Martwy ciag (kg)</label>
                            <input type="number" step="0.5" name="deadlift" class="form-control" value="<?= old('deadlift') ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Zapisz i dalej</button>
                        <a href="<?= e(url('/onboarding.php?step=3')) ?>" class="btn btn-outline-secondary">Pomin ten krok</a>
                    </div>
                </form>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-7">
                        <h2 class="h4 mb-3">Dodaj kilka osob do swojej paczki</h2>
                        <p class="muted">To opcjonalne. Mozesz wyslac zaproszenia teraz albo wrocic do tego pozniej.</p>
                        <div id="onboarding-friends-feedback" class="mb-3" aria-live="polite"></div>
                        <?php if (!$discoverUsers): ?>
                            <div class="empty-state">Na razie nie ma nikogo nowego do dodania.</div>
                        <?php else: ?>
                            <div class="stack-list" data-onboarding-friends>
                                <?php foreach (array_slice($discoverUsers, 0, 6) as $user): ?>
                                    <div class="friend-card" data-friend-card>
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fw-semibold"><?= e($user['name']) ?></div>
                                                <div class="meta-line"><?= e($user['goal'] ?? 'Cwiczy regularnie') ?></div>
                                            </div>
                                            <form method="post" action="<?= e(url('/friendships/add.php?id=' . (string) $user['id'])) ?>" data-onboarding-add-friend>
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-primary btn-sm" type="submit">Dodaj</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-5">
                        <div class="soft-card p-4">
                            <h2 class="h4 mb-2">Gotowe do wejscia</h2>
                            <p class="muted mb-4">Masz juz podstawowy profil. Mozesz zaczac od dashboardu, a reszte dopracowac pozniej.</p>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="step" value="3">
                                <button class="btn btn-primary w-100" type="submit">Wejdz do GymBRO</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once base_path('app/includes/footer.php'); ?>

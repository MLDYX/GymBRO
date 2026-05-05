<?php

declare(strict_types=1);

class TrainingPlanRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO training_plans (user_id, name, description, level, goal, visibility)
             VALUES (:user_id, :name, :description, :level, :goal, :visibility)
             RETURNING id'
        );
        $statement->execute([
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'description' => trim((string) $data['description']) ?: null,
            'level' => trim((string) $data['level']) ?: null,
            'goal' => trim((string) $data['goal']) ?: null,
            'visibility' => $data['visibility'] === 'public' ? 'public' : 'private',
        ]);

        return (int) $statement->fetchColumn();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE training_plans
             SET name = :name, description = :description, level = :level, goal = :goal, visibility = :visibility
             WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim((string) $data['name']),
            'description' => trim((string) $data['description']) ?: null,
            'level' => trim((string) $data['level']) ?: null,
            'goal' => trim((string) $data['goal']) ?: null,
            'visibility' => $data['visibility'] === 'public' ? 'public' : 'private',
        ]);

        return $statement->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM training_plans WHERE id = :id AND user_id = :user_id');
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT tp.*, u.name AS author_name
             FROM training_plans tp
             JOIN users u ON u.id = tp.user_id
             WHERE tp.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $plan = $statement->fetch();

        return $plan ?: null;
    }

    public function userPlans(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT tp.*, COUNT(tpd.id) AS day_count
             FROM training_plans tp
             LEFT JOIN training_plan_days tpd ON tpd.plan_id = tp.id
             WHERE tp.user_id = :user_id
             GROUP BY tp.id
             ORDER BY tp.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function publicPlans(): array
    {
        $statement = $this->pdo->query(
            "SELECT tp.*, u.name AS author_name, COUNT(tpd.id) AS day_count
             FROM training_plans tp
             JOIN users u ON u.id = tp.user_id
             LEFT JOIN training_plan_days tpd ON tpd.plan_id = tp.id
             WHERE tp.visibility = 'public'
             GROUP BY tp.id, u.name
             ORDER BY tp.created_at DESC"
        );

        return $statement->fetchAll();
    }

    public function publicPlansByUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT * FROM training_plans
             WHERE user_id = :user_id AND visibility = 'public'
             ORDER BY created_at DESC"
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function addDay(int $planId, int $userId, array $data): bool
    {
        if (!$this->ownsPlan($planId, $userId)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO training_plan_days (plan_id, name, day_order)
             VALUES (:plan_id, :name, :day_order)'
        );

        return $statement->execute([
            'plan_id' => $planId,
            'name' => trim((string) $data['name']),
            'day_order' => max(1, (int) $data['day_order']),
        ]);
    }

    public function addExerciseToDay(int $dayId, int $userId, array $data): bool
    {
        if (!$this->ownsDay($dayId, $userId)) {
            return false;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO training_plan_exercises
             (day_id, exercise_id, sets, reps, rest_seconds, notes, exercise_order)
             VALUES (:day_id, :exercise_id, :sets, :reps, :rest_seconds, :notes, :exercise_order)'
        );

        return $statement->execute([
            'day_id' => $dayId,
            'exercise_id' => (int) $data['exercise_id'],
            'sets' => max(1, (int) $data['sets']),
            'reps' => trim((string) $data['reps']),
            'rest_seconds' => $data['rest_seconds'] !== '' ? (int) $data['rest_seconds'] : null,
            'notes' => trim((string) $data['notes']) ?: null,
            'exercise_order' => max(1, (int) $data['exercise_order']),
        ]);
    }

    public function deleteExercise(int $exerciseEntryId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM training_plan_exercises tpe
             USING training_plan_days tpd, training_plans tp
             WHERE tpe.id = :id
               AND tpd.id = tpe.day_id
               AND tp.id = tpd.plan_id
               AND tp.user_id = :user_id'
        );
        $statement->execute(['id' => $exerciseEntryId, 'user_id' => $userId]);

        return $statement->rowCount() > 0;
    }

    public function getPlanWithDaysAndExercises(int $planId): ?array
    {
        $plan = $this->find($planId);
        if (!$plan) {
            return null;
        }

        $dayStatement = $this->pdo->prepare(
            'SELECT * FROM training_plan_days WHERE plan_id = :plan_id ORDER BY day_order ASC, id ASC'
        );
        $dayStatement->execute(['plan_id' => $planId]);
        $days = $dayStatement->fetchAll();

        $exerciseStatement = $this->pdo->prepare(
            'SELECT tpe.*, e.name AS exercise_name, e.muscle_group, e.equipment
             FROM training_plan_exercises tpe
             JOIN exercises e ON e.id = tpe.exercise_id
             WHERE tpe.day_id = :day_id
             ORDER BY tpe.exercise_order ASC, tpe.id ASC'
        );

        foreach ($days as &$day) {
            $exerciseStatement->execute(['day_id' => $day['id']]);
            $day['exercises'] = $exerciseStatement->fetchAll();
        }
        unset($day);

        $plan['days'] = $days;

        return $plan;
    }

    public function canAccess(int $planId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            "SELECT 1
             FROM training_plans
             WHERE id = :id AND (user_id = :user_id OR visibility = 'public')
             LIMIT 1"
        );
        $statement->execute(['id' => $planId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }

    public function countByUser(int $userId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM training_plans WHERE user_id = :user_id');
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    private function ownsPlan(int $planId, int $userId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM training_plans WHERE id = :id AND user_id = :user_id LIMIT 1');
        $statement->execute(['id' => $planId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }

    private function ownsDay(int $dayId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1
             FROM training_plan_days tpd
             JOIN training_plans tp ON tp.id = tpd.plan_id
             WHERE tpd.id = :day_id AND tp.user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['day_id' => $dayId, 'user_id' => $userId]);

        return (bool) $statement->fetchColumn();
    }
}

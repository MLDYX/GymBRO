<?php

declare(strict_types=1);

class TrainingPlanRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function create(int $userId, array $data): int
    {
        return postgres_create_training_plan($userId, $data);
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return postgres_update_training_plan($id, $userId, $data);
    }

    public function delete(int $id, int $userId): bool
    {
        return postgres_delete_training_plan($id, $userId);
    }

    public function find(int $id): ?array
    {
        return postgres_find_training_plan($id);
    }

    public function userPlans(int $userId): array
    {
        return postgres_get_user_training_plans($userId);
    }

    public function publicPlans(): array
    {
        return postgres_get_public_training_plans();
    }

    public function publicPlansByUser(int $userId): array
    {
        return postgres_get_public_training_plans_by_user($userId);
    }

    public function addDay(int $planId, int $userId, array $data): bool
    {
        return postgres_add_training_plan_day($planId, $userId, $data);
    }

    public function addExerciseToDay(int $dayId, int $userId, array $data): bool
    {
        return postgres_add_exercise_to_training_plan_day($dayId, $userId, $data);
    }

    public function deleteExercise(int $exerciseEntryId, int $userId): bool
    {
        return postgres_delete_training_plan_exercise($exerciseEntryId, $userId);
    }

    public function getPlanWithDaysAndExercises(int $planId): ?array
    {
        return postgres_get_training_plan_with_days_and_exercises($planId);
    }

    public function canAccess(int $planId, int $userId): bool
    {
        return postgres_can_access_training_plan($planId, $userId);
    }

    public function countByUser(int $userId): int
    {
        return postgres_count_training_plans_by_user($userId);
    }

    public function allAdmin(): array
    {
        return postgres_get_all_training_plans_admin();
    }

    public function updateAdmin(int $id, array $data): bool
    {
        return postgres_update_training_plan_admin($id, $data);
    }

    public function deleteAdmin(int $id): bool
    {
        return postgres_delete_training_plan_admin($id);
    }

    public function addDayAdmin(int $planId, array $data): bool
    {
        return postgres_add_training_plan_day_admin($planId, $data);
    }

    public function findDay(int $dayId): ?array
    {
        return postgres_find_training_plan_day($dayId);
    }

    public function updateDayAdmin(int $dayId, array $data): bool
    {
        return postgres_update_training_plan_day_admin($dayId, $data);
    }

    public function deleteDayAdmin(int $dayId): bool
    {
        return postgres_delete_training_plan_day_admin($dayId);
    }

    public function addExerciseToDayAdmin(int $dayId, array $data): bool
    {
        return postgres_add_exercise_to_training_plan_day_admin($dayId, $data);
    }

    public function findExerciseEntry(int $exerciseEntryId): ?array
    {
        return postgres_find_training_plan_exercise($exerciseEntryId);
    }

    public function updateExerciseAdmin(int $exerciseEntryId, array $data): bool
    {
        return postgres_update_training_plan_exercise_admin($exerciseEntryId, $data);
    }

    public function deleteExerciseAdmin(int $exerciseEntryId): bool
    {
        return postgres_delete_training_plan_exercise_admin($exerciseEntryId);
    }

    public function countAll(): int
    {
        return postgres_count_training_plans();
    }
}

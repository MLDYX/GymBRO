<?php

declare(strict_types=1);

class CommentRepository
{
    public function __construct(private mixed $connection = null)
    {
    }

    public function addPlanComment(int $planId, int $userId, string $userName, string $content, int $rating): void
    {
        mongo_add_plan_comment($planId, $userId, $userName, $content, $rating);
    }

    public function getPlanComments(int $planId): array
    {
        return mongo_get_plan_comments($planId);
    }

    public function all(int $limit = 0): array
    {
        $options = ['sort' => ['created_at' => -1]];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }

        return mongo_admin_list_documents('plan_comments', [], $options);
    }

    public function findAdmin(string $id): ?object
    {
        return mongo_admin_find_document('plan_comments', $id);
    }

    public function createAdmin(array $data): ?string
    {
        return mongo_admin_create_document('plan_comments', $data);
    }

    public function updateAdmin(string $id, array $data): bool
    {
        return mongo_admin_update_document('plan_comments', $id, $data);
    }

    public function deleteAdmin(string $id): bool
    {
        return mongo_admin_delete_document('plan_comments', $id);
    }

    public function countAll(): int
    {
        return mongo_admin_count_documents('plan_comments');
    }
}

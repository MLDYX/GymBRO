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
}

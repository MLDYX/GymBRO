<?php

declare(strict_types=1);

class CommentRepository
{
    private ?MongoDB\Collection $collection = null;

    public function __construct(?MongoDB\Database $database)
    {
        if ($database) {
            $this->collection = $database->selectCollection('plan_comments');
        }
    }

    public function addPlanComment(int $planId, int $userId, string $userName, string $content, int $rating): void
    {
        if (!$this->collection) {
            return;
        }
        $this->collection->insertOne([
            'plan_id' => $planId,
            'user_id' => $userId,
            'user_name' => $userName,
            'content' => $content,
            'rating' => max(1, min(5, $rating)),
            'created_at' => now_string(),
        ]);
    }

    public function getPlanComments(int $planId): array
    {
        if (!$this->collection) {
            return [];
        }
        return $this->collection->find(['plan_id' => $planId], ['sort' => ['created_at' => -1]])->toArray();
    }
}

<?php

declare(strict_types=1);

class ProgressRepository
{
    private ?MongoDB\Collection $collection = null;

    public function __construct(?MongoDB\Database $database)
    {
        if ($database) {
            $this->collection = $database->selectCollection('progress_measurements');
        }
    }

    public function create(array $data): void
    {
        if (!$this->collection) {
            return;
        }
        $this->collection->insertOne($data);
    }

    public function findByUser(int $userId, ?string $type = null): array
    {
        if (!$this->collection) {
            return [];
        }
        $filter = ['user_id' => $userId];
        if ($type) {
            $filter['type'] = $type;
        }

        return $this->collection->find($filter, ['sort' => ['date' => -1, 'created_at' => -1]])->toArray();
    }

    public function latestByUser(int $userId, int $limit): array
    {
        if (!$this->collection) {
            return [];
        }
        return $this->collection
            ->find(['user_id' => $userId], ['sort' => ['date' => -1], 'limit' => $limit])
            ->toArray();
    }

    public function chartData(int $userId, string $type): array
    {
        if (!$this->collection) {
            return ['labels' => [], 'values' => []];
        }
        $documents = $this->collection
            ->find(['user_id' => $userId, 'type' => $type], ['sort' => ['date' => 1]])
            ->toArray();

        $labels = [];
        $values = [];
        foreach ($documents as $document) {
            $labels[] = $document->date;
            $values[] = (float) $document->value;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}

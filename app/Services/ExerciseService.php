<?php

namespace App\Services;

use App\Models\ExerciseModel;
use App\Models\ExerciseTagLinkModel;
use App\Models\ExerciseTagModel;
use CodeIgniter\I18n\Time;

class ExerciseService
{
    protected ExerciseModel $exercises;
    protected ExerciseTagModel $tags;
    protected ExerciseTagLinkModel $links;

    public function __construct()
    {
        $this->exercises = new ExerciseModel();
        $this->tags = new ExerciseTagModel();
        $this->links = new ExerciseTagLinkModel();
    }

    public function list(array $filters = [], int $perPage = 15, string $group = 'exercises'): array
    {
        $model = $this->exercises->where('deleted_at', null);

        if (!empty($filters['search'])) {
            $model = $model->groupStart()
                ->like('title', $filters['search'])
                ->orLike('description', $filters['search'])
                ->groupEnd();
        }
        if (!empty($filters['objective'])) {
            $model = $model->where('objective', $filters['objective']);
        }
        if (!empty($filters['age_group'])) {
            $model = $model->where('age_group', $filters['age_group']);
        }
        if (!empty($filters['intensity'])) {
            $model = $model->where('intensity', $filters['intensity']);
        }
        if (!empty($filters['status'])) {
            $model = $model->where('status', $filters['status']);
        }
        if (!empty($filters['tag'])) {
            $model = $model
                ->join('exercise_tag_links', 'exercise_tag_links.exercise_id = exercises.id', 'left')
                ->join('exercise_tags', 'exercise_tags.id = exercise_tag_links.tag_id', 'left')
                ->where('exercise_tags.name', $filters['tag']);
        }

        $items = $model->orderBy('id', 'DESC')->paginate($perPage, $group);
        return ['items' => $items, 'pager' => $model->pager];
    }

    public function find(int $id): ?array
    {
        return $this->exercises->find($id) ?: null;
    }

    public function findWithTags(int $id): ?array
    {
        $exercise = $this->find($id);
        if (!$exercise) {
            return null;
        }

        $exercise['tags'] = $this->tagsForExercise($id);
        return $exercise;
    }

    public function create(array $data, int $userId): int
    {
        $payload = [
            'title' => $data['title'],
            'objective' => $data['objective'] ?? null,
            'description' => $data['description'] ?? null,
            'age_group' => $data['age_group'] ?? 'all',
            'intensity' => $data['intensity'] ?? 'medium',
            'duration_min' => $this->nullableInt($data['duration_min'] ?? null),
            'players_min' => $this->nullableInt($data['players_min'] ?? null),
            'players_max' => $this->nullableInt($data['players_max'] ?? null),
            'materials' => $data['materials'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_by' => $userId,
            'created_at' => Time::now()->toDateTimeString(),
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $exerciseId = (int) $this->exercises->insert($payload);
        $this->syncTags($exerciseId, (string) ($data['tags'] ?? ''));
        return $exerciseId;
    }

    public function update(int $id, array $data): bool
    {
        $payload = [
            'title' => $data['title'],
            'objective' => $data['objective'] ?? null,
            'description' => $data['description'] ?? null,
            'age_group' => $data['age_group'] ?? 'all',
            'intensity' => $data['intensity'] ?? 'medium',
            'duration_min' => $this->nullableInt($data['duration_min'] ?? null),
            'players_min' => $this->nullableInt($data['players_min'] ?? null),
            'players_max' => $this->nullableInt($data['players_max'] ?? null),
            'materials' => $data['materials'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'status' => $data['status'] ?? 'active',
            'updated_at' => Time::now()->toDateTimeString(),
        ];

        $updated = $this->exercises->update($id, $payload);
        $this->syncTags($id, (string) ($data['tags'] ?? ''));
        return $updated;
    }

    public function delete(int $id): bool
    {
        return $this->exercises->delete($id);
    }

    public function listTags(): array
    {
        return $this->tags->orderBy('name', 'ASC')->findAll();
    }

    public function createTag(string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            return 0;
        }

        $existing = $this->tags->where('name', $name)->first();
        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $this->tags->insert([
            'name' => $name,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function tagsForExercise(int $exerciseId): array
    {
        return $this->tags
            ->select('exercise_tags.*')
            ->join('exercise_tag_links', 'exercise_tag_links.tag_id = exercise_tags.id', 'inner')
            ->where('exercise_tag_links.exercise_id', $exerciseId)
            ->orderBy('exercise_tags.name', 'ASC')
            ->findAll();
    }

    public function syncTags(int $exerciseId, string $tagsCsv): void
    {
        $this->links->where('exercise_id', $exerciseId)->delete();

        $tags = array_filter(array_map('trim', explode(',', $tagsCsv)));
        foreach ($tags as $tagName) {
            $tagId = $this->createTag($tagName);
            if ($tagId <= 0) {
                continue;
            }
            $this->links->insert([
                'exercise_id' => $exerciseId,
                'tag_id' => $tagId,
            ]);
        }
    }

    protected function nullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }
}

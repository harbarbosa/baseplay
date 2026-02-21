<?php

namespace App\Services;

use App\Models\TacticalBoardTemplateModel;
use App\Services\TacticalBoardService;
use App\Services\TacticalBoardStateService;
use CodeIgniter\I18n\Time;

class TacticalBoardTemplateService
{
    protected TacticalBoardTemplateModel $templates;
    protected TacticalBoardService $boards;
    protected TacticalBoardStateService $states;

    public function __construct()
    {
        $this->templates = new TacticalBoardTemplateModel();
        $this->boards = new TacticalBoardService();
        $this->states = new TacticalBoardStateService();
    }

    public function listTemplates(array $filters = [], bool $includeInactive = false): array
    {
        $model = $this->templates;

        if (!$includeInactive) {
            $model = $model->where('is_active', 1);
        }

        if (!empty($filters['field_type'])) {
            $model = $model->where('field_type', $filters['field_type']);
        }

        if (!empty($filters['tag'])) {
            $tag = trim((string) $filters['tag']);
            if ($tag !== '') {
                $model = $model->like('tags', $tag);
            }
        }

        return $model->orderBy('is_default', 'DESC')->orderBy('title')->findAll();
    }

    public function listActiveTemplates(array $filters = []): array
    {
        return $this->listTemplates($filters, false);
    }

    public function getTemplate(int $id, bool $onlyActive = true): ?array
    {
        if ($onlyActive) {
            return $this->templates->where('is_active', 1)->find($id);
        }

        return $this->templates->find($id);
    }

    public function createBoardFromTemplate(int $templateId, array $boardData, int $userId): int
    {
        $template = $this->getTemplate($templateId);
        if (!$template) {
            return 0;
        }

        $decoded = json_decode((string) $template['template_json'], true);
        if (!is_array($decoded)) {
            return 0;
        }

        $boardId = $this->boards->create($boardData, $userId);
        if ($boardId <= 0) {
            return 0;
        }

        $stateJson = json_encode($decoded, JSON_UNESCAPED_UNICODE);
        $this->states->saveNewVersion($boardId, $stateJson, $userId);

        return $boardId;
    }

    public function seedTemplates(array $items): void
    {
        $now = Time::now()->toDateTimeString();
        foreach ($items as $item) {
            $exists = $this->templates->where('title', $item['title'])->first();
            $payload = [
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'field_type' => $item['field_type'] ?? 'full',
                'tags' => $item['tags'] ?? null,
                'preview_image' => $item['preview_image'] ?? null,
                'template_json' => $item['template_json'],
                'updated_at' => $now,
            ];

            if ($exists) {
                if (empty($exists['created_by']) && (int) ($exists['is_default'] ?? 0) === 1) {
                    $this->templates->update((int) $exists['id'], $payload);
                }
                continue;
            }

            $payload['is_default'] = 1;
            $payload['is_active'] = 1;
            $payload['created_by'] = null;
            $payload['created_at'] = $now;

            $this->templates->insert($payload);
        }
    }
}
